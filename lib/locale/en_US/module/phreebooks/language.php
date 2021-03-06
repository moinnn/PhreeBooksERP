<?php
/*
 * Language translation for PhreeBooks module 
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
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-06-06
 * @filesource /locale/en_US/module/phreebooks/language.php
 */

$lang = [
    'title' => 'PhreeBooks Accounting',
    'description' => 'The phreebooks module provides double entry accounting. Functions include Purchase Orders, Sales Orders, Invoicing, Journal Entries and more. <b>NOTE: This is a core module and cannot be removed!</b>',
    // Settings
    'set_round_tax_auth' => '[Default No] Enabling this feature will cause PhreeBooks to round calculated taxes by authority prior to adding up all applicable authorities. For tax rates with a single authority, this will only keep math precision errors from entering the journal. For multi-authority tax rates, this could cause too much or too little tax from being collected. If not sure, leave set to No.',
    'set_shipping_taxed' => '[Default No] Tax shipping? If Yes is selected, the rate used will be the default rate as set at the customers record. If no customer is selected the rate will be zero. If set to Yes make sure the shipping total is AFTER all tax calculations otherwise tax may be calculated on the taxed shipping value instead of the untaxed shipping value resulting in a double tax calculation.',
	'set_isolate_stores' => '[Default No] If using more than one store, this option will calculate the cost of goods sold on a store by store basis. If set to Yes, inventory must be present at the store the purchase/sale is posted against. If stock is not present at the store, the transaction will queue in the cost of goods sold owed table to be re-posted when inventory becomes available. If not sure, leave this setting set to No.',
    'set_auto_currency' => 'Determines if the currency exchange rates should be automatically updated when logging into Bizuno.',
	'set_gl_payables' => 'Default accounts payable account to use for all purchases. Typically a Accounts Payable type account.',
	'set_gl_receivables' => 'Default Accounts Receivables account. Typically an Accounts Receivable type account.',
	'set_gl_purchases' => 'Default account to use for purchased items. This account can be over written through the individual item record. Typically an Inventory or Expense type account.',
	'set_gl_sales' => 'Default account to use for sales transactions. Typically an Income type account.',
	'set_gl_cash' => 'Default account to use for cash transactions involving payment of invoices. Typically a Cash type account.',
	'set_gl_discount' => 'Default account to use for discounts when invoices are paid on early terms. Typically a Income type account for customers, Cost of Goods Sold type account for vendors.',
	'set_gl_deposit_cash' => 'Default account to use for cash deposits. Typically a Cash type account.',
	'set_gl_liability' => 'Default account to use to hold sales tax and deposit liabilities. Typically an Other Current Liabilities type account.',
	'set_gl_expense' => 'Default account to use for expenses. Typically an Expense type account.',
	'set_terms_text' => 'Default payment terms and credit limit.',
	'set_auto_add' => 'If set to Yes, this option will automatically create a new contact when the entry is posted.',
	'set_show_status' => 'This feature displays a contact status popup on the order screens when a customer/vendor is selected from the search popup. It displays balances, account aging as well as the active status of the contact.',
	'set_include_all'=> 'Include all lines that have SKUs when filling a Sales Order irregardless of whether they are being filled with this invoice. No - only line items that have a non-zero quantity will be added to the invoice. Yes - All line items with non-blank SKUs will be included in the invoice, handy for displaying shipped prior/balance due information on blanket purchase orders.',
    // Messages
    'msg_gl_fiscal_year_edit' => 'Fiscal period calendar dates can be modified here. Please note that fiscal year dates cannot be changed for any period up to and including the last general journal entry in the system.',
	'msg_gl_fiscal_year_confirm' => 'Are you sure you want to add fiscal year %s',
    'del_fiscal_year_btn' => 'Close/Delete Fiscal Year',
    'fy_del_title' => 'Close Fiscal Year',
    'fy_del_desc' => 'This tool deletes a fiscal year from the database.<br /><br />NOTE: ALL DATA WILL BE LOST! You should make a backup before performing this operation.<br /><br />This process can take several minutes and no one should be accessing your business data when it is running. Further instructions will be provided along with options for each module that may be affected.',
    'fy_del_instr' => '<p style="color:red">WARNING: THIS OPERATION WILL CLOSE FISCAL YEAR %s.</p><p>Please review the settings for each tab below and adjust accordingly. Bizuno has selected defaults settings for each module that is impacted by fiscal years, you may change them to meet your business preferences.</p><p><b>When you are ready, press the Start button in the toolbar.</b></p>',
    'fy_del_btn_go' => 'Yes I am ready! lets proceed ...',
    'fy_del_btn_cancel' => 'Not Now, thanks.',
	'msg_gl_repost_journals_confirm' => '<b>BE SURE TO BACKUP YOUR DATA BEFORE YOU RE-POST ANY JOURNALS!</b><br />Note 1: Re-posting journals can take some time, you may want to limit the re-posts by entering a smaller date range or a limited number of journals.',
	'msg_gl_db_purge' => 'Purge all Journal Transactions (retains contacts and settings)',
	'msg_gl_db_purge_confirm' => 'Are you sure you want to clear all journal entries? Type the word [purge] in the box and press the Purge Journal Entries button.',
    'msg_attach_clean_success' => 'Successfully removed %s attachments',
    'msg_attach_clean_empty' => 'No attachments were found prior to your date criteria!',
    'msg_pb_admin_roles' => 'Select the PhreeBooks roles that apply to this Bizuno role. If selected, users will appear in the Rep ID drop downs in Sales, Purchases, etc.',
	'msg_gl_replace_confirm' => 'Are you sure you want to replace your GL Accounts?',
	'msg_gl_replace_success' => 'Your GL accounts have been replaced, you should log out and back in to reload the settings.',
    'phreebooks_purge_success' => 'Journal Entries were purged successfully!',
    'coa_import_blocked' => 'Importing/Uploading chart of accounts has been disabled since there are journal entries present. This operation can only be performed on a clean general journal at start-up. To delete your journal and start clean, perform the purge operation on the Journal Tools tab.',
    'recur_desc' => 'This transaction can be duplicated in the future by selecting the number of entries to be created and the frequency for which they are posted. The current entry is considered the first recurrence, other post dates will be calculated automatically.',
	'recur_times' => 'Total number of entries created',
	'recur_frequency' => 'Select how frequent to post entries, post dates cannot exceed any fiscal year set up in Bizuno.',
	'msg_currency_update' => 'The exchange rate for %s (%s) was updated successfully to %s via %s.',
	'msg_contact_status_good' => 'Account in Good Standing',
	'msg_contact_status_over_limit' => 'Account is Over Credit Limit',
	'msg_contact_status_past_due' => 'Account has Past Due Balance',
	'msg_contact_past_due_amount' => 'Past Due Balance: %s',
    'msg_invoice_rqd'=>'For recurring entries, a starting invoice number is required.',
	'msg_inv_waiting'=>'For purchases, either an invoice number must be entered of the waiting checkbox must be selected.',
	'msg_negative_stock'=>'There are not enough of SKU %s in stock to fill this order, current quantity in stock is %i',
	'msg_recur_edit'=>'This is a recurring entry. Do you want to update future entries as well? (Press Cancel to update only this entry)',
	'msg_save_as_closed' =>'Since this record is closed, it cannot be Saved As or Moved To a new record!',
	'msg_save_as_linked' =>'It looks like this record has a reference record or is a recurring record, it cannot be Saved As or Moved To a new record!',
    'bal_decrease' => 'Balance Decreases',
    'bal_increase' => 'Balance Increases',

    // Error Messages
    'err_gl_chart_delete' => 'The GL account cannot be deleted if there are entries assigned to the account. There is at least one entry in table %s using this account!',
    'err_tax_rate_delete' => 'The tax rate cannot be deleted if there are gl entries posted against it!',
	'err_currency_change' => 'The default currency cannot be changed once entries have been entered in the system!',
	'err_currency_delete_default' => 'The default currency cannot be deleted! Set another currency as default and then retry deleting this one.',
	'err_currency_cannot_delete' => 'The currency cannot be deleted if ther are entries in the system using this currency!',
    'err_pb_repost_empty' => 'Please select at least one journal to process.',
	'err_currency_bad_iso' => 'The exchange rate for %s (%s) was not updated via %s. Is it a valid currency code?',
	'err_gl_xfr_same_store' => 'The source and destination store cannot be the same!',
    'err_dup_order' => 'Invoice %s is already present in Bizuno, it will be skipped!',
    'err_debits_credits_not_zero' => 'Error: The debits total must equal the credits total to post a general journal entry.',
    'err_journal_delete' => 'This %s record cannot be deleted because there is a future transaction posted that is dependent on this record. The future transaction must be deleted first.',
    'err_total_not_match' => 'The calculated total of %s is not equal to the submitted total of %s, this is typically a tax calculation or rounding problem, please contact PhreeSoft for assistance!',
    // Buttons
    'title_gl_test' => 'Validate and Repair General Ledger Account Balances',
	'new_currency' => 'New Currency',
	'new_currency_desc' => 'Select a new currency from the drop down menu and press Next.',
	'restrict_period' => 'Restrict the transactions of this user to the current period.',
	'desc_import_journal' => 'Used for importing beginning balances into PhreeBooks journals typically for the \'Line in the Sand\' approach when converting from another accounting application. Refer to the help file for format requirements.',
    'phreebooks_import_inv' => 'Import Inventory',
	'phreebooks_import_ap' => 'Import Accounts Payable',
	'phreebooks_import_ar' => 'Import Accounts Receivable',
	'phreebooks_import_po' => 'Import Purchase Orders',
	'phreebooks_import_so' => 'Import Sales Orders',
    'phreebooks_purge_db_journal' => 'Purge Journal Entries',
    // labels
    'new_gl_account' => 'New GL Account',
    'phreebooks_repost_title' => 'Re-post Journal Entries',
    'phreebooks_fiscal_years' => 'Fiscal Years',
	'phreebooks_journal_periods' => 'Accounting Periods',
    'expected_delivery_dates' => 'Expected Delivery Dates',
    'status_orders_invoice' => 'Invoice Orders/Quotes',
    'status_open_j9'  => 'Open Quotes',
    'status_open_j10' => 'Open Orders',
    'status_open_j12' => 'Unpaid Invoices',
    'status_open_j13' => 'Unpaid Credits',
    // General
    'create_credit' => 'Create Credit Memo',
    'fill_purchase' => 'Receive/Fill Purchase',
    'fill_sale' => 'Invoice/Fill Sale',
    'pb_inv_unit' => 'Unit Price',
    'pb_tax_by_journal' => 'Tax by Journal',
    'pb_total_by_journal' => 'Total by Journal',
    'pb_so_status' => 'SO Item Details',
    'pb_gl_age_00' => 'Aging Current',
	'pb_gl_age_30' => 'Aging 30 Days',
	'pb_gl_age_60' => 'Aging 60 Days',
	'pb_gl_age_90' => 'Aging Over 90',
    'over_90' => 'Over 90',
	'pb_is_ytd' => 'Income YTD',
	'pb_is_budget_ytd' => 'Budget YTD',
	'ly_actual' => 'Last Year',
	'pb_is_last_ytd' => 'Last YTD',
	'ly_budget' => 'Last Year Budget',
	'pb_is_last_bdgt_ytd' => 'LY Budget YTD',
	'fiscal_dates' => 'Fiscal Dates',
	'desc_gl_db_purge' => 'Delete all Journal Entries (type \'purge\' in the text box and press purge button)<br />',
    'set_invoice_num' => 'Set Invoice Number',
    'enter_invoice_num' => 'Enter the Invoice number, the waiting flag will also be cleared.',
    // Currency
    'neg_prefix' => 'Negative Prefix',
	'neg_suffix' => 'Negative Suffix',
    'dec_point' => 'Decimal Point',
	'dec_length' => 'Fractional digits',
    'not_current' => 'Not In Effect',
    // Tools
    'pb_prune_cogs_title' => 'Prune Journal Cost Of Goods Sold Owed',
    'pb_prune_cogs_desc' => 'If you sell inventory controlled products that are not in stock, inventory will go negative and the journal entry will be queued for re-posting when the inventory is received back in stock. This tool will force a repost of all journal entries that have cost of goods sold values pending. When your system is properly recording cogs owed, the table will only contain entries for inventory with a negative quantity in stock value.',
    'pb_attach_clean_title' => 'Clean PhreeBooks Journal Attachments',
    'pb_attach_clean_desc' => 'This tool removes attachments from journal entries prior to a specific date. The tool is useful to reduce backup file size, speed up attachment searches and remove no longer needed documents.',
	'pb_attach_clean_btn' => 'Select a date to clean to, all files prior to this date will be deleted',
    'pb_attach_clean_confirm' => 'Are you sure you want to deleted ALL attachments prior to this date? This operation cannot be undone.',
    'pb_admin_totals_desc' => 'Sequencing for total purchases can be changed here. Orders are totaled sequentially by the order sequence listed here. To add a totaling method, drag it from the left list and place it in the desired position. To remove a sequence, drag it from the list on the right and drop on the list on the left.',
	'pb_admin_totals_dup' => 'Only one total method may be used on each column!',
	'phreebooks_budget_wizard_desc' => 'This wizard will take the selected fiscal year data and transfer the data to the next fiscal year. Select the options to adjust the results as desired.',
	'build_next_fy_desc' => 'If the next fiscal year is not available, it can be created here. NOTE: Do not generate more fiscal years than you work with. Every journal entry is posted to all forward months from the post date and will be slower with uneccessary fiscal years.',
	'budget_dest_fy' => 'Transfer to Budget for FY ',
	'budget_src_fy' => ' from FY ',
	'budget_using' => ' using source data ',
	'budget_adjust' => ' increased by ',
	'budget_average' => ' Check to average the values within a GL account across each period in new fiscal year and press => ',
	'journal_tools' => 'Journal Tools',
	'primary_gl_acct' => 'Primary GL Account',
	'coa_import_title' => 'Change your Chart of Accounts',
	'btn_coa_upload' => 'Upload and Replace',
	'btn_coa_preview' => 'GL Chart Preview',
	'coa_upload_file' => 'or browse your computer for a custom chart of accounts to upload',
	'coa_import_desc' => 'Your businesses Chart of Accounts can be imported/uploaded here to meet the needs of your business and conform to local regulations.<br />NOTE: Your current chart of accounts cannot be replaced once journal entries are present!',
	'phreebooks_new_fiscal_year' => 'Generate The Next Fiscal Year',
	'pbtools_gl_test_desc' => 'This operation validates and repairs the chart of account balances. If the trial balance or balance sheet are not in balance, this is where to start. First validate the balances to see if there is an error and repair if necessary.<br /><br />Before you repair, YOU SHOULD PRINT FINANCIAL SATEMENTS AND BACKUP YOUR BUSINESS FIRST!',
    'tax_bulk_title' => 'Tax Rate Change (Bulk)',
    'tax_bulk_src' => 'This tool changes the current tax rate of your customers/vendors/inventory, in bulk, to a new rate. This tool should be used to quickly change all customers in a certain taxing district when the rate changes.<br /><br />Select the tax rate to search for:',
    'tax_bulk_dest' => 'Select the tax rate to be applied:',
    'tax_subject' => 'To be applied to what subject:',
    'tax_bulk_success' => 'Finished, the total number of rates changed was: %s',
    // Install notes
    'note_phreebooks_install_1' => 'PRIORITY HIGH: Change or import chart of accounts from default settings (Account (Login Name) -> Settings -> Bizuno Tab -> PhreeBooks (Settings) -> Chart of Accounts tab)',
	'note_phreebooks_install_2' => 'PRIORITY MEDIUM: Enter business information (Account (Login Name) -> Settings -> Bizuno Tab -> Bizuno ERP Settings icon -> Settings tab -> My Business accordion',
	'note_phreebooks_install_3' => 'PRIORITY MEDIUM: Update default general ledger accounts for customer and vendors, after loading GL accounts (Account (Login Name) -> Settings -> Bizuno Tab -> PhreeBooks (Settings) -> Customers/Vendors Accordion',
    ];
