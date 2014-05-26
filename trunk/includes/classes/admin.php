<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /includes/classes/admin.php
//
namespace core\classes;
class admin {
	public $id;
	public $text;
	public $description;
	public $sort_order  	= 99;
	public $notes 			= array();// placeholder for any operational notes
	public $prerequisites 	= array();// modules required and rev level for this module to work properly
	public $keys			= array();// Load configuration constants for this module, must match entries in admin tabs
	public $dirlist			= array();// add new directories to store images and data
	public $tables			= array();// Load tables
	public $dashboards		= array();// holds all classes in a array
	public $methods			= array();// holds all classes in a array
	public $status			= 1.0; // stores the moduel status
	public $version			= 1.0; // stores availible version of the module
	public $installed		= false;
	public $core			= false;

	/**
	 * this is the general construct function called when the class is created.
	 */
	function __construct(){
		if (defined('MODULE_' . strtoupper($this->id) . '_STATUS')){
			$this->installed = true;
			$this->status  = constant('MODULE_' . strtoupper($this->id) . '_STATUS');
		}
		$this->methods 		= $this->return_all_methods('methods');
		$this->dashboards 	= $this->return_all_methods('dashboards');
	}

	/**
	 * this will install a module
	 * @param bool $demo
	 * @param string $path_my_files location to the my_files folder
	 */

	function install($path_my_files, $demo = false) {
		$this->check_prerequisites_versions();
		$this->install_dirs($path_my_files);
		$this->install_update_tables();
		foreach ($this->keys as $key => $value) write_configure($key, $value);
  		if ($demo) $this->load_demo(); // load demo data
  		$this->load_reports();
  		admin_add_reports($this->id);
  		$this->after_install();
		foreach ($this->methods as $method) {
	  		write_configure('MODULE_' . strtoupper($this->id) . '_' . strtoupper($method->id) . '_STATUS', $method->version);
	  		foreach ($method->key as $key) write_configure($key['key'], $key['default']);
	  		if (method_exists($method, 'install')) $method->install();
		}
		foreach ($this->dashboards as $dashboard) {
	    	foreach ($dashboard->key() as $key) write_configure($key['key'], $key['default']);
	    	if (method_exists($dashboard, 'install')) $dashboard->install();
		}
		$this->installed = true;
		$this->status 	 = $this->version;
	}

	/**
	 * this function will be called after you log in.
	 */

  	function initialize() {
  	}

  	/**
  	 * this function will be called when a module is upgraded.
  	 * it will update tables directories and keys
  	 */

	function upgrade() {
		$this->check_prerequisites_versions();
		$this->install_dirs($path_my_files);
		$this->install_update_tables();
		foreach ($this->keys as $key => $value) if(!defined($key)) write_configure($key, $value);
		foreach ($this->methods as $method) {
			if ($method->installed && $method->should_update()){
	    		foreach ($method->key() as $key) if(!defined($key['key'])) write_configure($key['key'], $key['default']);
				if (method_exists($method, 'upgrade')) $method->upgrade();
				write_configure('MODULE_' . strtoupper($this->id) . '_' . strtoupper($method->id) . '_STATUS', $method->version);
				gen_add_audit_log(sprintf(TEXT_MODULE_ARGS, $method->text) . TEXT_UPDATE, $method->version);
	   			$messageStack->add(sprintf(GEN_MODULE_UPDATE_SUCCESS, $method->id, $method->version), 'success');
			}
		}
		$this->status = $this->version;
	}

	function delete($path_my_files) {
		if ($this->core) throw new \core\classes\userException("can not delete core module " .$this->text);
		foreach ($this->methods as $method) {
			if ($method->installed){
	    		if (method_exists($method, 'delete')) $method->delete();
			}
	  	}
		foreach ($this->dashboards as $dashboard) {
	    	$dashboard->delete();
		}
	    $this->remove_tables();
	    $this->remove_dirs($path_my_files);
	    remove_configure('MODULE_' . strtoupper($this->id) . '_STATUS');
	    $this->installed = false;
	}

  	function release_update($version, $path = '') {
    	global $db;
		if (file_exists($path)) { include_once ($path); }
		write_configure('MODULE_' . strtoupper($this->id) . '_STATUS', $version);
		return $version;
  	}

	function load_reports() {
	}

	function load_demo() {
	}

	function should_update(){
		if (!$this->installed) return false;
		if (version_compare($this->version, constant('MODULE_' . strtoupper($this->id) . '_STATUS')) < 0 ) return true;
		else return false;
	}

	/**
	 * This function checks if a module is allowed to install using the prerequisites
	 * @throws \core\classes\userException
	 */

	function check_prerequisites_versions() {
		global $admin_classes;
		if (is_array($this->prerequisites) && sizeof($this->prerequisites) > 0) {
			foreach ($this->prerequisites as $module_class => $RequiredVersion) {
		  		if ( $admin_classes[$module_class]->installed == false) throw new \core\classes\userException (sprintf(ERROR_MODULE_NOT_INSTALLED, $this->id, $admin_classes[$module_class]->id));
		  		if ( version_compare($admin_classes[$module_class]->version, $RequiredVersion) < 0 ) throw new \core\classes\userException (sprintf(ERROR_MODULE_VERSION_TOO_LOW, $this->id, $admin_classes[$module_class]->id, $RequiredVersion, $this->version));
			}
		}
		return true;
	}

	/**
	 * this function installes the required dirs under my_files\mycompany
	 * @throws \core\classes\userException
	 */

	function install_dirs($path_my_files) {
		foreach ($this->dirlist as $dir) {
			validate_path($path_my_files . $dir, 0755);
	  	}
	}

	function remove_dirs($path_my_files) {
		foreach(array_reverse($this->dirlist) as $dir) {
			if (!@rmdir($path_my_files . $dir)) throw new \core\classes\userException (sprintf(ERROR_CANNOT_REMOVE_MODULE_DIR, $path_my_files . $dir));
	  	}
	}

	/**
	 * This funtion installs the tables.
	 * If table exists nothing will happen.
	 * @throws \core\classes\userException
	 */
	function install_update_tables() {
	  	global $db;
	  	foreach ($this->tables as $table => $create_table_sql) {
	    	if (!db_table_exists($table)) {
		  		if (!$db->Execute($create_table_sql)) throw new \core\classes\userException (sprintf("Error installing table: %s", $table));
			}
	  	}
	}

	function remove_tables() {
	  	global $db;
	  	foreach ($this->tables as $table) {
			if (db_table_exists($table)){
				if ($db->Execute('DROP TABLE ' . $table)) throw new \core\classes\userException (sprintf("Error deleting table: %s", $table));
			}
	  	}
	}

	function add_report_heading($doc_title, $doc_group) {
	  	global $db;
	  	$result = $db->Execute("select id from ".TABLE_PHREEFORM." where doc_group = '$doc_group'");
	  	if ($result->RecordCount() < 1) {
	    	$db->Execute("INSERT INTO ".TABLE_PHREEFORM." (parent_id, doc_type, doc_title, doc_group, doc_ext, security, create_date) VALUES
	      	  (0, '0', '" . $doc_title . "', '".$doc_group."', '0', 'u:0;g:0', now())");
	    	return db_insert_id();
	  	} else {
	    	return $result->fields['id'];
	  	}
	}

	function add_report_folder($parent_id, $doc_title, $doc_group, $doc_ext) {
	  	global $db;
	  	if ($parent_id == '') throw new \core\classes\userException("parent_id isn't set for document $doc_title");
	  	$result = $db->Execute("select id from ".TABLE_PHREEFORM." where doc_group = '$doc_group' and doc_ext = '$doc_ext'");
	  	if ($result->RecordCount() < 1) {
	    	$db->Execute("INSERT INTO ".TABLE_PHREEFORM." (parent_id, doc_type, doc_title, doc_group, doc_ext, security, create_date) VALUES
	      	  (".$parent_id.", '0', '" . $doc_title . "', '".$doc_group."', '".$doc_ext."', 'u:0;g:0', now())");
	  	}
	}

	/**
	 * this loads all methods/dashboards that are in a modules sub folder
	 * @param string $type
	 * @return multitype:|multitype:unknown
	 */
	function return_all_methods($type ='methods') {
	    $choices     = array();
	    $method_dir  = DIR_FS_MODULES . "$this->id/$type/";
	    if (!is_dir($method_dir)) return $choices;
	    $methods = @scandir($method_dir);
	    if($methods === false) throw new \core\classes\userException("couldn't read or find directory $method_dir");
	    foreach ($methods as $method) {
			if ($method == '.' || $method == '..' || !is_dir($method_dir . $method)) continue;
		  	load_method_language($method_dir, $method);
		  	$class = "\\$this->id\\$type\\$method\\$method";
		  	$choices[$method] = new $class;
	    }
		uasort($choices, "arange_object_by_sort_order");
	    return $choices;
	}

	final function phreedom_main_validateLogin(){
		global $db, $admin_classes;
  		// Errors will happen here if there was a problem logging in, logout and restart
 	 	if (!is_object($db)) throw new \core\classes\userException("Database isn't created", "phreedom", "main", "template_login");
	    $admin_name     = db_prepare_input($_POST['admin_name']);
	    $admin_pass     = db_prepare_input($_POST['admin_pass']);
	    $_SESSION['company']	= db_prepare_input($_POST['company']);
	    $_SESSION['language']	= db_prepare_input($_POST['language']);
	    $sql = "select admin_id, admin_name, inactive, display_name, admin_email, admin_pass, account_id, admin_prefs, admin_security
		  from " . TABLE_USERS . " where admin_name = '" . db_input($admin_name) . "'";
	    if ($db->db_connected) $result = $db->Execute($sql);
		if (!$result || $admin_name <> $result->fields['admin_name'] || $result->fields['inactive']) throw new \core\classes\userException(sprintf(GEN_LOG_LOGIN_FAILED, ERROR_WRONG_LOGIN, $admin_name),  "phreedom", "main", 'template_login');
		\core\classes\encryption::validate_password($admin_pass, $result->fields['admin_pass']);
		$_SESSION['admin_id']       = $result->fields['admin_id'];
		$_SESSION['display_name']   = $result->fields['display_name'];
		$_SESSION['admin_email']    = $result->fields['admin_email'];
		$_SESSION['admin_prefs']    = unserialize($result->fields['admin_prefs']);
		$_SESSION['account_id']     = $result->fields['account_id'];
		$_SESSION['admin_security'] = \core\classes\user::parse_permissions($result->fields['admin_security']);
		// set some cookies for the next visit to remember the company, language, and theme
		$cookie_exp = 2592000 + time(); // one month
		setcookie('pb_company' , $_SESSION['company'],  $cookie_exp);
		setcookie('pb_language', $_SESSION['language'], $cookie_exp);
		// load init functions for each module and execute
		foreach ($admin_classes as $key => $module_class) {
		  	if ($module_class->should_update()) $module_class->upgrade();
		}
	  	foreach ($admin_classes as $key => $module_class){
	  		if ($module_class->installed === true) $module_class->initialize();
	  	}
		if (defined('TABLE_CONTACTS')) {
		    $dept = $db->Execute("select dept_rep_id from " . TABLE_CONTACTS . " where id = " . $result->fields['account_id']);
		    $_SESSION['department'] = $dept->fields['dept_rep_id'];
		}
		gen_add_audit_log(TEXT_USER_LOGIN ." -->" . $admin_name);
		// check for session timeout to reload to requested page
		$get_params = '';
		if (isset($_SESSION['pb_module']) && $_SESSION['pb_module']) {
			$get_params  = 'module='    . $_SESSION['pb_module'];
		    if (isset($_SESSION['pb_page']) && $_SESSION['pb_page']) $get_params .= '&amp;page=' . $_SESSION['pb_page'];
		    if (isset($_SESSION['pb_jID'])  && $_SESSION['pb_jID'])  $get_params .= '&amp;jID='  . $_SESSION['pb_jID'];
		    if (isset($_SESSION['pb_type']) && $_SESSION['pb_type']) $get_params .= '&amp;type=' . $_SESSION['pb_type'];
		    if (isset($_SESSION['pb_list']) && $_SESSION['pb_list']) $get_params .= '&amp;list=' . $_SESSION['pb_list'];
		    unset($_SESSION['pb_module']);
  			unset($_SESSION['pb_page']);
  			unset($_SESSION['pb_jID']);
  			unset($_SESSION['pb_type']);
  			unset($_SESSION['pb_list']);
		    gen_redirect(html_href_link(FILENAME_DEFAULT, $get_params, 'SSL'));
		}
		// check safe mode is allowed to log in.
		if (get_cfg_var('safe_mode')) throw new \core\classes\userException(SAFE_MODE_ERROR); //@todo is this removed asof php 5.3??
	}
}
?>