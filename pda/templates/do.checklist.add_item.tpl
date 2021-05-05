{*
11/06/2020 11:26 AM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu)  and link to module menu page
*}

{include file='header.tpl'}

<script>
{literal}

function add_item(){
	if(document.f_a['barcode'].value.trim() == "" || document.f_a['qty'].value.trim() == "") return false;
	
	document.f_a.submit();
}

function check_key(obj, event){
	var THIS = this;
	if(event.keyCode==13){
		if(obj.name == "barcode") document.f_a['qty'].focus();
		else{
			add_item();
		}
	}
}
{/literal}
</script>
<h1>
{$smarty.session.scan_product.name}
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id=do">DO</a><span>
<div style="margin-bottom:10px;"></div>
{include file='do.checklist.top_include.tpl'}<br /><br />



{if $err}
<div id=err><div class=errmsg><ul>
{foreach from=$err item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}

{if $item_added}<img src="/ui/approved.png" title="Item Added" border=0> Barcode has been added.<br /><br />{/if}
<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return false;">
	<input type="hidden" name="a" value="add_checklist_item" />
	<table width="100%" id="item_tbl" class="item_tbl" border="0" class="small">
		<tr>
			<td><b>Barcode</b></td>
			<td><input type="text" name="barcode" value="{$form.barcode}" size="20" onkeypress="check_key(this, event);" /></td>
		</tr>
		<tr>
			<td><b>Qty</b></td>
			<td><input type="text" name="qty" value="{$form.qty}" onchange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" onkeypress="check_key(this, event);" size="10" /></td>
		</tr>
		<tr>
			<td align="center" colspan="2"><input type="button" value="Add" onClick="add_item();" /></td>
		</tr>
	</table>
</form>

<font size="3">{$items_details.total_item|qty_nf} Item(s), {$items_details.total_pcs|qty_nf} pcs</font>
</div>
<script>
	document.f_a['barcode'].focus();
</script>

{include file='footer.tpl'}
