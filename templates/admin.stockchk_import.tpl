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
<h1>{$PAGE_TITLE}</h1>
{if $err}
    <br>
    <h4>Stock cannot be import</h4>
    <div class="errmsg">
        <ul>
            {foreach from=$err item=e}
                <li>{$e}</li>
            {/foreach}
        </ul>
		
    </div>
	{if $pc_file_path && file_exists($pc_file_path)}
		<b><a href="{$pc_file_path}" download>Click here to download</a> for those missing SKU items which did not stock take completely.</b>
		<br /><br />
	{/if}
    <a href="{$smarty.server.PHP_SELF}">Click here to go back.</a>
{elseif $succ}
    <h4>Import successfully</h4>
    <ul>
        {foreach from=$succ item=s}
            <li>{$s}</li>
        {/foreach}
    </ul>
    <a href="{$smarty.server.PHP_SELF}">Click here to continue.</a>
{else}
    <h2>Import</h2>
    <form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return STOCK_CHECK_IMPORT.check_form();" method="post">
        <input type="hidden" name="a" value="import_data">
        <table class="form_table">
			{if $BRANCH_CODE eq "HQ"}
				<tr>
					<th>Branch</th>
					<td>
						<select name="branch_id">
							{foreach from=$branch_list key=k item=i}
								<option value="{$k}">{$i}</option>
							{/foreach}
						</select>
					</td>
				</tr>
			{else}
				<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}">
			{/if}
            <tr>
                <th>Action [<a href="javascript:void(alert('
					1. No Auto Fill Zero\n
					- The system will import all Stock Take according to the import file only, without zerolise the rest of the non-scanned Stock Take items.\n\n
					2. Auto Add Zero for Non-scan Items\n
					- The system will import all Stock Take according to the import file and then zerolise the rest of the non-scanned Stock Take items.\n\n
					3. Auto Add Zero for Same SKU Parent\n
					- The system will auto zerolise the rest of the items under the same SKU family (Parent & Child) base on the items from import file.\n\n
					'));">?</a>]
				</th>
                <td>
					<select name="fill_zero_options">
						<option value="no_fill" selected>No auto fill zero</option>
						<option value="fill_zero">Auto add zero for non-scan items</option>
						<option value="fill_parent">Auto add zero for same SKU parent</option>
					</select>
				</td>
            </tr>
            <tr>
                <th>Import File Format [<a href="javascript:void(alert('
					Please do not put header into the CSV file as it will count as one of the data to be imported.\n\n
					'));">?</a>]
			</th>
                <td>
                    <input type="radio" name="import_type" value="argos" onclick="showdiv('argos_opt')"> CSV / ARGOS (mcode/arms_code, qty, description, selling_price) <br />
                    <input type="radio" name="import_type" value="pspv" onclick="showdiv('argos_opt')"> PSPV (mcode/arms_code &nbsp;&nbsp;&nbsp; qty) <br />
                    <input type="radio" name="import_type" value="atp" checked onclick="hidediv('argos_opt')"> CSV / ATP/Multics (arms_code, mcode/{$config.link_code_name}/Art No, count_by, location, shelf, item_no, qty, selling, cost)<br />
                    {*if $config.stock_check_arms_stock_format*}
                        <input type="radio" name="import_type" value="arms_stock" onclick="showdiv('argos_opt')"> CSV (ARMS Code, Qty)<br />
                    {*/if*}
                    {if $config.stock_check_artno_stock_format}
                        <input type="radio" name="import_type" value="artno_stock" onclick="showdiv('argos_opt')"> CSV (Art No, Qty)<br />
                    {/if}
					<input type="radio" name="import_type" value="arms_stock_2" onclick="showdiv('argos_opt')"> CSV (ARMS Code/MCode/{$config.link_code_name}, Qty)<br />
                </td>
            </tr>
            <tr>
                <th>Cut-off Date(yyyy-mm-dd)*</th>
                <td><input name="cut_off_date" size="12" value="{$form.cut_off_date}"></td>
            </tr>
            <tbody id=argos_opt style="display:none">
                <tr><th>Scanned By *</th><td><input name="scanned_by" maxlength="15" /> (Max 15 Characters)</td></tr>
                <tr><th>Location *</th><td><input name="location" maxlength="15" /> (Max 15 Characters)</td></tr>
                <tr><th>Shelf No *</th><td><input name="shelf_no" maxlength="15" /> (Max 15 Characters)</td></tr>
            </tbody>
            <tr>
                <th>File</th>
                <td><input type="file" name="import_csv"/>Select File to Import</td>
            </tr>
            <tr>
                <th>Allow Duplicate Entry</th>
                <td><input type="checkbox" name="allow_duplicate" value="1"> (if found duplicate will sum up qty)</td>
            </tr>
        </table><br>
		<input type="submit" value="Upload">
    </form>
    <br/>
    <h2>Export Database</h2>
    <form name="f_b" class="stdframe">
		<input type="hidden" name="a" value="export_data">
		<div style="color: blue;">* Export format: MCode/Arms Code, Stock Balance, Description, Selling Price</div>
        <table>
            <tr>
                <th>Select data type to export</th>
                <td>
                    <select name="export_type">
                        <option value="pspv">PSPV</option>
                        <option value="csv">CSV</option>
                    </select>
                </td>
                <td><input type=submit value="Download"></td>
            </tr>
        </table>
    </form>
    <br/>
    <h2>Edit Database</h2>
    <form name="f_c" enctype="multipart/form-data" class="stdframe" onsubmit="return false;">
        <input type="button" onclick="STOCK_CHECK_IMPORT.add_record();"  value="Add Record" style="color:#fff;background:#090;font:20px Arial"><br/>
		<div class="stdframe" id="div_new_record" style="display:none;margin:10px 0;background:#fff">
			<input type="hidden" name="a" value="add_data">
			{if $BRANCH_CODE == 'HQ'}
			Branch
			<select name="branch_id">
				{foreach from=$branch_list key=k item=i}
					<option value="{$k}">{$i}</option>
				{/foreach}
			</select>&nbsp;&nbsp;&nbsp;
			{else}
				<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}">
			{/if}
			Cut-Off Date (yyyy-mm-dd) <input name="cut_off_date" value=""><br><br>
			* Leave the cost and selling empty to use latest cost price and selling price
			<table border="0" cellpadding="4" cellspacing="0" class="tb">
				<tr>
					<th>ARMS Code</th>
					<th>Scanned By</th>
					<th>Location</th>
					<th>Shelf No</th>
					<th>Item No</th>
					<th>Qty</th>
					<th>Selling</th>
					<th>Cost</th>
				</tr>
				<tbody id="tbody_rows">
				</tbody>
				<tr id="row_template" style="display:none">
					<td nowrap><img src="/ui/icons/delete.png" onclick="STOCK_CHECK_IMPORT.del_row(this)"><input onchange="STOCK_CHECK_IMPORT.check_item_code(this)" size="15" class="sku_item_code" name="sku_item_code[]"></td>
					<td><input onchange="uc(this)" size="5" name="scanned_by[]"></td>
					<td><input onchange="uc(this)" size="8" class="location" name="location[]"></td>
					<td><input onchange="uc(this)" size="8" class="shelf_no" name="shelf_no[]"></td>
					<td><input onchange="uc(this)" size="3" class="item_no" name="item_no[]"></td>
					<td><input onchange="uc(this)" size="5" name="qty[]"></td>
					<td><input onchange="uc(this)" size="5" name="selling[]"></td>
					<td><input onchange="uc(this)" size="5" name="cost[]"></td>
				</tr>
				<tr>
					<td colspan="10" align="center">
					<input onclick="STOCK_CHECK_IMPORT.insert_row()" type="button" value="Insert Row" style="color:#fff;background:#f90;font:20px Arial">
					<input onclick="STOCK_CHECK_IMPORT.do_save()" type="button" value="Save" style="color:#fff;background:#090;font:20px Arial">
					<input onclick="STOCK_CHECK_IMPORT.edit_cancel()" type="button" value="Cancel" style="color:#fff;background:#09f;font:20px Arial">
					</td>
				</tr>
			</table>
		</div>
	</form><br>
	<div class="stdframe">
		<form name="f_d" onsubmit="STOCK_CHECK_IMPORT.check_search_form(); return false">
			<input type="hidden" name="a" value="search_data">
			<table>
				<tr>
					{if $BRANCH_CODE eq "HQ"}
						<th>Branch</th>
						<td>
							<select name="search_branch_id" onchange="STOCK_CHECK_IMPORT.get_stock_check_date();">
								<option value="">-- Please Select --</option>
								{foreach from=$branch_list key=k item=i}
									<option value="{$k}" {if $smarty.request.search_branch_id eq $k}selected{/if}>{$i}</option>
								{/foreach}
							</select>&nbsp;&nbsp;&nbsp;
						</td>
					{else}
						<input type="hidden" name="search_branch_id" value="{$sessioninfo.branch_id}">
					{/if}
					<th>Date</th>
					<td>
						<select name="search_date">
							<option value="">-- Please Select --</option>
						</select>&nbsp;&nbsp;&nbsp;
					</td>
					<th>Search</th>
					<td>
						<select name="search_type">
							{foreach from=$search_type key=k item=i}
								<option value="{$k}" {if $smarty.request.search_type eq $k}selected{/if}>{$i}</option>
							{/foreach}
						</select>&nbsp;
					</td>
					<th>for <input name="search_value"></th>
					<td><input type="submit" value="Find"></td>
				</tr>
			</table>
		</form>
		<div id="div_stock_check_table">
			{include file="admin.stockchk_import.table.tpl"}
		</div>
	</div>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
STOCK_CHECK_IMPORT.initialize();
{/literal}
</script>