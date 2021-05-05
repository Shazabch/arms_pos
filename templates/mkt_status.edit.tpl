{if !$check.approval_screen}
{include file=header.tpl}
{/if}
{literal}
<style>
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
.st_block input[disabled] { color:#000;border:none;background:transparent; }
.st_block input[readonly] { color:#00f;}
</style>

<script>
function do_confirm(){
	if(!confirm('Are you sure want to confirm this MKT?')){
        return;
	}
	document.f_a.a.value='confirm';
	document.f_a.submit();
}

/*
function do_approve(){
	document.f_a.a.value='approve';
	document.f_a.reason.value='Approved';
	document.f_a.submit();
}

function do_reject()
{
	var s = prompt('Enter Reason:');
	if (s==null)
	{
	    return false;
	}
	document.f_a.reason.value=s;
	document.f_a.a.value='reject';
	document.f_a.submit();
}
*/

</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

{include file=approval_history.tpl}

<form name=f_a method=post>
<input type=hidden name=a value=open>
<input type=hidden name=reason>
<input type=hidden name=id value="{$mkt0.id}">
<input type=hidden name=dept_id value="{$check.dept_id}">
<input type=hidden name=approvals value="{$check.approvals}">
<input type=hidden name=approval_history_id value="{$check.approval_history_id}">
{assign var=branch_id value=$sessioninfo.branch_id}

<div class=stdframe style="background:#fff">
<table cellspacing=0 cellpadding=4 border=0 class=tl>
<tr>
	<td colspan=2 class=small>Created: {$mkt0.added}, Last Update: {$mkt0.last_update}</td>
</tr>
<tr>
	<th nowrap>Participating Branches</th>
	<td>
	{foreach from=$branches item=branch key=i}
	<img src=/ui/checked.gif> {$i}
	{/foreach}
	</td>
</tr><tr>
	<th>Promotion Title</th>
	<td>{$mkt0.title}</td>
</tr><tr>
	<th nowrap>Promotion Period</th>
	<td>
		{$mkt0.offer_from|date_format:'%d/%m/%Y'} to {$mkt0.offer_to|date_format:'%d/%m/%Y'}
	</td>
</tr><tr>
	<th nowrap>Submit Due Date</th>
	<td>
		{$mkt0.submit_due_date_4|date_format:'%d/%m/%Y'}
	</td>
</tr>
<tr>
	<th>Publish Date </th>
	<td>
{section name=x start=1 loop=6}
{assign var=x value=$smarty.section.x.iteration}
{if $mkt0.publish_dates[$x]!=''}
<img align=absbottom src="ui/calendar.gif" id="b_p_date[{$x}]" title="Select Date">{$mkt0.publish_dates[$x]}&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
{/section}
	</td>
</tr>
<tr>
	<th nowrap>Attachments</th>
	<td>
		{foreach from=$mkt0.attachments.name key=idx item=fn}
		{if $fn}<img src=/ui/icons/attach.png align=absmiddle> <a href="javascript:void(window.open('{$image_path}{$mkt0.filepath[$idx]}'))">{$fn}</a> &nbsp;&nbsp; {/if}
		{/foreach}
	</td>
</tr><tr>
	<th nowrap>Promotion<br>Period Remark</th>
	<td>
		{$mkt0.remark|nl2br}
	</td>
</tr>
</table>
</div>
<br>


{foreach from=$form key=f item=f_item}
<h3>Offer Items &nbsp;({$f})</h3>
<table width="100%" cellpadding=0 cellspacing=0 border=0 class=st_block>
<tr>
<th colspan=3>&nbsp;</td>
{foreach name=j from=$branches item=branch key=i}{/foreach}
<th colspan={$smarty.foreach.j.total}>Sell</td>
<th colspan={$smarty.foreach.j.total}>Limit</td>
<th colspan={$smarty.foreach.j.total}>A-Kad</td>
<th>&nbsp;</td>
</tr>

<tr>
<th>Code</td>
<th>Description</td>
<th>Department</td>
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
<th>Remarks</td>
</tr>

{assign var=x value=0}
{foreach from=$mkt4.offers item=offer key=o}
{if $f_item.$o.check==1}
{assign var=x value=1}
<tr>
<td align=center>{$offer.sku_item_code}&nbsp;</td>
<td NOWRAP align=center>{$offer.description}</td>
<td align=center>{$f_item.$o.dept}</td>
{foreach from=$branches item=branch key=b}
<td align=center>{$form.$f.$o.$b.sell | default:$mkt4.offers.$o.$b.selling}&nbsp;</td>
{/foreach}
{foreach from=$branches item=branch key=b}
<td align=center>{$form.$f.$o.$b.limit | default:$mkt4.offers.$o.$b.limit}&nbsp;</td>
{/foreach}
{foreach from=$branches item=branch key=b}
<td align=center><input type="checkbox"  DISABLED {if $mkt4.offers.$o.$b.member!='' || $form.$f.$o.$b.member!=''}checked{/if}></td>
{/foreach}
<td>
<textarea rows="1" cols="20" name=offers[{$o}][remark] readonly>{$f_item.$o.remark}</textarea>
</td>
</tr>
{/if}
{/foreach}
</table>
{if $x<=0}
<table width='100%' class=st_block ><td align=center>No Records Found</td></table>
{/if}


<h3>Brand Discount &nbsp;({$f})</h3>
<table width="100%" cellpadding=0 cellspacing=0 border=0 class=st_block>
<tr>
<th colspan=3>&nbsp;</td>
{foreach name=j from=$branches item=branch key=i}{/foreach}
<th colspan={$smarty.foreach.j.total}>Discount/Mechanic</td>
<th colspan={$smarty.foreach.j.total}>Limit</td>
<th colspan={$smarty.foreach.j.total}>A-Kad</td>
<th>&nbsp;</td>
</tr>

<tr>
<th>Brand id</td>
<th>Brand</td>
<th>Department</td>
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
<th>Remarks</td>
</tr>
{assign var=x value=0}
{foreach from=$mkt4.brands item=brand key=br}
{if $f_item.$br.check==1}
{assign var=x value=1}
<tr>

<td align=center>{$br}&nbsp;</td>
<td NOWRAP align=center>{$brand.brand}</td>
<td align=center>{$f_item.$br.dept}&nbsp;</td>
{foreach from=$branches item=branch key=b}
<td align=center>{$form.$f.$br.$b.discount_or_mechanic|default:$mkt4.brands.$br.$b.discount_or_mechanic}&nbsp;</td>
{/foreach}
{foreach from=$branches item=branch key=b}
<td align=center>{$form.$f.$br.$b.limit|default:$mkt4.brands.$br.$b.limit}&nbsp;</td>
{/foreach}
{foreach from=$branches item=branch key=b}
<td align=center><input type="checkbox" DISABLED {if $mkt4.brands.$br.$b.member!='' || $form.$f.$br.$b.member!=''}checked{/if}></td>
{/foreach}
<td>
<textarea rows="1" cols="20" name=brands[{$br}][remark] readonly>{$f_item.$br.remark}</textarea>
</td>
</tr>
{/if}
{/foreach}
</table>
{if $x<=0}
<table width='100%' class=st_block><td align=center>No Records Found</td></table>
{/if}
<br><br><br>
{/foreach}

<p align=center>
{if $check.status eq '1' and $check.approved==0 and $check.approval_screen and $check.is_approval and $mkt5_privilege.MKT5_APPROVAL.$branch_id}
<input type=button value="Approve" style="background-color:#f90; color:#fff;" onclick="do_approve()">
<input type=button value="Reject" style="background-color:#f90; color:#fff;" onclick="do_reject()">
{/if}

{if $check.status ne '1' and $smarty.request.a ne 'view' and $mkt5_privilege.MKT5_EDIT.$branch_id and ($mkt4.offers or $mkt4.brands)}
<input type=button value="Confirm" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_confirm()">
{/if}

{if !$check.approval_screen}
<input type=button value="Back" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/mkt5.php'">
{/if}
</p>
{if !$check.approval_screen}
{include file=footer.tpl}
{/if}
<script>

</script>

