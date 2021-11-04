{*
4/13/2017 5:11 PM Justin
- Enhanced to have export feature.

4/14/2017 9:42 AM Justin
- Bug fixed on tabs that got spacing in between the tab and table when using chrome.
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
var tab = 1;
{literal}
var IMPORT_VENDOR_PYMT_VCH = {
    f_a: undefined,
	initialize: function() {
		this.f_a = document.f_a;
		this.f_b = document.f_b;
		
		// even when user click "cancel" and "close"
		$('b_chkall').observe('click', function(){
            IMPORT_VENDOR_PYMT_VCH.checkall_clicked("branch");
		});
		$('ac_chkall').observe('click', function(){
            IMPORT_VENDOR_PYMT_VCH.checkall_clicked("acct_code");
		});
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
    import_pymt_vch: function(m) {
        if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
			
		var file = '';
		switch(m) {
			case 1:
				file = this.f_a['file_name'].value; break;
		}
        
        $('import_btn').disabled = true;
		$('span_sr_loading_'+m).show();
        
        var params = {
			a: 'ajax_import_pymt_vch',
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
						alert("Successfully Imported Vendor Payment Voucher Data.");
						$('div_result').hide();
						if (ret['partial_ok'] == 1) {
							$('div_invalid_'+m).show();
							$('invalid_link_'+m).href = 'attachments/vendor_pymt_vch_import/invalid_'+file;
						}
						return;
					}else{  // save failed
						if(ret['fail'] == 1)	err_msg = 'Import failed, nothing to update.';
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
	},
	
	list_sel: function(n){
		if(n == 1){ // is import section
			$("div_import").show();
			$("div_export").hide();
			if($("div_result") != undefined) $("div_result").show();
			$("lst1").addClassName('selected');
			$("lst2").removeClassName('selected');
		}else{ // is export section
			$("div_import").hide();
			$("div_export").show();
			if($("div_result") != undefined) $("div_result").hide();
			$("lst1").removeClassName('selected');
			$("lst2").addClassName('selected');
		}
		
		tab = n;
	},
	
	check_export: function(){
		var branch_is_checked = 0;
		var ac_is_checked = 0;

		$$('#div_export .branch_list').each(function(li){
			if(li.checked == true){
				branch_is_checked = 1;
			}
		});
		
		$$('#div_export .acct_code_list').each(function(li){
			if(li.checked == true){
				ac_is_checked = 1;
			}
		});
		
		if(branch_is_checked == 0){
			alert("Please select at least one Branch in order to export.");
			return false;
		}
		
		if(ac_is_checked == 0){
			alert("Please select at least one Account Code in order to export.");
			return false;
		}
		
		return true;
	},
	
	checkall_clicked: function(field_type){
		if(field_type == "branch"){
			$$('#div_export .branch_list').each(function(li){
				if($('b_chkall').checked == true){
					$(li).checked = true;
				}else{
					$(li).checked = false;
				}
			});
		}else{
			$$('#div_export .acct_code_list').each(function(li){
				if($('ac_chkall').checked == true){
					$(li).checked = true;
				}else{
					$(li).checked = false;
				}
			});
		}
	}
}
{/literal}
</script>
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if $errm && $method == '1'}
	<div class="alert alert-danger mx-3 rounded">
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

<div class="tab row mx-3" style="white-space:nowrap;">
	<a href="javascript:IMPORT_VENDOR_PYMT_VCH.list_sel(1);" id="lst1" class="btn btn-outline-primary btn-rounded" {if !$is_export}class="active"{/if}>&nbsp;&nbsp;Import&nbsp;&nbsp;</a>
&nbsp;&nbsp;	<a href="javascript:IMPORT_VENDOR_PYMT_VCH.list_sel(2);" id="lst2" class="btn btn-outline-primary btn-rounded" {if $is_export}class="active"{/if}>&nbsp;&nbsp;Export&nbsp;&nbsp;</a>
</div>

<div id="div_import" {if $is_export}style="display:none;"{/if}>
	<div class="card mx-3 mt-2">
		<div class="card-body">
			<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return IMPORT_VENDOR_PYMT_VCH.check_file(this);" method="post">
		
				<h4 class="form-label">Import Payment Voucher Code</h4>
				<input type="hidden" name="a" value="show_result" />
				<input type="hidden" name="method" value="1" />
				<input type="hidden" name="file_name" value="{$file_name}" />
				<table>
					<tr>
						<td colspan="4" style="color:#0000ff;">
							<div class="alert alert-primary rounded">
								<b>Note:</b><br />
							<ul>
								<li>Please ensure the file extension <b>".csv"</b>.</li>
								<li>Please ensure the import file contains header.</li>
								<li>Acct Code from 1 to 10 is optional (leave empty will not update).</li>
							</ul>
							</div>
						</td>
					</tr>
					<tr>
						<td><b class="form-label">Upload CSV <br />(<a href="?a=download_sample_pymt_vch&method=1">Download Sample</a>)</b></td>
						<td>
							&nbsp;&nbsp;<input type="file" name="import_csv"/>&nbsp;&nbsp;&nbsp;
							<input type="Submit" class="btn btn-primary" value="Show Result" />
						</td>
					</tr>
				</table>
				<div class="div_tbl">
					<h4 class="text-primary">Sample</h4>
					<div class="table-responsive">
						<table id="si_tbl" width="100%">
							<thead class="bg-gray-100">
								<tr >
									{foreach from=$sample_headers[1] item=i}
										<th>{$i}</th>
									{/foreach}
								</tr>
							</thead>
							{foreach from=$sample[1] item=s}
								<div class="tbody fs-08">
									<tr>
										{foreach from=$s item=i}
											<td>{$i}</td>
										{/foreach}
										</tr>
								</div>
							{/foreach}
						</table>
					</div>
				</div>
				<div id="div_invalid_1" style="display: none">
					<div style="border: solid 2px red; padding: 5px; background-color: yellow">
						<p style="font-weight: bold">* Import Successfully. Click <a id="invalid_link_1" href='#' download>this</a> to download and view the invalid data.</p>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<div id="div_export" {if !$is_export}style="display:none;"{/if}>
	<form name="f_b" class="stdframe" onsubmit="return IMPORT_VENDOR_PYMT_VCH.check_export();" method="post">
		<div class="breadcrumb-header justify-content-between">
			<div class="my-auto">
				<div class="d-flex">
					<h4 class="content-title mb-0 my-auto ml-4 text-primary">Export Payment Voucher Code</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
				</div>
			</div>
		</div>
		
		<h2></h2>
		{if $errm}
			<div class="alert alert-danger">
				<div id=err><div class=errmsg><ul>
				{foreach from=$errm item=e}
					<li> {$e}
				{/foreach}
			</ul></div></div>
			</div>
		{/if}
		<input type="hidden" name="a" value="export_data" />
		<input type="hidden" name="method" value="1" />
		<div class="card mx-3">
			<div class="card-body">
				<table>
					<tr>
						<td><b class="form-label">Vendor:</b></td>
						<td colspan="2">
							<select class="form-control" name="vendor_id">
								<option value="">All</option>
								{foreach from=$vd_list key=vd_id item=v}
									<option value="{$v.id}" {if $smarty.request.vendor_id eq $v.id}selected{/if}>{$v.code} - {$v.description}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td style="vertical-align:top; padding-top:4px;"><b class="form-label">Branch:</b></td>
						<td style="vertical-align:top; padding-top:1px;">
							<input type="checkbox" id="b_chkall" /> All
						</td>
						<td>
							{foreach from=$branch_list key=code item=b name=blist}
								{assign var=bid value=$b.id}
								{if $smarty.foreach.blist.iteration %8 == 0}
									<br />
								{/if}
								<input type="checkbox" name="branch[{$bid}]" class="branch_list" value="1" {if !$is_export || ($is_export && $smarty.request.branch.$bid)}checked{/if} /> {$code}&nbsp;&nbsp;
							{/foreach}
						</td>
					</tr>
					<tr>
						<td style="vertical-align:top; padding-top:4px;"><b class="form-label">Account Code:</b></td>
						<td style="vertical-align:top; padding-top:1px;">
							<input type="checkbox" id="ac_chkall" /> All
						</td>
						<td>
							{foreach from=$acct_code_list key=dummy item=id name=aclist}
								{if $smarty.foreach.aclist.iteration %6 == 0}
									<br />
								{/if}
								<input type="checkbox" name="acct_code[{$id}]" class="acct_code_list" value="1" {if !$is_export || ($is_export && $smarty.request.acct_code.$id)}checked{/if} /> Acct Code {$id}&nbsp;&nbsp;
							{/foreach}
						</td>
					</tr>
				</table>
			
		<div class="alert alert-primary rounded">
			<ul>
				<li>Please choose at least one Branch and Account Code in order to export.</li>
			</ul>
		</div>
		
		<input type="Submit" class="btn btn-primary" value="Export" />

	</div>
</div>
	</form>
</div>
<br>
{if $item_lists && $method == '1'}
	<div class="div_result" id="div_result">
		{include file="masterfile_vendor.import_pymt_vch.result.tpl"}
	</div>
{/if}
<br><br>
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
IMPORT_VENDOR_PYMT_VCH.initialize();
{/literal}
</script>