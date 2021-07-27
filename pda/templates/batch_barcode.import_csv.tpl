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
			notify('error','Please select a valid csv file','center')
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
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.IMPORT_BATCH_BARCODE}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInDown">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		{if $smarty.request.find_batch_barcode}
		<li class="breadcrumb-item">
			<a  href="{$smarty.request.PHPSELF}?a=open&find_batch_barcode={$smarty.request.find_batch_barcode}">{$LNG.BACK_TO_SEARCH}</a>
		</li>
		{/if}
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id=batch_barcode">{$module_name}</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->
<div class="container-fluid">
	<div class="alert alert-info mb-0 mt-2 pb-0 p-2 mb-2" role="alert">
	  <h5 class="alert-heading"><i class="fas fa-bullhorn"></i> Note</h5>
	  <ul>
	  	<li>{$LNG.PLZ_ENSURE_FILE_EXTENSION} <b>".{$LNG.CSV}"</b></li>
	  	<li>{$LNG.PLZ_ENSURE_IMPORT_FILE_CONTAINS_HEADER}</li>
	  </ul>
	</div>
	<div class="card ">
		<div class="card-header border-bottom">
			<div class="d-flex justify-content-between align-items-center">
				<h3>{$LNG.NEW}</h3>
				<a onclick="window.location='/pda/batch_barcode.php';" class="btn btn-primary">{$LNG.BACK}</a>
			</div>
		</div>
		<div class="card-body">
			<div class="">
				<form name="f_a" enctype="multipart/form-data"  method="post" onsubmit="return IMPORT_BATCH.check_file(this);">
					<input type="hidden" name="method" value="1" />
					<input type="hidden" name="a" value="show_result" />
					<input type="hidden" name="file_name" value="{$file_name}" />
					<div class="form-group mt-2">
						<label class="font-weight-bold">{$LNG.UPLOAD_CSV}</label><a href="{$smarty.server.PHP_SELF}?a=download_sample_batch&method=1" class="fs-07"><i class="ml-3 text-muted fas fa-file-download"></i> {$LNG.DOWNLOAD_SAMPLE}</a>
						<input type="file" class="form-control mt-2" data-height="200" name="import_csv" />
					</div>
					<div class="form-group mt-2">
						<div class="checkbox">
							<div class="custom-checkbox custom-control">
								<input type="checkbox" data-checkboxes="mygroup" class="custom-control-input" id="checkbox-2" name="allow_duplicate" value="1" {if $form.allow_duplicate}checked{/if}>
								<label for="checkbox-2" class="custom-control-label mt-1">{$LNG.AUTO_ADD_QTY_WHEN_ITEM_DUPLICATE}</label>
							</div>
						</div>
					</div>
					<input type="Submit" class="btn btn-success" value="{$LNG.SHOW_RESULT}">
				</form>
			</div>
		</div>
	</div>
</div>
<!--Table-->
<div class="col-xl-12">
	<div class="card">
		<div class="card-header pb-0">
			<div class="d-flex justify-content-between">
				<h4 class="card-title mg-b-0">{$LNG.SAMPLE}</h4>
			</div>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover mb-0 text-md-nowrap">
					<thead>
						<tr>
							{foreach from=$sample_headers[1] item=i}
								<th>{$i}</th>
							{/foreach}
						</tr>
					</thead>
					<tbody>
						{foreach from=$sample[1] item=s}
							<tr>
							{foreach from=$s item=i}
								<td>{$i}</td>
							{/foreach}
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- /Table -->

{if $item_lists && ($method == '1' || $partial_ok =='1')}
<div id="div_result">
	<div class="mx-3">
		{if $partial_ok neq '1'}{$LNG.RESULT_STATUS}:{else}{$LNG.ITEM_FAILED_TO_IMPORT}:{/if}
			{if $result.import_row}
				<div class="alert alert-success fade show">
					{$LNG.TOTAL} {$result.import_row} {$LNG.OF} {$result.ttl_row} {$LNG.ITEMS_WILL_BE_IMPORTED}<br />
				</div>
			{/if}
			{if $result.error_row > 0}
				<div class="alert alert-danger fade show">
					{$LNG.TOTAL} {$result.error_row} {$LNG.OF} {$result.ttl_row} {$LNG.FAILED_IMPORT_ERR_MSG}<br />
				</div>
			{/if}

		{if $partial_ok neq '1'}
		<button class="btn btn-success mb-2 " type="button" id="import_btn" name="import_btn" value="Import" onclick="IMPORT_BATCH.import_csv({$method});" {if !$result.import_row}disabled{/if}><i class="fas fa-file-upload mr-2"></i> Import</button>
		{/if}
	</div>

<!--Table-->
<div class="col-xl-12">
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover mb-0 text-md-nowrap">
					<thead>
						<tr>
							<th>#</th>
							{foreach from=$item_header item=i}
								<th>{$i}</th>
							{/foreach}
						</tr>
					</thead>
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
		</div>
	</div>
</div>
<!-- /Table -->
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
