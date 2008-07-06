<?php

$page_security = 14;
$path_to_root="../..";
include($path_to_root . "/includes/session.inc");

page(_("Sales Types"));

include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/sales/includes/db/sales_types_db.inc");

simple_page_mode(true);
//----------------------------------------------------------------------------------------------------

function can_process() 
{
	if (strlen($_POST['sales_type']) == 0) 
	{
		display_error(_("The sales type description cannot be empty."));
		set_focus('sales_type');
		return false;
	} 

	if (!check_num('factor', 0)) 
	{
		display_error(_("Calculation factor must be valid positive number."));
		set_focus('factor');
		return false;
	} 
	return true;
}

//----------------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' && can_process()) 
{
	add_sales_type($_POST['sales_type'], isset($_POST['tax_included']) ? 1:0, 
	    input_num('factor'));
	display_notification(_('New sales type has been added'));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------------

if ($Mode=='UPDATE_ITEM' && can_process()) 
{

	update_sales_type($selected_id, $_POST['sales_type'], isset($_POST['tax_included']) ? 1:0,
	     input_num('factor'));
	display_notification(_('Selected sales type has been updated'));
	$Mode = 'RESET';
} 

//----------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'debtor_trans'

	$sql= "SELECT COUNT(*) FROM ".TB_PREF."debtor_trans WHERE tpe='$selected_id'";
	$result = db_query($sql,"check failed");
	check_db_error("The number of transactions using this Sales type record could not be retrieved", $sql);

	$myrow = db_fetch_row($result);
	if ($myrow[0] > 0) 
	{
		display_error(_("Cannot delete this sale type because customer transactions have been created using this sales type."));

	} 
	else 
	{

		$sql = "SELECT COUNT(*) FROM ".TB_PREF."debtors_master WHERE sales_type='$selected_id'";
		$result = db_query($sql,"check failed");
  		check_db_error("The number of customers using this Sales type record could not be retrieved", $sql);
  					
		$myrow = db_fetch_row($result);
		if ($myrow[0] > 0) 
		{
			display_error(_("Cannot delete this sale type because customers are currently set up to use this sales type."));
		} 
		else 
		{
			delete_sales_type($selected_id);
			display_notification(_('Selected sales type has been deleted'));
			$Mode = 'RESET';
		}
	} //end if sales type used in debtor transactions or in customers set up
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	unset($_POST);
}
//----------------------------------------------------------------------------------------------------

$result = get_all_sales_types();

start_form();
start_table("$table_style width=30%");

$th = array (_('Type Name'), _('Factor'), _('Tax Incl'), '','');
table_header($th);
$k = 0;
$base_sales = get_base_sales_type();

while ($myrow = db_fetch($result)) 
{
	if ($myrow["id"] == $base_sales)
	    start_row("class='overduebg'");
	else
	    alt_table_row_color($k);
	label_cell($myrow["sales_type"]);
	$f = number_format2($myrow["factor"],4);
	if($myrow["id"] == $base_sales) $f = "<I>"._('Base')."</I>";
	label_cell($f);	
	label_cell($myrow["tax_included"] ? _('Yes'):_('No'), 'align=center');	
 	edit_button_cell("Edit".$myrow['id'], _("Edit"));
 	edit_button_cell("Delete".$myrow['id'], _("Delete"));
	end_row();
}

end_table();
end_form();
display_note(_("Marked sales type is the company base pricelist for prices calculations."), 0, 0, "class='overduefg'");

//----------------------------------------------------------------------------------------------------

start_form();
 if (!isset($_POST['tax_included']))
	$_POST['tax_included'] = 0;
 if (!isset($_POST['base']))
	$_POST['base'] = 0;

start_table("$table_style2 width=30%");

if ($selected_id != -1) 
{

 	if ($Mode == 'Edit') {
		$myrow = get_sales_type($selected_id);
	
		$_POST['sales_type']  = $myrow["sales_type"];
		$_POST['tax_included']  = $myrow["tax_included"];
		$_POST['factor']  = number_format2($myrow["factor"],4);
	}
	hidden('selected_id', $selected_id);
} else {
		$_POST['factor']  = number_format2(1,4);
}

text_row_ex(_("Sales Type Name").':', 'sales_type', 20);
amount_row(_("Calculation factor").':', 'factor', null, null, null, 4);
check_row(_("Tax included").':', 'tax_included', $_POST['tax_included']);

end_table(1);

submit_add_or_update_center($selected_id == -1, '', true);

end_form();

end_page();

?>
