{*
*}
{include file=header.tpl}
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
var UPDATE_MODULE = {
	f_a: undefined,
	initialize: function() {
		this.f_a = document.f_a;
	},
	check_file: function(obj) {
		// only accept csv file
		var filename = this.f_a['update_csv'].value;
		if(filename.indexOf('.csv')<0){
			alert('Please select a valid csv file');
			return false;
		}
		return true;
	},
	import_debtor: function(){
		if(!confirm('Are you sure? \nIMPORTANT: This action cannot be UNDO.')) return false;
		
		$('update_btn').disabled = true;
		$('span_loading').show();
		
		var file = this.f_a['file_name'].value;
		var params = $(document.f_b).serialize()+'&a=ajax_import_debtor_price&file_name='+file;
		
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
						alert("Successfully Updated Debtor Price.");
						$('div_result').hide();
						if (ret['partial_ok'] == 1) {
							$('div_invalid').show();
							$('invalid_link').href = 'attachments/update_debtor_price/invalid_'+file;
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
	branch_checkbox_changed: function(){
		var bid_checked = 0;
		var branch_list = document.f_b['branch_list[]'];
		for (var i=0; i< branch_list.length; i++) {
			if (branch_list[i].checked) {
				bid_checked += 1;
			}
		}
		
		//make sure the branch_list at least checked 1
		if(bid_checked > 0) $('update_btn').disabled = false;
		else $('update_btn').disabled = true;
	},
	check_all_branch : function(obj){
		var branch_list = document.f_b['branch_list[]'];
		for(var i=0,len=branch_list.length; i < len; i++){
			if(obj.checked == true){
				$(branch_list[i]).checked = true;
			}else{
				$(branch_list[i]).checked = false;
			}
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

{if $err}
	<div class="alert alert-danger mx-3 rounded">
		<ul >
			{foreach from=$err item=e}
				<li><b>{$e}</b></li>
			{/foreach}
		</ul>
	</div>
{/if}
<span id="span_loading" style="display:none; background:yellow; padding:2px;">
	<img src="/ui/clock.gif" align="absmiddle" /> Loading...
</span>
<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return UPDATE_MODULE.check_file(this);" method="post">
			<input type="hidden" name="a" value="show_result" />
			<input type="hidden" name="file_name" value="{$file_name}" />
			<table>
				<tr>
					
						<td colspan="4">
							<div class="alert alert-primary mx-3 rounded">
							<b>Note:</b><br />
							* Please ensure the file extension <b>".csv"</b>.<br />
							* Please ensure the import file contains header.<br /><br />
						</div>
						</td>
					
				</tr>
				<tr>
					<td><b class="form-label">&nbsp;&nbsp;Upload CSV <br />&nbsp;&nbsp;(<a href="?a=download_sample">Download Sample</a>)</b></td>
					<td>
						&nbsp;&nbsp;<input type="file" name="update_csv"/>
						<input type="Submit" class="btn btn-primary" value="Show Result" />
					</td>
				</tr>
			</table>
			
			<div class="div_tbl">
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto ml-4 text-primary">Sample</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
						</div>
					</div>
				</div>
				<div >
					<div >
						<div class="table-responsive">
							<table id="si_tbl" class=" table mb-0 text-md-nowrap  table-hover">
								
								<thead class="bg-gray-100">
									<tr >
										{foreach from=$sample_headers item=i}
											<th>{$i}</th>
										{/foreach}
									</tr>
								</thead>
								<tbody class="fs-08">
									<tr>
										{foreach from=$sample item=s}
											<td>{$s}</td>
										{/foreach}
										</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div id="div_invalid" style="display: none">
				<div style="border: solid 2px red; padding: 5px; background-color: yellow">
					<p style="font-weight: bold">* Update Successfully. Click <a id="invalid_link" href='#' download>this</a> to download and view the invalid data.</p>
				</div>
			</div>
		</form>
	</div>
</div>
<br>
{if $item_lists}
	<div id="div_result" class="div_result">
		{include file="masterfile_import_debtor_price.result.tpl"}
	</div>
{/if}
<script type="text/javascript">
{literal}
	UPDATE_MODULE.initialize();
{/literal}
</script>
{include file=footer.tpl}