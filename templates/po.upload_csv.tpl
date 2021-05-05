{*
12/2/2020 9:32 AM Andy
- Removed "var" to fix javascript error in approval screen.

4/2/2021 10:10 AM Ian
- Added note to make sure user entered correct branch code before add item from csv
*}

{literal}
<style>
.tr_error{
	color: red;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
PO_UPLOAD_CSV = {
    f_csv: undefined,
	initialize: function() {
		this.f_csv = document.f_csv;
	},
	show_result: function(){
		var filename = this.f_csv['import_csv'].value;
		
        // only accept csv file
		if(filename.indexOf('.csv')<0){
			alert('Please select a valid csv file');
			this.f_csv['import_csv'].value = '';
			return false;
		}
		
		$('div_reload_upload_result').update(_loading_);
		$('btn_show_result').disabled = true;
		$('btn_close').disabled = true;
		this.f_csv['a'].value = 'show_result';
		this.f_csv.submit();
	},
	ajax_show_result: function(file_name, err_message = ''){
		this.f_csv['file_name'].value = file_name;
		this.f_csv['import_csv'].value = '';
		$('div_reload_upload_result').update('');
		$('btn_show_result').disabled = false;
		$('btn_close').disabled = false;
		
		$('div_item_invalid').hide();
		$('download_item_invalid_link').href = '';
		
		if(err_message != ''){
			alert(err_message);
			return false;
		}
		
		this.f_csv['a'].value = 'ajax_get_uploaded_csv_result';
		var form_data = this.f_csv.serialize();
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: form_data,
			onComplete: function(msg){			    
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';
				
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] && ret['html']){ // success
						$('div_upload_result').update(ret['html']);
						$('div_upload_result').show();
						return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				alert(err_msg);
			}
		});
	},
	import_po_items: function() {
		var po_tmp_item = document.querySelectorAll('[id^="po_tmp_item-"]');
		var item_checked = 0;
		if(typeof(po_tmp_item) !='undefined'){
			for(var i=0;i<po_tmp_item.length;i++){
				if(po_tmp_item[i].checked == true)  item_checked += 1;
			}
		}
		if(item_checked == 0){
			alert('Please select at least 1 item to import PO item.');
			return false;
		}
        if(!confirm('Are you sure?')) return false;
		
        $('import_btn').disabled = true;
		$('btn_close').disabled = true;
		$('div_reload_import').update(_loading_);
		
		this.f_csv['a'].value = 'ajax_import_po_items';
		var form_data = this.f_csv.serialize();
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: form_data,
			onComplete: function(msg){			    
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';
				
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok']){
						if(ret['html']){
							for(let i=0; i < ret['html'].length; i++){
								var tb = $('po_items');
								
								if(ret['html'][i]['duplicate']){
									var item_id = ret['html'][i]['existed_item_id'];
									if(ret['html'][i]['multi_delivery_branch']){
										if(ret['html'][i]['qty_loose_allocation']){
											for (var bid in ret['html'][i]['qty_loose_allocation']) {
												var qty = ret['html'][i]['qty_loose_allocation'][bid];
												document.f_a['qty_loose_allocation['+item_id+']['+bid+']'].value = float(document.f_a['qty_loose_allocation['+item_id+']['+bid+']'].value) + float(qty);
											}
										}
									}else{
										var qty = ret['html'][i]['qty_loose'];
										document.f_a['qty_loose['+item_id+']'].value = float(document.f_a['qty_loose['+item_id+']'].value) + float(qty);
									}
								}
								if(ret['html'][i]['rowdata']){
									new Insertion.Bottom(tb, ret['html'][i]['rowdata']);
								}
							}
							recalculate_all_items();
						}
						
						if(ret['file'] !=''){
							$('div_item_invalid').show();
							$('download_item_invalid_link').href = 'attachments/import_po_item/'+ret['file'];
						}
						
						if(ret['msg'] != '')  alert(ret['msg']);
						return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
				alert(err_msg);
			}
		});
		
		$('div_reload_import').update('');
		$('import_btn').disabled = false;
		$('btn_close').disabled = false;
		$('div_upload_result').update('');
		$('div_upload_result').hide();
	},
	//check all checkbox
	check_all_item: function(obj){
		var po_tmp_item = document.querySelectorAll('[id^="po_tmp_item-"]');
		
		if(typeof(po_tmp_item) !='undefined'){
			for(var i=0;i<po_tmp_item.length;i++){
				if(obj.checked == true){
					po_tmp_item[i].checked = true;
				}else{
					po_tmp_item[i].checked = false;
				}
			}
		}
	},
	close_popup_div: function(){
		$('div_upload_csv').hide()
		curtain(false,'curtain2');
	}
}
{/literal}
</script>


<form name="f_csv" onSubmit="return false;" method="post" enctype="multipart/form-data" action="{$smarty.server.PHP_SELF}" target="if_csv">
	<input type="hidden" name="a" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="department_id" value="{$form.department_id}" />
	<input type="hidden" name="po_branch_id" value="{$form.po_branch_id}" />
	<input type="hidden" name="vendor_id" value="{$form.vendor_id}" />
	<input type="hidden" name="po_date" value="{$form.po_date}" />
	<input type="hidden" name="is_under_gst" value="{$form.is_under_gst}" />
	<input type="hidden" name="is_request" value="{$form.is_request}" />
	<input type="hidden" name="po_create_type" value="{$form.po_create_type}" />
	<input type="hidden" name="branch_is_under_gst" value="{$form.branch_is_under_gst}" />
	<input type="hidden" name="currency_code" value="{$form.currency_code}" />
	<input type="hidden" name="currency_rate" value="{$form.currency_rate}" />
	<input type="hidden" name="search_other_department" value="{$form.search_other_department}" />
	{if $form.deliver_to}
		{foreach from=$form.deliver_to item=r}
			<input type="hidden" name="deliver_to[]" value="{$r}" />
		{/foreach}
	{/if}
	<input type="hidden" name="file_name" value="" />
	<input type="hidden" name="method" value="1" />
	
	<h3>Add item by CSV</h3>
	
	<table width="100%">
		<tr>
			<td colspan="2" style="color:#0000ff;">
				Note:<br />
				* Please ensure the file extension <b>".csv"</b>.<br />
				* Please ensure the import file contains header.<br />
				* Please ensure the import file entered branch code is correct. <br />
				* When Order Cost is empty, the system will automatically retrieve.<br /><br />
			</td>
		</tr>
		<tr>
			<td><b>Upload CSV<br />(<a target="_blank" href="po.php?a=download_sample_po&sample_format={$form.sample_format}">Download Sample</a>)</b></td>
			<td><input type="file" name="import_csv" /></td>
		</tr>
		<tr>
			<td colspan="2">
				<label><input type="checkbox" name="allow_duplicate" value="1" {if $form.allow_duplicate}checked{/if} />  Automatically add qty when item duplicate</label>
			</td>
		</tr>
		<tr>
			<td><input type="button" id="btn_show_result" value="Show Result" onClick="PO_UPLOAD_CSV.show_result();" /></td>
			<td></td>
		</tr>
	</table>
	<br />
	<div class="div_tbl">
		<h3>Sample</h3>
		<table id="si_tbl" width="100%">
			<tr bgcolor="#ffffff">
				{foreach from=$sample_headers[$form.sample_format] item=i}
					<th>{$i}</th>
				{/foreach}
			</tr>
			{foreach from=$sample[$form.sample_format] item=s}
				<tr>
				{foreach from=$s item=i}
					<td>{$i}</td>
				{/foreach}
				</tr>
			{/foreach}
		</table>
	</div>
	
    <div id="div_item_invalid" style="display: none">
		<div style="border: solid 2px red; padding: 5px; background-color: yellow">
			<p style="font-weight: bold">* Import Successfully. Click <a id="download_item_invalid_link" href='#' download>this</a> to download and view the invalid data.</p>
		</div>
	</div>
	
	<div id="div_reload_upload_result"></div>
	<div id="div_upload_result" style="display: none;"></div>
	
	<div id="div_reload_upload_result"></div>
</form>
<iframe name="if_csv" style="width:1px;height:1px;visibility:hidden;"></iframe>

<p align="center"><input type="button" id="btn_close" value="Close" onClick="PO_UPLOAD_CSV.close_popup_div();" /></p>

<script>
{literal}
	PO_UPLOAD_CSV.initialize();
{/literal}
</script>