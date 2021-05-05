{*

04/17/2020 04:29 PM Sheila
- Modified layout to compatible with new UI.

10/23/2020 2:05 PM William
- Enhanced to addd new qty column and button "save".

11/04/2020 10:12 AM Sheila
- Fixed title, table and form css

12/17/2020 9:56 AM Andy
- Fixed spelling mistake.
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
	}else if(act=='save'){
		var qty_list = $('input.qty').get();
		for(var i=0; i < qty_list.length; i++){
			if(qty_list[i].value <= 0){
				alert('Invalid Qty');
				qty_list[i].focus();
				return false;
			}
		}
		document.f_a['a'].value = 'save';
	}else{
        document.f_a['a'].value = 'save_items';
	}
	document.f_a.submit();
}
{/literal}
</script>
<h1>
{$smarty.session.scan_product.name}
</h1>
<span class="breadcrumbs">
<a href="home.php">Back to home</a> > <a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$module_name}</a> {if $smarty.request.find_batch_barcode} > <a href="{$smarty.request.PHPSELF}?a=open&find_batch_barcode={$smarty.request.find_batch_barcode}">Back to search</a> {/if}
</span>
<div style="margin-bottom:10px;"></div>

{include file='batch_barcode.top_include.tpl'}<br><br>


{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}
<div class="stdframe" style="background:#fff">
{if $items}
    <div style="float:right;" class="btn_padding">
        <input type="button" value="Delete" onClick="submit_items('delete');" />
		<input type="button" value="Save" onClick="submit_items('save');" />
	</div>
	{count var=$items} item(s)

	<form name="f_a" method="post" onSubmit="return false;">
	<div style="clear:both;"></div>

	<input type="hidden" name="a" />
	<table width="100%" border="1" cellspacing="0" cellpadding="4">
	    <tr>
	    	<th>#</th>
	        <th width="20">DEL<br /><input type="checkbox" class="toggle_chx" /></th>
	        <th>ARMS Code</th>
	        <th>Description</th>
			<th>Qty</th>
	    </tr>
	    {foreach from=$items key=row item=r name=i}
	        <tr>
	        	<td>{$smarty.foreach.i.iteration}.</td>
	            <td><input type="checkbox" name="item_chx[{$r.id}]" class="item_chx" /></td>
	            <td>{$r.sku_item_code}</td>
	            <td>{$r.sku_description}</td>
				<td align="center">
					<input type="text" name="qty[{$r.id}]" class="qty items r" size="3" value="{$r.qty}" />
				</td>
	        </tr>
	    {/foreach}
	</table>
	</form>
	</div>

	<div style="float:right;" class="btn_padding">
        <input type="button" value="Delete" onClick="submit_items('delete');" />
		<input type="button" value="Save" onClick="submit_items('save');" />
	</div>
{else}
	No Item
{/if}

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
