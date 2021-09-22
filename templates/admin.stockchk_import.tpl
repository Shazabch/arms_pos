{*
11/11/2016 10:37 AM Andy
- Enhanced to open (ARMS Code, Qty) format to all. Removed checking of config "stock_check_arms_stock_format".
- Added new format (ARMS Code/MCode/Old Code, Qty).

4/21/2017 8:58 AM Qiu Ying
- Bug fixed on showing Branch Selection when already in Branch

8/9/2017 11:15 AM Justin
- Enhanced to have more options on the fill zero feature.

3/2/2018 3:34 PM HockLee
- Add new note for Import File Format to tell user do not put header into the CSV file as it will count as data to be imported.
*}

{include file='header.tpl'}
{literal}
<style>
table.form_table tr th {
	vertical-align: top;
    text-align: left;
}
</style>
{/literal}
<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var branch_code = '{$BRANCH_CODE}'; 
var branch_id = '{$sessioninfo.branch_id}';
{literal}
var STOCK_CHECK_IMPORT = {
    f_a: undefined,
	f_c: undefined,
	f_d: undefined,
    initialize: function() {
        this.f_a = document.f_a;
		this.f_c = document.f_c;
		this.f_d = document.f_d;
		this.get_stock_check_date();
    },
    check_form: function() {
        if(!this.f_a['cut_off_date'].value) {
            alert("Please fill in cut-off date.");
            return false;
        }
        
        if(this.f_a['import_type'].value != "atp") {
            if(!this.f_a['scanned_by'].value || !this.f_a['location'].value || !this.f_a['shelf_no'].value) {
                alert("Please complete all required field.");
                return false;
            }
        }
        
        if(!this.f_a['import_csv'].value){
            alert("Please select file to upload.");
            return false;
        }
        return true;
    },
	add_record: function(){
		$('div_new_record').show();
		this.insert_row();
	},
	insert_row: function(){
		var new_row;
		new_row = $('row_template').cloneNode(true);
		new_row.style.display='';
		new_row.id='';
		$('tbody_rows').appendChild(new_row);
	},
	del_row: function(obj){
		if (confirm('Delete row?')) (obj.parentNode.parentNode).remove();
	},
	check_item_code: function(obj){
		var v = obj.value.trim();
		obj.value = 'checking';
		obj.style.background = '';
		new Ajax.Request(phpself+'?a=ajax_check_code&code='+v, {
			onComplete:function(m){
				obj.value = v;
				if (m.responseText!='OK') {
					obj.select();
					obj.focus();
					alert('In-House Code is not valid: '+obj.value);
				}
				else{
					obj.style.background = '#ff0';
					obj.style.border = '1px solid #eee';
				}
			}
		});
	},
	edit_cancel: function(){
		this.f_c.reset();
		$('tbody_rows').innerHTML='';
		$('div_new_record').hide();
	},
	do_save: function() {
		var missing = false;
	
		//check input box
		$$('#tbody_rows .sku_item_code').each(function(ele){
			ele.parentNode.parentNode.style.background ="#ffffff";
		});
	
		$$('#tbody_rows .sku_item_code').each(function(ele){
			if (!ele.value){
				ele.parentNode.parentNode.style.background ="#ff8888";
				missing = true;
			}
		});
	
		$$('#tbody_rows .location').each(function(ele){
			if (!ele.value){
				ele.parentNode.parentNode.style.background ="#ff8888";
				missing = true;
			}
		});
	
		$$('#tbody_rows .shelf_no').each(function(ele){
			if (!ele.value){
				ele.parentNode.parentNode.style.background ="#ff8888";
				missing = true;
			}
		});
	
		$$('#tbody_rows .item_no').each(function(ele){
			if (!ele.value){
				ele.parentNode.parentNode.style.background ="#ff8888";
				missing = true;
			}
		});
	
		if (missing){
			alert("Data missing. Required ARMS code, location, shelf no and item no.");
			return;
		}
	
		if (this.f_c['cut_off_date'].value == '') {
			alert('Cut-off date is empty');
			this.f_c['cut_off_date'].focus();
			return;
		}
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: Form.serialize(this.f_c),
			onComplete: function(m) {
				if (/^Error:/.test(m.responseText)){
					alert(m.responseText);
					return;
				}
				//clear tbody
				$('tbody_rows').innerHTML = '';
				$('div_new_record').hide();
				alert(m.responseText);
			}
		});
	},
	get_stock_check_date: function(){
		var bid;
		if(branch_code == "HQ"){
			bid = this.f_d['search_branch_id'].value;
		}else{
			bid = branch_id;
		}
		
		if(bid==''){    // no branch selected
			$(this.f_d['search_date']).update('<option value="">-- Please Select --</option>');
		}else{
			$(this.f_d['search_date']).update('<option>Loading...</option>');
			new Ajax.Updater(this.f_d['search_date'],phpself,{
				parameters:{
					a: 'ajax_load_date',
					branch_id: bid
				},
				evalScripts: true
			});
		}
	},
	check_search_form: function(){
		if(this.f_d['search_branch_id'].value == '' || this.f_d['search_date'].value == ''){
			alert('Missing branch and date.');
			return false;	
		}
		
		$('div_stock_check_table').update(_loading_);
		
		new Ajax.Updater('div_stock_check_table', phpself,{
			parameters: Form.serialize(this.f_d)
		});
	},
	change_page: function(){
		document.f_e['a'].value = "search_data";
		
		new Ajax.Updater('div_stock_check_table', phpself,{
			parameters: Form.serialize(document.f_e)
		});
	},
	button_change_page: function(ind){
		document.f_e['pg'].value = ind;
		this.change_page();
	},
	toggle_all: function(obj){
		$$('#content_tbl .content_checkbox').each(function(ele){
			ele.checked = obj.checked;
		});
	},
	edit_data: function(obj, ind){
		var param = "branch_id="+$('branch_id_'+ind).innerHTML+
					"&date="+$('date_'+ind).innerHTML+
					"&sku_item_code="+$('sku_item_code_'+ind).innerHTML+
					"&location="+$('location_'+ind).innerHTML+
					"&shelf_no="+$('shelf_no_'+ind).innerHTML+
					"&item_no="+$('item_no_'+ind).innerHTML+
					"&field="+obj.className;
					
		var ed = new Ajax.InPlaceEditor(obj,phpself+'?a=update_data&'+param,{size:15,callback:function(f,v){return 'newvalue='+escape(v)},onComplete:function(){this.dispose()}});
		ed.enterEditMode('click');
	},
	delete_data: function(){
		if(!confirm("Are you sure want to delete selected records?"))	return;
		
		var param = Form.serialize(document.f_e);
		var del_checked = false;
		$$('#content_tbl .content_checkbox').each(function(ele){
			if(ele.checked){
				var par = "&branch_id[]="+$('branch_id_'+ele.value).innerHTML+
						  "&date[]="+$('date_'+ele.value).innerHTML+
						  "&sku_item_code[]="+$('sku_item_code_'+ele.value).innerHTML+
						  "&location[]="+$('location_'+ele.value).innerHTML+
						  "&shelf_no[]="+$('shelf_no_'+ele.value).innerHTML+
						  "&item_no[]="+$('item_no_'+ele.value).innerHTML;
				param = param.concat(par);
				del_checked = true;
			}
		});
		
		if (!del_checked) {
			alert("No item is selected.");
            return false;
        }
		
		new Ajax.Updater('div_stock_check_table', phpself,{
			parameters: param
		});
		alert("Item(s) has been deleted.");
	}
}
{/literal}
</script>
<div class="">
	
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div> 

{if $err}
   <div class="card mx-3">
	   <div class="card-body">
		<b class="text-danger">Stock cannot be import</b>
		<div class="errmsg">
			<ul class="text-muted">
				{foreach from=$err item=e}
					<li>{$e}</li>
				{/foreach}
			</ul>
			
		</div>
	   </div>
   </div>
  
	{if $pc_file_path && file_exists($pc_file_path)}
		<div class="card mx-3">
			<div class="card-body">
				<b><a href="{$pc_file_path}" download>Click here to download</a> for those missing SKU items which did not stock take completely.</b>
		
			</div>

		</div>
	{/if}
    <a href="{$smarty.server.PHP_SELF}">Click here to go back.</a>
{elseif $succ}
    <b class="text-success">Import successfully</b>
    <ul class="text-muted">
        {foreach from=$succ item=s}
            <li>{$s}</li>
        {/foreach}
    </ul>
    <a href="{$smarty.server.PHP_SELF}">Click here to continue.</a>
{else}
  <div class="">
	<div class="card mx-3">
		<div class="card-body">
			<label><h5>Import</h5></label>
    <form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return STOCK_CHECK_IMPORT.check_form();" method="post">
        <input type="hidden" name="a" value="import_data">
        <table class="form_table">
			{if $BRANCH_CODE eq "HQ"}
					<label>Branch</label>
						<select class="form-control" name="branch_id">
							{foreach from=$branch_list key=k item=i}
								<option value="{$k}">{$i}</option>
							{/foreach}
						</select>
				
			{else}
				<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}">
			{/if}
            <tr>
               <label class="mt-2">
				Action [<a href="javascript:void(alert('
				1. No Auto Fill Zero\n
				- The system will import all Stock Take according to the import file only, without zerolise the rest of the non-scanned Stock Take items.\n\n
				2. Auto Add Zero for Non-scan Items\n
				- The system will import all Stock Take according to the import file and then zerolise the rest of the non-scanned Stock Take items.\n\n
				3. Auto Add Zero for Same SKU Parent\n
				- The system will auto zerolise the rest of the items under the same SKU family (Parent & Child) base on the items from import file.\n\n
				'));">?</a>]
			   </label>
					<select class="form-control" name="fill_zero_options">
						<option value="no_fill" selected>No auto fill zero</option>
						<option value="fill_zero">Auto add zero for non-scan items</option>
						<option value="fill_parent">Auto add zero for same SKU parent</option>
					</select>
				
            <div class="row mt-2">
           <div class="col-md-6">
			<label class="mt-1">
				Import File Format [<a href="javascript:void(alert('
				Please do not put header into the CSV file as it will count as one of the data to be imported.\n\n
				'));">?</a>]
			   </label>    
		   </div>
		   <div class="col-md-6 ">
			
			<div class="fs-07">
				<input class="mt-1" type="radio" name="import_type" value="argos" onclick="showdiv('argos_opt')"> CSV / ARGOS (mcode/arms_code, qty, description, selling_price) <br />
			<input class="mt-1" type="radio" name="import_type" value="pspv" onclick="showdiv('argos_opt')"> PSPV (mcode/arms_code &nbsp;&nbsp;&nbsp; qty) <br />
			<input class="mt-1" type="radio" name="import_type" value="atp" checked onclick="hidediv('argos_opt')"> CSV / ATP/Multics (arms_code, mcode/{$config.link_code_name}/Art No, count_by, location, shelf, item_no, qty, selling, cost)<br />
			{*if $config.stock_check_arms_stock_format*}
				<input class="mt-1" type="radio" name="import_type" value="arms_stock" onclick="showdiv('argos_opt')"> CSV (ARMS Code, Qty)<br />
			{*/if*}
			{if $config.stock_check_artno_stock_format}
				<input class="mt-1" type="radio" name="import_type" value="artno_stock" onclick="showdiv('argos_opt')"> CSV (Art No, Qty)<br />
			{/if}
			<input class="mt-1" type="radio" name="import_type" value="arms_stock_2" onclick="showdiv('argos_opt')"> CSV (ARMS Code/MCode/{$config.link_code_name}, Qty)<br />
			</div>
			
		   </div>

            </div>
           <div class="mb-2">
		
                <label class="mt-3 mt-md-1">Cut-off Date(yyyy-mm-dd)<span class="text-danger">*</span></label>
                <input class="form-control" name="cut_off_date" size="12" value="{$form.cut_off_date}">
          
		   </div>
			
          
            
			<div class="row mt-4">
				<div class="col-md-4">
					<p class=""><b>File :</b> </p>
					<input type="file" name="import_csv"/>
					<br>
					<p class="fs-09 mt-sm-2">Select File to Import</p>
					<button class="btn btn-primary  ml-md-0 mt-2 mt-md-0">Upload</button>
	
				</div>

				<div class="col md-4">
					<div class="mt-md-4">
						Allow Duplicate Entry <br>
						<input type="checkbox" name="allow_duplicate" value="1"> (if found duplicate will sum up qty)
					</div>
				</div>

				<div class="col-md-4">
					<div id=argos_opt style="display:none " class="mt-2" >
						Scanned By <span class="text-danger">*</span><input class="form-control" name="scanned_by" maxlength="15" /> (Max 15 Characters)
						Location <span class="text-danger">*</span><input class="form-control" name="location" maxlength="15" /> (Max 15 Characters)
						Shelf No <span class="text-danger">*</span><input class="form-control" name="shelf_no" maxlength="15" /> (Max 15 Characters)
					</div>
				</div>
			</div>
            
  
            
        </table><br>
		
    </form>
	
</div>
</div>
  </div>
   
    <div class="">
		<div class="card mx-3">
			<div class="card-body">
				<h5>Export Database</h5>
    <form name="f_b" class="stdframe">
		<input type="hidden" name="a" value="export_data">
		<div class="mt-3"><span class="text-danger">*</span> Export format: MCode/Arms Code, Stock Balance, Description, Selling Price</div>
        <div class="row mt-1">
            	<div class="col-md-4">
					<div class="mt-2">
						<b>Select data type to export</b>
					</div>
				</div>
			
		</div>
		<div class="row mt-2">
			<div class="col-md-4">
				<select class="form-control select2" name="export_type">
					<option value="pspv">PSPV</option>
					<option value="csv">CSV</option>
				</select>
			</div>
			   
			<div class="col-md-4 mt-2 mt-md-0">
				<button class="btn btn-primary">Download</button>
			</div>
		</div>
    </form>
    <br/>
</div>
</div>
<div class="">
	<div class="card mx-3">
		<div class="card-body">
			<h5>Edit Database</h5>
			<form name="f_c" enctype="multipart/form-data" class="stdframe" onsubmit="return false;">
				<button class="btn btn-success" onclick="STOCK_CHECK_IMPORT.add_record();">Add Record</button>
				<br/>
				<div class="stdframe" id="div_new_record" style="display:none;margin:10px 0;background:#fff">
					<input type="hidden" name="a" value="add_data">
					{if $BRANCH_CODE == 'HQ'}
					<label>Branch</label>
					<select class="form-control select2" name="branch_id">
						{foreach from=$branch_list key=k item=i}
							<option value="{$k}">{$i}</option>
						{/foreach}
					</select>&nbsp;&nbsp;&nbsp;
					{else}
						<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}">
					{/if}
					<br/>
					<label>Cut-Off Date (yyyy-mm-dd) </label>
					<input class="form-control" name="cut_off_date" value="">
					<b class="text-danger">*</b> Leave the cost and selling empty to use latest cost price and selling price
				</div>
			</div>
		
		<div class="table-responsive">
			<table border="0" cellpadding="4" class="tb table mb-0 text-md-nowrap  table-hover ">
		<thead class="bg-gray-100 ">
			<tr style="height:30px;">
				<th>ARMS Code</th>
				<th>Scanned By</th>
				<th>Location</th>
				<th>Shelf No</th>
				<th>Item No</th>
				<th>Qty</th>
				<th>Selling</th>
				<th>Cost</th>
			</tr>
		</thead>
				<tbody id="tbody_rows">
				</tbody>
				<tr id="row_template" style="display:none">
					
					<td nowrap class="form-inline"><i class="fas fa-times text-danger mr-2" onclick="STOCK_CHECK_IMPORT.del_row(this)"></i>
					<input class="form-control" onchange="STOCK_CHECK_IMPORT.check_item_code(this)" size="15" class="sku_item_code" name="sku_item_code[]"></td>
					<td><input class="form-control" onchange="uc(this)" size="5" name="scanned_by[]"></td>
					<td><input class="form-control" onchange="uc(this)" size="8" class="location" name="location[]"></td>
					<td><input class="form-control" onchange="uc(this)" size="8" class="shelf_no" name="shelf_no[]"></td>
					<td><input class="form-control" onchange="uc(this)" size="3" class="item_no" name="item_no[]"></td>
					<td><input class="form-control" onchange="uc(this)" size="5" name="qty[]"></td>
					<td><input class="form-control" onchange="uc(this)" size="5" name="selling[]"></td>
					<td><input class="form-control" onchange="uc(this)" size="5" name="cost[]"></td>
				</tr>
				
			</table>
			<div colspan="10" class="text-center my-3">
				<button class="btn btn-warning mt-2 mt-md-0" onclick="STOCK_CHECK_IMPORT.insert_row()">Insert Row</button>
				<button class="btn btn-success mt-2 mt-md-0" onclick="STOCK_CHECK_IMPORT.do_save()">Save</button>
				<button class="btn btn-primary mt-2 mt-md-0" onclick="STOCK_CHECK_IMPORT.edit_cancel()">Cancel</button>
			</div>
		</div>
				
		
			</form><br>
			<div class="stdframe mx-4">
				<form name="f_d" onsubmit="STOCK_CHECK_IMPORT.check_search_form(); return false">
					<input type="hidden" name="a" value="search_data">
					<table>
						<tr>
							{if $BRANCH_CODE eq "HQ"}
								<label>Branch	</label>
									<select class="form-control select2" name="search_branch_id" onchange="STOCK_CHECK_IMPORT.get_stock_check_date();">
										<option value="">-- Please Select --</option>
										{foreach from=$branch_list key=k item=i}
										<option value="{$k}" {if $smarty.request.search_branch_id eq $k}selected{/if}>{$i}</option>
										{/foreach}
									</select>
								
							{else}
								<input class="form-control" type="hidden" name="search_branch_id" value="{$sessioninfo.branch_id}">
							{/if}
							<label class="mt-2">Date</label>
								<select class="form-control select2" name="search_date">
									<option value="">-- Please Select --</option>
								</select>
							
							<label class="mt-2">Search</label>
								<select class="form-control select2" name="search_type">
									{foreach from=$search_type key=k item=i}
										<option value="{$k}" {if $smarty.request.search_type eq $k}selected{/if}>{$i}</option>
									{/foreach}
								</select>&nbsp;
							
							<label class="mt-2">For</label>
							<input class="form-control" name="search_value">
							<br>
							<button class="btn btn-primary">Find</button>
						</tr>
					</table>
				</form>
				
			</div>
			
		</div>
		<div id="div_stock_check_table">
			{include file="admin.stockchk_import.table.tpl"}
		</div>
	</div>
</div>

</div>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
STOCK_CHECK_IMPORT.initialize();
{/literal}
</script>