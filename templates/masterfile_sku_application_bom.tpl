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
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div id=show_last>
{if $smarty.request.t eq 'completed'}
<img src=/ui/approved.png align=absmiddle> SKU ID#{$smarty.request.sku_id} generated. 
{/if}
</div>

<form name=f_a method=post>
<input type=hidden name=a value="save">

<div class="stdframe card mx-3" >
	<div class="card-body">
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
	<b class="form-label mt-2">Department</b>
	<select class="form-control" name="category_id">
	<option value="">-- Select --</option>
	{section name=i loop=$dept}
	<option value={$dept[i].id} {if $form.category_id eq $dept[i].id}selected{/if}>{$dept[i].description}</option>
	{/section}
	</select>

</tr>

<tr>
<b class="form-label mt-2">Brand</b>
	<select class="form-control" name="brand_id">
	<option value="">-- Select --</option>
	{section name=i loop=$brand}
	<option value={$brand[i].id} {if $form.brand_id eq $brand[i].id}selected{/if}>{$brand[i].description}</option>
	{/section}
	</select>

</tr>

<tr>
		<b class="form-label mt-2">Remark</b>
		<input class="form-control" name=remark size=20 value="New Application BOM" readonly>
</tr>

<tr>
	<b class="form-label mt-2">Sku Type</b>
	<select class="form-control" name="sku_type">
		<option value="">-- Select --</option>
		{section name=i loop=$sku_type_list}
		<option value={$sku_type_list[i].code} {if $form.sku_type eq $sku_type_list[i].code}selected{/if}>{$sku_type_list[i].description}</option>
		{/section}
	</select>

</tr>

</table>
	</div>
</div>

<p align=center>
<input type=button class="btn btn-primary" value="Submit Application" onclick="do_submit();">
</p>

</form>
{include file=footer.tpl}
