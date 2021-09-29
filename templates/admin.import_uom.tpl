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
var IMPORT_UOM = {
    f_a: undefined,
	initialize: function() {
		this.f_a = document.f_a;
	},
    check_file: function(obj) {
		switch(obj.name) {
			case 'f_a':
				var filename = this.f_a['import_csv'].value;
				break;
        }
        
        // only accept csv file
		if(filename.indexOf('.csv')<0){
			alert('Please select a valid csv file');
			return false;
		}
		return true;
    },
    import_uom: function(m) {
        if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
			
		var file = '';
		switch(m) {
			case 1:
				file = this.f_a['file_name'].value; break;
		}
        
        $('import_btn').disabled = true;
		$('span_sr_loading_'+m).show();
        
        var params = {
			a: 'ajax_import_uom',
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
						alert("Successfully Imported UOM Data.");
						$('div_result').hide();
						if (ret['partial_ok'] == 1) {
							$('div_invalid_'+m).show();
							$('invalid_link_'+m).href = 'attachments/uom_import/invalid_'+file;
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
<div class="alert alert-danger mx-3 rounded">
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
		<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return IMPORT_UOM.check_file(this);" method="post">
			<input type="hidden" name="a" value="show_result" />
			<input type="hidden" name="method" value="1" />
			<input type="hidden" name="file_name" value="{$file_name}" />
			<table>
				
						<div class="alert alert-primary">
							<b>Note:</b><br />
						* Please ensure the file extension <b>".csv"</b>.<br />
						* Please ensure the import file contains header.<br />
						* Code's maximum characters is 6.<br />
						</div>
				
				<tr>
					<td><b class="fs-08">Upload CSV <br />(<a href="?a=download_sample_uom&method=1">Download Sample</a>)</b></td>
					<td>
						&nbsp;&nbsp;&nbsp;<input class="fs-08" type="file" name="import_csv"/>&nbsp;&nbsp;&nbsp;
						<input class="fs-06 btn btn-primary" type="Submit" value="Show Result" />
					</td>
				</tr>
			</table>
			<div class="div_tbl mt-2">
				<h5 class="bg-gray-100 px-2 py-1">Sample</h5>
				<table id="si_tbl" width="100%">
					<thead class="bg-gray-100">
						<tr>
							{foreach from=$sample_headers[1] item=i}
								<th>{$i}</th>
							{/foreach}
						</tr>
					</thead>
					{foreach from=$sample[1] item=s}
						<div class="fs-08">
							<tr>
								{foreach from=$s item=i}
									<td>{$i}</td>
								{/foreach}
								</tr>
						</div>
					{/foreach}
				</table>
			</div>
			<div id="div_invalid_1" style="display: none">
				<div class="alert alert-success rounded">
					
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
			{include file="admin.import_uom.result.tpl"}
		</div>
	</div>
</div>
{/if}
<br><br>
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
IMPORT_UOM.initialize();
{/literal}
</script>