{*

04/17/2020 04:29 PM Sheila
- Modified layout to compatible with new UI.

05/11/2020 3:01PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu), then link to module menu page
	-Added cellspacing in table
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

function save(){
	if(!confirm("Click 'OK' to save.")) return;
	document.f_a.submit();
}

function add_row(){
	var $tr = $('.temp_row');
    var $clone = $tr.clone();
    $clone.find(':text').val('');
	$clone.removeClass('temp_row');
	$clone.find(".tmp_row_no").removeClass().addClass('row_no');
	$clone.find(".tmp_item_chx").removeClass().addClass('item_chx');
	$('#item_tbl tr:last').after($clone);
	reset_row_no();
}

function reset_row_no(){
	var row_no = 0;
	$(".row_no").each(function() {
		row_no += 1;
		$(this).html(row_no+".");
	});
}

function delete_row(){
	if(!confirm("Are you sure want to delete?\nNOTE: this will delete the items permanently.")) return;
	document.f_a['a'].value = "delete_checklist_items";
	
	document.f_a.submit();
}
{/literal}
</script>
<h1>
{$smarty.session.scan_product.name}
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard </a> > <a href="home.php?a=menu&id=do">DO</a></span>
<div style="margin-bottom:10px;"></div>
{include file='do.checklist.top_include.tpl'}<br /><br />



{if $err}
<div id=err><div class=errmsg><ul>
{foreach from=$err item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}
<div class="stdframe" style="background:#fff">
<table style="display:none;">
	<tr class="temp_row">
		<td class="tmp_row_no"></td>
		<td><input type="checkbox" name="item_chx[]" class="tmp_item_chx" /></td>
		<td align="center"><input type="text" name="barcode[]" value="" size="20" /></td>
		<td align="center"><input type="text" name="qty[]" class="r" value="{$r.qty}" size="5" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td>
	</tr>
</table>

{if $items}

<div style="float:right;" class="btn_padding">
	<!--input type="button" value="Add Row" onClick="add_row();" /-->
	<input type="button" value="Delete" onClick="delete_row();" />
	<input type="button" value="Save" onClick="submit_items('save');" />
</div>
<form name="f_a" method="post" onSubmit="return false;">
<div style="clear:both;"></div>

<input type="hidden" name="a" value="save_checklist_items" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="branch_id" value="{$form.branch_id}" />
<table width="100%" id="item_tbl" class="item_tbl" border="1" class="small" cellspacing="0">
	<tr>
		<th>#</th>
		<th width="20">DEL<br /><input type="checkbox" class="toggle_chx" /></th>
		<th>barcode</th>
		<th>Qty<br />(pcs)</th>
	</tr>
	{foreach from=$items key=row item=r name=i}
		<tr>
			<td class="row_no">{$smarty.foreach.i.iteration}.</td>
			<td><input type="checkbox" name="item_chx[{$r.id}]" class="item_chx" /></td>
			<td align="center"><input type="text" name="barcode[{$r.id}]" value="{$r.barcode}" size="20" /></td>
			<td align="center"><input type="text" name="qty[{$r.id}]" class="r" value="{$r.qty}" size="5" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td>
		</tr>
	{/foreach}
</table>
</form>

<div style="float:right;" class="btn_padding">
	<!--input type="button" value="Add Row" onClick="add_row();" /-->
	<input type="button" value="Delete" onClick="delete_row();" />
	<input type="button" value="Save" onClick="save();" />
</div>

{else}
<p align="center" >- No item -</p>
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
