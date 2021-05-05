{*

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

04/17/2020 04:29 PM Sheila
- Modified layout to compatible with new UI.

9/22/2020 9:06 AM William
- Enhanced to show error message.

11/04/2020 6:00 PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu) and link to module menu page
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
		$('#submit_btn1').attr('disabled', 'disabled');
		$('#submit_btn2').attr('disabled', 'disabled');
        document.f_a['a'].value = 'save_items';
	}
	document.f_a.submit();
}
{/literal}
</script>
<h1>
{$smarty.session.scan_product.name}
</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard </a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a> > {if $form.adj_no}<a href="adjustment.php?a=open&adj_no={$form.adj_no}">Back to search</a>  {/if}  {if $form.find_adjustment}<a href="adjustment.php?a=open&find_adjustment={$form.find_adjustment}">Back to search</a>{/if}
<div style="margin-bottom:10px;"></div>

{include file='adjustment.top_include.tpl'}<br><br>
{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}
<div class="stdframe" style="background:#fff;">
{if $items}
    <div style="float:right;" class="btn_padding">
        <input type="button" value="Delete" onClick="submit_items('delete');" />
		<input type="button" id="submit_btn1" value="Save" onClick="submit_items('save');" />
	</div>
	{count var=$items} item(s)
	<form name="f_a" method="post" onSubmit="return false;">
	<div style="clear:both;"></div>

	<input type="hidden" name="a" />
	<table width="100%" border="1" class="small" cellspacing="0">
	    <tr>
	    	<th rowspan="2">#</th>
	        <th rowspan="2" width="15">DEL<br /><input type="checkbox" class="toggle_chx" /></th>
	        <th rowspan="2">ARMS Code</th>
	        <th rowspan="2">Description</th>
	        <th colspan="2">Qty<br />(pcs)</th>
	    </tr>
		<tr>
			{if !$form.adj_type || $form.adj_type eq "+"}
				<th>(+)</th>
			{/if}
			{if !$form.adj_type || $form.adj_type eq "-"}
				<th>(-)</th>
			{/if}
		</tr>
	    {foreach from=$items item=r name=i}
	        <tr>
	        	<td>{$smarty.foreach.i.iteration}.</td>
	            <td>
					<input type="checkbox" name="item_chx[{$r.id}]" class="item_chx" />
					<input type="hidden" name="item[{$r.id}]" value="{$r.id}" />
				</td>
	            <td>{$r.sku_item_code}</td>
	            <td>{$r.sku_description}</td>
				{if !$form.adj_type || $form.adj_type eq "+"}
					<td align="right">
						<input type="text" name="p_item_qty[{$r.id}]" value="{if $r.qty > 0}{$r.qty}{/if}" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" class="r" />
					</td>
				{/if}
				{if !$form.adj_type || $form.adj_type eq "-"}
					<td align="right">
						<input type="text" name="n_item_qty[{$r.id}]" value="{if $r.qty < 0}{$r.qty|abs}{/if}" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" class="r" />
					</td>
				{/if}
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
