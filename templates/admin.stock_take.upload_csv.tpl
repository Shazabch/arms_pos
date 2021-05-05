{*
12/19/2018 5:14 PM Andy
- Enhanced to can add new stock take by csv.
*}

{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

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

var IMPORT_STOCK_TAKE = {
	f_a: undefined,
	f_b: undefined,
	initialise: function(){
		this.f_a = document.f_a;
		if(document.f_b)	this.f_b = document.f_b;
		
		this.init_calendar();
	},
	init_calendar: function(){
		
		Calendar.setup({
			inputField     :    "inp_date",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
			//,
			//onUpdate       :    load_data
		});
		
	},
	// function to check form before show result
	check_form: function(){		
		if(!check_required_field(this.f_a))	return false;
		
		// Check csv file
		var filename = this.f_a['import_csv'].value;
		if(filename.indexOf('.csv')<0){
			alert('Please select a valid csv file');
			return false;
		}
		
		return true;
	},
	// function when users click on show result
	show_result: function(){
		if(!this.check_form()) return false;
		
		this.f_a.submit();
		
		return true;
	},
	
	// function when users click on import
	import_clicked: function(){		
		if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
		
		$('import_btn').disabled = true;
		$('span_loading').show();
		
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters: $(THIS.f_b).serialize(),
			onComplete: function(msg){	
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';
				$('span_loading').hide();
				
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1 || ret['partial_ok'] == 1){ // success
						alert("Successfully Imported.");
						//$('div_result').hide();
						if (ret['partial_ok'] == 1) {
							$('div_invalid').show();
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
			}
		});
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return false;" method="post">
	<input type="hidden" name="show_result" value="1" />
	
	<table border="0">
		<tr>
			<td colspan="4" style="color:#0000ff;">
				Note:<br />
				* Please ensure the file extension <b>".csv"</b>.<br/>
				* Please ensure the csv file contains header.<br/>
				* This will import as Stock Take Pre Data, you need to use "Import / Reset Stock Take" Module to import again as Real Stock Take.<br/>
				
				{if $config.enable_fresh_market_sku}
					* This module can import Fresh Market SKU and Non-Fresh Market SKU. But you need to refer to Fresh Market Stock Take Module after import Fresh Market SKU.<br/>
				{/if}
			</td>
		</tr>
		
		{* Branch *}
		{if $can_select_branch}
			<tr>
				<td><b>Branch</b></td>
				<td>
					<select name="branch_id" class="required" title="Branch">
						<option value="">-- Please Select --</option>
						{foreach from=$branches_list key=bid item=b}
							<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/foreach}
					</select>
					<img src="/ui/rq.gif" align="absmiddle" />
				</td>
			</tr>
		{/if}
		
		{* Date *}
		<tr>
			<td><b>Date</b></td>
			<td>
				<input name="date" id="inp_date" size="12" value="{$smarty.request.date|default:$smarty.now|date_format:'%Y-%m-%d'}" readonly /> 
				<img align="absmiddle" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date">
				<img src="/ui/rq.gif" align="absmiddle" />
			</td>
		</tr>
		
		{* Location *}
		<tr>
			<td><b>Location</b></td>
			<td>
				<input type="text" name="location" maxlength="15" class="required" title="Location" value="{$smarty.request.location}" />
				<img src="/ui/rq.gif" align="absmiddle" />
				(Max 15 Characters)
			</td>
		</tr>
		
		{* Shelf No *}
		<tr>
			<td><b>Shelf No</b></td>
			<td>
				<input type="text" name="shelf_no" maxlength="15" class="required" title="Shelf No" value="{$smarty.request.shelf_no}" />
				<img src="/ui/rq.gif" align="absmiddle" />
				(Max 15 Characters)
			</td>
		</tr>
				
		{* Allow Duplicate Entry *}
		</tr>
			<td><b>Allow Duplicate Entry</b></td>
			<td>
				<input type="checkbox" name="sum_duplicate" value="1" {if $smarty.request.sum_duplicate}checked {/if} />
				(if found duplicate will sum up qty)
			</td>
		</tr>
		
		{* CSV *}
		<tr>
			<td><b>Upload CSV <br />(<a href="?a=download_sample">Download Sample</a>)</b></td>
			<td>
				<input type="file" name="import_csv"/>&nbsp;&nbsp;&nbsp;
				<input type="button" value="Show Result" onClick="IMPORT_STOCK_TAKE.show_result();" />
			</td>
		</tr>
		
		
	</table>
	<div class="div_tbl">
		<h3>Sample</h3>
		<table id="si_tbl" width="25%">
			<tr bgcolor="#ffffff">
				{foreach from=$sample_data.header item=i}
					<th>{$i}</th>
				{/foreach}
			</tr>
			{foreach from=$sample_data.items item=s}
				<tr>
					{foreach from=$s item=i}
						<td>{$i}</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>
	</div>
</form>


{if $item_lists}
	<h3>Result Status:</h3>
	<p style="color:blue;">
		{if $result.import_row}
			Total {$result.import_row} of {$result.ttl_row} item(s) will be imported.<br />
			{if $result.fresh_market_sku > 0}
				Total {$result.fresh_market_sku} of {$result.import_row} item(s) are Fresh Market SKU, please refer to Fresh Market Stock Take Module after imported.<br />
			{/if}
		{/if}
		{if $result.error_row > 0}
			Total {$result.error_row} of {$result.ttl_row} item(s) will fail to import due to some error found, please check the error message at the end of <span style="color:red">highlighted</span> row.<br />
			Additionally, click <a id="invalid_link" href="attachments/{$folder_name}/invalid/{$file_name}">HERE</a> to download and view the invalid data.<br />
		{/if}
		
		* Please ENSURE the result data is fill to the header accordingly before proceed to import.<br />
	</p>
	
	<div id="div_action" class="stdframe">
		<form name="f_b" onSubmit="return false;">
			<input type="hidden" name="a" value="ajax_import_stock_take" />
			<input type="hidden" name="file_name" value="{$file_name}" />
			
			{if $can_select_branch}
				<input type="hidden" name="branch_id" value="{$smarty.request.branch_id}" />
			{/if}
			
			<input type="hidden" name="date" value="{$smarty.request.date}" />
			<input type="hidden" name="location" value="{$smarty.request.location}" />
			<input type="hidden" name="shelf_no" value="{$smarty.request.shelf_no}" />
			<input type="hidden" name="sum_duplicate" value="{$smarty.request.sum_duplicate}" />
			
			<table>
				{if $can_select_branch}
					<tr>
						<td><b>Branch</b></td>
						<td>: {$branches_list[$smarty.request.branch_id].code}</td>
					</tr>
				{/if}
				
				<tr>
					<td><b>Date</b></td>
					<td>: {$smarty.request.date}</td>
				</tr>
				
				<tr>
					<td><b>Location</b></td>
					<td>: {$smarty.request.location}</td>
				</tr>
				
				<tr>
					<td><b>Shelf No</b></td>
					<td>: {$smarty.request.shelf_no}</td>
				</tr>
				
				<tr>
					<td><b>Sum Duplicate Entry</b></td>
					<td>: {if $smarty.request.sum_duplicate}Yes{else}No{/if}</td>
				</tr>
				
				
			</table>
		</form>
		
		<br />
		<input type="button" id="import_btn" value="Import" onclick="IMPORT_STOCK_TAKE.import_clicked();" {if !$result.import_row}disabled{/if} />
		
		<span id="span_loading" style="display:none; background:yellow; padding:2px;">
			<img src="/ui/clock.gif" align="absmiddle" /> Loading...
		</span>
		
		
	</div>
	
	<div id="div_invalid" style="display: none">
		<div style="border: solid 2px red; padding: 5px; background-color: yellow">
			<p style="font-weight: bold">* Import Successfully. Click <a href="attachments/{$folder_name}/invalid/{$file_name}">this</a> to download and view the invalid data.</p>
		</div>
	</div>
		
	<br/>

	<div class="div_result" id="div_result">
		<table width="100%">
			<tr bgcolor="#ffffff">
				<th>#</th>
				{foreach from=$sample_data.header item=i}
					<th>{$i}</th>
				{/foreach}
				<th>Error</th>
			</tr>
			
			<tbody>
				{foreach from=$item_lists item=r name=fi}
					<tr class="{if $r.info.error}tr_error{/if}">
						<td>{$smarty.foreach.fi.iteration}.</td>
						{foreach from=$r.raw item=v}
							<td>{$v}</td>
						{/foreach}
						<td>{$r.info.error}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{/if}

<script>
	IMPORT_STOCK_TAKE.initialise();
</script>
{include file='footer.tpl'}