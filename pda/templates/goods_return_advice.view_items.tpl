{*

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

4/24/2015 10:54 AM Justin
- Enhanced to capture Document No. and GST information.

7/21/2017 11:50 AM Justin
- Enhanced to keep sku_item_id and selling_price as hidden for save data usage.

8/7/2017 1:47 PM Justin
- Enhanced to store gst selling price onto the hidden field.

04/17/2020 04:28 PM Sheila
- Modified layout to compatible with new UI.

11/04/2020 3:38 PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu)  and link to module menu page
	-Add cellspacing in table
*}

{include file='header.tpl'}

<script>
{literal}

function change_row_color(ele){
    if($(ele).attr('checked')){
		$(ele).parent().parent().css('background-color','yellow');
	}else{
        $(ele).parent().parent().css('background-color','#fff');
	}
}

function submit_items(act){
	if(act=='delete'){
        // check selected item
		if($('input.item_chx:checked').get().length<=0){
			alert('Please checked at least one item.');
			return false;
		}
		if(!confirm('Click OK to confirm delete.')) return false;
		
        document.f_a['a'].value = 'delete_items';
	}else{
        document.f_a['a'].value = 'save_items';
		$('#submit_btn1').attr('disabled', 'disabled');
		$('#submit_btn2').attr('disabled', 'disabled');
	}
	document.f_a.submit();
}
{/literal}
</script>
<h1>
{if $smarty.session.scan_product.name}
$smarty.session.scan_product.name
{else}
Item List
{/if}
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a></span>
<div style="margin-bottom:10px;"></div>

{include file='goods_return_advice.top_include.tpl'}<br><br>

<div class="stdframe" style="background:#fff">
{if $items}
    <div style="float:right;" class="btn_padding">
        <input type="button" value="Delete" onClick="submit_items('delete');" />
		<input type="button" id="submit_btn1" value="Save" onClick="submit_items('save');" />
	</div>
	{count var=$items} item(s)
	<form name="f_a" method="post" onSubmit="return false;">
	<div style="clear:both;"></div>

	<input type="hidden" name="a" />
	<table width="100%" border="1" cellspacing="0">
	    <tr>
	    	<th>#</th>
	        <th width="20">DEL<br /><input type="checkbox" class="toggle_chx" /></th>
	        <th>ARMS Code</th>
	        <th>Description</th>
	        <th>Qty<br />(pcs)</th>
	        <th>Price</th>
	    </tr>
	    {foreach from=$items item=r name=i}
	        <tr>
	        	<td>{$smarty.foreach.i.iteration}.</td>
	            <td>
					<input type="checkbox" name="item_chx[{$r.id}]" class="item_chx" />
					<input type="hidden" name="sku_item_id[{$r.id}]" value="{$r.sku_item_id}" />
					<input type="hidden" name="selling_price[{$r.id}]" value="{$r.selling_price}" />
					<input type="hidden" name="gst_selling_price[{$r.id}]" value="{$r.gst_selling_price}" />
				</td>
	            <td>{$r.sku_item_code}</td>
	            <td>{$r.sku_description}</td>
	            <td><input type="text" name="item_qty[{$r.id}]" value="{$r.qty}" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" class="r" /></td>
	            <td><input type="text" name="item_price[{$r.id}]" value="{$r.cost}" size="10" onchange="this.value=float(round(this.value, {$config.global_cost_decimal_points}));" class="r" /></td>
	        </tr>
			<tr>
				<td align="right" nowrap colspan="6" style="padding:5px">
					Inv/DO No. <input type="text" name="doc_no[{$r.id}]" class="txt-width-30" value="{$r.doc_no}" /> 
					{if $form.is_under_gst}
						<br/><br/>&nbsp;&nbsp;
						GST Code
						<select class="txt-width-30" name="gst_id[{$r.id}]">
							{foreach from=$gst_list key=dummy item=gst}
								<option value="{$gst.id}" {if $r.gst_id eq $gst.id}selected{/if}>{$gst.code} ({$gst.rate|default:'0'}%)</option>
							{/foreach}
						</select>
						<input type="hidden" name="old_gst_id[{$r.id}]" class="txt-width-30" value="{$r.gst_id}" />
					{/if}
				</td>
			</tr>
	    {/foreach}
	</table>
	</form>
	
	<div style="float:right;" class="btn_padding">
        <input type="button" value="Delete" onClick="submit_items('delete');" />
		<input type="button" id="submit_btn2" value="Save" onClick="submit_items('save');" />
	</div>
{else}
	No Item
{/if}
<br style="clear:both;">
</div>
<script>
{literal}
    $('input.item_chx').click(function(){
        change_row_color($(this).get(0));
	});
	
	$('input.toggle_chx').click(function(){
		$('input.item_chx').attr('checked',$(this).attr('checked')).each(function(i){
			change_row_color($(this).get(0));
		});
	});
{/literal}
</script>
{include file='footer.tpl'}
