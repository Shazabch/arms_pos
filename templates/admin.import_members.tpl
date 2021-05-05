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
	<div style="border:2px solid red;padding:5px;background-color:yellow;color:red;font-weight:bold;font-size:120%;">
		Warning: 
		<ul>
			<li> Please prevent to import at business hours, it will directly slow down the performance of all counters across branches.</li>
			<li> It is recommended to import maximum of 500 SKU in a batch, and wait for 10 minutes while counter do sync process.</li>
			<li> This action CANNOT be undo.</li>
		</ul>
		
	</div>
{/if}
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
<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return IMPORT_MEMBERS.check_file(this);" method="post">
	<input type="hidden" name="a" value="show_result" />
	<input type="hidden" name="method" value="1" />
	<input type="hidden" name="file_name" value="{$file_name}" />
	<table>
		<tr>
			<td colspan="4" style="color:#0000ff;">
				Note:<br />
				* Please ensure the file extension <b>".csv"</b>.<br/>
				* Please ensure the import file contains header.<br/>
				* Verify and Expiry Date format must be in YYYY-MM-DD.<br/>
				* The system will auto remove the special character and empty space of the import column(Card No, Points, Name, NRIC, Race, DOB, Nationality, Mobile, Phone, Member Type, Fax, City, State, Postcode).</br>
				
				<br />Member Type<br />
				<table style="background-color:#fff;">
					{foreach from=$config.membership_type key=k item=desc}
						<tr>
							<td>{$k}</td>
							<td> = </td>
							<td>{$desc}</td>
						</tr>
					{/foreach}
				</table>
			</td>
		</tr>
		<tr>
			<td><b>Upload CSV <br />(<a href="?a=download_sample_members&method=1">Download Sample</a>)</b></td>
			<td>
				<input type="file" name="import_csv"/>&nbsp;&nbsp;&nbsp;
				<input type="Submit" value="Show Result" />
			</td>
		</tr>
	</table>
	<div class="div_tbl">
		<h3>Sample</h3>
		<table id="si_tbl" width="100%">
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
		{include file="admin.import_members.result.tpl"}
	</div>
{/if}
<br><br>

{include file='footer.tpl'}
<script type="text/javascript">
{literal}
IMPORT_MEMBERS.initialize();
{/literal}
</script>