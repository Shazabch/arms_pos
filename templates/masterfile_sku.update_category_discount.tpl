{*
6:02 PM 11/29/2018 Justin
- Bug fixed on invalid data download link is broken.

1/21/2019 03:12 PM Justin
- Enhanced to have new note to guide user how to clean up the discount value from database.
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
var UPDATE_SKU_CATEGORY_DISCOUNT_MODULE = {
	f_a: undefined,
	f_b: undefined,
	initialize: function() {
		this.f_a = document.f_a;
		this.f_b = document.f_b;
		
		this.toggle_branch_update_method();
	},
	update_sku: function(m) {
		if(this.validate() == false) return false;
	
		if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
		
		$('update_btn').disabled = true;
		$('span_loading').show();
		
		var file = this.f_a['file_name'].value;
		var q = $(this.f_b).serialize();
		
		var params = {
			a: 'ajax_update_sku',
			file_name: file,
			method: m
		};
		q += '&'+$H(params).toQueryString();
		
		new Ajax.Request(phpself, {
			parameters: q,
			onComplete: function(msg){	
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';
	
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1 || ret['partial_ok'] == 1){ // success
						alert("Successfully Updated SKU Data.");
						$('div_result').hide();
						if (ret['partial_ok'] == 1) {
							$('div_invalid').show();
							$('invalid_link').href = 'attachments/update_sku_category_discount/invalid_'+file;
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
			},
			onSuccess: function(msg){
				$('span_loading').hide();
			}
		});
	},
	
	validate: function(obj){
		// only accept csv file
		if(obj != undefined && obj.name == "f_a"){
			var filename = this.f_a['update_csv'].value;
			if(filename.indexOf('.csv')<0){
				alert('Please select a valid csv file');
				return false;
			}
		}
	
		// validation for branch selection
		var update_method = getRadioValue(this.f_a['update_method']);
		if(update_method == "by_branch"){
			var have_branch_ticked = false;
			$$('#div_branch_list .branch_sel_list').each(function(ele, i){
				if(ele.checked == true) have_branch_ticked = true;
			});
			
			if(have_branch_ticked == false){
				alert("You must choose at least one branch to update");
				return false;
			}
		}
		
		return true;
	},
	
	toggle_branch_update_method: function(){
		var update_method = getRadioValue(this.f_a['update_method']);
		$$('#div_branch_list .branch_sel_list').each(function(ele, i){
			if(update_method == "all_branch") ele.disabled = true;
			else ele.disabled = false;
		});
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
{if $errm}
	<div class="errmsg">
		<div class="alert alert-danger mx-3 rounded">
			<ul>
				{foreach from=$errm item=e}
					<li> {$e}</li>
				{/foreach}
			</ul>
		</div>
	</div>
{/if}
	

<span id="span_loading" style="display:none; background:yellow; padding:2px;">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>
<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return UPDATE_SKU_CATEGORY_DISCOUNT_MODULE.validate(this);" method="post">
	<input type="hidden" name="a" value="show_result" />
	<input type="hidden" name="method" value="1" />
	<input type="hidden" name="file_name" value="{$file_name}" />
	<div class="card mx-3">
		<div class="card-body">
			<table>
				<tr>
					<td colspan="4" >
					<div class="alert alert-primary rounded">
						<b>Note:</b><br />
						* This module will update the Category Discount from SKU Masterfile.<br/>
						* Member and Non-member fields are mandantory.<br />
						* Category Discount field can accepts only "Inherit", "No" or "Override".<br />
						&nbsp;&nbsp;&nbsp;> Inherit = Follow Category<br />
						&nbsp;&nbsp;&nbsp;> No = No Discount<br />
						&nbsp;&nbsp;&nbsp;> Override = Override Discount<br />
						* Insert "clear" onto the discount value will clean up the Discount value from database.<br />
						<br />
					</div>
					</td>
				</tr>
				<tr>
					<td><b class="form-label">Upload CSV <br />(<a href="?a=download_sample&method=1">Download Sample</a>)</b></td>
					<td>
						&nbsp;&nbsp;<input type="file" name="update_csv"/>&nbsp;&nbsp;&nbsp;
						&nbsp;<input type="Submit" class="btn btn-primary" value="Show Result" />
					</td>
				</tr>
				<tr>
					<td valign="top"><div style="margin-top:3px;"><b class="form-label">Update Category Discount by</b></div></td>
					<td>
						&nbsp;<input type="radio" name="update_method" value="all_branch" onclick="UPDATE_SKU_CATEGORY_DISCOUNT_MODULE.toggle_branch_update_method();" {if !$smarty.request.update_method || $smarty.request.update_method eq 'all_branch'}checked{/if} />&nbsp;<b>All Branch</b>
						<br />
						&nbsp;<input type="radio" name="update_method" value="by_branch" onclick="UPDATE_SKU_CATEGORY_DISCOUNT_MODULE.toggle_branch_update_method();" {if $smarty.request.update_method eq 'by_branch'}checked{/if} />&nbsp;<b>By Branch</b>
						<br />
						<div id="div_branch_list" style="margin-left:2px; margin-top:3px;">
							{foreach from=$branch_list key=bid item=bcode}
								<input type="checkbox" class="branch_sel_list" name="branch_list[{$bid}]" value="1" {if $smarty.request.branch_list.$bid}checked{/if} /> <b>{$bcode}</b>&nbsp;&nbsp;
							{/foreach}
						</div>
					</td>
				</tr>
			</div>
			</table>
		</div>
	</div>
	<div class="div_tbl">
		<div class="breadcrumb-header justify-content-between">
			<div class="my-auto">
				<div class="d-flex">
					<h4 class="content-title mb-0 my-auto ml-4 text-primary">Sample</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
				</div>
			</div>
		</div>
		<div class="card mx-3">
			<div class="card-body">
				<table id="si_tbl" width="100%">
					<thead class="bg-gray-100">
						<tr >
							{foreach from=$sample_headers.1 item=i}
								<th>{$i}</th>
							{/foreach}
						</tr>
					</thead>
					{foreach from=$sample.1 item=s}
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
	</div>
	<div id="div_invalid" style="display: none">
		<div style="border: solid 2px red; padding: 5px; background-color: yellow">
			<p style="font-weight: bold">* Update Successfully. Click <a id="invalid_link" href='#' download>this</a> to download and view the invalid data.</p>
		</div>
	</div>
</form>
<br>
{if $item_lists}
	<form name="f_b" method="post">
		<div class="div_result" id="div_result">
			{include file="masterfile_sku.update_category_discount.result.tpl"}
		</div>
	</form>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
UPDATE_SKU_CATEGORY_DISCOUNT_MODULE.initialize();
{/literal}
</script>