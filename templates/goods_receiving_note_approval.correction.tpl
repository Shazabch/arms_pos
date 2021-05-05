{* 
9/23/2010 5:34:46 PM Justin
- Added prompt out window for user to do reconcile.
- Matched item will be presented "Strike" on the entire row.
- Added a Clear All button on the prompt out window to clear all strike rows.
- Added save button to allow user save content instead of confirm.
- Enabled prompt out window for reconcile that able to draggable.

11/10/2010 12:00:43 PM Justin
- Modified the reconcile function to have following changes:
  -> Added focus on amount input whenever user press enter.
  -> Removed the message alert when amount matched.
  -> Customized the reconcile window become individual form.
  -> Moved the Reconcile and Clear buttons and place on top of the item's table.
  
12/8/2010 12:36:54 PM Justin
- Fixed the bugs not to strike all the same amount items during reconcile.

4/5/2011 11:58:05 AM Justin
- Added remarks for selling price highlight in red.

4/13/2011 5:46:30 PM Justin
- Added new notes for the different selling prices which highlight in differnt colors.

2/22/2017 4:40 PM Justin
- Enhanced to show "Excluded GST" message for PO amount while it is under GST.
*}

{include file=header.tpl}
<script>
{literal}
function do_reject()
{
	document.f_a.reject_comment.value = '';
	var p = prompt('Enter reason to reject:');
	if (p.trim()=='' || p==null) return;
	document.f_a.reject_comment.value = p;
	if (confirm('Press OK to Reject this GRN.'))
	{
	    document.f_a.a.value = "reject";
	    document.f_a.submit();
	}
}


function do_close()
{
	if (!confirm('Discard changes and close?'))
	{
	    return;
	}
	document.location = '/goods_receiving_note_approval.account.php';
}

function f_check()
{
	if (empty(document.f_a.acc_action, 'Account Action cannot be empty'))
	{
		return false;
	}
	
	if (document.f_a.dn_issued!=undefined)
	{
		if (document.f_a.dn_issued.checked)
		{
		    // verify DN number and amount
		    if (empty(document.f_a.dn_number, 'D/N Number cannot be empty'))
		    {
		        return false;
			}
		    if (empty(document.f_a.dn_amount, 'D/N Amount cannot be empty'))
		    {
		        return false;
			}
		}
		else
		{
		    // no D/N issued, ask for reason
			if (empty(document.f_a.dn_reason, 'D/N Reason cannot be empty'))
		    {
		        return false;
			}
		}
	}
	return true;
}

function strike_row(){
	var amt_strike = round(document.rcc.amt_strike.value, 2);

	if(!document.rcc.amt_strike.value){
		alert("You must key in amount to reconcile!");
		document.rcc.amt_strike.focus();
		return false;
	}

	var fields = $('tblist').getElementsByTagName("INPUT");
	var val = '';
	var parent_tr = '';
	//var row_count = 0;
	var rcc_status = '';
	var is_rcc = false;

	$A(fields).each(
		function (r,idx){

			if(r.name.indexOf("rcc_status[")==0){
				rcc_status = r.value;
			}
		
			if (r.name.indexOf("amt[")==0 && is_rcc == false){
				//row_count++;
				var amount = round(r.value, 2); 
				if(amount == amt_strike && rcc_status == 0){
					val = r.title.split(",");
					parent_tr = $(r).parentNode.parentNode;

					//if(confirm("Found matched SKU Item Code "+val[1]+" in Row ["+row_count+"], reconcile?")){
					$(parent_tr).setStyle({'text-decoration': 'line-through'});
					document.f_a['rcc_status['+val[0]+']'].value = 1;
					is_rcc = true;
				}
			}
		}
	);
	
	if(!parent_tr){
		alert("No Match Found.");
	}

	document.rcc.amt_strike.value = '';
	document.rcc.amt_strike.focus();
}

function clear_all_strikes(){
	if(!confirm("Are you sure want to clear all reconciles?")) return false;
	
	var fields = $('tblist').getElementsByTagName("INPUT");
	var parent_tr = '';

	$A(fields).each(
		function (r,idx){
			if (r.name.indexOf("amt[")==0){
				parent_tr = $(r).parentNode.parentNode;
				$(parent_tr).setStyle({'text-decoration': ''});
			}
			if (r.name.indexOf("rcc_status[")==0){
				r.value = 0;
			}
		}
	);
}

function curtain_clicked(){
	hidediv('reconcile_menu');
	document.rcc.amt_strike.value = '';
}
{/literal}
</script>
<form name="rcc" onsubmit="strike_row(); return false;">
<div id="reconcile_menu" style="display:none; padding:10px; background-color: #fff; border:4px solid #999; position:fixed; top:200px; left:200px;">
	<div class=small style="position:absolute; right:10px;">
		<a href="javascript:void(curtain_clicked())"><img src=ui/closewin.png border=0 align=absmiddle></a>
	</div>
	<div class="stdframe">
		<p>
			<h4>Reconcile Menu:</h4><br>
			Amount: <input type="text" size="10" name="amt_strike" id="amt_strike" style="text-align:right;">
		</p> 
		<p align="center" id="choices">
			<input type="submit" style="font:bold 14px Arial; background-color:#090; color:#fff;" value="Ok">
		</p>
	</div>
</div>
</form>
<form name=f_a method=post onsubmit="return f_check()">

<h1>GRN (Account Verification) for GRN{$form.id|string_format:"%05d"}</h1>

{include file=approval_history.tpl}

<div class="stdframe" style="background:#fff">
<h4>General Information</h4>

<input type=hidden name=a value="save_confirm">
<input type=hidden name=reject_comment value="">
<input type=hidden name=id value={$form.id}>
<input type=hidden name=doc_no value="{$grr.doc_no}">
<input type=hidden name=type value="{$grr.type}">
<input type=hidden name=approval_history_id value="{$form.approval_history_id}">
<input type=hidden name=approved value="{$form.approved}">

<table border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>GRN Amount</b></td><td><font class="hilite" color=red>{$form.amount|number_format:2}</font></td>
<td><b>Account Amount</b></td><td><font class="hilite" color=red>{$form.account_amount|number_format:2}</font></td>
<td><b>Invoice/DO No</b></td><td>{$form.account_doc_no}</td>
<tr>
<td><b>GRR No</b></td><td>GRR{$grr.grr_id|string_format:"%05d"}</td>
<td><b>GRR ID</b></td><td>#{$grr.grr_item_id}</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.u}</td>
</tr><tr>
<td><b>GRR Amount</b></td><td>{$grr.grr_amount|number_format:2}</td>
<td><b>Received Qty</b></td><td>Ctn:{$grr.grr_ctn|number_format} / Pcs:{$grr.grr_pcs|number_format}</td>
<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.rcv_u}</td>
</tr><tr>
<td><b>Department</b></td><td colspan=3>{$form.department|default:$grr.department}</td>
</tr><tr>
<td><b>Vendor</b></td><td colspan=3>{$grr.vendor}</td>
<td><b>Lorry No</b></td><td>{$grr.transport}</td>
</tr><tr>
<td width=100><b>Document Type.</b></td><td width=100><font color=blue>{$grr.type}</font></td>
<td width=100><b>Document No.</b></td><td width=100><font color=blue>{$grr.doc_no}</font></td>
{if $grr.type eq 'PO'}
<td width=100><b>PO Amount{if $grr.po_is_under_gst}<br />(Excluded GST){/if}</b></td><td width=100><font color=blue>{$grr.po_amount|number_format:2}</font></td>
<td width=100><b>Partial Delivery</b></td><td width=100><font color=blue>{if $config.use_grn_future}{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}{else}{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}{/if}</font></td>
{/if}
</tr>
{if $config.grn_have_tax}
	<tr>
	    <td><b>Tax</b></td>
	    <td>{$form.grn_tax|number_format:2} %</td>
	</tr>
{/if}
</table>
</div>

<br>
{if $smarty.request.a eq 'confirm_detail'}
<input type="button" value="Reconcile" style="font:bold 16px Arial; background-color:#4e387e; color:#fff;" onclick="showdiv('reconcile_menu'); document.rcc.amt_strike.focus();;">
<input type="button" style="font:bold 16px Arial; background-color:#900; color:#fff;" value="Clear All" onclick="clear_all_strikes();">
<div align=right style="margin-top:-1.5em;">
	<img src="ui/flag.png" align=absmiddle> <span class=hilite>Enter the correct INVOICE Quantity or Price if different from Received</span>
	{if $grr.type eq 'PO'}
		<br />
		<img src="ui/rq.gif" align=absmiddle> <span class=hilite>Selling highlighted in <font color="red"><b>RED</b></font> indicate the current S/P from SKU item is different with S/P from GRN item</span>
		<br />
		<img src="ui/rq.gif" align=absmiddle> <span class=hilite>Selling highlighted in <font color="blue"><b>BLUE</b></font> indicate current S/P from SKU item</span>
	{/if}
</div>
{/if}

<div id="tblist">
{include file=goods_receiving_note.view.list.tpl is_correction=1}
</div>

<p align=center>
{if $smarty.request.a eq 'confirm_detail'}
<input type="button" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="if (!confirm('Save and Close GRN?')) return false; document.f_a.a.value='save'; this.disabled=true;form.submit();">
<input type=button value="Reject" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_reject()">

<input type="button" value="Confirm" style="font:bold 20px Arial; background-color:#090; color:#fff;" onclick="if(!f_check()) return false; if (!confirm('Click OK to continue.')) return false; this.disabled=true;form.submit();">
{/if}

<input type="button" value="Close" style="font:bold 20px Arial; background-color:#09f; color:#fff;" onclick="do_close()">
</p>


</form>

<script>
{if $smarty.request.a eq 'confirm_detail'}
_init_enter_to_skip(document.f_a);
{else}
Form.disable(document.f_a);
{/if}
new Draggable('reconcile_menu');
</script>

{include file=footer.tpl}
