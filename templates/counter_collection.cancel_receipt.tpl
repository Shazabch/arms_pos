{*
3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

2/14/2017 10.40 AM Zhi Kai
- Remove counter id that will be shown under Receipt Detail.

4/16/2019 5:17 PM Justin
- Added page locking and show loading window while processing cancel receipt.
- Disabled the "Undo cancelled receipt" while the transaction contain eWallet payment.
- Disabled the cancel receipt function while the transaction contain eWallet payment that are currently not on the present date.

4/22/2021 2:36 PM Andy
- Enhanced Cancel Receipt to can search by receipt ref no.
- Added column "Receipt Ref No".
*}
{include file=header.tpl}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var LOADING = '<img src="/ui/clock.gif" />';
{literal}
function cancel_receipt(date,counter_id,pos_id,obj)
{
	var ewallet_type = $(obj).readAttribute('ewallet_type');
	var receipt_ref_no = $(obj).readAttribute('receipt_ref_no');

	var v = 0;
	if (obj.checked){
		if(ewallet_type != "" && !confirm("Transaction contains eWallet payment, eWallet will be refunded to customer once cancelled.\nAre you sure want to cancel it? (cannot be undo)")){
			obj.checked = false;
			return;
		}
		v = 1;
	}
	
	center_div('wait_popup');
	curtain(true,'curtain2');
	Element.show('wait_popup');
	new Ajax.Request(phpself,
	{
	    method: 'post',
	    parameters:{
			a: 'ajax_cancel_receipt',
			date: date,
			counter_id: counter_id,
			pos_id: pos_id,
			v:v,
			ewallet_type: ewallet_type,
			receipt_ref_no: receipt_ref_no
		},
		evalScripts: true,
		onSuccess: function (m) {
			alert(m.responseText);
			if (/^Error:/i.test(m.responseText)) obj.checked = !v;    	
			else if(ewallet_type != "") obj.disabled = true;
			
			Element.hide('wait_popup');
			curtain(false,'curtain2');
		}
	});

}

function trans_detail(counter_id,cashier_id,date,pos_id)
{
	curtain(true);
	center_div('div_item_details');
	
    $('div_item_details').show();
	$('div_item_content').update(LOADING+' Please wait...');

	new Ajax.Updater('div_item_content',phpself,
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			counter_id: counter_id,
			pos_id: pos_id,
			cashier_id: cashier_id,
			date: date
		}
	});
}

function curtain_clicked()
{
	curtain(false);
	hidediv('div_sales_details');
	hidediv('div_item_details');
}
{/literal}
</script>
{literal}
<style>
#div_sales_details,#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}
</style>
{/literal}
<!-- Transaction Details-->
<div id="div_sales_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="curtain_clicked()" src="/ui/closewin.png" /></div>
<div id="div_sales_content">
</div>
</div>
<!-- End of Transaction Details-->
<!-- Item Details -->
<div id="div_item_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="curtain_clicked()" src="/ui/closewin.png" /></div>
<div id="div_item_content">
</div>
</div>
<!-- End of Item Details-->
{if $smarty.request.msg}{assign var=msg value=$smarty.request.msg}{/if}
<p align=center><font color=red>{$msg}</font></p>
<h1><div style="float:left:width:200px">Cancel Receipt</div></h1>


<form class="form" name="f_a" method="post" style="position: relative;">

	<input name=counter_id value="{$smarty.request.counter_id}" type=hidden>
	<input name=date value="{$smarty.request.date}" type=hidden>
	<b>Receipt Ref No / Receipt No</b> <input name=receipt_no value="{$smarty.request.receipt_no}">
	<input name=fsubmit type=submit value="Search">

	<div style="position:absolute; top: 5; right: 10px;"><input type=button value="Back to Counter Collection" onclick="window.location ='/counter_collection.php?date_select={$smarty.request.date}';"></div>
</form>
{assign var=counter_id value=$smarty.request.counter_id}
{assign var=user_id value=$items[0].cashier_id}
{if $items}
<h1>Receipt Detail</h1>
<h3>{$counters.$counter_id.network_name} /  {$username.$user_id}</h3>

<!-- show loading panel -->
<div id="wait_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
	<p align="center">
		Please wait..<br /><br /><img src="ui/clock.gif" border="0" />
	</p>
</div>
<!-- End of show loading panel -->

<form name=f_b method=post>
<input name=a value="save_cancel_receipt" type=hidden>
<input name=date value="{$smarty.request.date}" type=hidden>
<input name=counter_id value="{$smarty.request.counter_id}" type=hidden>
<table class="tb" width=100% cellpadding=4 cellspacing=0 border=0>
<tr class=header style="background:#fe9">
<th>POS ID</th>
<th>Receipt No</th> 
<th>Receipt Ref No</th> 
<th>Pos Time</th>
<th>Date</th>
<th>Amount</th>
<th>Amount Tender</th>
<th>Amount Change</th>
<th>Cancel Status</th>
</tr>
{foreach from=$items item=item}
<tr>
<td>{$item.id}</td>
<td><a href="javascript:void(0)" onclick="trans_detail({$item.counter_id},{$item.cashier_id},'{$item.date}',{$item.id})">{receipt_no_prefix_format branch_id=$item.branch_id counter_id=$item.counter_id receipt_no=$item.receipt_no}</a></td>
<td>{$item.receipt_ref_no}</td>
<td>{$item.pos_time}</td>
<td>{$item.date}</td>
<td align=right>{$item.amount|number_format:2}</td>
<td align=right>{$item.amount_tender|number_format:2}</td>
<td align=right>{$item.amount_change|number_format:2}</td>
<td align=center><input type="checkbox" onclick="cancel_receipt('{$smarty.request.date}',{$smarty.request.counter_id},{$item.id},this)" ewallet_type="{$item.ewallet_type}" receipt_ref_no="{$item.receipt_ref_no}" {if $item.cancel_status}checked{/if} {if $item.ewallet_error}disabled{/if}>{if $item.ewallet_error}<img style="padding-bottom:5px;" src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="alert('{$item.ewallet_error_msg}');" />{/if}</td>
</tr>
{/foreach}
</table>
{*<p align=center><input type=submit value="{if $items[0].cancel_status == 1}Uncancel{else}Cancel{/if}" style="font:bold 20px Arial; background-color:#f90; color:#fff;"></p>*}
</form>
{else}
<p align=center>- No Data -</p>
{/if}
{include file=footer.tpl}
