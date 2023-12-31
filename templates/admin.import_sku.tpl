{*
8/6/2013 3:38 PM Justin
- Enhanced to show card no list before confirm to insert new member.

2/25/2014 3:11 PM Justin
- Enhanced to add new notes to provide more info towards the import format.

7/11/2014 10:50 AM Justin
- Enhanced to have 5 category levels.

10/2/2014 2:52 PM Justin
- Enhanced to have capture Parent & Child by Artno/Old Code.

10/3/2014 10:40 AM Justin
- Bug fixed on system always do search for parent and child.

10/9/2014 2:47 PM Justin
- Enhanced to have checking on UOM (if found got check by insert parent & child and first item UOM not "EACH", then the rest will capture as error).
- Enhanced the duplicate check to include UOM.

11/30/2015 3:38 PM Justin
- Enhanced to have import GST information by SKU.

12/18/2015 3:00 PM DingRen
- add Note for SKU type

04/26/2016 14:30 Edwin
- Enhanced on add or update PO max and min qty if filled
- Added parent_arms_code, parent_mcode and parent_artno to check and assign parent-child sku_items

07/27/2016 11:00 Edwin
- Change coding structure

10/25/2016 11:26 AM Andy
- Modify Note.

1/10/2017 4:30 PM Andy
- Remove inclusive tax column.

7/5/2018 3:02 PM Justin
- Modified the note for Custom MPrice.

10/15/2018 3:06 PM Andy
- Fixed "Loading" icon position.

10/23/2018 4:48 PM Justin
- Enhanced the remark to load the SKU Type from database instead of hardcoded.

10/9/2020 4:24 PM Andy
- Enhanced to auto detect last column name for display in note.

10/29/2020 5:57 PM William
- Enhanced to show file error download link before import csv file. 

12/28/2020 12:58 PM William
- Enhanced to added note for new column "RSP" and "RSP Discount".
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
var IMPORT_SKU = {
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
	import_sku: function(m) {
		if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
		
		var file = '';
		switch(m) {
			case 1:
				file = this.f_a['file_name'].value; break;
		}
		
		$('import_btn').disabled = true;
		$('span_sr_loading_'+m).show();
	
		var params = {
			a: 'ajax_import_sku',
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
						alert("Successfully Imported SKU Data.");
						$('div_result').hide();
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
{if !$config.consignment_modules}
	
	<div class="card mx-3 mt-3">
		<div class="card-body">
			<b class="text-danger fs-09">Warning:</b> 
		<ul class="text-muted">
			<li> Please prevent to import at business hours, it will directly slow down the performance of all counters across branches.</li>
			<li> It is recommended to import maximum of 500 SKU in a batch, and wait for 10 minutes while counter do sync process.</li>
			<li> This action CANNOT be undo.</li>
		</ul>
		</div>
	</div>

{/if}
<div class="container-fluid">
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div> 
</div>

{if $errm && $method == '1'}
	<div class="alert alert-danger rounded">
		<div class="errmsg">
			<ul>
				<li>{$errm}</li>
			</ul>
		</div>
	</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return IMPORT_SKU.check_file(this);" method="post">
			<input type="hidden" name="a" value="show_result" />
			<input type="hidden" name="method" value="1" />
			<input type="hidden" name="file_name" value="{$file_name}" />
			<table>
				<div class="alert alert-primary rounded">
					<b>Note:</b><br />
						* Please ensure the file extension <b>".csv"</b>.<br />
						* Please ensure the import file contains header.<br />
						* Left empty for UOM column will result to get default value as "EACH".<br />
						* Product Description and Receipt Description cannot contain double quotes ", system will automatically remove it when found.<br />
						* UOM maximum allow 6 characters.<br />
						* Scale Type: 0 = Not Scale Item, 1 = Fixed Price, 2 = Weight.<br />
						* Category levels is optional starting from 2, 3, 4 and 5.<br />
						* Left empty for Is Input Tax, Output Tax column will result to get default value as "inherit".<br />
						* SKU type: 
						  {foreach from=$sku_type_list key=st_code item=st name=stl}
								{$st.description}
								{if !$smarty.foreach.stl.last} or {/if}
						  {/foreach}
						  only(empty field will auto assign to OUTRIGHT).<br />
						* You can append the Custom MPrice after the last column ({$last_col_name}). Example: "{$last_col_name}", "member1", "member2" and follow by Custom MPrice.<br />
						* The custom mprice header must match with system mprice settings. (You can refer to SKU Change Selling Price module)<br />
						* When data import has RSP and RSP Discount, the system will automatically calculate selling price.<br /><br />
				</div>
				<div class="row mt-4">
						<div class="col-md-3">
							<b>Upload CSV <br>(<a href="?a=download_sample_sku&method=1">Download Sample</a>)</b>
						</div>
					
						<div class="col-md-3 mt-2">
							<input type="file" name="import_csv"/>&nbsp;&nbsp;&nbsp;
						</div>
						<div class="col-md-3">
							<input type="Submit" class="btn btn-primary" value="Show Result" />
						</div>
					
				</div>
			</table>
			<div class="div_tbl mt-4">
				<label class="mt-2"><h4>Sample </h4></label>
				<div class="table-responsive">
					<table id="si_tbl" class="report_table table mb-0 text-md-nowrap  table-hover" >
						<thead class="bg-gray-100">
							<tr >
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
		
		</form>
	</div>
</div>
<br>

<span id="span_sr_loading_1" style="display:none;" class="bg-warning">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>

{if $item_lists && $method == '1'}
	<div class="div_result" id="div_result">
		<div class="card mx-3">
			<div class="card-body">
				{if $error_link neq ''}
				<div id="div_invalid_1">
					
				<div class="alert alert-danger rounded">
					<p >Click <a id="invalid_link_1" href='{$error_link}' download>this</a> to download and view the invalid data.</p>
				</div>
	
				
				</div>
				{/if}
				{include file="admin.import_sku.result.tpl"}
			</div>
		</div>
	</div>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
IMPORT_SKU.initialize();
{/literal}
</script>