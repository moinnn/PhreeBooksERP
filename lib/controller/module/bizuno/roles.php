<?php
/*
 * Handles user Roles
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2017-08-17
 * @filesource /lib/controller/module/bizuno/roles.php
 */

namespace bizuno;

class bizunoRoles
{
	public $moduleID = 'bizuno';

	function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }
    
	/**
     * Roles manager main entry point
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function manager(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'roles', 1)) { return; }
		$title = lang('roles');
		$layout = array_replace_recursive($layout, viewMain(), [
            'pageTitle' => $title,
			'divs' => [
                'heading'=> ['order'=>30, 'type'=>'html',     'html'=>"<h1>$title</h1>\n"],
				'roles'  => ['order'=>60, 'type'=>'accordion','key'=>'accRoles']],
			'accordion' => ['accRoles'=>  ['divs'=>  [
                'divRolesManager'=> ['order'=>30,'label'=>lang('manager'),'type'=>'datagrid','key'=>'dgRoles'],
				'divRolesDetail' => ['order'=>70,'label'=>lang('details'),'type'=>'html','html'=>'&nbsp;']]]],
			'datagrid' => ['dgRoles' => $this->dgRoles('dgRoles', $security)]]);
	}

	/**
     * List roles available filtered per user request
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function managerRows(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'roles', 1)) { return; }
		$structure = $this->dgRoles('dgRoles', $security);
		$data = ['type'=>'datagrid', 'structure'=>$structure];
        $layout = array_replace_recursive($layout, $data);
	}

    /**
     * Saves the user preferences for the roles datagrid in a session array
     */
    private function managerSettings()
    {
		$data = ['path'=>'bizunoRoles', 'values' => [
            ['index'=>'rows',  'clean'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')],
            ['index'=>'page',  'clean'=>'integer','default'=>'1'],
            ['index'=>'sort',  'clean'=>'text',   'default'=>BIZUNO_DB_PREFIX."roles.title"],
            ['index'=>'order', 'clean'=>'text',   'default'=>'ASC'],
            ['index'=>'search','clean'=>'text',   'default'=>'']]];
		$this->defaults = updateSelection($data);
	}

	/**
     * Datagrid structure for roles manager
     * @param string $name - DOM id of the datagrid
     * @param integer $security - security setting for the user
     * @return array - datagrid structure
     */
    private function dgRoles($name, $security=0)
    {
        $this->managerSettings();
        return [
            'id'     => $name,
			'rows'   => $this->defaults['rows'],
			'page'   => $this->defaults['page'],
			'events' => [
                'rowStyler'    => "function(index, row) { if (row.inactive==1) { return {class:'row-inactive'}; }}",
				'onDblClickRow'=> "function(rowIndex, rowData){ accordionEdit('accRoles', 'dgRoles', 'divRolesDetail', '".lang('details')."', 'bizuno/roles/edit', rowData.id); }"],
			'attr'   => ['type' =>'table',
				'url'         => BIZUNO_AJAX."&p=bizuno/roles/managerRows",
				'toolbar'     => '#'.$name.'Toolbar',
				'pageSize'    => getModuleCache('bizuno', 'settings', 'general', 'max_rows'),
				'idField'     => 'id'],
			'source' => [
                'tables' => ['roles'=>['table'=>BIZUNO_DB_PREFIX."roles"]],
				'actions' => [
                    'newRole'  => ['order'=>10, 'html'=>['icon'=>'new',  'events'=>['onClick'=>"accordionEdit('accRoles', 'dgRoles', 'divRolesDetail', '".lang('details')."', 'bizuno/roles/edit', 0);"]]],
					'clrSearch'=> ['order'=>50, 'html'=>['icon'=>'clear','events'=>['onClick'=>"jq('#search').val(''); ".$name."Reload();"]]]],
				'search' => [BIZUNO_DB_PREFIX."roles".'.title'],
				'sort'   => ['s0'=>  ['order'=>10, 'field'=>($this->defaults['sort'].' '.$this->defaults['order'])]],
				'filters'=> ['search' => ['order'=>'90', 'html'=>['attr'=>['value'=>$this->defaults['search']]]]]],
			'columns' => [
                'id'      => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX."roles.id",      'attr'=>['hidden'=>true]],
				'inactive'=> ['order'=>0, 'field'=>BIZUNO_DB_PREFIX."roles.inactive",'attr'=>['hidden'=>true]],
				'action'  => ['order'=>1, 'label'=>lang('action'), 'attr'=>['width'=>150], 'events'=>['formatter'=>$name.'Formatter'],
					'actions'=> [
						'edit' => ['icon'=>'edit', 'size'=>'small', 'order'=>20,
							'events'=>  ['onClick'=>"accordionEdit('accRoles', 'dgRoles', 'divRolesDetail', '".lang('details')."', 'bizuno/roles/edit', idTBD);"]],
						'copy' => ['icon'=>'copy', 'size'=>'small', 'order'=>40,
							'events'=> ['onClick' => "var title=prompt('".lang('msg_copy_name_prompt')."'); jsonAction('bizuno/roles/copy', idTBD, title);"]],
						'delete' => ['icon'=>'trash', 'size'=>'small', 'order'=>90, 'hidden'=>$security>3?false:true,
							'events'=> ['onClick' => "if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('bizuno/roles/delete', idTBD);"]]]],
				'title'=> ['order'=>10, 'field'=>BIZUNO_DB_PREFIX."roles.title", 'label'=>pullTableLabel(BIZUNO_DB_PREFIX."roles", 'title'),
					'attr' => ['width'=>100, 'sortable'=>true, 'resizable'=>true]]]];
	}

	/**
     * Structure to handle editing roles
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function edit(&$layout=[])
    {
        require_once(BIZUNO_LIB."controller/module/bizuno/functions.php");
        if (!$security = validateSecurity('bizuno', 'roles', 3)) { return; }
		$rID = clean('rID', 'integer', 'get');
		$securityChoices = [
            ['id'=>'-1','text'=>lang('select')],
			['id'=>'0', 'text'=>lang('none')],
			['id'=>'1', 'text'=>lang('readonly')],
			['id'=>'2', 'text'=>lang('add')],
			['id'=>'3', 'text'=>lang('edit')],
			['id'=>'4', 'text'=>lang('full')]];
		$data = ['type'=>'divHTML',
			'divs'    => ['detail'=> ['order'=>10, 'src'=>BIZUNO_LIB."view/module/bizuno/accRolesDetail.php"]],
			'toolbars'=> ['tbRoles'=>  ['icons' => [
				'save' => ['order'=>20,'hidden'=>$security>1?'0':'1',   'events'=>['onClick'=>"jq('#frmRoles').submit();"]],
                'new'  => ['order'=>40,'hidden'=>$security>1?false:true,'events'=>['onClick'=>"accordionEdit('accRoles','dgRoles','divRolesDetail','".jsLang('details')."','bizuno/roles/edit', 0);"]],
				'help' => ['order'=>99,'index' =>'']]]],
			'tabs'  => ['tabRoles'=>['attr'=>['tabPosition'=>'left', 'headerWidth'=>200]]],
			'forms' => ['frmRoles'=>['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/roles/save"]]],
			'fields'=> [
                'selFill' => ['label'=>$this->lang['desc_security_fill'],'values'=>$securityChoices,'events'=>['onChange'=>"autoFill();"],'attr'=>['type'=>'select']]]];
		$dbData = dbGetRow(BIZUNO_DB_PREFIX."roles", "id='$rID'");
		$dbData['settings'] = json_decode($dbData['settings'], true);
        msgDebug("\nRead from db = ".print_r($dbData, true));
		$data['roles'] = dbLoadStructure(BIZUNO_DB_PREFIX."roles");
		unset($data['roles']['settings']);
		dbStructureFill($data['roles'], $dbData);
        $data['roles']['restrict'] = ['label'=>$this->lang['roles_restrict'],'position'=>'after',
            'attr'=>['type'=>'checkbox', 'checked'=>isset($dbData['settings']['restrict']) && $dbData['settings']['restrict'] ? 1 : 0]];
		$data['tabs']['tabRoles']['divs'] = roleTabs(is_array($dbData['settings']['security']) ? $dbData['settings']['security'] : []);
		$data['pageTitle'] = lang('roles').' - '.($rID ? $dbData['title'] : lang('new'));
		$layout = array_replace_recursive($layout, $data);
	}

	/**
     * Structure for saving roles after edit
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function save(&$layout=[])
    {
        $request = $_POST;
		$rID = clean('id', 'integer', 'post');
        $restrict = clean('restrict', 'boolean', 'post');
        if (!$security = validateSecurity('bizuno', 'roles', $rID?3:2)) { return; }
		$values = requestData(dbLoadStructure(BIZUNO_DB_PREFIX."roles"));
		$dup = dbGetValue(BIZUNO_DB_PREFIX."roles", 'id', "title='".addslashes($values['title'])."' AND id<>$rID");
        if ($dup) { return msgAdd(lang('error_duplicate_id')); }
		$settings = [];
        $settings['restrict'] = $restrict;
		foreach ($request as $key => $value) { //extract the security
			if (substr($key, 0, 4) == 'sID:') { // it's a valid security ID
				$code = substr($key, 4);
				$settings['security'][$code] = $value;
			}
		}
		$values['settings'] = json_encode($settings);
		$result = dbWrite(BIZUNO_DB_PREFIX."roles", $values, $rID?'update':'insert', "id=$rID");
        if (!$rID) { $rID = $_GET['rID'] = $result; }
        dbClearCache(); // force reload of all users cache with next page access
		msgLog(lang('table').' '.BIZUNO_DB_PREFIX."roles - ".lang('save')." {$values['title']} ($rID)");
		msgAdd(lang('msg_record_saved'), 'success');
		$title = lang('manager');
		$data = ['content'=>['rID'=>$rID, 'action'=>'eval','actionData'=>"jq('#accRoles').accordion('select','$title'); jq('#dgRoles').datagrid('reload');"]];
        $layout = array_replace_recursive($layout, $data);
	}

	/**
     * Structure for copying roles as a quick add/edit
     * @param array $layout -  structure coming in
     * @return modified $layout
     */
    public function copy(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'roles', 2)) { return; }
		$rID   = clean('rID', 'integer','get');
		$title = clean('data','text',   'get');
        if (!$rID || !$title) { return msgAdd(lang('err_copy_name_prompt')); }
		$role = dbGetRow(BIZUNO_DB_PREFIX."roles", "id=$rID");
		unset($role['id']);
		$role['title'] = $title;
		$nID = $_GET['rID'] = dbWrite(BIZUNO_DB_PREFIX."roles", $role);
        if ($nID) { msgLog(lang('roles')."-".lang('copy')." $title ($rID => $nID)"); }
		$data = ['content'=>['action'=>'eval','actionData'=>"jq('#dgRoles').datagrid('reload'); accordionEdit('accRoles','dgRoles','divRolesDetail','".jsLang('details')."', 'bizuno/roles/edit',$nID);"]];
        $layout = array_replace_recursive($layout, $data);
	}

	/**
     * Structure to delete a role
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function delete(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'roles', 4)) { return; }
		$rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd(lang('err_delete_name_prompt')); }
		$block = dbGetMulti(BIZUNO_DB_PREFIX."users", "role_id=$rID", "title");
		if (sizeof($block) > 0) {
			$users = [];
            foreach ($users as $row) { $users[] = $row['email']; }
			return msgAdd(sprintf(lang('err_delete_role'), implode(', ', $users)));
		}
		$title = dbGetValue(BIZUNO_DB_PREFIX."roles", 'title', "id='$rID'");
		msgLog(lang('table').' '.BIZUNO_DB_PREFIX."roles".'-'.lang('delete')." $title ($rID)");
		$data = [
            'content' => ['action'=>'eval','actionData'=>"jq('#dgRoles').datagrid('reload');"],
			'dbAction' => [BIZUNO_DB_PREFIX."roles"=>"DELETE FROM ".BIZUNO_DB_PREFIX."roles"." WHERE id='$rID'"],
            ];
        $layout = array_replace_recursive($layout, $data);
	}
}
