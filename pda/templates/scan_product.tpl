{*
7/26/2011 1:06:26 PM Justin
- Added Back to Search feature.

6/14/2012 4:31:34 PM Justin
- Added new option "Add item when match one result".
- Enhanced to show item added info once added a item.

7/27/2012 4:42:34 PM Justin
- Added new function to allow item can scan by GRN barcoder.

7/31/2012 9:51:24 AM Justin
- Enhanced to disable the checkbox for "Add item that matched 1 result" when scan by GRN barcoder.

8/8/2012 3:57:12 PM Justin
- Modified the grn_barcode_type from 1 become 0 and 2 become 1.

9/7/2012 12:17 PM Justin
- Enhanced to tick barcode scan choice on ARMS Code while user is in GRN module.

9/7/2012 4:04 PM Andy
- Fix module won't auto tick on search ARMS Code if create new GRN or continue last GRN.

11/30/2012 2:52:PM Fithri
- PDA - GRA Module

12/14/2012 2:17:00 PM Fithri
- remove config checking on scan barcode

12/26/2012 12:01 PM Justin
- Enhanced to memorize the current barcode type.

3/7/2013 3:55 PM Justin
- Bug fixed on system allow user to tick on auto add item when user search item with GRN barcoder.
- Enhanced to allow user can tick on auto add item while they are searching item with ARMS Code, MCode or etc.

4/6/2018 6:06 PM Justin
- Enhanced to show/hide scan GRN barcoder base on config.

4/10/2018 11:03 AM Andy
- Fixed "Add item when match one result" checkbox cannot be tick when first time access.

9/22/2020 11:35 AM William
- Enhanced error message able to enter to next line.

11/04/2020 10:12 AM Sheila
- Fixed title, table and form css

11/04/2020 4:03 PM Rayleen
-Modified H1 title, add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['product_code'].value.trim()=='')   return false;
	return true;
}

function check_barcode_type(obj){
	if(obj.value == 0){
		document.f_a['auto_add_item'].disabled = true;
		document.f_a['auto_add_item'].checked = false;
	}else document.f_a['auto_add_item'].disabled = false;
	
	document.f_a['product_code'].focus();
}
{/literal}
</script>

<h1>
{$smarty.session.scan_product.name}
NEW BATCH
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$module_name}</a> {if $form.search_var} > <a href="{$smarty.server.PHP_SELF}?a=open&find_{$module_name|lower}={$form.search_var}">Back to search</a> {/if}</span>
<div style="margin-bottom:10px;"></div>

{if !$is_grn_module || $smarty.session.$module.barcode_type eq 0}
	{assign var=use_barcode_type value=0}
{/if}
{if $is_grn_module || $smarty.session.$module.barcode_type eq 1 || !$config.enable_grn_barcoder}
	{assign var=use_barcode_type value=1}
{/if}

<div class="stdframe" style="background:#fff">

{if $top_include}{include file=$top_include }{/if}
 



<form name="f_a" method="post" onSubmit="return check_form();">
{assign var=module value=$smarty.session.scan_product.type|strtolower}
<input type="hidden" name="type" value="{$smarty.session.scan_product.type}" />
<input type="hidden" name="a" value="show_scan_product" />

<p>
	Scan
	<input type="text" class="txt-width-50" name="product_code" />
	<input class="btn btn-primary" type="submit" value="Enter" />
	<br />
		{if $config.enable_grn_barcoder}
			<input type="radio" name="grn_barcode_type" value="0" {if $use_barcode_type eq 0}checked{/if} onChange="check_barcode_type(this);" /> GRN Barcoder
		<br />
		{/if}
		<input type="radio" name="grn_barcode_type" value="1" {if $use_barcode_type eq 1}checked{/if} onChange="check_barcode_type(this);" /> ARMS Code / MCode / Art.No / {$config.link_code_name}
		<br />
</p>
	<span style="color:red;white-space: normal!important;">
	    {if $err}
	        <ul>
	        {foreach from=$err item=e}
	            <li>{$e}</li>
	        {/foreach}
	        </ul>
	    {/if}
	</span>
<p>
	{*if $auto_add && !$err}<br /><img src="/ui/approved.png" title="Item Added" border=0> Item added<br /><br />{/if*}
	<input type="checkbox" value="1" name="auto_add_item" {if $auto_add}checked{/if} {if ($form.find_grn || !$is_grn_module) && $use_barcode_type ne 1 && !$is_grn_module}disabled{/if} /> Add item when match one result {if is_item_check}(Allow Duplicate){/if}
	{if $is_item_check}
		<br /><a href="batch_barcode.php?a=import_csv"> [Import SKU by CSV]</a>
	{/if}
	<br /><br />
	{if $smarty.session.scan_product.type == 'GRA'}
		Return Type:&nbsp;
		<select name=return_type>
		{foreach from=$return_type_list item=rt}
			<option>{$rt}</option>
		{/foreach}
		</select>
	{/if}
</p>
</form>
{if $btm_include}{include file=$btm_include}{/if}
</div>
<script>
{literal}
document.f_a['product_code'].select();
{/literal}
</script>
{include file='footer.tpl'}
