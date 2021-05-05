{*
11/04/2020 10:12 AM Sheila
- Fixed title, table and form css

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}
{include file='header.tpl'}
{literal}
<style>
.tr_error{
	color: red;
}
</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var IMPORT_BATCH = {
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
    import_csv: function(m) {
        if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
		
        $('import_btn').disabled = true;
		document.f_a['a'].value = 'import_batch';
		document.f_a.submit();
	}
}
{/literal}
</script>
<h1>
Import Batch Barcode
</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > {if $smarty.request.find_batch_barcode}<a href="{$smarty.request.PHPSELF}?a=open&find_batch_barcode={$smarty.request.find_batch_barcode}">Back to search</a> > {/if} <a href="home.php?a=menu&id=batch_barcode">{$module_name}</a></span>
<div style="margin-bottom: 10px"></div>


<div style="float:right;" class="btn_padding">
  <input type="button" value="Back" onclick="window.location='/pda/batch_barcode.php';" />
</div>

<div class="stdframe" style="background:#fff">
<form name="f_a" enctype="multipart/form-data"  method="post" onsubmit="return IMPORT_BATCH.check_file(this);">
	<div style="clear:both;"></div>
	<input type="hidden" name="method" value="1" />
	<input type="hidden" name="a" value="show_result" />
	<input type="hidden" name="file_name" value="{$file_name}" />
	
	<h4>{if $smarty.session.batch_barcode.id}#{$smarty.session.batch_barcode.id}{else}New{/if}</h4>
	
	<table cellspacing="0" cellpadding="4" border="1" width="100%">
		<tr>
			<td colspan="4" style="color:#0000ff;">
				Note:<br />
				* Please ensure the file extension <b>".csv"</b>.<br />
				* Please ensure the import file contains header.<br />
			</td>
		</tr>
		<tr>
			<td><b>Upload CSV<br />(<a href="{$smarty.server.PHP_SELF}?a=download_sample_batch&method=1">Download Sample</a>)</b></td>
			<td><input type="file" style="width: 100%;" name="import_csv" /></td>
		</tr>
		<tr>
			<td><input type="checkbox" name="allow_duplicate" value="1" {if $form.allow_duplicate}checked{/if} /></td>
			<td>Automatically add qty when item duplicate</td>
		</tr>
		<tr>
			<td></td>
			<td><input type="Submit" value="Show Result" /></td>
		</tr>
	</table>

	<br>
	Sample
	<table width="100%" border="1" cellspacing="0" cellpadding="4">
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
</form>
</div>
<br><br>
{if $item_lists && ($method == '1' || $partial_ok =='1')}
<div id="div_result">
	{if $partial_ok neq '1'}Result Status:{else}Item Failed to Import:{/if}
	<p style="color:blue;">
		{if $result.import_row}
			Total {$result.import_row} of {$result.ttl_row} item(s) will be imported.<br />
		{/if}
		{if $result.error_row > 0}
			Total {$result.error_row} of {$result.ttl_row} item(s) will fail to import due to some error found, please check the error message at the end of <span style="color:red">highlighted</span> row.<br />
		{/if}
	</p>
	{if $partial_ok neq '1'}
	<div style="float:left;" class="btn_padding">
		<input type="button" id="import_btn" name="import_btn" value="Import" onclick="IMPORT_BATCH.import_csv({$method});" {if !$result.import_row}disabled{/if} />
	</div>
	{/if}
	
	<table width="100%" border="1">
		<tr bgcolor="#ffffff">
			<th>#</th>
			{foreach from=$item_header item=i}
				<th>{$i}</th>
			{/foreach}
		</tr>
		<tbody>
		{foreach from=$item_lists item=i name=batch_barcode}
			<tr class="{if $i.error}tr_error{/if}">
				<td>{$smarty.foreach.batch_barcode.iteration}.</td>
				{foreach from=$i key=k item=r}
					<td>{$r}</td>
				{/foreach}
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>
{else}
	{if $errm}
		<ul style="color:red;">
			{foreach from=$errm item=e}
				<li>{$e}</li>
			{/foreach}
		</ul>
	{/if}
{/if}

<script>
{literal}
	IMPORT_BATCH.initialize();
{/literal}
</script>
{include file='footer.tpl'}
