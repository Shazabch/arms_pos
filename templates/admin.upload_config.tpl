{include file='header.tpl'}

{literal}
<style>
#config_upload_result tr:hover td {background:#F3E2A9}
</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function upload()
{
	if (document.f_a['import_csv'].value == '') {
		alert('Select a CSV file to upload');
		return false;
	}
	
	if(!confirm('Are you sure?')) return false;
	
	document.f_a['import_btn'].disabled = true;
	$('span_sr_loading').show();
	
	return true;
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<span id="span_sr_loading" style="display:none;background:yellow;padding:2px;">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>

{if $show_result}
<div class="stdframe">

	{if $result}
	<span style="font-size:110%;font-weight:bold;">Successfully uploaded.</span><br/><br />
	<table id="config_upload_result" cellpadding="1" cellspacing="0">
	<th align="left" style="border-bottom:solid 1px;">Config Name</th>
	<th style="border-bottom:solid 1px;">&nbsp;</th>
	<th align="left" style="border-bottom:solid 1px;">Value</th>
	<th style="border-bottom:solid 1px;">&nbsp;</th>
	<th align="left" style="border-bottom:solid 1px;">&nbsp;</th>

		{foreach from=$result item=r}
		<tr>
		{if $r.error}
			<td colspan="5">
				<span style="color:#000;font-size:90%;">
					<br /><span style="color:#F00;font-weight:bold;">(Error)</span> - {$r.error}<br /><br />
				</span>
			</td>
		{else}
			<td><span style="color:#21610B;font-size:90%;">&nbsp;&nbsp;<b>{$r.name}</b></span></td>
			<td><span style="font-size:90%;">&nbsp;&nbsp;&nbsp;</span></td>
			<td><span style="color:{$r.color};font-size:90%;">&nbsp;&nbsp;<b>{$r.value}</b></span></td>
			<td><span style="font-size:90%;">&nbsp;&nbsp;&nbsp;</span></td>
			<td><span style="color:#585858;font-size:90%;font-style:italic;">&nbsp;&nbsp;&nbsp;{$r.desc}</span></td>
		{/if}
		</tr>
		{/foreach}
		
	</table><br />
	{else}
	<span style="font-size:110%;font-weight:bold;">Empty result.</span><br/><br />
	{/if}
</div>
<br />
{/if}

<form name="f_a" enctype="multipart/form-data" onsubmit="return upload();" method="post">
	<input type="hidden" name="a" value="upload" />
	<table>
		<tr>
			<td colspan="4" style="color:#0000ff;">
				<b>Note:</b>
				<ul>
					<li>Please ensure the file extension is <b>CSV</b>.</li>
					<li>Uploaded config will replace existing one.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td><b>Upload CSV <br />(<a href="?a=view_sample">View Sample</a>)</b></td>
			<td>
				&nbsp;&nbsp;&nbsp;
				<input type="file" name="import_csv"/>&nbsp;&nbsp;&nbsp;
				<input type="submit" name="import_btn" value="Upload" />
			</td>
		</tr>
	</table>
</form>

{include file='footer.tpl'}
