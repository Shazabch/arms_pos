{include file='header.tpl'}

{literal}
<style>
.div_tbl{
	
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
var UPDATE_SKU_MASTER_COST = {
    f_a: undefined,
	initialize: function() {
		this.f_a = document.f_a;
	},
    check_file: function(m) {
		var filename = '';
		if(m == 1){
			filename = this.f_a['csv_file'].value;
		}	
        
        // only accept csv file
		if(filename.indexOf('.csv')<0){
			alert('Please select a valid csv file');
			return false;
		}
		return true;
    },
	show_loading: function(){
		center_div($('div_wait_popup').show());
		curtain(true, 'curtain2');
	},
	hide_loading: function(){
		$('div_wait_popup').hide();
		curtain(false, 'curtain2');
	},
    start_import: function(m) {
        if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
		
		var params = '';
		if(m == 1){
			params = $(this.f_a).serialize()+'&a=ajax_start_import';
		}
		
        $('import_btn').disabled = true;
		this.show_loading();
        
        
        var THIS = this;
		
        new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){	
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';
	
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1){ // success
						alert("Successfully Imported.");
						$('div_result').hide();
						if (ret['error_file_name']) {
							$('div_invalid_'+m).show();
							$('invalid_link_'+m).href = '?a=download_file&f='+ret['error_file_name'];
						}
						return;
					}else{  // save failed
						if(ret['fail'] == 1)	err_msg = 'Import Failed.';
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
				$('import_btn').disabled = false;
				// prompt the error
				alert(err_msg);
			},
			onSuccess: function(msg){
				THIS.hide_loading();
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
{if $err}
<div class="card mx-3">
	<div class="card-body">
	<div class="errmsg">
		<ul>
			{foreach from=$err item=e}
				<li>{$e}</li>
			{/foreach}
		</ul>
	</div>
	</div>
</div>
{/if}
<div id="div_wait_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
	<p align="center">
		Processing..
		<br /><br />
		<img src="ui/clock.gif" border="0" />
	</p>
</div>
<div class="container">
	<div class="card mx-3">
		<div class="card-body">
			
<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return UPDATE_SKU_MASTER_COST.check_file('1');" method="post">
    <input type="hidden" name="a" value="show_result" />
	<input type="hidden" name="method" value="1" />
	<input type="hidden" name="file_name" value="{$file_name}" />
	<input type="hidden" name="error_file_name" value="{$error_file_name}" />
    <table>
		<div >
				
					<label><b>Note:</b></label><br />
					<span class="text-danger">*</span> <span class="text-muted">Please ensure the file extension</span> ".csv".<br />
					<span class="text-danger">*</span> <span class="text-muted">Please ensure the import file contains header.</span><br />
				
		</div>
	<div class="mt-2">
		<br>
			<b class="mt-2">Upload CSV <br />(<a href="?a=download_sample&method=1"><span class="text-primary">Download Sample</span></a>)</b></td>
				&nbsp;&nbsp;<input type="file" name="csv_file"/>
				<input type="Submit" class="btn btn-primary ml-0 ml-md-2 mt-2 mt-md-0" value="Show Result" />
	</div>
	</table>
    <div class="div_tbl mt-2">
		<label><b>Sample</b></label>
		<table id="si_tbl" width="100%">
			<tr>
				{foreach from=$sample_headers[1] item=i}
					<th>{$i}</th>
				{/foreach}
			</tr>
			{foreach from=$sample[1] item=s}
			
			<tr>
				{foreach from=$s item=i}
					<td class="text-muted">{$i}</td>
				{/foreach}
				</tr>
			{/foreach}
		</table>
	</div>
    <div id="div_invalid_1" style="display: none">
		<div style="border: solid 2px red; padding: 5px; background-color: yellow">
			<p style="font-weight: bold">* Import Successfully. Click <a id="invalid_link_1" href='#' target="_blank">this</a> to download and view the invalid data.</p>
		</div>
	</div>
</form>

<br>
{if $item_lists}
	<div class="div_result" id="div_result">
		<h3>Result Status:</h3>
		<p style="color:blue;">
			{if $result.import_row}
				Total {$result.import_row} of {$result.ttl_row} item(s) will be Updated.<br />
			{/if}
			{if $result.error_row > 0}
				Total {$result.error_row} of {$result.ttl_row} item(s) will fail to update due to some error found, please check the error message at the end of <span style="color:red">highlighted</span> row.<br />
			{/if}
			* Please ENSURE the result data is fill to the header accordingly before proceed.<br />
			<br/>
			<input type="button" id="import_btn" name="import_btn" value="Import" onclick="UPDATE_SKU_MASTER_COST.start_import('{$form.method}');" {if !$result.import_row}disabled{/if} />
			{if $result.error_row > 0}
				&nbsp;&nbsp;
				<a href="?a=download_file&f={$error_file_name}" target="_blank"><img src="/ui/icons/script_delete.png" align="absmiddle" /> Click here to download error csv</a>
			{/if}
		</p>
		<div class="div_tbl">
			<table id="si_tbl" width="100%">
				<tr bgcolor="#ffffff">
					<th>#</th>
					{foreach from=$item_header item=i}
						<th>{$i}</th>
					{/foreach}
				</tr>
				<tbody>
				{foreach from=$item_lists item=i name=uom}
					<tr class="{if $i.error}tr_error{/if}">
						<td>{$smarty.foreach.uom.iteration}.</td>
						{foreach from=$i key=k item=r}
							<td>{$r}</td>
						{/foreach}
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>

	</div>
{/if}
		</div>
	</div>
</div>
<br><br>

<script type="text/javascript">
{literal}
UPDATE_SKU_MASTER_COST.initialize();
{/literal}
</script>


{include file='footer.tpl'}