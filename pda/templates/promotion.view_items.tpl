{*

04/17/2020 04:28 PM Sheila
- Modified layout to compatible with new UI.

04/11/2020 3:24PM Rayleen
- Modified page style/layout. 
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
{$smarty.session.scan_product.name}
</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id=promotion">{$module_name}</a></span>
<div style="margin-bottom: 10px"></div>


{include file='promotion.top_include.tpl'}<br><br>
<div class="stdframe" style="background:#fff">
{if $items}
	<div style="float:right;" class="btn_padding">
		<input type="button" value="Delete" onClick="submit_items('delete');" />
		{*<input type="button" id="submit_btn1" value="Save" onClick="submit_items('save');" />*}
	</div>
	{if $total_rows>$records_per_page}
		Showing items <b>{$start_row}-{$end_row}</b> from total of <b>{$total_rows}</b> items.<br />
		<b>Page :&nbsp;&nbsp;</b>
		{foreach from=$page_list item=p}
		{if $p eq $page}
		{$p}&nbsp;
		{else}
		<a href="?a=view_items&page={$p}">{$p}&nbsp;</a>
		{/if}
		{/foreach}
	{/if}
	<form name="f_a" method="post" onSubmit="return false;">
	<div style="clear:both;"></div>

	<input type="hidden" name="a" />
	<table width="100%" border="1" class="small">
		<tr>
			<th>#</th>
			<th width="20">DEL<br /><input type="checkbox" class="toggle_chx" /></th>
			<th>ARMS Code</th>
			<th>Description</th>
		</tr>
		{foreach from=$items item=r name=i}
		<tr>
			<td>{$smarty.foreach.i.iteration+$start_row-1}.</td>
			<td><input type="checkbox" name="item_chx[{$r.id}]" class="item_chx" /></td>
			<td>{$r.sku_item_code}</td>
			<td>{$r.description}</td>
		</tr>
		{/foreach}
	</table>
	</form>
	
	<div style="float:right;" class="btn_padding">
		<input type="button" value="Delete" onClick="submit_items('delete');" />
		{*<input type="button" id="submit_btn2" value="Save" onClick="submit_items('save');" />*}
	</div>
{else}
	No Item
{/if}
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
