{*
REVISION HISTORY
=================
3/26/2008 5:38:37 PM gary
- refresh the session every 25 minutes to avoid timeout.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field
*}
{include file=header.tpl}
{literal}
<script>
function load_vendor_sku(){
	$('sku_table').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater("sku_table","po.from_vendor.php",{
			method: 'post',
			parameters: Form.serialize(document.f_a) + '&a=ajax_load_sku',
			evalScripts: true
	})
}

// expand the item's varieties
function toggle_vendor_sku(sku_id,id){
	if ($('xp'+id).innerHTML == "varieties"){
		$('xp'+id).innerHTML = "hide varieties";
		$('cb'+id).disabled = true;
		$('cb'+id).checked = false;
		insert_after = $('li'+id);

		new Ajax.Updater(insert_after,"po.from_vendor.php",{
				method:'post',
				parameters: '&a=ajax_expand_sku&sku_id='+sku_id,
			    evalScripts: true,
		 	    insertion: Insertion.Bottom
		});
  	}
  	else{
  		$('xp'+id).innerHTML = "varieties";
		$('cb'+id).disabled = false;
  		Element.remove('ul'+sku_id);
	}
}

function do_generate_po(){
	
	var f_s_serialize = Form.serialize(document.f_s);
	if (f_s_serialize == ''){
		alert('You have not selected any item');
		return;
	}	
	if (!confirm('Click OK to confirm generate PO from selected SKU.')){
		return;		
	}	
	$('sku_table').innerHTML = '<img src=ui/clock.gif align=absmiddle> Generating PO...';	
	params = Form.serialize(document.f_a) + '&' + f_s_serialize;	
	new Ajax.Updater("sku_table","po.from_vendor.php",{
			method: 'post',
			parameters: params + '&a=ajax_generate_po',
			evalScripts: true
	});	
}

//refresh the session each 25 minutes to avoid timeout when user take long time (>30 mins) to select sku. (request by SLLEE)
new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">PO from Vendor SKU</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
{if $config.po_set_max_items}
<p>
Selected SKU will be saved into multiple PO with {php}echo $config[po_set_max_items]{/php} items for each PO.
</p>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name=f_a>
			<table  border=0 cellspacing=0 cellpadding=4>
			<tr>
				<td><b class="form-label">Vendor <span class="text-danger" title="Required Field"> *</span></b></td>
				<td>
					<div class="form-inline">
						<input class="form-control" name="vendor_id" size=1 value="{$form.vendor_id}" readonly>
					&nbsp;&nbsp;<input class="form-control" id="autocomplete_vendor" name="vendor" value="{$form.vendor}" size=50>
					</div>
					<div id="autocomplete_vendor_choices" class="autocomplete"></div>
					
				</td>
			</tr>
			<tr>
				<td><b class="form-label">Department<span clas</b></td>
				<td>
					<select class="form-control" name="department_id">
					{section name=i loop=$dept}
					<option value={$dept[i].id} {if $form.department_id eq $dept[i].id}selected{/if}>{$dept[i].description}</option>
					{/section}
					</select> 
				</td>
			</tr>
			</table>
			<p ><input class="btn btn-primary mt-2" type=button value="Load SKU" onclick="load_vendor_sku()"> 
				<input class="btn btn-danger mt-2" type=button value="Close" onclick="document.location='po.php'"></p>
			</form>
			
	</div>
</div>
<div id="sku_table">
</div>

{include file=footer.tpl}

<script>
{literal}
new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", { afterUpdateElement: function (obj, li) { document.f_a.vendor_id.value = li.title; }});
{/literal}
</script>