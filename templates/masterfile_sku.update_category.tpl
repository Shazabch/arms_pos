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
var UPDATE_SKU_CATEGORY_MODULE = {
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
							$('invalid_link').href = 'attachments/update_sku_category/invalid_'+file;
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
		
		return true;
	}
}

{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>
{if $errm}
	<div class="errmsg">
		<ul>
			<li>{$errm}</li>
		</ul>
	</div>
{/if}
<span id="span_loading" style="display:none; background:yellow; padding:2px;">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>
<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return UPDATE_SKU_CATEGORY_MODULE.validate(this);" method="post">
	<input type="hidden" name="a" value="show_result" />
	<input type="hidden" name="method" value="1" />
	<input type="hidden" name="file_name" value="{$file_name}" />
	<table>
		<tr>
			<td colspan="4" style="color:#0000ff;">
				Note:<br />
				* This module will update the SKU's category.<br />
				* Category is mandatory.
			</td>
		</tr>
		<tr>
			<td><b>Upload CSV <br />(<a href="?a=download_sample&method=1">Download Sample</a>)</b></td>
			<td>
				<input type="file" name="update_csv"/>&nbsp;&nbsp;&nbsp;
				<input type="Submit" value="Show Result" />
			</td>
		</tr>
	</div>
	</table>
	<div class="div_tbl">
		<h3>Sample</h3>
		<table id="si_tbl" width="25%">
			<tr bgcolor="#ffffff">
				{foreach from=$sample_headers.1 item=i}
					<th>{$i}</th>
				{/foreach}
			</tr>
			{foreach from=$sample.1 item=s}
				<tr>
				{foreach from=$s item=i}
					<td>{$i}</td>
				{/foreach}
				</tr>
			{/foreach}
		</table>
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
			{include file="masterfile_sku.update_category.result.tpl"}
		</div>
	</form>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
UPDATE_SKU_CATEGORY_MODULE.initialize();
{/literal}
</script>