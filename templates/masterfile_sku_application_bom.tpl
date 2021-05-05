{*
11/7/2019 9:00 AM William
- Enhanced to added new Sku Type.

6/23/2020 03:30 PM Sheila
- Updated button css
*}
{include file=header.tpl}
{literal}
<script>

function do_submit(){
	if (empty(document.f_a.category_id, "You must select Department")){
	    return false;
	}
	if (empty(document.f_a.brand_id, "You must select Brand")){
	    return false;
	}
	if(empty(document.f_a.sku_type, "You must select Sku Type")){
		return false;
	}
	document.f_a.submit();
}

</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>

<div id=show_last>
{if $smarty.request.t eq 'completed'}
<img src=/ui/approved.png align=absmiddle> SKU ID#{$smarty.request.sku_id} generated. 
{/if}
</div>

<form name=f_a method=post>
<input type=hidden name=a value="save">

<div class="stdframe" style="background:#fff">
<h4>General Information</h4>
Only one SKU per Brand per Department is allowed. To create BOM content, use the <a href="/bom.php">BOM Editor</a> under Master File.
{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}
<table border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>Department</b></td>
<td>
	<select name="category_id">
	<option value="">-- Select --</option>
	{section name=i loop=$dept}
	<option value={$dept[i].id} {if $form.category_id eq $dept[i].id}selected{/if}>{$dept[i].description}</option>
	{/section}
	</select>
</td>
</tr>

<tr>
<td><b>Brand</b></td>
<td>
	<select name="brand_id">
	<option value="">-- Select --</option>
	{section name=i loop=$brand}
	<option value={$brand[i].id} {if $form.brand_id eq $brand[i].id}selected{/if}>{$brand[i].description}</option>
	{/section}
	</select>
</td>
</tr>

<tr>
	<td><b>Remark</b></td>
	<td><input name=remark size=20 value="New Application BOM" readonly></td>
</tr>

<tr>
<td><b>Sku Type</b></td>
<td>
	<select name="sku_type">
		<option value="">-- Select --</option>
		{section name=i loop=$sku_type_list}
		<option value={$sku_type_list[i].code} {if $form.sku_type eq $sku_type_list[i].code}selected{/if}>{$sku_type_list[i].description}</option>
		{/section}
	</select>
</td>
</tr>

</table>
</div>

<p align=center>
<input type=button class="btn btn-primary" value="Submit Application" onclick="do_submit();">
</p>

</form>
{include file=footer.tpl}
