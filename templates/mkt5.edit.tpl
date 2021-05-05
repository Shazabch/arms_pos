{include file=header.tpl}
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
.st_block th { background:#efffff; padding:4px;}
.st_block .lastrow th { background:#f00; color:#fff;}
.st_block .title { background:#e4efff; color:#00f;  }
.st_block input { color:#00f;border:1px solid #fff; margin:0;padding:0;text-align:center; }
.st_block input:hover { border:1px solid #00f; }
.st_block input.focused { border:1px solid #fec; background:#ffe; }
.st_block input[disabled] { color:#000;border:none;background:transparent; }
.st_block input[readonly] { color:#00f;border:none;background:transparent; }
</style>

<script>
function do_save()
{
	document.f_a.a.value='save';
	if(check_a()) document.f_a.submit();
}
function do_confirm()
{
	if(!confirm('Are you sure want to confirm this MKT?')){
        return;
	}
	document.f_a.a.value='confirm';
	if(check_a()) document.f_a.submit();
}

function recalc_row(id)
{
	var sstr = '['+id+']';
	var el = document.f_a.elements;
	
	el['offers[tcost]'+sstr].value = round2(el['offers[qty]'+sstr].value*el['offers[cost]'+sstr].value);
	el['offers[tsell]'+sstr].value = round2(el['offers[qty]'+sstr].value*el['offers[propose_selling]'+sstr].value);
	el['offers[mkup]'+sstr].value = round2(el['offers[tsell]'+sstr].value - el['offers[tcost]'+sstr].value);
	el['offers[mkup_pct]'+sstr].value = round2(el['offers[mkup]'+sstr].value/el['offers[tcost]'+sstr].value*100);
}

function check_a()
{
	return true;
}
</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>

<form name=f_a method=post>
<input type=hidden name=a value=open>
<input type=hidden name=date_id value="{$date_id}">
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
</tr><tr>
	<th nowrap>Department</th>
	<td>
		{$mkt0.department}
	</td>
</tr>

</table>
</div>
<br>

{if $mkt0.mkt5_status ne '1'}
{assign var=readonly value=''}
{assign var=disable value=''}
{else}
{assign var=readonly value='readonly'}
{assign var=disable value='disabled'}
{/if}
<h3>Offer Items &nbsp;({$date_id})</h3>
<table width="100%" cellpadding=0 cellspacing=0 border=0 class=st_block>
<tr>
<th colspan=3>&nbsp;</td>
{foreach name=j from=$branches item=branch key=i}{/foreach}
<th colspan="{$smarty.foreach.j.total*2}">Cost</td>
<th colspan="{$smarty.foreach.j.total*2}">Sell</td>
<th colspan="{$smarty.foreach.j.total*2}">Limit</td>
<th colspan="{$smarty.foreach.j.total*2}">A-Kad</td>
<th>&nbsp;</td>
</tr>

<tr>
<th>&nbsp;</td>
<th>Code</td>
<th>Description</td>
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th colspan=2>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th colspan=2>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th colspan=2>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th colspan=2>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
<th>Remarks</td>
</tr>

{foreach from=$mkt4.offers item=offer key=o}
<tr>
<td>
<input type="checkbox" name="offers[{$o}][check]" value="1" {$disable} {if $form.offers.$o.check!=''}checked{/if}>
</td>
<td align=left>{$offer.sku_item_code}&nbsp; </td>
<td NOWRAP align=left>{$offer.description}&nbsp; </td>

{foreach from=$branches item=branch key=b}
<td align=center colspan=2 class=keyin>
<input type=text size=5 onchange="mfz(this)" name=offers[{$o}][{$b}][cost] value="{$form.offers.$o.$b.cost|default:$mkt4.offers.$o.$b.cost}" {$readonly} class=keyin></td>
{/foreach}

{foreach from=$branches item=branch key=b}
<th align=center><input disabled size=5 value="{$mkt4.offers.$o.$b.selling}"></td>
<td align=center class=keyin>
<input type=text size=5 onchange="mfz(this)" name=offers[{$o}][{$b}][sell] value="{$form.offers.$o.$b.sell|default:$mkt4.offers.$o.$b.propose_selling|default:$mkt4.offers.$o.$b.selling}" {$readonly} class=keyin></td>
{/foreach}

{foreach from=$branches item=branch key=b}
<th align=center><input disabled size=3 value="{$mkt4.offers.$o.$b.limit}"></td>
<td align=center class=keyin><input type=text size=3 name=offers{$o}][{$b}][limit] {$readonly} value="{$form.offers.$o.$b.limit|default:$mkt4.offers.$o.$b.limit}" class=keyin></td>
{/foreach}

{foreach from=$branches item=branch key=b}
<th align=center>
<input type="checkbox" DISABLED {if $mkt4.offers.$o.$b.member!=''}checked{/if}></td>
<td align=center><input type="checkbox" {$disable} name=offers[{$o}][{$b}][member] {if $form.offers.$o.$b.member!='' or $mkt4.offers.$o.$b.member!=''}checked{/if}></td>
{/foreach}

<td>
<textarea rows="1" cols="20" name=offers[{$o}][remark] {$readonly}>{if $form.offers.$o.check!=''}{$form.offers.$o.remark}{/if}</textarea>
</td>
</tr>
{/foreach}
</table>
<br><br>


<h3>Brand Discount &nbsp;({$date_id})</h3>
<table width="100%" cellpadding=0 cellspacing=0 border=0 class=st_block>
<tr>
<th colspan=3>&nbsp;</td>
{foreach name=j from=$branches item=branch key=i}{/foreach}
<th colspan={$smarty.foreach.j.total*2}>Discount/Mechanic</td>
<th colspan={$smarty.foreach.j.total*2}>Limit</td>
<th colspan={$smarty.foreach.j.total*2}>A-Kad</td>
<th>&nbsp;</td>
</tr>

<tr>
<th>&nbsp;</td>
<th>Brand id</td>
<th>Brand</td>
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th colspan=2>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th colspan=2>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
{foreach from=$branches item=branch key=i}
	{foreach item="curr_branch" from=$db_branch}
		{if $curr_branch.code==$i}
<th colspan=2>{$curr_branch.report_prefix}</td>
		{/if}
	{/foreach}
{/foreach}
<th>Remarks</td>
</tr>

{foreach from=$mkt4.brands item=brand key=br}
<tr>
<td align=center>
<input type="checkbox" name="brands[{$br}][check]" {$disable} value="1" {if $form.brands.$br.check!=''}checked{/if}>
</td>
<td align=left>{$br}&nbsp; </td>
<td NOWRAP align=left>{$brand.brand}&nbsp; </td>

{foreach from=$branches item=branch key=b}
<th align=center>
<input disabled size=5 value="{$mkt4.brands.$br.$b.discount_or_mechanic}"></td>
<td align=center class=keyin>
<input type=text {$readonly} class=keyin size=5 name=brands[{$br}][{$b}][discount_or_mechanic] value="{$form.brands.$br.$b.discount_or_mechanic|default:$mkt4.brands.$br.$b.discount_or_mechanic}"></td>
{/foreach}

{foreach from=$branches item=branch key=b}
<th align=center><input disabled size=3 value="{$mkt4.brands.$br.$b.limit}"></td>
<td align=center class=keyin><input class=keyin type=text size=3 {$readonly} name=brands[{$br}][{$b}][limit] value="{$form.brands.$br.$b.limit|default:$mkt4.brands.$br.$b.limit}"></td>
{/foreach}

{foreach from=$branches item=branch key=b}
<td align=center><input type="checkbox" DISABLED {if $mkt4.brands.$br.$b.member!=''}checked{/if}></td>
<td align=center><input type="checkbox"  {$disable} {if $mkt4.brands.$br.$b.member!='' or $form.brands.$br.$b.member!=''}checked{/if}></td>
{/foreach}
<td>
<textarea rows="1" cols="40" name=brands[{$br}][remark] {$readonly}>{if $form.brands.$br.check!=''}{$form.brands.$br.remark}{/if}</textarea>
</td>
</tr>
{/foreach}
</table>
</form>
<br>

<p id=submitbtn align=center>
{if $mkt5_privilege.MKT5_EDIT.$branch_id and ($mkt5.status eq '2' or !$mkt5.status) and !$mkt5.approved}
<input name=bsubmit type=button value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save()">&nbsp;&nbsp;&nbsp;
{/if}
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/mkt5.php'">
</p>
{include file=footer.tpl}
<script>
{if $mkt5.approved or !$mkt5_privilege.MKT5_EDIT.$branch_id or ($mkt5.status>0 and $mkt5.status ne '2')}
Form.disable(document.f_a);
{/if}
</script>

