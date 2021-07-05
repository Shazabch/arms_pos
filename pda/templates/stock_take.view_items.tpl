{*

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

2/24/2014 4:24 PM Andy
- Fix the variable bug. (sometime "loc" sometime "location").
- Added link to allow user travel to "item list" or "Scan".

04/17/2020 03:50 PM Sheila
- Modified layout to compatible with new UI.

9/21/2020 9:07 AM William
- Enhanced to show error message.

04/11/2020 3:24PM Rayleen
- Modified page style/layout. 
*}
{include file='header.tpl'}

<script type="text/javascript">
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
{$smarty.session.st.title}
</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="stock_take.php?a=show_scan">Stock Take</a> > <a href="stock_take.php?a=view_items">View Items</a></span>
<div style="margin-bottom: 10px"></div>


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
		<input type="button" id="submit_btn1" value="Save" onClick="submit_items('save');" />
	</div>
	{count var=$items} item(s)
	<form name="f_a" method="post" onSubmit="return false;">
	<div style="clear:both;"></div>

	<input type="hidden" name="a" />
	<table width="100%" border="1" class="small">
	    <tr>
	    	<th>#</th>
	        <th width="20">DEL<br /><input type="checkbox" class="toggle_chx" /></th>
	        <th>ARMS Code</th>
	        <th>Description</th>
	        <th>Qty<br />(pcs)</th>
	    </tr>
	    {foreach from=$items item=r name=i}
	        <tr>
	        	<td>{$smarty.foreach.i.iteration}.</td>
	            <td><input type="checkbox" name="item_chx[{$r.item_id}]" class="item_chx" /></td>
	            <td>
					{$r.sku_item_code}
					{if $r.mcode}
						<br /><font color="blue">{$r.mcode}</font>
					{/if}
					{if $r.artno}
						<br /><font color="brown">{$r.artno}</font>
					{/if}
				</td>
	            <td valign="top">{$r.description}</td>
	            <td><input type="text" name="qty[{$r.item_id}]" value="{$r.qty}" {if $r.doc_allow_decimal}size="6"{else}size="3"{/if} onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" class="r" /></td>
	        </tr>
	    {/foreach}
	</table>
	</form>
	
	<div style="float:right;" class="btn_padding">
        <input type="button" value="Delete" onClick="submit_items('delete');" />
		<input type="button" id="submit_btn2" value="Save" onClick="submit_items('save');" />
	</div>
{else}
	<div class="alert alert-danger">No Item</div>
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
</div>
</script>
{include file='footer.tpl'}
