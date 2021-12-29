{*
9/27/2016 16:04 Qiu Ying
- Change Vendor to Member

8/5/2020 10:51 AM William
- Enhanced to add new note for remove special character from import list.

8/19/2020 1:25 PM Andy
- Enhanced to have member type sample.
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
var IMPORT_MEMBERS = {
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
	import_members: function(m) {
		if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
			
		var file = '';
		switch(m) {
			case 1:
				file = this.f_a['file_name'].value; break;
		}
		
		$('import_btn').disabled = true;
		$('span_sr_loading_'+m).show();
		
		var params = {
			a: 'ajax_import_members',
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
						alert("Successfully Imported Member Data.");
						$('div_result').hide();
						if (ret['partial_ok'] == 1) {
							$('div_invalid_'+m).show();
							$('invalid_link_'+m).href = 'attachments/members_import/invalid_'+file;
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
{if !$config.consignment_modules}

	<div class="card mx-3 mt-3">
		<div class="card-body">
			<b class="text-danger fs-09">
				Warning:
			</b>
			<ul class="text-muted">
				<li> Please prevent to import at business hours, it will directly slow down the performance of all counters across branches.</li>
				<li> It is recommended to import maximum of 500 SKU in a batch, and wait for 10 minutes while counter do sync process.</li>
				<li> This action CANNOT be undo.</li>
			</ul>
		</div>
	</div>
		

{/if}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div> 
{if $errm && $method == '1'}
	<div class="alert alert-danger rounded mx-3">
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
<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return IMPORT_MEMBERS.check_file(this);" method="post">
	<input type="hidden" name="a" value="show_result" />
	<input type="hidden" name="method" value="1" />
	<input type="hidden" name="file_name" value="{$file_name}" />
	
	
			<div class="alert alert-primary rounded mx-3">
				<b>Note:</b><br />
				* Please ensure the file extension <b>".csv"</b>.<br/>
				* Please ensure the import file contains header.<br/>
				* Verify and Expiry Date format must be in YYYY-MM-DD.<br/>
				* The system will auto remove the special character and empty space of the import column(Card No, Points, Name, NRIC, Race, DOB, Nationality, Mobile, Phone, Member Type, Fax, City, State, Postcode).</br>
			</div>
				<div class="card mx-3">
					<div class="card-body">
					<table>
				<b class="fs-09">Member Type</b>
				
				{foreach from=$config.membership_type key=k item=desc}
					<tr class="fs-09">
						<td>{$k}</td>
						<td> = </td>
						<td>{$desc}</td>
					</tr>
				{/foreach}
			</table>
		</td>
		<table>
	</tr>
	<hr>
	<tr>
		<td><b class="fs-09">Upload CSV <br />(<a class="fs-08" href="?a=download_sample_members&method=1">Download Sample</a>)</b></td>
		<td>
		&nbsp;&nbsp;	<input type="file" class="fs-08"  name="import_csv"/>
			<input type="Submit" class="btn btn-primary fs-08 mt-2 mt-md-0 ml-2 ml-md-0" value="Show Result" />
		</td>
	</tr>
</table>
<div class="div_tbl mt-3">
	<h5>Sample : </h5>
	<div class="table-responsive">
		<table id="si_tbl" class="report_table table mb-0 text-md-nowrap  table-hover " >
		<thead class="bg-gray-100">
			<tr>
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
<div id="div_invalid_1" style="display: none">
	
	<div class="alety alert-success">
		<p style="font-weight: bold">* Import Successfully. Click <a id="invalid_link_1" href='#' download>this</a> to download and view the invalid data.</p>
	</div>

</div>
</form>

					</div>
				</div>
				{if $item_lists && $method == '1'}
				<div class="div_result" id="div_result">
					{include file="admin.import_members.result.tpl"}
				</div>
				{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
IMPORT_MEMBERS.initialize();
{/literal}
</script>