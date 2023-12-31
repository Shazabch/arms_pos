{*
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
var UPDATE_MODULE = {
	f_a: undefined,
	initialize: function() {
		this.f_a = document.f_a;
	},
	check_file: function(obj) {
		// only accept csv file
		var filename = this.f_a['update_csv'].value;
		if(filename.indexOf('.csv')<0){
			alert('Please select a valid csv file');
			return false;
		}
		return true;
	},
	update_sku: function(m) {
		if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
		
		$('update_btn').disabled = true;
		$('span_loading').show();
		
		var file = this.f_a['file_name'].value;
		if($('force_upd_price_type').checked == true) var upd_price_type = 1;
		else var upd_price_type = 0;
		
		var params = {
			a: 'ajax_update_sku',
			file_name: file,
			method: m,
			force_upd_price_type: upd_price_type
		};
		
		new Ajax.Request(phpself, {
			parameters: params,
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
							$('invalid_link').href = 'attachments/update_sku_'+m+'/invalid_'+file;
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
	<div class="alert alert-danger mx-3">
		<div class="errmsg">
			<ul>
				<li>{$errm}</li>
			</ul>
		</div>
	</div>
{/if}
<span id="span_loading" style="display:none; background:yellow; padding:2px;">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>
<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return UPDATE_MODULE.check_file(this);" method="post">
	<input type="hidden" name="a" value="show_result" />
	<input type="hidden" name="method" value="{$method}" />
	<input type="hidden" name="file_name" value="{$file_name}" />
	<div class="card mx-3">
		<div class="card-body">
			<table>
				
						<div class="alert alert-primary">
							<b>Note:</b><br />
							* This module will update the {$method|ucwords} and Price Type from SKU Masterfile.<br/>
							* {$method|ucwords} Code or Price Type is optional but cannot left both empty.<br/>
						</div>
					
					<div class="row">
						
					<div class="col-md-2">
						<b class="form-label">Upload CSV <br />(<a href="?a=download_sample&method={$method}">Download Sample</a>)</b>
					</div>
					<div class="col-md-3 mt-2">
						<input type="file" name="update_csv"/>
					</div>
					<div class="col-md-2">
						<input type="Submit" class="btn btn-primary mt-2 mt-md-0" value="Show Result" />
					</div>
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
								{foreach from=$sample_headers.$method item=i}
									<th>{$i}</th>
								{/foreach}
							</tr>
						</thead>
						{foreach from=$sample.$method item=s}
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
	<div class="div_result" id="div_result">
		{include file="masterfile_sku.update_brand_vendor.result.tpl"}
	</div>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
UPDATE_MODULE.initialize();
{/literal}
</script>