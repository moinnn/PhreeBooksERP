<?php
/*
 * Payment module - Main methods
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
 * @copyright  2008-2018, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-07-09
 * @filesource /lib/controller/module/payment/main.php
 */

namespace bizuno;

class paymentMain
{
	public $moduleID  = 'payment';
	
	public function __construct()
    {
		$this->lang = getLang($this->moduleID);
	}

	/**
     * Generates the structure for viewing enabled payment methods
     * @param array $layout - Structure coming in
     * @output modified $layout
     */
    public function render(&$layout=[])
    {
		$jID  = clean('jID',  'integer','get');
        $type = clean('type', 'char',   'get');
        if (!$type) { $type = in_array($jID, [17, 20, 21]) ? 'v' : 'c'; }
		$layout = array_replace_recursive($layout,[
            'fields' => ['selMethod'=>['label'=>lang('payment_method'),'values'=>viewMethods('payment'),'attr'=>['type'=>'select'],
                'events'=>['onChange'=>'selPayment(this);']]]]);
	}

    /**
     * Manager structure for storing credit cards and other payment information, typically used as a tab in contacts
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('contacts', 'mgr_c', 2)) { return; }
        $rID   = clean('rID', 'integer', 'get');
        $cc_exp= pullExpDates();
        $js = "function paymentNew() {\njq('#payment_id').val('');
    jq('#payment_name').val(''); jq('#payment_num').val('');
    jq('#payment_mon').val('" .date('m')."');
    jq('#payment_year').val('".date('Y')."');
    jq('#payment_cvv').val('');\n}\n";
        $data = ['type'=>'divHTML',
			'toolbars'  => ['tbPayment'=>['icons'=>[
                    'pmtNew' => ['order'=>10,'icon'=>'new', 'label'=>lang('new'), 'events'=>['onClick'=>"paymentNew();"]],
                    'pmtSave'=> ['order'=>20,'icon'=>'save','label'=>lang('save'),'events'=>['onClick'=>"divSubmit('payment/main/save&rID=$rID', 'frmPayment');"]]]]],
			'divs'      => ['pmtMgr' => ['order'=>50, 'src'=>BIZUNO_LIB."view/module/payment/accPmtManager.php"]],
            'jsHead'    => ['manager'=>$js],
			'datagrid'  => ['dgPayment'=> $this->dgPayment('dgPayment', $rID, $security)],
            'fields'    => [
                'payment_id'  => ['attr'  =>['type'=>'hidden']],
                'payment_name'=> ['label' =>lang('payment_name')],
                'payment_num' => ['label' =>lang('payment_number')],
                'payment_mon' => ['label' =>lang('payment_expiration'), 'values'=>$cc_exp['months'], 'attr'=>['type'=>'select', 'value'=>date('m')]],
                'payment_year'=> ['values'=>$cc_exp['years'],   'attr'=>['type'=>'select', 'value'=>date('Y')]],
                'payment_cvv' => ['label' =>lang('payment_cvv'),'attr'=>['type'=>'text', 'size'=>'4']]]];
        $layout = array_replace_recursive($layout, $data);
    }

	/**
     * Lists the payments for a specific contact
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function managerRows(&$layout=[])
    {
        if (!$security = validateSecurity('contacts', 'mgr_c', 1)) { return; }
		$rID = clean('rID', 'integer', 'get');
		$structure = $this->dgPayment('dgPayment', $rID, $security);
        $layout = array_replace_recursive($layout, ['type'=>'datagrid', 'structure'=>$structure]);
	}
	
	/**
     * Creates the structure for editing payment data
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function edit(&$layout=[])
    {
        if (!$security = validateSecurity('contacts', 'mgr_c', 3)) { return; }
		$rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd('The record was not found!'); }
		msgLog(lang('payment')." ".lang('edit')." ($rID)");
        require_once(BIZUNO_LIB."model/encrypter.php");
		$encrypt= new encryption();
        $fields = [];
        if (!$encrypt->decryptCC($rID, $fields)) { return false; } // update $fields with stored data
        $respTasks = "jq('#payment_id').val('".$rID."');"
                   . "jq('#payment_name').val('".$fields['name']  ."');"
                   . "jq('#payment_num').val('" .$fields['number']."');"
                   . "jq('#payment_mon').val('" .$fields['month'] ."');"
                   . "jq('#payment_year').val('".$fields['year']  ."');"
                   . "jq('#payment_cvv').val('" .$fields['cvv']   ."');";
        $layout = array_replace_recursive($layout, ['content' =>['action'=>'eval', 'actionData'=>$respTasks]]);
	}

    /**
     * Saves the payments for a given contact
     * @param array $layout - structure coming in
     * @return modified $layout
     */
	public function save(&$layout=[])
    {
        $rID  = clean('rID',         'integer','get');
        $pID  = clean('payment_id',  'integer','post');
        $name = clean('payment_name','text',   'post');
        $nmbr = clean('payment_num', 'numeric','post');
        if (!$security = validateSecurity('contacts', 'mgr_c', 2)) { return; }
		if (!$rID || !$name || !$nmbr) { return msgAdd('Please make sure all fields are filled out!');; } // allow for save contact if no payment data
		$fields = [
            'id'    => $pID,
            'name'  => $name,
			'number'=> str_replace(' ', '', $nmbr), 
			'month' => clean('payment_mon', 'integer','post'),
			'year'  => clean('payment_year','integer','post'),
			'cvv'   => clean('payment_cvv', 'integer','post'),
			'module'=> 'contacts',
			'ref_1' => $rID]; // record in contacts table
        msgDebug("\nWorking with payment fields: ".print_r($fields, true));
        require_once(BIZUNO_LIB."model/encrypter.php");
		$encrypt = new encryption();
		$encrypt->encryptCC($fields);
		msgAdd(lang('msg_record_saved'), 'success');
		msgLog(lang('payment')." ".lang('save')." (rID=$rID and pID=$pID)");
		$data = ['content' =>['action'=>'eval', 'actionData'=>"paymentNew(); jq('#dgPayment').datagrid('reload');"]];
        $layout = array_replace_recursive($layout, $data);
	}

	/**
     * Deletes a specific payment data record
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function delete(&$layout=[])
    {
        if (!$security = validateSecurity('contacts', 'mgr_c', 4)) { return; }
		$rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd('The record was not deleted, the proper id was not passed!'); }
		msgLog(lang('payment')." ".lang('delete')." ($rID)");
		$data = ['content' =>['action'=>'eval', 'actionData'=>"jq('#dgPayment').datagrid('reload');"],
			     'dbAction'=>[BIZUNO_DB_PREFIX."data_security" => "DELETE FROM ".BIZUNO_DB_PREFIX."data_security WHERE id=$rID"]];
        $layout = array_replace_recursive($layout, $data);
	}

	/**
     * Datagrid structure for the payments stored for a specific customer
     * @param string $name - DOM field name
     * @param integer $rID - Contact database record id
     * @param integer $security - users security
     * @return structure for payment datagrid
     */
    private function dgPayment($name, $rID=0, $security=0)
    {
		$rows   = clean('rows', ['format'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')], 'post');
		$page   = clean('page', ['format'=>'integer','default'=>1], 'post');
		$sort   = clean('sort', ['format'=>'text',   'default'=>'exp_date'],'post');
		$order  = clean('order',['format'=>'text',   'default'=>''],    'post');
		$data = [
            'id'     => $name,
			'rows'   => $rows,
			'page'   => $page,
			'attr'   => [
                'url'     => BIZUNO_AJAX."&p=payment/main/managerRows&rID=$rID",
				'pageSize'=> getModuleCache('bizuno', 'settings', 'general', 'max_rows'),
				'idField' => 'id'],
			'source' => [
                'tables' => ['data_security'=>['table'=>BIZUNO_DB_PREFIX."data_security"]],
				'filters' => [
                    'module'=>  ['order'=>99,'hidden'=>true,'sql'=>BIZUNO_DB_PREFIX."data_security.module='contacts'"],
					'rID'   =>  ['order'=>99,'hidden'=>true,'sql'=>BIZUNO_DB_PREFIX."data_security.ref_1=$rID"]],
				'sort' => ['s0'=>  ['order'=>10, 'field'=>"$sort $order"]]],
			'columns'=> [
                'id'     => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX."data_security.id", 'attr'=>['hidden'=>true]],
				'action' => ['order'=>1, 'label'=>'', 'attr'=>['width'=>75],
					'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
					'actions'=> [
                        'pmtEdit' => ['icon'=>'edit','size'=>'small', 'order'=>20, 'label'=>lang('edit'), 'hidden'=>$security>2?false:true,
							'events'=> ['onClick' => "jsonAction('payment/main/edit', idTBD);"]],
                        'pmtTrash' => ['icon'=>'trash','size'=>'small', 'order'=>50, 'label'=>lang('delete'), 'hidden'=>$security>3?false:true,
							'events'=> ['onClick' => "if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('payment/main/delete', idTBD);"]]]],
				'enc_value' => ['order'=>10, 'field'=>BIZUNO_DB_PREFIX."data_security.enc_value", 'format'=>'encryptName',
					'label' => lang('address_book_primary_name'), 'attr'=>['width'=>200, 'sortable'=>true, 'resizable'=>true]],
				'hint'   => ['order'=>20, 'field'=>BIZUNO_DB_PREFIX."data_security.hint",
					'label' => lang('hint'),  'attr'=>['width'=>150, 'sortable'=>true, 'resizable'=>true]],
				'exp_date'=> ['order'=>30, 'field' => BIZUNO_DB_PREFIX."data_security.exp_date", 'format'=>'date',
					'label' => lang('payment_expiration'), 'attr'=>['width'=>120, 'sortable'=>true, 'resizable'=>true]]]];
		return $data;
	}

    /**
     * This method accepts post variables from ALL methods, determines the method and submits all credit cards for authorization
     * @return array - user message if failed, success contains the authorization_code for credit cards, ref field if supplied.
     */
    public function authorize($ledger=[])
    {
        if (!$security = validateSecurity('phreebooks', "j12_mgr", 2)) { return; }
        $method = clean('method_code','text', 'post');
        $amount = clean('pmt_amount', 'float','post');
        if (!getModuleCache('payment', 'methods', $method, 'path')) {
			return msgAdd("Cannot apply payment since the method is not installed or detected!");
		}
        if (!$fields = $this->process($method, $ledger)) { return; }
        $pmtSet = getModuleCache('payment','methods',$method,'settings');
        $fqcn = "\\bizuno\\$method";
        $merchant = new $fqcn($pmtSet);
        $txID = '1';
		if (method_exists($merchant, 'paymentAuth')) {
            if (!$response = $merchant->paymentAuth($fields, $amount)) { return; }
            $txID = $response['txID'];
		}
        return $txID;
    }

	/**
	 * This method is the parent to process a sale, both authorize and capture are supported
	 * @param array $method - typically $_POST variables to gather the payment details
	 * @param array $ledger - contains the current PhreeBooks ledger object with journal details
	 * @return false on failure and transaction information array on success
	 */
	public function sale($method, $ledger)
	{
		if (!getModuleCache('payment', 'methods', $method, 'path')) {
			return msgAdd("Cannot apply payment to method: $method since the method is not installed!");
		}
		$iID = dbGetValue(BIZUNO_DB_PREFIX."journal_item", ['id', 'description'], "ref_id={$ledger->main['id']} AND gl_type='ttl'");
		$desc = [];
		$props = explode(";", $iID['description']);
		if (sizeof($props) > 0) { foreach ($props as $row) { // decode the description field
			$tmp = explode(":", $row);
            if ($tmp[0]) { $desc[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : ''; }
        } }
        if (!$fields = $this->process($method, $ledger)) { return; }
        $pmtSet = getModuleCache('payment','methods',$method,'settings');
        $fqcn = "\\bizuno\\$method";
        $merchant = new $fqcn($pmtSet);
		if (method_exists($merchant, 'sale')) {
            if (!$result = $merchant->sale($fields, $ledger)) { return; }
		} else {
			$result['txID'] = '';
		}
		// add to the description
		$desc['method']= $method;
		$desc['status']= 'cap';
        if (isset($fields['hint'])) { $desc['hint'] = $fields['hint']; }
		$output = [];
        foreach ($desc as $key => $value) { $output[] = "$key:$value"; }
		$fields = ['description'=>implode(';', $output), 'trans_code'=>$result['txID']];
		dbWrite(BIZUNO_DB_PREFIX."journal_item", $fields, 'update', "id={$iID['id']}");
		return $result;
	}

    /**
     * Entry point for processing a credit card payment
     * @param string $method - user selected payment method
     * @param array $ledger - working data supplied by the user in a form post
     * @return array of data for success, false on error
     */
    private function process($method, $ledger=[])
    {
        $request=$_POST;
        require_once(BIZUNO_LIB."model/encrypter.php");
        $methods = getModuleCache('payment', 'methods');
        require_once($methods[$method]['path']."$method.php");
		$encrypt= new encryption();
		$name   = clean($method."_name",  'text', 'post');
		$action = clean($method."_action",'char', 'post');
        $fields = [
            'action'     => $action,
			'id'         => 0,
            'name'       => $name,
			'first_name' => substr($name, 0, strpos($name, ' ')),
			'last_name'  => substr($name, strpos($name, ' ')+1, strlen($name)),
			'number'     => '', // must be text to avoid overflow of integer length
			'month'      => '',
			'year'       => '',
			'cvv'        => '',
			'module'     => 'contacts',
			'ref_1'      => isset($ledger->main['contact_id_b']) ? $ledger->main['contact_id_b'] : '']; // link to contact record
		switch ($action) { // auth code is present, just finish the payment process
			case 'c': // capture an authorized credit card
				$fields['txID'] = $request[$method.'trans_code'];
				break;
			case 's': // stored credit card
				$fields['id'] = $request[$method.'selCards'] ? clean($request[$method.'selCards'], 'integer') : 0;
                if (!$encrypt->decryptCC($fields['id'], $fields)) { return false; } // update $fields with stored data
				break;
			case 'n': // new credit card
				$fields['number']= clean(preg_replace('/ /', '', $request[$method.'_number']), 'text'); // must be text to avoid overflow of integer length
				$fields['month'] = substr('0'.clean($request[$method.'_month'], 'integer'), -2);
				$fields['year']	 = clean($request[$method.'_year'], 'integer');
				$fields['cvv']   = clean($request[$method.'_cvv'],  'integer');
				if (isset($request[$method.'_save']) && $request[$method.'_save']) { $encrypt->encryptCC($fields); }
				$fields['hint']  = substr($fields['number'], 0, 4);
                for ($a = 0; $a < (strlen($fields['number']) - 8); $a++) { $fields['hint'] .= '*'; }
				$fields['hint'] .= substr($fields['number'], -4);
				break;
			default:
                if (isset($request[$method.'_ref_1'])) { $fields['ref_1'] = clean($request[$method.'_ref_1'], 'text'); } // change to clean 'text' so non-numeric values can be entered
				break;
		}
        // Error Check
		// if the card number has the blanked out middle number fields, it has been processed, show message that
		// the charges were not processed through the merchant gateway and continue posting payment.
		if (strpos($fields['number'], '*') !== false) { return msgAdd($this->lang['err_payment_dup'], 'caution'); }
        if ($fields['number'] && !$encrypt->validate($fields['number'])) { return; }
        if ($fields['cvv'] !== '') { $fields['cvv'] = $this->fixCvv($fields['cvv'], $fields['number']); }
        return $fields;
    }

	/**
     * Cleans and modifies CVV to meet credit card processor standards and expectations
     * @param integer $cvv - user supplied cvv code
     * @param string $ccNum - credit card number to determine how long to make returning cvv
     * @return string - cleaned cvv ready to submit to method processor
     */
    private function fixCvv($cvv, $ccNum) 
	{
		return substr("0000".$cvv, substr($ccNum,0,2)=='37' ? -4 : -3);
	}
}
