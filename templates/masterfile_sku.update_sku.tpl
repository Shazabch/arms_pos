{*
01/05/2020 10:31 AM Rayleen
- New module "Update SKU Info by CSV"
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

#download_csv{
	background-color: #32405b;
	border-color: #32405b;
	color: #fff;
	padding: 4px 12px;
	font-size: 13px;
	line-height: 1.42857143;
	vertical-align: middle;
	font-weight: bold;
}
</style>
{/literal}
<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var UPDATE_SKU_INFO = {
	f_a: undefined,
	f_b: undefined,
	initialize: function() {
		this.f_a = document.f_a;
		this.f_b = document.f_b;
		this.f_c = document.f_b;
	},
	update_sku: function(m) {
		if(this.validate() == false) return false;
	
		if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
		
		$('update_btn').disabled = true;
		$('span_loading').show();
		
		var file = this.f_b['file_name'].value;
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
		if(obj != undefined && obj.name == "f_b"){
			var filename = this.f_b['update_csv'].value;
			console.log(filename);
			if(filename.indexOf('.csv')<0){
				alert('Please select a valid csv file');
				return false;
			}
		}
		
		return true;
	},
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
<form name="f_a" enctype="multipart/form-data" class="stdframe" method="post" >
	<input type="hidden" name="a" value="download_csv" />
	<input type="hidden" name="method" value="1" />
	<span style="color:#0000ff;">
	Note:<br />
	* This module will update the SKU Info.<br />
	* The SKU Code column can be the ARMS Code, MCode, {$config.link_code_name} or Artno.<br />
	</span><br>
	<table>
		<tr>
			<td><b>Fields To Update</td>
			<td>
				{foreach from=$fields key=field_id item=field}
					{assign var="hide" value="0"}
					{assign var="field" value=$field}
					{if $field_id eq 'rsp_discount'}
						{assign var="hide" value="1"}
					{/if}
					{if $field_id eq 'rsp_price'}
						{assign var="field" value="RSP and RSP Discount"}
					{/if}
					<span {if $hide}style="display:none;"{/if}><input type="checkbox" id="{$field_id}" name="fields[]" value="{$field_id}" >{$field}<br></span>
				{/foreach}
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input type="Submit" value="Download Sample CSV File" name="download_csv" id="download_csv" />
			</td>
		</tr>
	</table>
</form>
<br>
<form name="f_b" enctype="multipart/form-data" class="stdframe" onsubmit="return UPDATE_SKU_INFO.validate(this);" method="post">
	<input type="hidden" name="a" value="upload_csv" />
	<input type="hidden" name="method" value="1" />
	<input type="hidden" name="file_name" value="{$file_name}" />

	<table>
		<tr>
			<td><b>Upload CSV</b>&emsp;&emsp;</td>
			<td>
				<input type="file" name="update_csv"/>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input type="Submit" value="Show Result" name="upload_csv" />
			</td>
		</tr>
	</table>
	<div class="div_tbl" style="display: none;">
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
	<form name="f_c" method="post">
		<div class="div_result" id="div_result">
			{include file="masterfile_sku.update_sku.result.tpl"}
		</div>
	</form>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
UPDATE_SKU_INFO.initialize();

$('rsp_price').observe('click', function(e) {
    var checked = this.checked;
    if(checked){
    	$('rsp_discount').setAttribute('checked', checked);
	}else{
    	$('rsp_discount').removeAttribute('checked', false);
	}
});
{/literal}
</script>