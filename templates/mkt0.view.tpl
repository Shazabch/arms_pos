{include file=header.tpl}
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
</style>
{/literal}


<h1>{$PAGE_TITLE} (MKT{$form.id|string_format:"%05d"})</h1>

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
	{if $form.branches[$br]}<img src=/ui/checked.gif> {$branch.code}{/if}
	{/foreach}
	</td>
</tr><tr>
	<th>Promotion Title</th>
	<td>{$form.title}</td>
</tr><tr>
	<th>Offer Period</th>
	<td>
		{$form.offer_from|date_format:'%d/%m/%Y'} to {$form.offer_to|date_format:'%d/%m/%Y'}
	</td>
</tr><tr>
	<th>Submit Due Date</th>
	<td>
		{$form.submit_due_date|date_format:'%d/%m/%Y'}
	</td>
</tr>
</table>
</div>

<h4>A&P Expenses</h4>
<table id=expenses_table class=st_block cellpadding=4 cellspacing=0 border=0>
<tr>
	<th>Material</th>
	<th>Production Cost</th>
	<th>Hanging Fee</th>
	<th>Distribution Fee</th>
	<th>Permit Fee</th>
</tr>
<tbody id=expenses_rows>
{foreach from=$form.expenses.material key=i item=dummy}
<tr>
	<td nowrap width=400>{$form.expenses.material[$i]|default:"&nbsp;"}</td>
	<td align=right>{$form.expenses.production_cost[$i]|default:"&nbsp;"}</td>
	<td align=right>{$form.expenses.hanging_fee[$i]|default:"&nbsp;"}</td>
	<td align=right>{$form.expenses.dist_fee[$i]|default:"&nbsp;"}</td>
	<td align=right>{$form.expenses.permit_fee[$i]|default:"&nbsp;"}</td>
</tr>
{/foreach}
</tbody>
</tbody>
</table>
<br>

<p id=submitbtn align=center>
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/mkt0.php'">
</p>

</form>


{include file=footer.tpl}
