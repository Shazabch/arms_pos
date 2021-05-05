{*
10/11/2011 11:50:11 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

7/25/2012 5:08:43 PM yinsee
- add row number for items

04/17/2020 04:29 PM Sheila
- Modified layout to compatible with new UI.

9/21/2020 9:42 AM William
- Enhanced to show error message.

05/11/2020 1:38PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu)
	-Remove class small in table and added cellspacing and cellpadding

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
	}
	document.f_a.submit();
}
{/literal}
</script>
<h1>
{$smarty.session.scan_product.name}
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id=do">DO</a> </span>
<div style="margin-bottom:10px;"></div>
{include file='do.top_include.tpl'}<br><br>

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
	<table width="100%" border="1" cellpadding="4" cellspacing="0">
	    <tr>
	    	<th>#</th>
	        <th width="20">DEL<br /><input type="checkbox" class="toggle_chx" /></th>
	        <th>ARMS Code</th>
	        <th>Description</th>
	        <th>Qty<br />(pcs)</th>
	    </tr>
	    {assign var="no" value=1}
	    {foreach from=$items item=r name=i}
	        <tr>
	        	<td>{$smarty.foreach.i.iteration}.</td>
	            <td><input type="checkbox" name="item_chx[{$r.id}]" class="item_chx" /></td>
	            <td>{$r.sku_item_code}</td>
	            <td>{$r.sku_description}</td>
	            <td><input type="text" name="item_qty[{$r.id}]" value="{$r.pcs}" size="{if $r.doc_allow_decimal}6{else}3{/if}" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td>
	        </tr>
	    {/foreach}
	</table>
	</form>
	
	<div style="float:right;" class="btn_padding">
        <input type="button" value="Delete" onClick="submit_items('delete');" />
		<input type="button" value="Save" onClick="submit_items('save');" />
	</div>
{else}
	No Item
{/if}
<br style="clear:both">
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
