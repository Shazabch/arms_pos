{*
05/11/2020 1:33PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu)
	
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
{/literal}
</script>
{assign var=search_var value=$smarty.session.do_picking_verification.search_var}
<h1>
{$form.do_title}
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id=do">{$module_name}</a> > <a href="{$smarty.server.PHP_SELF}?a=open&do_no={$search_var}">Back to search</a></span>
<div style="margin-bottom:10px;"></div>
{if $smarty.session.$module.barcode_type eq 0}
	{assign var=use_barcode_type value=0}
{/if}
{if $smarty.session.$module.barcode_type eq 1 || !$config.enable_grn_barcoder}
	{assign var=use_barcode_type value=1}
{/if}
<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return check_form();">
{assign var=module value=$smarty.session.scan_product.type|strtolower}
<input type="hidden" name="a" value="scan_item" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="branch_id" value="{$form.branch_id}" />

<p>
	Scan
	<input type="text" name="product_code" class="txt-width-50" />
	<input type="submit" value="Enter" class="btn btn-primary"/>
	<br />
		{if $config.enable_grn_barcoder}
			<input type="radio" name="grn_barcode_type" value="0" {if $use_barcode_type eq 0}checked{/if} /> GRN Barcoder
		<br />
		{/if}
		<input type="radio" name="grn_barcode_type" value="1" {if $use_barcode_type eq 1}checked{/if} /> ARMS Code / MCode / Art.No / {$config.link_code_name}
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
	<br />
</p>
</form>

{if $result}
	{if $result.not_found}
		<div align="center"><div style="color:white;padding:10px 85px;background:#ce0000;width:120px;border-radius: 5px;text-align:center;font-size:20px; font-weight:bold;">NOT FOUND</div></div>
	{elseif $result.data}
		<div align="center">
			<div style="color:white;padding:10px 85px;background:#27d321;width:120px;border-radius: 5px;text-align:center;font-size:16px; font-weight:bold;">FOUND</div>
			<br />
			<div class="large" style="margin-left:5px; margin-right:5px;" align="left"><b>{$result.data.description} <span style="color:red;">({$result.data.packing_uom_code|default:'EACH'})</span></b></div>
			<br />
			<table width="98%" border="1" cellspacing="0" cellpadding="0" class="large">
				<tr>
					<th>DO UOM</th>
					<th>CTN</th>
					<th>PCS</th>
				</tr> 
				<tr>
					<td align="center">{$result.data.uom_code}</td>
					<td align="center">{$result.data.ctn}</td>
					<td align="center">{$result.data.pcs}</td>
				</tr>
			</table>
		</div>
	{/if}
{/if}
</div>
<script>
{literal}
document.f_a['product_code'].select();
{/literal}
</script>
{include file='footer.tpl'}
