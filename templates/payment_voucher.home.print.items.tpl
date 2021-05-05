{*
4/5/2011 4:23:38 PM Justin
- Added choice for print cheque menu during print voucher tab which base on config['payment_voucher_print_cheque_choice'].
- Added new JS function to rename the print and update cheque button.

4/19/2017 11:55 AM Khausalya
- Enhanced changes from RM to use config setting. 
*}

{literal}
<script>
function active_print(){
	var obj = $('keyin_popup').getElementsByClassName('print_select');
	for(j=0;j<obj.length;j++){
		if (obj[j].checked){
			$('print_selected').disabled=false;
			return;
   		}
   		else{
			$('print_selected').disabled=true;	   
		}
	}
}

function check_all(v){
	var obj = $('keyin_popup').getElementsByTagName('input');
	for(j=0;j<obj.length;j++){
		if (obj[j].type=='checkbox'){
			obj[j].checked = v;
   		}
	}
	active_print();
}

function change_btn_name(ele){
	if(ele.checked == true){
		document.f_keyin.pu_btn.value = "Print Cheque/Update Cheque No";
	}else document.f_keyin.pu_btn.value = "Update Cheque No";
}
</script>
{/literal}
<div class=small style="position:absolute; right:20; text-align:right;float:right;">
<img src=ui/closewin.png border=0 align=absmiddle onclick=curtain_clicked();>
</div>

<!--START CHEQUE WITH LS---------------------------------------------------------------->
{if $form.type eq 'cheques_by_ls'}
{if $keyin}
<h3 align=center>Print all selected cheques</h3>
<div style="height:370px;overflow:auto;">
<table align=center cellpadding=1 cellspacing=0 border=0>
<tr>
<td width=80>&nbsp;</td>
<td>
<input type=checkbox onclick="check_all(this.checked)">Select/Unselect All</td>
</tr>
{section name=i loop=$keyin}
<tr>
<td width=80>Voucher No :</td>
<td><input type=checkbox name=print_items[{$keyin[i].voucher_no}] value="{$keyin[i].voucher_no}" onclick="active_print(this);" class="print_select">
{$keyin[i].voucher_no} ({$keyin[i].issue_name|default:$keyin[i].vendor})
</td>
</tr>
{if $keyin[i].cheque_no}
{assign var=have_printed value=1}
{/if}
{/section}
{if $have_printed && !$sessioninfo.privilege.PAYMENT_VOUCHER_EDIT}
<tr>
<td  width=80 align=left>Username :</td>
<td>
<input type=password name=u id=u style="background:yellow;">
</td>
</tr>
<tr>
<td  width=80 align=left>Password :</td>
<td>
<input type=password name=p id=p style="background:yellow;">
</td>
</tr>
{/if}
<br>
<tr>
<th colspan=2>Format : A4 Landscape</th>
</tr>
</table>
</div>

<p align=center>
<input type=hidden name=print_type value="{$form.type}">
<input type=button id=print_selected  value="Print Cheques" onclick="print_selected_cheques();">
</p>

{else}
<p align=center>- No record -</p>
{/if}
<!--END CHEQUE WITH LS ------------------------------------------------------------------>




<!--START CHEQUE ---------------------------------------------------------------------->
{elseif $form.type eq 'cheque'}
<script>
curtain(true);	
</script>
{if $keyin}
<h3 align=center>Print all selected cheques</h3>
<div style="height:370px;overflow:auto;">

<table align=center cellpadding=1 cellspacing=0 border=0>
{section name=i loop=$keyin}
<tr>
<td width=80 align=left>Voucher No :</td>
<td>
{$keyin[i].voucher_no} ({$keyin[i].issue_name|default:$keyin[i].vendor})
</td>
</tr>
{if $keyin[i].cheque_no}
{assign var=have_printed value=1}
{/if}
<input type=hidden name=print_items[{$keyin[i].voucher_no}] value="{$keyin[i].voucher_no}">
{/section}
{if $have_printed && !$sessioninfo.privilege.PAYMENT_VOUCHER_EDIT}
<tr>
<td  width=80 align=left>Username :</td>
<td>
<input type=password name=u id=u style="background:yellow;">
</td>
</tr>

<tr>
<td  width=80 align=left>Password :</td>
<td>
<input type=password name=p id=p style="background:yellow;">
</td>
</tr>
{/if}

<br>
<tr>
<th colspan=2>Format : A4 Landscape</th>
</tr>
</table>

</div>
<p align=center>
<input type=hidden name=print_type value="{$form.type}">
<input type=button value="Print" onclick="submit_print_list();">
<!--input type=button value="Back" onclick="select_print('{$form.type}');"-->
</p>

{else}
<p align=center>- No record -</p>
{/if}
<!--END CHEQUE ------------------------------------------------------------------------->

<!--START CHEQUE WITH DATE-------------------------------------------------------------->
{elseif $form.type eq 'cheque_date'}
<script>
curtain(true);	
</script>
{if $keyin}
<h3 align=center>Print all selected cheques</h3>
<div style="height:370px;overflow:auto;">

<table align=center cellpadding=1 cellspacing=0 border=0>

<tr>
<td width=80>&nbsp;</td>
<td>
<input type=checkbox onclick="check_all(this.checked)">Select/Unselect All</td>
</tr>

{section name=i loop=$keyin}
<tr>
<td width=80>Voucher No :</td>
<td><input type=checkbox name=print_items[{$keyin[i].voucher_no}] value="{$keyin[i].voucher_no}" onclick="active_print(this);" class="print_select">
{$keyin[i].voucher_no} ({$keyin[i].issue_name|default:$keyin[i].vendor})
</td>
</tr>
{if $keyin[i].cheque_no}
{assign var=have_printed value=1}
{/if}
<!--input type=hidden name=print_items[{$keyin[i].voucher_no}] value="{$keyin[i].voucher_no}"-->
{/section}
{if $have_printed && !$sessioninfo.privilege.PAYMENT_VOUCHER_EDIT}
<tr>
<td  width=80 align=left>Username :</td>
<td>
<input type=password name=u id=u style="background:yellow;">
</td>
</tr>

<tr>
<td  width=80 align=left>Password :</td>
<td>
<input type=password name=p id=p style="background:yellow;">
</td>
</tr>
{/if}

<br>

<tr>
<th colspan=2>Format : A4 Landscape</th>
</tr>

</table>
</div>
<p align=center>
<input type=hidden name=print_type value="{$form.type}">
<input type=button value="Print" id=print_selected onclick="submit_print_by_date();" disabled>
<!--input type=button value="Back" onclick="select_print_date('{$form.type}');"-->
</p>
{else}
<p align=center>- No record -</p>
{/if}
<!--END CHEQUE WITH DATE----------------------------------------------------------------->

<!--START VOUCHER------------------------------------------------------------------------>
{elseif $form.type eq 'voucher'}
<script>
curtain(true);	
</script>
{if $keyin}
<h3 align=center>Print all selected vouches</h3>
<div style="height:370px;overflow:auto;">

<table align=center cellpadding=1 cellspacing=0 border=0>
{section name=i loop=$keyin}
{if $keyin[i].voucher_type eq '2' && $BRANCH_CODE ne 'HQ'}
{else}
<tr>
<td width=80>Voucher No :</td>
<td>
{$keyin[i].voucher_no}  ({$keyin[i].issue_name|default:$keyin[i].vendor})
</td>
</tr>
<input type=hidden name=print_items[{$keyin[i].voucher_no}] value="{$keyin[i].voucher_no}">
{/if}
{/section}

<tr>
<td colspan=2>&nbsp;</td>
</tr>

<tr>
<td colspan=2>
<input type=checkbox id=vendor_copy name="print_vendor_copy" checked> Vendor's Copy
</td>
</tr>

<tr>
<td colspan=2>
<input type=checkbox id=branch_copy name="print_branch_copy" checked> Branch's Copy (Internal)
</td>
</tr>

<br>
<tr>
<th colspan=2>Format : A5 Landscape</th>
</tr>

</table>
</div>
<p align=center>
<input type=hidden name=print_type value="{$form.type}">
<input type=button value="Print" onclick="submit_print_list();">
<!--input type=button value="Back" onclick="select_print('{$form.type}');"-->
</p>
{else}
<p align=center>- No record -</p>
{/if}
<!--END VOUCHER------------------------------------------------------------------------->


<!--START VOUCHER CONTINUE VOUCHER DATE------------------------------------------------->
{elseif $form.type eq 'continue_voucher_date'}
<script>
curtain(true);	
</script>
{if $keyin}
<h3 align=center>Print all selected vouches</h3>
<div style="height:370px;overflow:auto;">

<table align=center cellpadding=1 cellspacing=0 border=0>
{section name=i loop=$keyin}
{if $keyin[i].voucher_type eq '2' && $BRANCH_CODE ne 'HQ'}
{else}
<tr>
<td width=80>Voucher No :</td>
<td>
{$keyin[i].voucher_no}  ({$keyin[i].issue_name|default:$keyin[i].vendor})
</td>
</tr>
<input type=hidden name=print_items[{$keyin[i].voucher_no}] value="{$keyin[i].voucher_no}">
{/if}
{/section}

<tr>
<td colspan=2>&nbsp;</td>
</tr>

<tr>
<td colspan=2>
<input type=checkbox id=vendor_copy name="print_vendor_copy" checked> Vendor's Copy
</td>
</tr>

<tr>
<td colspan=2>
<input type=checkbox id=branch_copy name="print_branch_copy" checked> Branch's Copy (Internal)
</td>
</tr>

<br>
<tr>
<th colspan=2>Format : A5 Landscape</th>
</tr>
</table>
</div>
<p align=center>
<input type=hidden name=print_type value="{$form.type}">
<input type=button value="Print" onclick="submit_print_by_date();">
<!--input type=button value="Back" onclick="select_print_date('voucher_date');"-->
</p>
{else}
<p align=center>- No record -</p>
{/if}
<!--END VOUCHER CONTINUE VOUCHER DATE--------------------------------------------------->



<!--START VOUCHER WITH DATE------------------------------------------------------------->
{elseif $form.type eq 'voucher_date'}
<script>
curtain(true);	
</script>
{if $keyin}
<h3 align=center>Print all selected vouches</h3>
<div style="height:370px;overflow:auto;">

<table align=center cellpadding=1 cellspacing=0 border=0>

<tr>
<td width=80>&nbsp;</td>
<td>
<input type=checkbox onclick="check_all(this.checked)">Select/Unselect All</td>
</tr>

{section name=i loop=$keyin}
{if $keyin[i].voucher_type eq '2' && $BRANCH_CODE ne 'HQ'}
{else}
<tr>
<td width=80>Voucher No :</td>
<td>
<input type=checkbox name=print_items[{$keyin[i].voucher_no}] value="{$keyin[i].voucher_no}" onclick="active_print(this);" class="print_select">
{$keyin[i].voucher_no}  ({$keyin[i].issue_name|default:$keyin[i].vendor})
</td>
</tr>
<!--input type=hidden name=print_items[{$keyin[i].voucher_no}] value="{$keyin[i].voucher_no}"-->
{/if}
{/section}
<tr>
<td colspan=2>&nbsp;</td>
</tr>

<tr>
<td colspan=2>
<input type=checkbox id=vendor_copy name="print_vendor_copy" checked> Vendor's Copy
</td>
</tr>

<tr>
<td colspan=2>
<input type=checkbox id=branch_copy name="print_branch_copy" checked> Branch's Copy (Internal)
</td>
</tr>

<br>
<tr>
<th colspan=2>Format : A5 Landscape</th>
</tr>
</table>
</div>
<p align=center>
<input type=hidden name=print_type value="{$form.type}">
<input type=button value="Print" id=print_selected onclick="submit_print_by_date();" disabled>
<!--input type=button value="Back" onclick="select_print_date('{$form.type}');"-->
</p>
{else}
<p align=center>- No record -</p>
{/if}
<!--END VOUCHER WITH DATE--------------------------------------------------------------->

<!--START KEYIN CHEQUE------------------------------------------------------------------>
{elseif $form.type eq 'keyin_cheque' || $form.type eq 'keyin_cheque_no'}
<script>
curtain(true);	
</script>
{if $keyin}
<h3 align=center>Keyin all selected cheques</h3>
<div style="height:370px;overflow:auto;">
<table cellpadding=2 cellspacing=0 border=0>
{section name=i loop=$keyin}
<tr>
<td width=220>
{$keyin[i].voucher_no} ({$config.arms_currency.symbol} {$keyin[i].total_credit-$keyin[i].total_debit|number_format:2})<br>
{$keyin[i].issue_name|default:$keyin[i].vendor}
</td>
<td>
{if $smarty.section.i.first}
<input type=text name=cheque_no[{$keyin[i].voucher_no}] id=item_1 value="{$keyin[i].cheque_no}" style="background:yellow;">
{else}
<input type=text name=cheque_no[{$keyin[i].voucher_no}] value="{$keyin[i].cheque_no}">
{/if}
</td>
</tr>
{if $keyin[i].cheque_no || $keyin[i].status eq '3'}
{assign var=have_printed value=1}
{/if}
<tr>
<td colspan=2>&nbsp;</td>
</tr>
<input type=hidden name=print_items[{$keyin[i].voucher_no}] value="{$keyin[i].voucher_no}">
{/section}

{if $have_printed && !$sessioninfo.privilege.PAYMENT_VOUCHER_EDIT && $form.type eq 'keyin_cheque_no'}
<tr>
<td  width=80 align=left>Username :</td>
<td>
<input type=password name=u id=u style="background:yellow;">
</td>
</tr>

<tr>
<td  width=80 align=left>Password :</td>
<td>
<input type=password name=p id=p style="background:yellow;">
</td>
</tr>
{/if}
</table>
</div>

<p align=center>
<input type=hidden name=print_type value="{$form.type}">

<input type=button value="Save/Update Cheque No" onclick="submit_keyin();">
<!--input type=button value="Save & Print Voucher" onclick="submit_keyin();"-->
</p>

{else}
<p align=center>- No record -</p>
{/if}
<!--END KEYIN CHEQUE-------------------------------------------------------------------->

<!--START KEYIN CHEQUE WITH DATE-------------------------------------------------------->
{elseif $form.type eq 'keyin_cheque_date'}
<script>
curtain(true);	
</script>
{if $keyin}
<h3 align=center>Keyin all selected cheques</h3>
<div style="height:370px;overflow:auto;">
<table cellpadding=2 cellspacing=0 border=0>
{section name=i loop=$keyin}
<tr>
<td width=220>
{$keyin[i].voucher_no} ({$config.arms_currency.symbol} {$keyin[i].total_credit-$keyin[i].total_debit|number_format:2})<br>
{$keyin[i].issue_name|default:$keyin[i].vendor}
</td>
<td>
{if $smarty.section.i.first}
<input type=text name=cheque_no[{$keyin[i].voucher_no}] id=item_1 value="{$keyin[i].cheque_no}" style="background:yellow;">
{else}
<input type=text name=cheque_no[{$keyin[i].voucher_no}] value="{$keyin[i].cheque_no}">
{/if}
</td>
</tr>

<tr>
<td colspan=2>&nbsp;</td>
</tr>
<input type=hidden name=print_items[{$keyin[i].voucher_no}] value="{$keyin[i].voucher_no}">
{/section}
</table>
</div>

<p align=center>
<input type=hidden name=print_type value="{$form.type}">
<input type=button value="Save & Update Cheque No" onclick="submit_keyin_by_date();">
</p>

{else}
<p align=center>- No record -</p>
{/if}
<!--END KEYIN CHEQUE WITH DATE--------------------------------------------------------->


<!--START SINGLE CHEQUE----------------------------------------------------------------->
{elseif $form.type eq 'single_cheque'}
<script>
curtain(true);	
</script>
{if $keyin}
<h3 align=center>Keyin the selected cheque</h3>

<table cellpadding=2 cellspacing=0 border=0>
<tr>
<td width=220>Cheque # of {$keyin.voucher_no}<br>({$keyin.issue_name|default:$keyin.vendor}):</td>
<td>
<input type=text name=cheque_no id=cheque_no value="{$keyin.cheque_no}" style="background:yellow;">
</td>
</tr>

<!--tr>
<td>
Offset margin-top in CM
</td>
<td>
<input type=text name=top id=top>
</td>
</tr>

<tr>
<td>
Offset margin-left in CM
</td>
<td>
<input type=text name=left id=left>
</td>
</tr-->


{if $keyin.cheque_no && !$sessioninfo.privilege.PAYMENT_VOUCHER_EDIT}
<tr>
<td width=220>Username :</td>
<td>
<input type=password name=u id=u style="background:yellow;">
</td>
</tr>
<tr>
<td width=220>Password :</td>
<td>
<input type=password name=p id=p style="background:yellow;">
</td>
</tr>
{/if}
<br>
{if $config.payment_voucher_print_cheque_choice}
	<tr>
		<th colspan="2"><input type="checkbox" name="print_cheque" value="1" onclick="change_btn_name(this);" checked> Print Cheque</th>
	</tr>
{/if}
<br>
<tr>
<th colspan="2">Format : A4 Landscape</th>
</tr>

<input type=hidden name=print_items value="{$keyin.voucher_no}">
<input type=hidden name=tpl value="{$keyin.vvc_code}">
<input type=hidden name=branch_id value="{$keyin.branch_id}">
<input type=hidden name=voucher_branch_id value="{$keyin.voucher_branch_id}">
<input type=hidden name=id value="{$keyin.id}">
<input type=hidden name=action value="{$form.action}">
</table>
<p align=center>

<input type="button" value="Print Cheque/Update Cheque No" name="pu_btn" onclick="print_single_cheque();">

</p>

{else}
<p align=center>- No record -</p>
{/if}
<!--END SINGLE CHEQUE ------------------------------------------------------------------->


<!--START SUMMARY PRINT----------------------------------------------------------------->
{elseif $form.type eq 'summary' || $form.type eq 'summary_date'}
<script>
curtain(true);	
</script>
{if $keyin}
<h3 align=center>Print Log Sheet of below vouches</h3>
<div style="height:370px;overflow:auto;">

<table align=center cellpadding=1 cellspacing=0 border=0>
<tr>
<td width=80>&nbsp;</td>
<td>
<input type=checkbox onclick="check_all(this.checked)">Select/Unselect All</td>
</tr>

{section name=i loop=$keyin}
<tr>
<td width=80>Voucher No :</td>
<td>
<input type=checkbox name=print_items[{$keyin[i].voucher_no}] value="{$keyin[i].voucher_no}" onclick="active_print(this);" class="print_select">
{$keyin[i].voucher_no}  ({$keyin[i].issue_name|default:$keyin[i].vendor})
</td>
</tr>
{/section}

<br>

<tr>
<th colspan=2>Format : A5 Portrait</th>
</tr>

</table>
</div>
<p align=center>
<input type=hidden name=print_type value="{$form.type}">
<input type=hidden name=from_date value="{$form.from_date}">
<input type=hidden name=to_date value="{$form.to_date}">
<input type=hidden name=branch_id value="{$form.branch_id}">

<input type=button id=print_selected  value="Print" onclick="submit_print_list();">
</p>
{else}
<p align=center>- No record -</p>
{/if}
{/if}
<!--END SUMMARY PRINT------------------------------------------------------------------->

