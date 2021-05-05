{*
3/13/2013 10:42 AM Justin
- Bug fixed on showing icons not in one row.
- Bug fixed on the "delete" icon does not show properly.

11/04/2020 10:12 AM Sheila
- Fixed title, table and form css

11/05/2020 11:50 AM Sheila
- Fixed breadcrumbs

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['find_batch_barcode'].value=='') return false;
	
	return true;
}

function delete_confirmation(){
	if(!confirm("Are you sure want to delete?")) return false;
	else return true;
}

function toggle_bb_list(){

	if($('#batch_barcode_list').get(0).style.display == "none") $('#batch_barcode_list').get(0).style.display = "";
	else $('#batch_barcode_list').get(0).style.display = "none";
}
{/literal}
</script>

<h1>
OPEN BATCH BARCODE
&nbsp;
</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$module_name}</a></span>
<div style="margin-bottom: 10px"></div>

<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return check_form();">
<p align="center">
	<span><h4>Search Batch Barcode</h4></span>
	Batch Barcode No.
	<input type="text" name="find_batch_barcode" class="txt-width-50" onChange="mi(this);" value="{$smarty.request.find_batch_barcode}" />
	<input type="submit" class="btn btn-primary" value="Enter" />
	<br />
	<span style="color:red;">
	    {if $err}
	        <ul>
	        {foreach from=$err item=e}
	            <li>{$e}</li>
	        {/foreach}
	        </ul>
	    {/if}
	</span>
</p>
</form>
</div>
{if !$smarty.request.find_batch_barcode && $bb_list}
	<a onclick="toggle_bb_list();" style="cursor:pointer;">Show/Hide Batch Barcode List</a>
{/if}

<div id="batch_barcode_list" {if !$smarty.request.find_batch_barcode || !$bb_list}style="display:none;"{/if}>
	<table width="100%" border="1" cellspacing="0" cellpadding="4">
		<tr>
			<th>&nbsp;</th>
			<th>ID</th>
			<th>Total Items</th>
		</tr>
		{foreach from=$bb_list item=r}
			<tr>
				<td nowrap>
					<a href="{$smarty.server.PHP_SELF}?a=change_batch_barcode&id={$r.id}&branch_id={$r.branch_id}&find_batch_barcode={$smarty.request.find_batch_barcode}"><img src="/ui/ed.gif" border="0" title="Open" /></a>&nbsp;
					<a href="{$smarty.server.PHP_SELF}?a=delete_batch_barcode&id={$r.id}&branch_id={$r.branch_id}&find_batch_barcode={$smarty.request.find_batch_barcode}" onclick="return delete_confirmation();"><img src="/ui/del.gif" border="0" title="Delete" /></a>
				</td>
				<td>#{$r.id}</td>
				<td align="right">{$r.total_items}</td>
			</tr>
		{/foreach}
	</table>
</div>

<script>
{literal}
document.f_a['find_batch_barcode'].select();
{/literal}
</script>
{include file='footer.tpl'}
