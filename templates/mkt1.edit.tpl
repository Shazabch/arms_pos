{if !$form.approval_screen}
{include file=header.tpl}
{/if}
{assign var=branch_id value=$form.branch_id|default:$sessioninfo.branch_id}
{literal}
<style>
.keyin{
	background-color:yellow;
}
.st_block {
	border-left:1px solid #ccc;
	border-top:1px solid #ccc;
}
.st_block td, .st_block th {
	border-right:1px solid #ccc;
	border-bottom:1px solid #ccc;
}
.st_block th { background:#efffff; padding:4px; }
.st_block .lastrow th { background:#f00; color:#fff;}
.st_block .title { background:#e4efff; color:#00f;  }
.st_block input { border:1px solid #fff; margin:0;padding:0; }
.st_block input:hover { border:1px solid #00f; }
.st_block input.focused { border:1px solid #fec; background:#ffe; }

.st_block input.readonly:hover{
	border:1px solid #fff;
	text-align:right;
}
.subtotal th, .subtotal td, .subtotal input {
	background:#fe9;
	font-weight:bold;
	text-align:left;
}
.subtotal input {
	border:none;
}
.total th, .total td, .total input {
	background:#f90;
	font-weight:bold;
	text-align:left;
}
.total input {
	border:none;
}

input[disabled]
{
	background:transparent;
	color:#000;
}
</style>

<script>
var n=0;
function add_row(){
	var new_row = $('expenses_rows').insertRow(-1);

	S = '<td><input class="keyin" size=60 name=expenses[material][]></td><td><input class="keyin r" onchange=miz(this);recalc_exp('+n+') size=6 name=expenses[qty][] ></td><td><input size=10 name=expenses[production_cost][] class="keyin r" onchange=mfz(this);recalc_exp('+n+')></td><td><input size=10 name=expenses[hanging_fee][] class="keyin r" onchange=mfz(this);recalc_exp('+n+')></td><td><input size=10 name=expenses[dist_fee][] class="keyin r" onchange=mfz(this);recalc_exp('+n+')></td><td><input size=10 name=expenses[permit_fee][] class="keyin r" onchange=mfz(this);recalc_exp('+n+')></td><td><input size=10 name=expenses[amount][] readonly class="readonly r"></td>';
	new_row.innerHTML=S.replace(/\[\]/g,"["+n+"]");
	n++;
}

/*
function do_approve()
{
    document.f_approve.reason.value = 'Approve';
	if (confirm('Press OK to Approve the MKT.'))
	{
	    document.f_approve.a.value = "approve";
	    document.f_approve.submit();
	}
}

function do_reject()
{
	document.f_approve.reason.value = '';
	var p = prompt('Enter reason to reject:');
	if (p.trim()=='' || p==null) return;
	document.f_approve.reason.value = p;
	if (confirm('Press OK to Reject the MKT.'))
	{
	    document.f_approve.a.value = "reject";
	    document.f_approve.submit();
	}
}
*/


function do_save()
{
	document.f_a.a.value='save';
	if(check_a()) document.f_a.submit();
}

function do_confirm()
{
	document.f_a.a.value='confirm';
	if(check_a()) document.f_a.submit();
}

function check_a()
{
	if (empty_or_zero(document.f_a.normal_sales, 'Normal Sales Target is invalid'))
	{
		return false;
	}
	if (empty_or_zero(document.f_a.normal_gp, 'Normal GP % is invalid'))
	{
		return false;
	}
	if (empty_or_zero(document.f_a.promo_sales, 'Promotion Sales Target is invalid'))
	{
		return false;
	}
	if (empty_or_zero(document.f_a.promo_gp, 'Promotion GP % is invalid'))
	{
		return false;
	}

	/*var e = $('contribution_table').getElementsByTagName('input');
	
	for(var i=0;i<e.length;i++){
	    if (/contrib_total\[\d+\]/.test(e[i].id)){
	        var lineid = e[i].name.substring(e[i].name.lastIndexOf('['));
			// if line's % is not zero, dept total must be 100%
			if (float(document.f_a.elements['dept_contribution[line]'+lineid].value) > 0)
	        {
				if (float(e[i].value) != 100){
				    alert('Line Total for ' + e[i].alt + ' is not equal to 100%');
				    e[i].focus();
				    return false;
				}
			}
			else
			{
			// if line's % is zero, dept total must be zero
				if (float(e[i].value) != 0) {
				    alert('Line Total for ' + e[i].alt + ' should be 0');
				    e[i].focus();
				    return false;
   				 }
   			}
  		}
 	}
	
	if ($('contrib_total').value!=100)
	{
	    alert('Total is not equal to 100%');
	    $('contrib_total').focus();
	    return false;
	}*/
	return true;
}

function recalc_st()
{
	stn = document.f_a.normal_sales.value;
	stng = document.f_a.normal_gp.value/100;
	stp = document.f_a.promo_sales.value;
	stpg = document.f_a.promo_gp.value/100;

	$('normal_gpamt').innerHTML = round2(stn * stng);
	$('promo_gpamt').innerHTML = round2(stp * stpg);
	$('inc_amt').innerHTML = stp - stn;
	$('inc_gp').innerHTML = round2((stp - stn) / stn * 100) + '%';
	$('expenses').innerHTML = round2(float(document.f_a.total_expenses.value) / stp * 100) + '%';
}

function recalc_contrib(el)
{
	var e = $('contribution_table').getElementsByTagName('input');
	var total = 0;
	
	for(var i=0;i<e.length;i++)	{
	    if (/dept_contribution\[line\]/.test(e[i].name)){
			total += float(e[i].value);
		}
	}
	if (total>100)
	{
		el.value='';
		alert('Warning: Total Contribution is over 100%');
	}
	else
		$('contrib_total').value = round2(total);
		

}

function recalc_dept_contrib(lineid,el)
{
	var sstr = '['+lineid+']';
	var e = $('contribution_table').getElementsByTagName('input');
	var total = 0;
	for(var i=0;i<e.length;i++)	{
		/*if(document.f_a.elements['dept_contribution[line]['+lineid+']'].value<=0){
			el.value='';
			alert('Warning: Total Contribution is low 0%');
			return false;
			break;
		}*/
		if (/dept_contribution\[dept\]/.test(e[i].name) && e[i].name.indexOf(sstr)>0)
			total += float(e[i].value);


	}
	if (total>100)
	{
		el.value='';
		alert('Warning: Total Contribution is over 100%');
	}
	else
		$('contrib_total'+sstr).value = round2(total);

}

function total_exp()
{
    var i = 0;
    var total = 0;
	while(document.f_a.elements['expenses[amount]['+i+']'])
	{
		total += float(document.f_a.elements['expenses[amount]['+i+']'].value);
		i++;
	}
	document.f_a.total_expenses.value = round2(total);
	$('expenses').innerHTML = round2((document.f_a.total_expenses.value) / stp * 100) + '%';
}

function recalc_exp(id)
{
	var sstr = '['+id+']';
	var unit_cost = (float(round(document.f_a.elements['expenses[production_cost]'+sstr].value,3)) +
	    float(document.f_a.elements['expenses[hanging_fee]'+sstr].value) +
	    float(document.f_a.elements['expenses[dist_fee]'+sstr].value) +
	    float(document.f_a.elements['expenses[permit_fee]'+sstr].value));
	document.f_a.elements['expenses[amount]'+sstr].value =
	    round2(document.f_a.elements['expenses[qty]'+sstr].value * unit_cost);
	    
    total_exp();
}
</script>
{/literal}

<h1>{$PAGE_TITLE} ({$form.current_branch}, MKT{$form.id|string_format:"%05d"})</h1>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<form name=f_a method=post>
<input type=hidden name=a value=save>
<input type=hidden name=approval_history_id value={$form.approval_history_id}>
<input type=hidden name=id value={$form.id}>
<input type=hidden name=mkt0_id value={$form.mkt0_id}>
<input type=hidden name=branch_id value={$form.branch_id|default:$smarty.request.branch_id}>

{include file=approval_history.tpl}

<div class=stdframe style="background:#fff">

<table cellspacing=0 cellpadding=4 border=0 class=tl>
<tr>
	<td colspan=2 class=small>Created: {$form.added}, Last Update: {$form.last_update}</td>
</tr>
<tr>
	<th nowrap>Participating Branches</th>
	<td>
	{foreach from=$branches item=branch}
	{assign var=br value=$branch.code}
	{if $form.branches[$br]}<img src=/ui/checked.gif> 
	{if $branch_id == $branch.id}<font color=red>{/if}
	{$branch.code}
	{if $branch_id == $branch.id}</font>{/if}
	{/if}
	{/foreach}
	</td>
</tr>
<tr>
	<th>Promotion Title</th>
	<td>{$form.title}</td>
</tr><tr>
	<th nowrap>Promotion Period</th>
	<td>
		{$form.offer_from|date_format:'%d/%m/%Y'} to {$form.offer_to|date_format:'%d/%m/%Y'}
	</td>
</tr><tr>
	<th nowrap>Submit Due Date</th>
	<td>
		{$form.submit_due_date_0|date_format:'%d/%m/%Y'}
	</td>
</tr>
<tr>
	<th>Publish Date </th>
	<td>
{section name=x start=1 loop=6}
{assign var=x value=$smarty.section.x.iteration}
{if $form.publish_dates[$x]!=''}
<img align=absbottom src="ui/calendar.gif" id="b_p_date[{$x}]" title="Select Date">{$form.publish_dates[$x]}&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
{/section}
	</td>
</tr>
<tr>
	<th nowrap>Attachments</th>
	<td>
		{foreach from=$form.attachments.name key=idx item=fn}
		{if $fn}<img src=/ui/icons/attach.png align=absmiddle>
		<a {if BRANCH_CODE eq 'HQ'}href="/{$form.filepath[$idx]}"{else}href="{$image_path}{$form.filepath[$idx]}"{/if} target="_blank">{$fn}</a> &nbsp;&nbsp; {/if}
		{/foreach}
	</td>
</tr><tr>
	<th nowrap>Promotion<br>Period Remark</th>
	<td>
		{$form.remark|nl2br}
	</td>
</tr>

<tr>
	<th valign=top>Sales Target</th>
	<td width=100%>
	    <table class="st_block" border=0 cellspacing=0 cellpadding=0>
	    <tr>
	    <th>Normal Forecast</th><td class="{if $smarty.request.a eq 'view' or $form.approval_screen}readonly r{else}keyin{/if}">
		<input size=8 class="{if $smarty.request.a eq 'view' or $form.approval_screen}readonly r{else}keyin{/if}" name=normal_sales onchange=mf(this);recalc_st() value="{$amount.normal_total|default:$form.normal_sales}"></td>
	    
	    <th>GP(%)</th><td class="{if $smarty.request.a eq 'view' or $form.approval_screen}readonly r{else}keyin{/if}"><input class="{if $smarty.request.a eq 'view' or $form.approval_screen}readonly r{else}keyin{/if}" size=2 name=normal_gp onchange=mf(this);recalc_st() value="{$form.normal_gp}">%</td>
	    <th>GP Amount</th>
	    <td width=100 id=normal_gpamt>&nbsp;</td>
	    </tr>
	    <tr>
	    <th>Target</th><td class="{if $smarty.request.a eq 'view' or $form.approval_screen}readonly r{else}keyin{/if}" ><input class="{if $smarty.request.a eq 'view' or $form.approval_screen}readonly r{else}keyin{/if}" size=8 name=promo_sales onchange=mf(this);recalc_st() value="{$amount.sales_total|default:$form.promo_sales}"></td>
	    
	    <th>GP(%)</th><td class="{if $smarty.request.a eq 'view' or $form.approval_screen}readonly r{else}keyin{/if}"><input class="{if $smarty.request.a eq 'view' or $form.approval_screen}readonly r{else}keyin{/if}" size=2 name=promo_gp onchange=mf(this);recalc_st() value="{$form.promo_gp}">%</td>
	    <th>GP Amount</th>
	    <td id=promo_gpamt>&nbsp;</td>
	    </tr>
	    <tr class="lastrow">
	    <th >Increased Amount</th>
	    <td id=inc_amt>&nbsp;</td>
	    <th>Increase %</th>
	    <td id=inc_gp>&nbsp;</td>
	    <th>Expenses %</th>
	    <td id=expenses>&nbsp;</td>
	    </table>
	</td>
</tr>
</table>
</div>

<!--h3>Department Contribution</h3-->
<!--table id=contribution_table class=st_block cellpadding=2 cellspacing=0 border=0>
<tr>
	<th>Line</th>
	<th>Department</th>
	<th>Contrib(%)</th>
</tr>
{section name=i loop=$departments}
{if $last_line ne $departments[i].line}
{if $last_line ne ''}
<tr class=subtotal>
    <th colspan=2>Line Total</th>
	<td><input size=5 id=contrib_total[{$lineid}] alt="{$last_line}" name="dept_contribution[line_total][{$lineid}]" value="{$form.dept_contribution.line_total.$lineid}" readonly></td>
</tr>
</tbody>
{/if}
{assign var=lineid value=$departments[i].line_id}
<tr>
<td colspan=2><a href="javascript:void(togglediv('tbd[{$lineid}]'))">{$departments[i].line} &darr;</a></td>
<td><input size=5 onchange="mf(this);recalc_contrib(this);" name=dept_contribution[line][{$lineid}] value="{$form.dept_contribution.line.$lineid}"></td>
</tr>
<tbody id=tbd[{$lineid}]>
{/if}
{assign var=deptid value=$departments[i].dept_id}
<tr>
	<td width=200>&nbsp;</td><td>{$departments[i].dept}</td>
	<td><input size=5 onchange="mf(this);recalc_dept_contrib({$lineid},this);" name=dept_contribution[dept][{$lineid}][{$deptid}] value="{$form.dept_contribution.dept.$lineid.$deptid}">
</tr>
{assign var=last_line value=$departments[i].line}
{/section}
<tr class=subtotal>
    <th colspan=2>Line Total</th>
	<td><input size=5 id=contrib_total[{$lineid}] alt="{$last_line}" name="dept_contribution[line_total][{$lineid}]" value="{$form.dept_contribution.line_total.$lineid}" readonly></td>
</tr>
</tbody>
<tr class=total>
	<th colspan=2>Total</th>
	<td><input size=5 id=contrib_total name="dept_contribution[total]" value="{$form.dept_contribution.total}" readonly></td>
</tr>
</table-->

<h3>A&P Expenses</h3>
- Rows without 'Material' column will not be saved.<br><br>
<table id=expenses_table class=st_block cellpadding=0 cellspacing=0 border=0>
<tr>
	<th>Material</th>
	<th>Qty</th>
	<th>Production<br>Cost</th>
	<th>Hanging Fee</th>
	<th>Distribution<br>Fee</th>
	<th>Permit Fee</th>
	<th>Amount</th>
</tr>
<tbody id=expenses_rows>
{foreach name=e from=$form.expenses.material key=i item=dummy}
<tr>
	<td><input readonly class="readonly" size=60 name=expenses[material][{$i}] value="{$form.expenses.material[$i]}"></td>
	<td><input class="{if $smarty.request.a eq 'view' or $form.approval_screen}readonly r{else}keyin{/if}" onchange=miz(this);recalc_exp({$i}) size=6 name=expenses[qty][{$i}] value="{$form.expenses.qty[$i]}"></td>
	<td><input readonly class="readonly r" size=10 name=expenses[production_cost][{$i}] value="{$form.expenses.production_cost[$i]}"></td>
	<td><input readonly class="readonly r" size=10 name=expenses[hanging_fee][{$i}] value="{$form.expenses.hanging_fee[$i]}"></td>
	<td><input readonly class="readonly r" size=10 name=expenses[dist_fee][{$i}] value="{$form.expenses.dist_fee[$i]}"></td>
	<td><input readonly class="readonly r" size=10 name=expenses[permit_fee][{$i}] value="{$form.expenses.permit_fee[$i]}"></td>
	<td><input readonly class="readonly r" size=10 name=expenses[amount][{$i}] value="{$form.expenses.amount[$i]}"></td>
</tr>
{/foreach}
<script>
n = {$smarty.foreach.e.iteration};
</script>
</tbody>
<tr>
	<td colspan=6 align=right><b>Total</b>&nbsp;&nbsp;</td>
	<td><input readonly class="readonly r" size=10 name=total_expenses value="{$form.total_expenses|string_format:"%.2f"}"></td>
</table>
{if $smarty.request.a ne 'view' and $mkt1_privilege.MKT1_EDIT.$branch_id and !$form.approval_screen}
<input type=button value="Add Extra" style="background:#f90;color:#fff" onclick="add_row()">
{/if}
</form>

{if $form.is_approval and $form.mkt1_status==1 and $form.approved==0 and $mkt1_privilege.MKT1_APPROVAL.$branch_id and $form.approval_screen}
<form name=f_approve>
<input type=hidden name=a>
<input type=hidden name=reason>
<input type=hidden name=id value={$form.id}>
<input type=hidden name=approvals value="{$form.approvals}">
<input type=hidden name=mkt0_id value={$form.mkt0_id}>
<input type=hidden name=branch_id value={$form.branch_id}>
<input type=hidden name=approval_history_id value="{$form.approval_history_id}">
</form>
{/if}

<p id=submitbtn align=center>

{if $form.is_approval and $form.mkt1_status==1 and $form.approved==0 and $form.approval_screen and $mkt1_privilege.MKT1_APPROVAL.$branch_id}
<input type=button value="Approve" style="background-color:#f90; color:#fff;" onclick="do_approve()">
<input type=button value="Reject" style="background-color:#f90; color:#fff;" onclick="do_reject()">
{/if}


{if $smarty.request.a ne 'view' and $mkt1_privilege.MKT1_EDIT.$branch_id and !$form.approval_screen}

<input name=bsubmit type=button value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save()" >

<input type=button value="Confirm" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_confirm()">
{/if}

{if !$form.approval_screen}
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/mkt1.php?branch_id={$branch_id}'">
{/if}

</p>

{if !$form.approval_screen}
{include file=footer.tpl}
{/if}

<script>
recalc_st();
//recalc_contrib();
{if $smarty.request.a eq 'view' or $form.approval_screen}
Form.disable(document.f_a);
{else}
_init_enter_to_skip(document.f_a);
_init_focus_input_class('#expenses_table input');
_init_focus_input_class('#contribution_table input');
{/if}
</script>

