{*
07/21/2016 15:30 Edwin
- Add new method to import brand
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
var IMPORT_BRAND = {
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
	import_brand: function(m) {
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
			a: 'ajax_import_brand',
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
						alert("Successfully Imported Brand Data.");
						$('div_result').hide();
						if (ret['partial_ok'] == 1) {
							$('div_invalid_'+m).show();
							$('invalid_link_'+m).href = 'attachments/brand_import/invalid_'+file;
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
<h1>{$PAGE_TITLE}</h1>
{if $errm && $method == '1'}
	<div class="errmsg">
		<ul>
			<li>{$errm}</li>
		</ul>
	</div>
{/if}
<span id="span_sr_loading_1" style="display:none; background:yellow; padding:2px;">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>
<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return IMPORT_BRAND.check_file(this);" method="post">
	<input type="hidden" name="a" value="show_result" />
	<input type="hidden" name="method" value="1" />
	<input type="hidden" name="file_name" value="{$file_name}" />
	<table>
		<tr>
			<td colspan="4" style="color:#0000ff;">
				Note:<br />
				* Please ensure the file extension <b>".csv"</b>.<br/>
				* Please ensure the import file contains header.<br/>
				* Code's maximum characters is 6.<br/>
			</td>
		</tr>
		<tr>
			<td><b>Upload CSV <br />(<a href="?a=download_sample_brand&method=1">Download Sample</a>)</b></td>
			<td>
				<input type="file" name="import_csv"/>&nbsp;&nbsp;&nbsp;
				<input type="Submit" value="Show Result" />
			</td>
		</tr>
	</table>
	<div class="div_tbl">
		<h3>Sample</h3>
		<table id="si_tbl" width="25%">
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
	<div id="div_invalid_1" style="display: none">
		<div style="border: solid 2px red; padding: 5px; background-color: yellow">
			<p style="font-weight: bold">* Import Successfully. Click <a id="invalid_link_1" href='#' download>this</a> to download and view the invalid data.</p>
		</div>
	</div>
</form>
<br>
{if $item_lists && $method == '1'}
	<div class="div_result" id="div_result">
		{include file="admin.import_brand.result.tpl"}
	</div>
{/if}
<br><br>
<h1>{$PAGE_TITLE} Method 2</h1>
{if $errm && $method == '2'}
	<div class="errmsg">
		<ul>
			<li>{$errm}</li>
		</ul>
	</div>
{/if}
<span id="span_sr_loading_2" style="display:none;background: yellow;padding:2px;">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>
<form name="f_b" enctype="multipart/form-data" class="stdframe" onsubmit="return IMPORT_BRAND.check_file(this);" method="post">
	<input type="hidden" name="a" value="show_result" />
	<input type="hidden" name="method" value="2" />
	<input type="hidden" name="file_name" value="{$file_name}" />
	<table>
		<tr>
			<td colspan="4" style="color:#0000ff;">
				Note:<br />
				* Please ensure the file extension <b>".csv"</b>.<br/>
				* Please ensure the import file contains header.<br/>
				* Code and Group Code's maximum characters is 6.<br/>
			</td>
		</tr>
		<tr>
			<td><b>Upload CSV <br />(<a href="?a=download_sample_brand&method=2">Download Sample</a>)</b></td>
			<td>
				<input type="file" name="import_csv"/>&nbsp;&nbsp;&nbsp;
				<input type="Submit" value="Show Result" />
			</td>
		</tr>
	</table>
	<div class="div_tbl">
		<h3>Sample</h3>
		<table id="si_tbl" width="50%">
			<tr bgcolor="#ffffff">
				{foreach from=$sample_headers[2] item=i}
					<th>{$i}</th>
				{/foreach}
			</tr>
			{foreach from=$sample[2] item=s}
				<tr>
				{foreach from=$s item=i}
					<td>{$i}</td>
				{/foreach}
				</tr>
			{/foreach}
		</table>
	</div>
	<div id="div_invalid_2" style="display: none">
		<div style="border:solid 2px red; padding: 5px; background-color:yellow">
			<p style="font-weight: bold">* Import Successfully. Click <a id="invalid_link_2" href='#' download>this</a> to download and view the invalid data.</p>
		</div>
	</div>
</form>
<br>
{if $item_lists && $method == '2'}
	<div class="div_result" id="div_result">
		{include file="admin.import_brand.result.tpl"}
	</div>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
IMPORT_BRAND.initialize();
{/literal}
</script>