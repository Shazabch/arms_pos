{*
23/9/2019 11:38 AM William 
- Added new module Purchase Order.

04/17/2020 04:16 PM Sheila
- Modified layout to compatible with new UI.

9/18/2020 5:07 PM William
- Enhanced to show error message.

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

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id=po">{$module_name}</a> > <a href="po.php?a=open">Back to Search</a></span>
<div style="margin-bottom: 10px"></div>

{include file='po.top_include.tpl'}<br><br>

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
		<thead>
	    <tr>
	    	<th>#</th>
	        <th width="20">DEL<br /><input type="checkbox" class="toggle_chx" /></th>
	        <th>Description</th>
			{if $sessioninfo.branch_id eq 1 && $items[0].branch_code}
				<th>Branch</th>
			{/if}
	        <th>Qty<br />(pcs)</th>
			<th>Foc<br />(pcs)</th></th>
	    </tr>
		</thead>
	    {assign var="no" value=1}
	    {foreach from=$items item=r name=i}
	        <tr>
	        	<td valign="top">{$smarty.foreach.i.iteration}.</td>
	            <td valign="top"><input type="checkbox" name="item_chx[{$r.id}]" class="item_chx" /></td>
	            <td valign="top">{$r.sku_description}</td>
				{if $sessioninfo.branch_id eq 1 && $r.branch_code}
				<td valign="top">
					<table>
						{if $r.branch_code}
							{foreach from=$r.branch_code item=b_code}
								<tr><td>{$b_code}</td></tr>
							{/foreach}
						{/if}
					</table>
				</td>
				{/if}
				<td valign="top">
					<table>
					{if $r.multi_bid}
						{foreach from=$r.multi_bid item=bid}
							<tr><td><input type="text" name="item_qty[{$r.id}][{$bid}]" value="{$r.qty_pcs.$bid}" size="{if $r.doc_allow_decimal}6{else}3{/if}" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td></tr>
						{/foreach}
					{else}
						<tr><td><input type="text" name="qty_loose[{$r.id}]" value="{$r.qty_loose}" size="{if $r.doc_allow_decimal}6{else}3{/if}" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td></tr>
					{/if}
					</table>
				</td>
				
				<td valign="top">
					<table>
					{if $r.multi_bid}
						{foreach from=$r.multi_bid item=bid}
							<tr><td><input type="text" name="foc_qty[{$r.id}][{$bid}]" value="{$r.foc_pcs.$bid}" size="{if $r.doc_allow_decimal}6{else}3{/if}" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td></tr>
						{/foreach}
					{else}
						<tr><td><input type="text" name="foc_loose[{$r.id}]" value="{$r.foc_loose}" size="{if $r.doc_allow_decimal}6{else}3{/if}" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td></tr>
					{/if}
					</table>
				</td>
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