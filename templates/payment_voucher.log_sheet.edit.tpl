{* 
4/19/2017 10:24 AM Khausalya 
- Enhanced changes from RM to use config setting. 

6/23/2020 03:24 PM Sheila
- Updated button css

*}


{include file=header.tpl}
{literal}
<script>
function do_save(){
	//if(check_cheque_no()){
	document.f_a.a.value="save";
	document.f_a.submit();	
	//}
}

function do_confirm(){
	//if(check_a() && check_cheque_no()){
	if(check_a()){
		document.f_a.a.value="confirm";
		document.f_a.submit();	
	}
}

//checking duplicate cheque_no in javascript, temporary no used
function check_cheque_no(){
	$used=new Array();
	var c_no = $('tbl_log_sheet').getElementsByClassName('cheque');
    //alert(c_no.length);
    if(c_no.length){
    	j=0;
		for(var i=0;i<c_no.length;i++){
			if(trim(c_no[i].value)!=''){
				if(!$used[c_no[i].value]){
					$used[c_no[i].value]=1;
				}
				else{
					alert('Cheque No. already Exist in this payment Voucher');
					return false;
				}			
			}
		}
		return true;
	}
}


function check_a(){
	var obj = $('tbl_log_sheet').getElementsByClassName('submit_select');
	i=0;
	for(j=0;j<obj.length;j++){
		if (obj[j].checked){
			checkbox_id=obj[j].title;
			if($('cheque_no'+checkbox_id).value==''){
				i++;
			}
   		}
	}
	if(i>0){
		alert('Please enter cheque no for selected voucher(s).');
	}
	else{
		return true;
	}
}

function check_all(v){
	var obj = $('tbl_log_sheet').getElementsByTagName('input');
	for(j=0;j<obj.length;j++){
		if (obj[j].type=='checkbox'){
			obj[j].checked = v;
   		}
	}
	active_submit();
}

function active_submit(){
	var obj = $('tbl_log_sheet').getElementsByClassName('submit_select');
	for(j=0;j<obj.length;j++){
		if (obj[j].checked){
			$('confirm').style.display='';
			return;
   		}
   		else{
			$('confirm').style.display='none';	   
		}
	}
}

function go_next_focus(row_now){
	new_row=row_now+1;
	if($('cheque_no'+new_row)){
		$('cheque_no'+new_row).focus();
	}
	else{
		$('save_btn').focus();
	}
}

function assign_title(obj,obj2){
	obj2.innerHTML=obj.value;
}

</script>
<style>
.keyin{
	background:yellow;
}
</style>
{/literal}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">Cheque Issue Log Sheet</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<div class="alert alert-danger"><li> {$e}</li></div>
{/foreach}
</ul>
</div></div>
{/if}

<form name=f_a method=post>
<input type=hidden name=a>
<input type=hidden name=ls_no value="{$form.ls_no}">

<table border=0 cellspacing=0 cellpadding=4>
<tr>
<th width=90>Log Sheet No :</th>
<td><h3>{$form.ls_no}{if $form.p}/{$form.p}{/if} ({$form.voucher_branch}) </h3></td>
</tr>
</table>

<br>

<div class="table-responsive">
	<table id="tbl_log_sheet" class="tb report_table table mb-0 text-md-nowrap  table-hover">
		<thead class="bg-gray-100">
			<tr>
				<th nowrap>
				{if $form.log_sheet_status ne '3'}
				<input type=checkbox onclick="check_all(this.checked)">
				{/if}
				 No.</th>
				<th>Ref No.</th>
				<th>Cheque Date</th>
				<th width=550>Pay To</th>
				<th nowrap>Banker</th>
				<th>Cheque No.</th>
				<th>Amount ({$config.arms_currency.symbol})</th>
				<th nowrap>Cheque<br>Collect At</th>
				</tr>
				
		</thead>
		{section name=i loop=$items}
		{assign var=n value=$smarty.section.i.iteration}
		<tr>
		<td>
		{if $form.log_sheet_status ne '3'}
		<input type=checkbox id=submit{$n-1} title="{$n-1}"  name=submit[{$items[i].voucher_no}] class="submit_select" onchange="active_submit();">
		{/if}
		 {$n}.
		</td>
		<td>{$items[i].voucher_no}</td>
		<td>{$items[i].payment_date|date_format:$config.dat_format}</td>
		<td>{$items[i].issue_name|default:$items[i].vendor} {if $items[i].voucher_type eq '3'} / {$items[i].vendor}{/if}
		</td>
		<td align=center nowrap>{$items[i].bank}</td>
		<td style="border:1px padding:1px;" align=center id=td_{$n-1}>
		<span id="div_sort_{$n-1}" style="display:none;">{$items[i].cheque_no}</span>
		<input class="cheque" id="cheque_no{$n-1}" name=cheque_no[{$items[i].voucher_no}] value="{if !$items[i].status}Cancelled{else}{$items[i].cheque_no}{/if}" onchange="uc(this);go_next_focus({$n-1});assign_title(this,$('div_sort_{$n-1}'));" {if $items[i].log_sheet_status eq '3'}disabled{/if} {if !$items[i].status}readonly{/if}>
		</td>
		<td align=right>{$items[i].total_credit-$items[i].total_debit|number_format:2}</td>
		<td align=center>{$items[i].c_branch_code}</td>
		</tr>
		{/section}
		</table>
</div>
</form>
<br>

<p align="center">
<input type="button" class="btn btn-danger" value="Close" onclick="document.location='/payment_voucher.log_sheet.php'">

{if $form.log_sheet_status ne '3'}
<input name="bsubmit" class="btn btn-success" type=button id="save_btn" value="Save" onclick="do_save()">

<input type="button" id="confirm" value="Confirm" class="btn btn-success" onclick="do_confirm()">
{/if}
</p>

{include file=footer.tpl}

</html>
<script>
ts_makeSortable($('tbl_log_sheet'));
</script>
