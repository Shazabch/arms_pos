{*
8/17/2011 5:53:44 PM Andy
- Fix counter collection adjustment add coupon not working.

8/19/2011 10:20:55 AM Andy
- Fix change payment type bugs.

12/11/2012 5:22 PM Andy
- Add checking to payment type to show adjust payment list.

2/5/2013 3:18 PM Fithri
- add adjusted payment receipt can revert to old payment type

06/09/2016 14:30 Edwin
- Reconstruct on 'add_payment_type' and 'del_payment_type'

5/4/2017 10:34 AM Qiu Ying
- Bug fixed on when key in receipt no and keep entering multiple times, the receipt no will show multiple times.

2017-09-18 14:51 PM Qiu Ying
- Bug fixed on blocking users to click multiple time on save button
*}

{include file=header.tpl}
<script type="text/javascript">
{literal}
function check_cash_credit(obj, receipt_no, pos_id, pay_id)
{
	parms = Form.serialize(document.f_a)+'&a=ajax_change_cash_credit&receipt_no='+receipt_no+'&type='+obj.value+'&id='+pay_id;
	
	new Ajax.Request("counter_collection.php",{
		method:'post',
		parameters: parms,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);						
		},
		onSuccess: function (m) {
			if (parseFloat(m.responseText)>0) $('amount_'+pos_id+'_'+pay_id).value = parseFloat(m.responseText).toFixed(2);
//			else
//			alert(m.responseText);
		}
	});
	

}
function add_payment_type(pos_id) {
	pindex = document.f["paytype_index["+pos_id+"]"].value;
	pcount = document.f["add_payment_index["+pos_id+"]"].value;
	parms = Form.serialize(document.f_a)+'&a=ajax_add_payment_type&pos_id='+pos_id+'&pindex='+pindex+'&pcount='+pcount;
	This = this;
	
	// insert new row
	new Ajax.Request("counter_collection.php",{
		method:'post',
		parameters: parms,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);						
		},
		onSuccess: function (m) {
			if(/^\w+/.test(m.responseText))
				alert(m.responseText);
			else{
				new Insertion.After($('tr_'+This.pindex+'_'+pos_id), m.responseText);
				document.f["paytype_index["+pos_id+"]"].value = Number(This.pindex) + 1;
				document.f["add_payment_index["+pos_id+"]"].value = Number(This.pcount) + 1;
			}		
		}
	});
}

function del_payment_type(obj, pos_id) {
	var pp_count = document.f["paytype_index["+pos_id+"]"].value;
	
	if (pp_count == 1) {
		alert('At least ONE payment type is required.')
		return;
    }
	
	if (confirm('Are You Sure?'))
		obj.closest('tr').remove();	
		
	var table = $('paytype_table['+pos_id+']');
	var row = table.rows.length;	
	for(i=0; i<row; i++) {
		table.rows[i].setAttribute('id', 'tr_'+Number(i+1)+'_'+pos_id);
	}
	document.f["paytype_index["+pos_id+"]"].value = Number(i);
}

//function add_coupon(receipt_no, item_id)
//{
//	parms = Form.serialize(document.f_a)+'&a=ajax_add_coupon&receipt_no='+receipt_no;	
//
// 	// insert new row
//	new Ajax.Request("counter_collection.php",{
//		method:'post',
//		parameters: parms,
//	    evalScripts: true,
//		onFailure: function(m) {
//			alert(m.responseText);						
//		},
//		onSuccess: function (m) {
//			if (/^\w+/.test(m.responseText))
//			alert(m.responseText);
//			else
//			{
//				//var v = m.responseText.replace(/#COUNT#/,receipt_no.length+1);
//				new Insertion.Before($('tbl_row_footer-'+item_id),m.responseText);
//			}		
//		}
//	});
//
//}
//
//function del_coupon(id, receipt_no)
//{
//	if (confirm('Are You Sure?'))
//	Element.remove('tr_'+id+'_'+receipt_no);
//}

function add_receipt(){
	document.f_a.receipt_no.value = document.f_a.tmp_receipt_no.value;
	if (document.f_a.tmp_receipt_no.value != '') {
		document.f_a.tmp_receipt_no.value = "";
		var receipt_no = $$('input.receipt_no');
		for(i=0;i<receipt_no.length;i++) {
			if (document.f_a.receipt_no.value == receipt_no[i].value) {
				alert('Duplicate Receipt');
				return;
			}
		}

		parms = Form.serialize(document.f_a) + '&a=ajax_add_receipt_row';	
	
	 	// insert new row
		new Ajax.Request("counter_collection.php",{
			method:'post',
			parameters: parms,
		    evalScripts: true,
			onFailure: function(m) {
				alert(m.responseText);						
			},
			onSuccess: function (m) {
				if (/^\w+/.test(m.responseText))
				alert(m.responseText);
				else {
					//var v = m.responseText.replace(/#COUNT#/,receipt_no.length+1);
					new Insertion.Before($('tbl_footer'),m.responseText);
				}		
			}
		});
	}else {
		alert("Please insert receipt no.");
		document.f_a.receipt_no.focus();
	}	
}

function ajax_revert_to_original(pos_id,btn) {
	if (!confirm('Are you sure?')) return;
	btn.disable();
	
	var current_action = document.f.a.value;
	document.f.a.value = 'ajax_revert_to_original';
	document.f.revert_pos_id.value = pos_id;
	var parms = Form.serialize(document.f);
	
	new Ajax.Request("counter_collection.php",{
		method:'post',
		parameters: parms,
		evalScripts: true,
		onFailure: function(m) {alert(m.responseText);},
		onSuccess: function (m) {
			if(m.responseText == 'OK') {
				alert('Successfully revert');
				btn.up(1).remove(); //remove the row
			}
			else alert(m.responseText);
		}
	});
	
	document.f.a.value = current_action; //restore previous action
}

function check_duplicate_cash_type() {
	// get all receipt rows
	var tr_receipt_row_list = $$('tr.tr_receipt_row');
	// loop each receipt rows
	for(var i=0; i<tr_receipt_row_list.length; i++){
		// get all payment type <select>
		var pt = '';
		var sel_payment_type_list = $(tr_receipt_row_list[i]).getElementsBySelector('select.sel_payment_type');
		for(var j=0; j<sel_payment_type_list.length; j++){
			if (sel_payment_type_list[j].value == 'Cash' && pt == '') {
				pt = 'Cash';
            }else if (sel_payment_type_list[j].value == 'Cash' && pt == 'Cash') {
                alert("Only ONE cash type is allow in one receipt.");
				return;
            }
		}
	}
	$("btnSave").disable();
	document.f.submit();
}

{/literal}
</script>

{if $smarty.request.msg}{assign var=msg value=$smarty.request.msg}{/if}
<p align="center"><font color="red">{$msg}</font></p>
<h1>{$PAGE_TITLE}</h1>
<h3>{$BRANCH_CODE}({$counters[$smarty.request.counter_id].network_name}) - {$smarty.request.date|strtotime|date_format:"%d/%m/%Y"}</h3>

<form name="f_a" method="post" onsubmit="add_receipt(); return false;">
	<input name="counter_id" value="{$smarty.request.counter_id}" type="hidden">
	<input name="cashier_id" value="{$smarty.request.cashier_id}" type="hidden">
	<input name="date" value="{$smarty.request.date}" type="hidden">
	<input name="s" value="{$smarty.request.s}" type="hidden">
	<input name="e" value="{$smarty.request.e}" type="hidden">
	<b>Receipt No</b> <input type="text" id="tmp_receipt_no" name="tmp_receipt_no"> <input type="submit" value="Add">
	<input type="hidden" id="receipt_no" name="receipt_no" >
</form>
<br>
<form name="f" method="post" onsubmit="check_duplicate_cash_type(); return false;">
	<input name="counter_id" value="{$smarty.request.counter_id}" type="hidden">
	<input name="cashier_id" value="{$smarty.request.cashier_id}" type="hidden">
	<input name="date" value="{$smarty.request.date}" type="hidden">
	<input name="a" value=save_change_payment type="hidden">
	<input name="s" value="{$smarty.request.s}" type="hidden">
	<input name="e" value="{$smarty.request.e}" type="hidden">
	<input type="hidden" name="revert_pos_id" value="0" />
	<table class="report_table" cellpadding="4" cellspacing="1" border="0">
		<tr class="header">
			<th>Receipt No</th>
			<th>Type</th>
			<th>Original</th>
		</tr>
		{assign var=i value=0}
		{foreach name=i from=$all_items key=pos_id item=items}
			{include file=counter_collection.change_payment.row.tpl}
		{/foreach}
		<tfoot id="tbl_footer"></tfoot>
	</table>
	<p align="center"><input id="btnSave" name="bsubmit" type="submit" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;"></p>
</form>
{include file=footer.tpl}
