<?php
/*
 * PhreeBooks Dashboard - Today's Customer Sales Orders
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
 * @version    2.x Last Update: 2017-12-07
 * @filesource /lib/controller/module/phreebooks/dashboards/todays_j10/todays_j10.php
 * 
 */

namespace bizuno;

define('DASHBOARD_TODAYS_J10_VERSION','1.0');

class todays_j10
{
    public $moduleID = 'phreebooks';
    public $methodDir= 'dashboards';
    public $code     = 'todays_j10';
    public $category = 'customers';
	
	function __construct($settings)
    {
		$this->security= getUserCache('security', 'j10_mgr', false, 0);
        $defaults      = ['jID'=>10,'max_rows'=>20,'users'=>'-1','roles'=>'-1','reps'=>'0','num_rows'=>5,'order'=>'desc'];
        $this->settings= array_replace_recursive($defaults, $settings);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $this->trim    = 20; // length to trim primary_name to fit in frame
        $this->noYes   = ['0'  =>lang('no'),        '1'   =>lang('yes')];
		$this->order   = ['asc'=>lang('increasing'),'desc'=>lang('decreasing')];
	}

    public function settingsStructure()
    {
        for ($i = 0; $i <= $this->settings['max_rows']; $i++) { $list_length[] = ['id'=>$i, 'text'=>$i]; }
        return [
            'jID'     => ['attr'=>['type'=>'hidden','value'=>$this->settings['jID']]],
            'max_rows'=> ['attr'=>['type'=>'hidden','value'=>$this->settings['max_rows']]],
            'users'   => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10, 'multiple'=>'multiple']],
            'roles'   => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10, 'multiple'=>'multiple']],
            'reps'    => ['label'=>lang('just_reps'),    'values'=>viewKeyDropdown($this->noYes),'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['reps']]],
            'num_rows'=> ['label'=>lang('limit_results'),'values'=>$list_length,'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['num_rows']]],
            'order'   => ['label'=>lang('sort_order'),   'values'=>viewKeyDropdown($this->order),'position'=>'after','attr'=>['type'=>'select','value'=>$this->settings['order']]]];
	}

	public function render()
    {
        global $currencies;
		$btn   = ['attr'=>['type'=>'button','value'=>lang('save')],'styles'=>['cursor'=>'pointer'],'events'=>['onClick'=>"dashboardAttr('$this->moduleID:$this->code', 0);"]];
        $data  = $this->settingsStructure();
		$html  = '<div>';
		$html .= '  <div id="'.$this->code.'_attr" style="display:none"><form id="'.$this->code.'Form" action="">';
		$html .= '    <div style="white-space:nowrap">'.html5($this->code.'num_rows',$data['num_rows']).'</div>';
		$html .= '    <div style="white-space:nowrap">'.html5($this->code.'order',   $data['order'])   .'</div>';
		$html .= '    <div style="text-align:right;">' .html5($this->code.'_btn', $btn).'</div>';
		$html .= '  </form></div>';
		// Build content box
        $filter = "journal_id={$this->settings['jID']} AND post_date='".date('Y-m-d')."'";
        if ($this->settings['reps'] && getUserCache('profile', 'contact_id', false, '0')) {
            if (getUserCache('security', 'admin', false, 0)<3) { $filter.= " AND rep_id='".getUserCache('profile', 'contact_id', false, '0')."'"; }
        }
        if (getUserCache('profile', 'restrict_store', false, -1) > 0) { $filter.= " AND store_id=".getUserCache('profile', 'restrict_store', false, -1).""; }
		$order  = $this->settings['order']=='desc' ? 'post_date DESC, invoice_num DESC' : 'post_date, invoice_num';
		$result = dbGetMulti(BIZUNO_DB_PREFIX."journal_main", $filter, $order, ['id','total_amount','currency','currency_rate','post_date','invoice_num', 'primary_name_b'], $this->settings['num_rows']);
		$total = 0;
		if (sizeof($result) > 0) {
			foreach ($result as $entry) {
				$currencies->iso  = $entry['currency'];
				$currencies->rate = $entry['currency_rate'];
				$html .= '  <div>';
				$html .= '    <div style="float:right;">'.viewFormat($entry['total_amount'], 'currency').'</div>';
				$html .= '    <div>'.viewDate($entry['post_date']).' #<a href='.BIZUNO_HOME.'&p=phreebooks/main/manager&jID='.$this->settings['jID'].'&rID='.$entry['id'].'" target="_blank">'.$entry['invoice_num'].'</a> ';
				$html .= viewText($entry['primary_name_b'], $this->trim).'</div>';
				$html .= '  </div>';
				$total += $entry['total_amount'];
			}
			$currencies->iso  = getUserCache('profile', 'currency', false, 'USD');
			$currencies->rate = 1;
			$html .= '<div style="float:right"><b>'.viewFormat($total, 'currency').'</b></div>';
			$html .= '<div><b>'.lang('total')."</b></div>\n";
		} else {
			$html .= '  <div>'.lang('no_results')."</div>\n";
		}
		$html .= '</div>';
		return $html;
	}

	public function save()
    {
		$menu_id  = clean('menuID', 'text', 'get');
        $settings['num_rows']= clean($this->code.'num_rows', 'integer','post');
		$settings['order']   = clean($this->code.'order', 'cmd', 'post');
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
	}
}
