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
var GRN_UPLOAD_CSV = {
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
	ajax_show_result: function(file_name,  err_message = ''){
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
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: {
				'a': 'ajax_get_uploaded_csv_result',
				'file_name': file_name,
				'method' : 1,
			},
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
	check_all_item: function(obj){
		var grn_items_list = document.querySelectorAll('[id^="grn_items_list-"]');
		
		if(typeof(grn_items_list) !='undefined'){
			for(var i=0;i<grn_items_list.length;i++){
				if(obj.checked == true){
					grn_items_list[i].checked = true;
				}else{
					grn_items_list[i].checked = false;
				}
			}
		}
	},
	import_grn: function() {
		var grn_items_list = document.querySelectorAll('[id^="grn_items_list-"]');
		var item_checked = 0;
		if(typeof(grn_items_list) !='undefined'){
			for(var i=0;i<grn_items_list.length;i++){
				if(grn_items_list[i].checked == true)  item_checked += 1;
			}
		}
		if(item_checked == 0){
			alert('Please select at least 1 item to import GRN item.');
			return false;
		}
        if(!confirm('Are you sure?')) return false;
		
        $('import_btn').disabled = true;
		$('btn_close').disabled = true;
		$('div_reload_import').update(_loading_);
		
		this.f_csv['a'].value = 'ajax_import_grn';
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
								if(ret['html'][i]){
									if(ret['html'][i]['duplicate']){
										var gi_id = ret['html'][i]['existed_item_id'];
										var doc_type = ret['html'][i]['item_group'];
										var pcs = ret['html'][i]['pcs'];
										$(doc_type+'_pcs'+gi_id).value = float($(doc_type+'_pcs'+gi_id).value) + float(pcs);
										recalc_row(gi_id, doc_type);
									}
									if(ret['html'][i]['rowdata']){
										var doc_type = ret['html'][i]['doc_type'];
										new Insertion.Bottom($$('.multiple_add_container_'+doc_type).first(), ret['html'][i]['rowdata']);
										reset_row();
									}
									recalc_totals();
								}
							}
						}
						
						if(ret['file'] !=''){
							$('div_item_invalid').show();
							$('download_item_invalid_link').href = 'attachments/import_grn_items/'+ret['file'];
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
	close_popup_div: function(){
		$('div_upload_csv').hide()
		curtain(false,'curtain2');
	},
}
{/literal}
</script>

<form name="f_csv" onSubmit="return false;" method="post" enctype="multipart/form-data" action="{$smarty.server.PHP_SELF}" target="if_csv">
	<input type="hidden" name="a" />
	<input type="hidden" name="branch_id" value="{$form.grn_bid}" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="doc_type" value="3" />
	<input type="hidden" name="rcv_date" value="{$form.rcv_date}" />
	<input type="hidden" name="file_name" value="" />
	<input type="hidden" name="type" value="{$form.type}" />
	<input type="hidden" name="allow_grn_without_po" value="{$form.allow_grn_without_po}" />
	<input type="hidden" name="is_under_gst" value="{$form.is_under_gst}" />
	<input type="hidden" name="branch_is_under_gst" value="{$form.branch_is_under_gst}" />
	<input type="hidden" name="method" value="1" />

	<h3>Add item by CSV</h3>
	<table width="100%">
		<tr>
			<td colspan="2" style="color:#0000ff;">
				Note:<br />
				* Please ensure the file extension <b>".csv"</b>.<br />
				* Please ensure the import file contains header.<br />
				* When column of cost is empty, the system will automatically retrieve.<br /><br />
			</td>
		</tr>
		<tr>
			<td><b>Upload CSV<br />(<a href="?a=download_sample_grn&method=1">Download Sample</a>)</b></td>
			<td><input type="file" name="import_csv" /></td>
		</tr>
		<tr>
			<td colspan="2">
				<label><input type="checkbox" name="allow_duplicate" value="1" {if $form.allow_duplicate}checked{/if} />  Automatically add qty when item duplicate</label>
			</td>
		</tr>
		<tr>
			<td><input type="button" id="btn_show_result" value="Show Result" onClick="GRN_UPLOAD_CSV.show_result();" /></td>
			<td></td>
		</tr>
	</table>
	<br />
	<div class="div_tbl">
		<h3>Sample</h3>
		<table id="si_tbl" width="100%">
			<tr bgcolor="#ffffff">
				{foreach from=$sample_headers[1] item=i}
					<th>{$i}</th>
				{/foreach}
			</tr>
			{foreach from=$sample[1] item=s}
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

<p align="center"><input type="button" id="btn_close" value="Close" onClick="GRN_UPLOAD_CSV.close_popup_div();" /></p>

<script>
{literal}
	GRN_UPLOAD_CSV.initialize();
{/literal}
</script>