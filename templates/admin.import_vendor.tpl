{*
07/21/2016 10:00 Edwin
- Add new method to import vendor
*}
{include file='header.tpl'}
{literal}
<style>
.div_tbl{
	padding:10px;
}

.div_result{

}

.tr_error{
	color: red;
}
</style>
{/literal}
<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var IMPORT_VENDOR = {
	f_a: undefined,
	f_b: undefined,
	initialize: function() {
		this.f_a = document.f_a;
		this.f_b = document.f_b;
	},
	check_file: function(obj) {
		switch(obj.name) {
			case 'f_a':
				var filename = this.f_a['import_csv'].value;
				break;
			case 'f_b':
				var filename = this.f_b['import_csv'].value;
				break;
		}
		
		// only accept csv file
		if(filename.indexOf('.csv')<0){
			alert('Please select a valid csv file');
			return false;
		}
		return true;
	},
	import_vendor: function(m) {
		if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
			
		var file = '';
		switch(m) {
			case 1:
				file = this.f_a['file_name'].value; break;
			case 2:
				file = this.f_b['file_name'].value; break;
		}
		
		$('import_btn').disabled = true;
		$('span_sr_loading_'+m).show();
		
		var params = {
			a: 'ajax_import_vendor',
			file_name: file,
			method: m
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
						alert("Successfully Imported Vendor Data.");
						$('div_result').hide();
						if (ret['partial_ok'] == 1) {
							$('div_invalid_'+m).show();
							$('invalid_link_'+m).href = 'attachments/vendor_import/invalid_'+file;
						}
						return;
					}else{  // save failed
						if(ret['fail'] == 1)	err_msg = 'Import Failed.';
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
				// prompt the error
				alert(err_msg);
			},
			onSuccess: function(msg){
				$('span_sr_loading_'+m).hide();
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
{if $errm && $method == '1'}
	<div class="alert alert-danger mx-3">
		<div class="errmsg">
			<ul>
				<li>{$errm}</li>
			</ul>
		</div>
	</div>
{/if}
<span id="span_sr_loading_1" style="display:none; background:yellow; padding:2px;">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>
<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return IMPORT_VENDOR.check_file(this);" method="post">
			<input type="hidden" name="a" value="show_result" />
			<input type="hidden" name="method" value="1" />
			<input type="hidden" name="file_name" value="{$file_name}" />
			<table>
				
				<div class="alert alert-primary  rounded">
					Note:<br />
					* Please ensure the file extension <b>".csv"</b>.<br />
					* Please ensure the import file contains header.<br />
					* If GST No is provided, the GST start date will be 2015-04-01.<br/>
				</div>
				
				<tr>
					<td><b class="fs-08">Upload CSV <br />(<a class="fs-08" href="?a=download_sample_vendor&method=1">Download Sample</a>)</b></td>
					<td>
					&nbsp;&nbsp;&nbsp;	<input class="fs-08" type="file" name="import_csv"/>&nbsp;&nbsp;&nbsp;
						<input type="Submit" class="btn btn-primary fs-06" value="Show Result" />
					</td>
				</tr>
			</table>
			<div class="div_tbl mt-2">
				<h5 class="bg-gray-100 px-2 py-1">Sample</h5>
				<div class="table-responsive">
					<table id="si_tbl"  class="report_table table mb-0 text-md-nowrap  table-hover" width="100%">
						<tr>
						<thead class="bg-gray-100">
							{foreach from=$sample_headers[1] item=i}
							<th>{$i}</th>
						{/foreach}
					</tr>
						</thead>
						{foreach from=$sample[1] item=s}
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
			<div id="div_invalid_1" style="display: none">
				
			<div class="alert alert-success">
				<p style="font-weight: bold">* Import Successfully. Click <a id="invalid_link_1" href='#' download>this</a> to download and view the invalid data.</p>
			</div>
				
			</div>
		</form>
	
	</div>
</div>
{if $item_lists && $method == '1'}
<div class="card mx-3">
	<div class="card-body">
		<div class="div_result" id="div_result">
			{include file="admin.import_vendor.result.tpl"}
		</div>
	</div>
</div>
{/if}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE} Method 2</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
{if $errm && $method == '2'}
	<div class="alert alert-danger mx-3">
		<div class="errmsg">
			<ul>
				<li>{$errm}</li>
			</ul>
		</div>
	</div>
{/if}
<span id="span_sr_loading_2" style="display:none;background: yellow;padding:2px;">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>
<div class="card mx-3">
	<div class="card-body">
		<form name="f_b" enctype="multipart/form-data" class="stdframe" onsubmit="return IMPORT_VENDOR.check_file(this);" method="post">
			<input type="hidden" name="a" value="show_result" />
			<input type="hidden" name="method" value="2" />
			<input type="hidden" name="file_name" value="{$file_name}" />
			<table>
				
				<div class="alert alert-primary rounded">
					Note:<br />
						* Please ensure the file extension <b>".csv"</b>.<br/>
						* Please ensure the import file contains header.<br/>
						* If GST No is provided and GST Start Date is empty, the date 2015-04-01 will be the default value.<br/>
						* Date format must be in YYYY-MM-DD.<br/>
				</div>
				
				
				<tr class=>
					<td><b class="fs-08 ">Upload CSV <br />(<a href="?a=download_sample_vendor&method=2">Download Sample</a>)</b></td>
					<td>
						&nbsp;&nbsp;&nbsp;<input class="fs-08" type="file" name="import_csv"/>&nbsp;&nbsp;&nbsp;
						<input class="btn btn-primary fs-06" type="Submit" value="Show Result" />
					</td>
				</tr>
			</table>
			<div class="div_tbl mt-2">
				<h5 class="bg-gray-100 px-2 py-1">Sample</h5>
				<div class="table-responsive">
					<table id="si_tbl" class="report_table table mb-0 text-md-nowrap  table-hover">
						<thead class="bg-gray-100">
							<tr >
								{foreach from=$sample_headers[2] item=i}
									<th>{$i}</th>
								{/foreach}
							</tr>
						</thead>
						{foreach from=$sample[2] item=s}
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
			<div id="div_invalid_2" style="display: none">
				<div class="alert alert-success">
					
					<p style="font-weight: bold">* Import Successfully. Click <a id="invalid_link_2" href='#' download>this</a> to download and view the invalid data.</p>
				</div>
				
			</div>
		</form>
	</div>
</div>
<br>
{if $item_lists && $method == '2'}
	<div class="div_result" id="div_result">
		<div class="card mx-3">
			<div class="card-body">
				{include file="admin.import_vendor.result.tpl"}
			</div>
		</div>
	</div>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
IMPORT_VENDOR.initialize();
{/literal}
</script>