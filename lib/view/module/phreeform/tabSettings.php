<?php
/*
 * View for PhreeForm -> Design -> Settings tab
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
 * @version    2.x Last Update: 2018-06-05
 * @filesource /lib/view/module/phreeform/tabSettings.php
 */

namespace bizuno;

$notes = '';
$output['body'] .= '
<table style="border-style:none;margin-left:auto;margin-right:auto;">
 <thead class="panel-header"><tr><th>'.lang('settings')."</th></tr></thead>
 <tbody>\n";
if ($viewData['fields']['rptType']['attr']['value'] == 'rpt') {
	$output['body'] .= '  <tr><td>'.html5('truncate',                   $viewData['fields']['TextTruncate'])."</td></tr>\n";
	$output['body'] .= '  <tr><td>'.html5('totalonly',                  $viewData['fields']['TotalOnly'])."</td></tr>\n";
} elseif ($viewData['fields']['rptType']['attr']['value'] == 'frm') {
	$output['body'] .= '  <tr><td>'.html5('serialform',                 $viewData['fields']['Serial'])."</td></tr>\n";
	$output['body'] .= '  <tr><td><sup>1</sup>'.html5('setprintedfield',$viewData['fields']['PrintedField'])."</td></tr>\n";
	$output['body'] .= '  <tr><td><sup>2</sup>'.html5('contactlog',     $viewData['fields']['ContactLog'])."</td></tr>\n";
	$output['body'] .= '  <tr><td>'.html5('defaultemail',               $viewData['fields']['DefaultEmail'])."</td></tr>\n";
	$output['body'] .= '  <tr><td>'.html5('formbreakfield',             $viewData['fields']['FormBreakField'])."</td></tr>\n";
	$output['body'] .= '  <tr><td>'.html5('skipnullfield',              $viewData['fields']['SkipNullField'])."</td></tr>\n";
	$notes .= '<br /><sup>1</sup>'.$viewData['lang']['msg_printed_set'];
	$notes .= '<br /><sup>2</sup>'.$viewData['lang']['tip_phreeform_contact_log'];
}
$output['body'] .= '
  <tr><td>'.html5('special_class',$viewData['fields']['SpecialClass'])."</td></tr>
  <tr><td>".html5('groupname',    $viewData['fields']['Group'])."</td></tr>
  <tr><td>".$viewData['lang']['msg_download_filename'].'<br />'.html5('filenameprefix', $viewData['fields']['FilenamePrefix']).html5('filenamefield', $viewData['fields']['FilenameField'])."</td></tr>
 </tbody>
</table>".'
<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
 <thead class="panel-header">
  <tr><th colspan="2">'.lang('security')."</th></tr>
  <tr><th>".lang('users').'</th><th>'.lang('groups').'</th></tr>
 </thead>
 <tbody>
  <tr><td>'.html5('user_all', $viewData['fields']['SecUsersAll']) .'</td><td>'.html5('group_all', $viewData['fields']['SecGroupsAll']).'</td></tr>
  <tr><td width="50%">'.html5('users[]', $viewData['fields']['SecurityUsers']).'</td><td width="50%">'.html5('groups[]',$viewData['fields']['SecurityGroups'])."</td></tr>
 </tbody>
</table>\n";
$output['body'] .= $notes;
