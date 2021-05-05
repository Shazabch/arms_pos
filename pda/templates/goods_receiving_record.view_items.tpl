{*
8/8/2011 11:05:11 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

10/4/2011 5:54:21 PM Justin
- Fixed the jquery that does not working properly for change document type.

10/7/2011 5:59:21 PM Justin
- Amended to adjust some fields to become 2nd row in order for user can see them in one screen.
- Removed common js functions since it is already assigned at forms.js.

7/25/2012 5:08:43 PM Justin
- add row number for items

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

4/22/2015 5:11 PM Justin
- Enhanced to have GST information.

9/25/2018 1:56 PM Justin
- Enhanced to have "Document Date" and it is compulsory.

10/11/2018 4:45 PM Andy
- Enhanced to prompt warning if users is going to perform delete but there are new unsaved item.

04/17/2020 04:35 PM Sheila
- Modified layout to compatible with new UI.

04/11/2020 5:14 PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu) and link to module menu page

*}
{include file='header.tpl'}

<script>

var row_count = '{$items|@count|default:0}';
var is_under_gst = '{$form.is_under_gst|default:0}';
{literal}

function change_row_color(ele){
    if($(ele).attr('checked')){
		$(ele).parent().parent().css('background-color','yellow');
		$(ele).parent().parent().next().css('background-color','yellow');
	}else{
        $(ele).parent().parent().css('background-color','#fff');
        $(ele).parent().parent().next().css('background-color','#fff');
	}
}

function submit_items(act){
	if(act=='delete'){
		// check selected item
		if($('input.item_chx:checked').get().length<=0){
			alert('no item checked to proceed delete action.');
			return false;
		}
	
		check_delete_status();
		
		if(!confirm('Click OK to confirm delete.')) return false;
        document.f_a['a'].value = 'delete_items';
	}else{
		$('#submit_btn1').attr('disabled', 'disabled');
		$('#submit_btn2').attr('disabled', 'disabled');
        document.f_a['a'].value = 'save_items';
	}

	document.f_a.submit();
}

function check_type(id){
	if(id == undefined || id == "") return;
	
	if(document.f_a.elements['type['+id+']'].value == "PO"){
		document.f_a.elements['ctn['+id+']'].value = "";
		document.f_a.elements['ctn['+id+']'].readOnly = true;
		document.f_a.elements['pcs['+id+']'].value = "";
		document.f_a.elements['pcs['+id+']'].readOnly = true;
		document.f_a.elements['amount['+id+']'].value = "";
		document.f_a.elements['amount['+id+']'].readOnly = true;
		if(is_under_gst == 1 && document.f_a.elements['gst_amount['+id+']'] != undefined){
			document.f_a.elements['gst_amount['+id+']'].value = "";
			document.f_a.elements['gst_amount['+id+']'].readOnly = true;
		}
	}else{
		document.f_a.elements['ctn['+id+']'].readOnly = false;
		document.f_a.elements['pcs['+id+']'].readOnly = false;
		document.f_a.elements['amount['+id+']'].readOnly = false;
		if(is_under_gst == 1 && document.f_a.elements['gst_amount['+id+']'] != undefined){
			document.f_a.elements['gst_amount['+id+']'].readOnly = false;
		}
	}
}

function add_row(){
	var d = new Date();
	var id = d.getTime();
	var new_tr1 = $('#temp_gi_row1').clone().attr('id', '').attr('class', '').addClass('is_new');
	var new_tr2 = $('#temp_gi_row2').clone().attr('id', '').attr('class', '');
	var new_tr_html1 =$(new_tr1).html();
	var new_tr_html2 =$(new_tr2).html();
	row_count=int(row_count)+1;
	new_tr_html1 = new_tr_html1.replace(/__row_num__/g,row_count);
	new_tr_html1 = new_tr_html1.replace(/__id__/g, id);
	new_tr_html2 = new_tr_html2.replace(/__id__/g, id);
	
	$(new_tr1).html(new_tr_html1);
	$(new_tr2).html(new_tr_html2);
	$('#gi_list').append(new_tr1);
	$('#gi_list').append(new_tr2);
	$('span.item_count').html(float($('span.item_count').html())+1);
}

function check_delete_status(){
	var need_prompt_unsaved = false;
	
	$('#gi_list input.item_chx').each(function(){
		if(!this.checked){
			var tr_parent = $(this).parent().parent();
			if(tr_parent.hasClass('is_new')){
				need_prompt_unsaved = true;
			}
		}
	});
	
	if(need_prompt_unsaved){
		alert('Attention: You have new unsaved item, proceeding to delete will lost the new item.');
	}
}
{/literal}
</script>
<h1>
{$smarty.session.scan_product.name}
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a> {if $smarty.request.find_grr} > <a href="goods_receiving_record.php?a=open&find_grr={$smarty.request.find_grr}">Back to search</a>{/if}</span>
<div style="margin-bottom:10px"></div>

{include file='goods_receiving_record.top_include.tpl'}<br><br>

{if $err.top}
<div id="err"><ul class="errmsg">
{foreach from=$err.top item=e}
&middot; {$e}<br />
{/foreach}
</ul></div>
{/if}
<div class="stdframe" style="background:#fff;">
<table style="display:none;">
	<tr id="temp_gi_row1" class="temp_gi_row1">
		<td>__row_num__</td>
		<td>
			<input type="checkbox" name="item_chx[__id__]" class="item_chx" />
			<input type="hidden" name="gi_id[__id__]" />
		</td>
		<td>
			No: &nbsp;&nbsp;&nbsp;<input type="text" name="doc_no[__id__]" size="8">
			<div style="margin-top:-7px;">&nbsp;</div>
			Date: <input type="text" name="doc_date[__id__]" size="8">
			<input type="hidden" name="prev_doc_no[__id__]" />
			<input type="hidden" name="prev_type[__id__]" />
		</td>
		<td>
			<select name="type[__id__]" class="item_type" onchange="check_type(__id__);">
				<option value="PO" selected>PO</option>
				<option value="INVOICE">INV</option>
				<option value="DO">DO</option>
				<option value="OTHER">OTH</option>
			</select>
		</td>
		<td align="center"><input type="text" name="ctn[__id__]" value="" size="5" class="r" onChange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" readonly /></td>
		<td align="center"><input type="text" name="pcs[__id__]" value="" size="5" class="r" onChange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" readonly /></td>
	</tr>
	<tr id="temp_gi_row2" class="temp_gi_row2">
		<td align="right" colspan="6">
			Amount
			<input type="text" name="amount[__id__]" value="" size="6" class="r" onChange="mf(this);" readonly />
			{if $form.is_under_gst}
				GST Amount
				<input type="text" name="gst_amount[__id__]" value="" size="6" class="r" onChange="mf(this);" readonly />
			{/if}
			Remark
			<input type="text" name="remark[__id__]" value="" size="20" />
		</td>
	</tr>
</table>

<div class="small">
* Document Date in YYYY-MM-DD format.
</div>
<br />

<div style="float:right;" class="btn_padding">
	<input type="button" value="Delete" onClick="submit_items('delete');" />
	<input type="button" value="Add Row" onClick="add_row();" />
	<input type="button" id="submit_btn1" value="Save" onClick="submit_items('save');" />
</div>
<span class="item_count">{count var=$items}</span> item(s)

<form name="f_a" method="post" onSubmit="return false;">
<div style="clear:both;"></div>
<input type="hidden" name="a" />
<table width="100%" border="1" cellspacing="0">
	<tr>
		<th rowspan="2">#</th>
		<th width="20" rowspan="2">DEL<br /><input type="checkbox" class="toggle_chx" /></th>
		<th rowspan="2">Doc<br />No & Date</th>
		<th rowspan="2">Doc<br />Type</th>
		<th colspan="2">Qty</th>
	</tr>
	<tr>
		<th>Ctn</th>
		<th>Pcs</th>
	</tr>
	<tbody id="gi_list">
	{foreach from=$items item=r name=i}
		<tr>
			<td>{$smarty.foreach.i.iteration}.</td>
			<td>
				<input type="checkbox" name="item_chx[{$r.id}]" class="item_chx" />
				<input type="hidden" name="gi_id[{$r.id}]" value="{$r.id}" />
			</td>
			<td>				
				No: &nbsp;&nbsp;&nbsp;<input type="text" name="doc_no[{$r.id}]" value="{$r.doc_no}" size="8">
				<div style="margin-top:-7px;">&nbsp;</div>
				Date: <input type="text" name="doc_date[{$r.id}]" value="{$r.doc_date}" size="8">
				
				<input type="hidden" name="prev_doc_no[{$r.id}]" value="{$r.prev_doc_no|default:$r.doc_no}" />
				<input type="hidden" name="prev_type[{$r.id}]" value="{$r.prev_type|default:$r.type}" />
			</td>
			<td>
				<select name="type[{$r.id}]" class="item_type txt-width" onchange="check_type({$r.id});">
					<option value="PO" {if $r.type eq 'PO'}selected{/if}>PO</option>
					<option value="INVOICE" {if $r.type eq 'INVOICE'}selected{/if}>INV</option>
					<option value="DO" {if $r.type eq 'DO'}selected{/if}>DO</option>
					<option value="OTHER" {if $r.type eq 'OTHER'}selected{/if}>OTH</option>
				</select>
			</td>
			<td align="center"><input type="text" name="ctn[{$r.id}]" value="{$r.ctn}" size="5" class="r" onChange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" {if $r.type eq 'PO'}readonly{/if} /></td>
			<td align="center"><input type="text" name="pcs[{$r.id}]" value="{$r.pcs}" size="5" class="r" onChange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" {if $r.type eq 'PO'}readonly{/if} /></td>
		</tr>
		<tr>
			<td align="right" colspan="6" style="padding:5px">
				Amount
				<input type="text" name="amount[{$r.id}]" value="{$r.amount}" class="txt-width-30" class="r" onChange="mf(this);" {if $r.type eq 'PO'}readonly{/if} />
				{if $form.is_under_gst}
					<br/><br/>GST Amount
					<input type="text" name="gst_amount[{$r.id}]" value="{$r.gst_amount}" class="txt-width-30" class="r" onChange="mf(this);" {if $r.type eq 'PO'}readonly{/if}  />
				{/if}
				<br/><br/>Remark
				<input type="text" name="remark[{$r.id}]" value="{$r.remark}" class="txt-width-30" />
			</td>
		</tr>
		{if $err[$r.id]}
			<tr>
				<td>&nbsp;</td>
				<td colspan="4" class="errmsg">
					<font class=small>
						{foreach from=$err[$r.id] item=e}
							&middot; {$e}<br>
						{/foreach}
					</font>
				</td>
			</tr>
		{/if}
	{/foreach}
	</tbody>
</table>
</form>

<div style="float:right;" class="btn_padding">
	<input type="button" value="Delete" onClick="submit_items('delete');" />
	<input type="button" value="Add Row" onClick="add_row();" />
	<input type="button" id="submit_btn2" value="Save" onClick="submit_items('save');" />
</div>
<br style="clear:both">
</div>
<script>
{literal}
    $('input.item_chx').live('click', function(){
        change_row_color($(this).get(0));
	});
	
	$('input.toggle_chx').live('click', function(){
		$('input.item_chx').attr('checked',$(this).attr('checked')).each(function(i){
			change_row_color($(this).get(0));
		});
	});
{/literal}
</script>
{include file='footer.tpl'}
