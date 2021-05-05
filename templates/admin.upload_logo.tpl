{*
3/24/2017 2:38 PM Justin
- Enhanced system can only accepts JPG/JPEG for logo upload.
- Enhanced to have validation on logo size of 5mb max only.

6/18/2019 10:38 AM William
- Added new Logo is Vertical Size setting checkbox.
*}

{include file='header.tpl'}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
function upload()
{
	if (document.f_a['logo'].value == '') {
		alert('Select a file to upload');
		return false;
	}else if (!/\.jpg|\.jpeg/i.test(document.f_a['logo'].value))
	{
		alert("Selected file must be a valid JPG/JPEG image");
		return false;
	}
	
	var oFile = document.f_a['logo'].files[0];
	if (oFile.size > 5242880) // 5 mb for bytes.
	{
		alert("Image File Size is limited to a maximum of 5MB only.");
		return false;
	}
	
	if(!confirm('Are you sure?')) return false;
	
	document.f_a['import_btn'].disabled = true;
	$('span_sr_loading').show();
	
	return true;
}
function update_logosetting(){
	if(!confirm('Are you sure?')) return false;
	return true;
}
function enable_hide_company_name(){
	var is_vertical_logo = $('is_vertical_logo');
	var verticle_logo_no_company_name = $('verticle_logo_no_company_name');
	
	if(is_vertical_logo.checked == true){
		verticle_logo_no_company_name.disabled = false;
	}else{
		verticle_logo_no_company_name.disabled = true;
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $smarty.request.updated}
	<img src="ui/approved.png" /> Settings Updated.<br /><br />
{/if}

<span id="span_sr_loading" style="display:none;background:yellow;padding:2px;">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>

<div class="stdframe" style="background-color: #fff;">
	{if $err}
		<div id="err"><div class="errmsg"><ul>
		{foreach from=$err item=e}
		<li> {$e}
		{/foreach}
		</ul></div></div>
	{/if}

	<h2>Current Logo:</h2>
	{if $system_settings.logo_vertical == 1}
		<img style="border:2px solid black;max-width:800px;max-height:250px;" src="{#LOGO_IMAGE#}?{$smarty.now}" /><br/><br />
	{else}
		<img style="border:2px solid black;max-width:250px;max-height:250px;height:250px" src="{#LOGO_IMAGE#}?{$smarty.now}" /><br/><br />
	{/if}
		
	<form name="f_a" enctype="multipart/form-data" onsubmit="return upload();" method="post">
		<input type="hidden" name="a" value="upload" />
		<table>
			<tr>
				<td style="color:#0000ff;">
					<b>Note:</b>
					<ul>
						<li>Please ensure the file is a valid image file (JPG/JPEG).</li>
						<li>Uploaded logo will replace existing one.</li>
						<li>Image File Size is limited to a maximum of 5MB only.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td>
					<b>Upload New Logo: </b>
					&nbsp;&nbsp;&nbsp;
					<input type="file" name="logo"/>&nbsp;&nbsp;&nbsp;
					<input type="submit" name="import_btn" value="Upload" />
				</td>
			</tr>
		</table>
	</form>
</div>

<br />
<div class="stdframe">
	<h2>Logo Settings</h2>
	<form name="f_a2"  enctype="multipart/form-data" onsubmit="return update_logosetting();" method="post">
		<input type="hidden" name="a" value="update_logosetting" />
		<table>
		<tr>
			<td colspan="2"><span><input id="is_vertical_logo" onchange="enable_hide_company_name()" type="checkbox" value="1" name="is_vertical_logo" {if $system_settings.logo_vertical eq 1}checked{/if} />  Logo is Vertical Size (<a href="#" onClick="alert('This logo will only used in document DO, PO, Sales Order and Adjustment for all branch.')">?</a>)</span></td>
			<td></td>
		</tr>
		<tr>
			<td colspan="2"><span><input id="verticle_logo_no_company_name"  {if $system_settings.logo_vertical neq 1}disabled{/if} type="checkbox" value="1" name="verticle_logo_no_company_name" {if $system_settings.verticle_logo_no_company_name eq 1}checked{/if} />  No Need Show Company Name When Logo Is Vertical </span></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="update_setting" value="Update Setting" /></td>
		</tr>
		</table>
	</form>
</div>
{include file='footer.tpl'}
