{include file=header.tpl}
{literal}
<style>
.c0 {
	background:#eff;
}
.c1 {
	background:#efa;
}
.csunday {
	color:#f00;
}
</style>
{/literal}
<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

<form method=post class=form>

<b>Date</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}">&nbsp;&nbsp;&nbsp;&nbsp;

<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}">&nbsp;&nbsp;&nbsp;&nbsp;

{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> {dropdown name=branch_id all="-- All --" values=$branches selected=$smarty.request.branch_id key=id value=code}
{/if}

<p>{include file="category_autocomplete.tpl"}</p>

{*
<b>Department</b>
<select name="department_id">
<option value=0>-- All --</option>
{foreach from=$departments item=dept}
<option value={$dept.id} {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
{/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;

*}
<input type=submit name=submit value="{#SHOW_REPORT#}">
<br>Note: Report Maximum Show 1 Day
</form>
{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
{capture assign=hrcount}{count var=$hr}{/capture}
<table class="report_table small_printing" width=100%>
	<tr class=header>
	<th>Category</th>
	{foreach from=$hr key=h item=hrs}
		<th>{$hrs}</th>
	{/foreach}
	<th>Total</th>
	<th>AVG Hour Amount</th>
	</tr>

</table>
{/if}
{include file=footer.tpl}

