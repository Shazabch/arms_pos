{*
10/24/2018 2:00 PM HockLee
- New template
- Create Transfer Delivery Order from CSV.

12/6/2018 4:47 PM Andy
- Fixed system cannot accept duplicate item upload from csv.
*}

{include file='header.tpl'}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';

{literal}
var DO_UPLOAD_CSV = {
	f_a: undefined,
	f_b: undefined,
	initialize: function() {
		this.f_a = document.f_a;
		
		if(document.f_b){
			this.f_b = document.f_b;
		}
	},
	check_form: function(){
		if(!this.f_a['import_csv'].value){
			alert("Please select a csv file.");
			return false;
		}
		return true;
	},
	// function when user click upload csv
	upload_csv: function(){
		if(!this.check_form())	return false;
		
		this.f_a.submit();
	},
	// function when user toggle all item selected
	toggle_all_item_selected: function(bid){
		var c = $('inp_all_item_selected-'+bid).checked;
		
		$$('#div_branch_data-'+bid+' input.inp_item_selected').each(function(inp){
			inp.checked = c;
		});
	},
	// function when user click Generate DO
	generate_do: function(bid){
		this.f_b['to_bid'].value = bid;
		this.process_generate_do();
	},
	// function when user click Generate All DO
	generate_all_do: function(){
		this.f_b['to_bid'].value = '';
		this.process_generate_do();
	},
	// Core function to generate DO
	process_generate_do: function(){
		if(!this.check_generate_do())	return false;
		
		if(!confirm('Are you sure?'))   return false;
		
        curtain(true, 'curtain2');
		center_div($('div_generate_do_dialog').show());
        $('div_generate_do_dialog_content').update('Generating DO...<br /><br />'+_loading_);
		
		new Ajax.Request(phpself+'?a=ajax_generate_multi_do', {
            method: 'post',
			parameters: $(this.f_b).serialize(),
			onComplete: function(msg){
                var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_generate_do_dialog_content').update(ret['html']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    close_curtain2();
			    alert(err_msg);
			}
		});
	},
	// function to check can generate do or not
	check_generate_do: function(){
		var to_bid = this.f_b['to_bid'].value;

		if(!to_bid){
			var container = $('div_all_branch');
		}else{
			var container = $('div_branch_data-'+to_bid);
		}
		
		// Check Got Item Selected or Not
		var got_item_selected = false;
		$(container).getElementsBySelector('input.inp_item_selected').each(function(inp){
			if(inp.checked){
				got_item_selected = true;
			}
		});
		if(!got_item_selected){
			alert('Please select at least one item.');
			return false;
		}
		
		return true;
	}
}

function close_curtain2(){
    curtain(false, 'curtain2');
	$('div_generate_do_dialog').hide();
}

{/literal}

</script>

<h1>Create Multiple DO from CSV
	- 
	{if $do_type eq 'transfer'}
		Transfer DO
	{/if}
</h1>

{if $err}
	<div><div class="errmsg"><ul>
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul></div></div>
{/if}

<!-- Generate DO dialog -->
<div id="div_generate_do_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:400px;height:200px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_generate_do_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;" id="span_generate_po_title">Generate DO</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_generate_do_dialog_content" style="padding:2px;height:170px;overflow-y:auto;"></div>
</div>
<!-- End of Generate DO dialog -->

<form name="f_a" enctype="multipart/form-data" class="stdframe" onSubmit="return false;" method="post">
	<input type="hidden" name="a" value="open_upload_csv" />
	<input type="hidden" name="do_type" value="{$do_type}" />

	<table border="0" cellspacing="0" cellpadding="4">
		<tr>
			<td colspan="2" style="color:#0000ff;">
				Note:<br />
				* Please ensure the file extension <b>".csv"</b>.<br />				
				* Please ensure the import file contains header.<br />
				* You can download the sample below and refer to the format.<br /><br />
			</td>
		</tr>
		<tr>
			<th width="150" align="left"><b>Upload CSV file<br />(<a href="?a=download_sample_do_csv&method=1">Download Sample</a>)</b></th>
			<td>
				<input type="file" name="import_csv" />&nbsp;&nbsp;&nbsp;
				<input type="button" value="Show Result" onClick="DO_UPLOAD_CSV.upload_csv();" />
			</td>
		</tr>
	</table>
</form>

<form name="f_b" method="post">
	<input type="hidden" name="from_bid" value="{$sessioninfo.branch_id}" />
	<input type="hidden" name="to_bid" value="" />
	<input type="hidden" name="do_type" value="{$smarty.request.do_type}" />
	
	<br />
	{if $show_result}
		{if !$data}
			* No Data
		{else}
			{if $data.error_data}
				<div class="stdframe" style="border:1px solid red;">
					<h3 style="background-color: yellow;">Data with Error</h3>
					
					<table class="report_table" width="100%" style="background-color: #fff;">
						<tr class="header">
							<th width="50">Line.</th>
							<th width="100">Deliver to Branch</th>
							<th width="200">Item Code</th>
							<th width="80">DO UOM</th>
							<th width="80">CTN</th>
							<th width="80">PCS</th>
							<th>Error</th>
						</tr>
						
						{foreach from=$data.error_data key=line_no item=r}
							<tr>
								<td align="center">{$line_no}</td>
								<td align="center">{$r.to_branch_code}</td>
								<td align="center">{$r.item_code}</td>
								<td align="center">{$r.uom_code}</td>
								<td align="right">{$r.ctn}</td>
								<td align="right">{$r.pcs}</td>
								<td style="color: red;">{$r.error}</td>
							</tr>
						{/foreach}
					</table>
				</div>
			{/if}
			
			<div id="div_all_branch">
				{foreach from=$data.branch_list key=bid item=b_data}
					<div class="stdframe" style="margin-top:20px;" id="div_branch_data-{$bid}">
						<h3>Deliver To: {$branches.$bid.code} - {$branches.$bid.description}</h3>
						
						<table class="report_table" width="100%" style="background-color: #fff;">
							<tr class="header">
								<th width="50">
									<input type="checkbox" onChange="DO_UPLOAD_CSV.toggle_all_item_selected('{$bid}');" id="inp_all_item_selected-{$bid}" checked />
								</th>
								<th width="100">ARMS Code</th>
								<th width="100">MCode</th>
								<th width="100">Art No</th>
								<th width="100">{$config.link_code_name}</th>
								<th>Description</th>
								<th width="80">DO UOM</th>
								<th width="80">CTN</th>
								<th width="80">PCS</th>
							</tr>
							
							{foreach from=$b_data item=r name=fr}
								{assign var=row_no value=$smarty.foreach.fr.iteration}
								{assign var=sid value=$r.sku_item_id}
								<tr>
									<td align="center">
										<input type="checkbox" name="item_list[{$bid}][{$row_no}][item_selected]" value="1" class="inp_item_selected" checked />
										
										{* Item Data *}
										<input type="hidden" name="item_list[{$bid}][{$row_no}][uom_id]" value="{$r.uom_id}" />
										<input type="hidden" name="item_list[{$bid}][{$row_no}][ctn]" value="{$r.ctn}" />
										<input type="hidden" name="item_list[{$bid}][{$row_no}][pcs]" value="{$r.pcs}" />
										<input type="hidden" name="item_list[{$bid}][{$row_no}][sid]" value="{$sid}" />
									</td>
									<td align="center">{$data.si_info.$sid.sku_item_code}</td>
									<td align="center">{$data.si_info.$sid.mcode|default:'-'}</td>
									<td align="center">{$data.si_info.$sid.artno|default:'-'}</td>
									<td align="center">{$data.si_info.$sid.link_code|default:'-'}</td>
									<td>{$data.si_info.$sid.description|default:'-'}</td>
									<td align="center">{$r.uom_code}</td>
									<td align="right">{$r.ctn|ifzero:'-'}</td>
									<td align="right">{$r.pcs}</td>
								</tr>
							{/foreach}
						</table>
						
						<input type="button" value="Generate DO" style="font:bold 20px Arial; background-color:#091; color:#fff;" onClick="DO_UPLOAD_CSV.generate_do('{$bid}');" />
					</div>
				{/foreach}
				
				{if $data.branch_list}
					<p align="center">
						<input type="button" value="Generate All DO" style="font:bold 20px Arial; background-color:#091; color:#fff;" onClick="DO_UPLOAD_CSV.generate_all_do();" />
					</p>
				{/if}
			</div>
		{/if}
	{/if}
</form>

{if $item_lists}
<p id="p_submit_btn" align="center">
	<input name="bsubmit" type="button" value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save()" id="btn_save" />
	<input name="bsubmit" type="button" value="Cancel" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="go_back()" />
</p>
{/if}

{include file='footer.tpl'}

<script>
DO_UPLOAD_CSV.initialize();
</script>