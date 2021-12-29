{*
6:02 PM 11/29/2018 Justin
- Bug fixed on invalid data download link is broken.
*}
{include file='header.tpl'}
{literal}
<style>
.div_tbl{
	padding:10px;
}

.div_result{
	border: solid 1px darkgrey;
	background: lightyellow;
	padding:10px;
}

.tr_error{
	color: red;
}
</style>
{/literal}
<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var UPDATE_SKU_PO_REORDER_QTY_MODULE = {
	f_a: undefined,
	f_b: undefined,
	initialize: function() {
		this.f_a = document.f_a;
		this.f_b = document.f_b;
	},
	update_sku: function(m) {
		if(this.validate() == false) return false;
	
		if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
		
		$('update_btn').disabled = true;
		$('span_loading').show();
		
		var file = this.f_a['file_name'].value;
		var q = $(this.f_b).serialize();
		
		var params = {
			a: 'ajax_update_sku',
			file_name: file,
			method: m
		};
		q += '&'+$H(params).toQueryString();
		
		new Ajax.Request(phpself, {
			parameters: q,
			onComplete: function(msg){	
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';
	
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1 || ret['partial_ok'] == 1){ // success
						alert("Successfully Updated SKU Data.");
						$('div_result').hide();
						if (ret['partial_ok'] == 1) {
							$('div_invalid').show();
							$('invalid_link').href = 'attachments/update_sku_po_reorder_qty/invalid_'+file;
						}
						return;
					}else{  // save failed
						if(ret['fail'] == 1)	err_msg = 'Update Failed.';
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
				// prompt the error
				alert(err_msg);
			},
			onSuccess: function(msg){
				$('span_loading').hide();
			}
		});
	},
	
	validate: function(obj){
		// only accept csv file
		if(obj != undefined && obj.name == "f_a"){
			var filename = this.f_a['update_csv'].value;
			if(filename.indexOf('.csv')<0){
				alert('Please select a valid csv file');
				return false;
			}
		}
	
		if(this.f_a['update_type'].value == "branch"){
			// reset row number
			var have_branch_ticked = false;
			$$('#div_branch_list .branch_sel_list').each(function(ele, i){
				if(ele.checked == true) have_branch_ticked = true;
			});
			
			if(have_branch_ticked == false){
				alert("You must choose at least one branch to update");
				return false;
			}
		}
		
		return true;
	},
	
	toggle_all_branches: function(obj){
		$$('#div_branch_list .branch_sel_list').each(function(ele, i){
			if(obj.checked == true) ele.checked = true;
			else ele.checked = false;
		});
	},
	
	toggle_update_type: function(obj){
		if(obj.value == "branch"){
			$('div_branch_list').show();
		}else{
			$('div_branch_list').hide();
		}
	}
}

{/literal}
</script>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
{if $errm}
	<div class="errmsg">
		<div class="alert alert-danger mx-3 rounded">
			<ul>
				<li>{$errm}</li>
			</ul>
		</div>
	</div>
{/if}
<span id="span_loading" style="display:none; background:yellow; padding:2px;">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>
<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return UPDATE_SKU_PO_REORDER_QTY_MODULE.validate(this);" method="post">
	<input type="hidden" name="a" value="show_result" />
	<input type="hidden" name="method" value="1" />
	<input type="hidden" name="file_name" value="{$file_name}" />
<div class="card mx-3">
	<div class="card-body">
		<table>
				<div class="alert alert-primary rounded ">
					<b>Note:</b><br />
				* This module will update the PO Reorder Qty from SKU Masterfile.<br/>
				* Min Qty is optional.<br/>
				* Notify Person is optional but must exists if provided.<br/>
				* Notify Person must have the privilege "NT_STOCK_REORDER".
				</div>
				
				<div class="row">
					<div class="col-md-2">
						<b class="form-label">Upload CSV <br />(<a href="?a=download_sample&method=1">Download Sample</a>)</b>
					</div>
				<div class="col-md-3 mt-2">
					<input type="file" name="update_csv"/>
				</div>
				<div class="col-md-2">
					<input type="Submit" class="btn btn-primary mt-2 mt-md-0" value="Show Result" />
				</div>
				</div>
				
					<div class="row">
						<div class="col-md-3 mt-2 mt-md-0">
							<b class="form-label">Update PO Reorder Qty by</b>
					<select class="form-control" name="update_type" onchange="UPDATE_SKU_PO_REORDER_QTY_MODULE.toggle_update_type(this);">
						{foreach from=$upd_type_list key=upd_type item=upd_desc}
							<option value="{$upd_type}" {if $smarty.request.update_type eq $upd_type}selected{/if}>{$upd_desc}</option>
						{/foreach}
					</select>
						</div>
					</div>

			<tr>
				<td>&nbsp;</td>
				<td  id="div_branch_list" {if $smarty.request.update_type ne 'branch'}style="display:none;"{/if}>
					<input type="checkbox" id="all_branch_chkbx" onclick="UPDATE_SKU_PO_REORDER_QTY_MODULE.toggle_all_branches(this);" /> <b>All</b> &nbsp;&nbsp;
					{foreach from=$branch_list key=bid item=bcode}
						<input type="checkbox" class="branch_sel_list" name="branch_list[{$bid}]" value="1" {if $smarty.request.branch_list.$bid}checked{/if} /> <b>{$bcode}</b>&nbsp;&nbsp;
					{/foreach}
				</td>
			</tr>
		</div>
		</table>
	</div>
</div>
	<div class="div_tbl">
		<div class="breadcrumb-header justify-content-between">
			<div class="my-auto">
				<div class="d-flex">
					<h4 class="content-title mb-0 my-auto ml-4 text-primary">Sample</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
				</div>
			</div>
		</div>
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table id="si_tbl" width="100%">
					<thead class="bg-gray-100">
						<tr >
							{foreach from=$sample_headers.1 item=i}
								<th>{$i}</th>
							{/foreach}
						</tr>
					</thead>
					{foreach from=$sample.1 item=s}
						<tbody class="fs-08">
							<tr>
								{foreach from=$s item=i}
									<td>{$i}</td>
								{/foreach}
								</tr>
						</tbody>
					{/foreach}
				</table>
			</div>
		</div>
	</div>
	</div>
	<div id="div_invalid" style="display: none">
		<div style="border: solid 2px red; padding: 5px; background-color: yellow">
			<p style="font-weight: bold">* Update Successfully. Click <a id="invalid_link" href='#' download>this</a> to download and view the invalid data.</p>
		</div>
	</div>
</form>
<br>
{if $item_lists}
	<form name="f_b" method="post">
		<div class="div_result" id="div_result">
			{include file="masterfile_sku.update_po_reorder_qty.result.tpl"}
		</div>
	</form>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
UPDATE_SKU_PO_REORDER_QTY_MODULE.initialize();
{/literal}
</script>