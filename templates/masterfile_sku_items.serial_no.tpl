{*
6/22/2011 11:06:29 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

12/19/2011 11:51:43 AM Justin
- Modified the CSS to use new id for row color display.

8/12/2013 1:45 PM Justin
- Enhanced to show user can change customer info while has privilege.

4/18/2014 2:54 PM Justin
- Enhanced to allow user search by Serial No.

5/30/2014 5:46 PM Justin
- Enhanced to have range insert for Serial No.
- Enhanced to reset the form while openning the Add S/N popup.

8/7/2014 9:19 AM Justin
- Enhanced to have remark column.

11/6/2014 2:09 PM Fithri
- show info of how many item has been scanned

6/18/2015 11:30 AM Justin
- Enhanced to check and show error message if user trying to add existing S/N from other branch.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

06/26/2020 Sheila 01:55 PM
- Updated button css.
*}

{include file="header.tpl"}
{literal}
<style>
.sold {
	color:#f00;
}

.serial_no_tbl tr:nth-of-type(odd) {
    background-color: #eeeeee;
}

.serial_no_tbl {
	border-top:1px solid #000;
	border-right:1px solid #000;
	white-space:no-wrap;
}

.serial_no_tbl tr.header td, .serial_no_tbl tr.header th{
	background:#fe9;
	padding:6px 4px;
}

.serial_no_tbl tr.sn_dtl:hover{
	background:#ffffcc !important;
}

.serial_no_tbl textarea {
	background-color:#fff;
}

</style>
{/literal}

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
// update autocompleter parameters when vendor_id or department_id changed
var sku_autocomplete = undefined;

function reset_sku_autocomplete(){
	//var param_str = "a=ajax_search_sku&dept_id={/literal}{$form.department_id}{literal}&type="+getRadioValue(document.f_a.search_type);
	var param_str = "a=ajax_search_sku&type="+getRadioValue(document.f_a.search_type)+"&check_sn=1";
	if (sku_autocomplete != undefined)
	{
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else
	{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_a.sku_item_id.value = s[0];
			document.f_a.sku_item_code.value = s[1];
			
		}});
	}
	clear_autocomplete();
}

function clear_autocomplete(){
	document.f_a.sku_item_id.value = '';
	document.f_a.sku_item_code.value = '';
	$('autocomplete_sku').value = '';
	$('autocomplete_sku').focus();
}

function find_items(obj, tab){
	var params = "";

	if(!document.f_a.sku_item_id.value && !document.f_a.sn_filter.value){
		alert('Please select a SKU item or provide Serial No to search.');
		return false;
	}

	if(obj != undefined) obj.disabled = true;

	if(tab != undefined){
		params += "&tab="+tab;
		if(tab == 3){
			if($('inp_sn_search').value) params += "&str_search="+$('inp_sn_search').value;
			else{
				alert("Please enter S/N and search.");
				return false;
			}
		}
	}

	new Ajax.Updater('serial_no_div', phpself, {
		method:'post',
		parameters: encodeURI(Form.serialize(document.f_a)+params),
		evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onSuccess: function(m){
			if(obj != undefined) obj.disabled = false;
		}

	});
}

function reset_row(){

	var e = $('serial_no_items').getElementsByClassName('no');
	var total_rows=e.length;

	for(var i=0;i<total_rows;i++)	{
 		var temp_1 =new RegExp('^no');
	 	if (temp_1.test(e[i].id)){
			td_1=(i+1)+'.';
			e[i].innerHTML=td_1;
			e[i].id='no'+(i+1);
		}
	}

	$('autocomplete_sku').select();
}

function delete_item(ele){
	if(!confirm('Are you sure?')) return;
	var parent_row=ele.parentNode.parentNode;

	if(!document.f_a.item_del_list.value) document.f_a.item_del_list.value += ele.id;
	else document.f_a.item_del_list.value += ","+ele.id;

	if($('delete_msg'))	$('delete_msg').remove();
	$(parent_row).remove();
	reset_row();
}

function delete_sn_items(id, branch_id, active, ele){
	if(ele.src == "/ui/clock.gif") return false;
	else ele.src = "/ui/clock.gif";

	if(active == 1){
		var msg = "activate";
		source = "/ui/act.png";
	}else{
		var msg = "inactive";
		source = "/ui/rejected.png";
	}

 	if (!confirm("Are you sure want to "+msg+" this S/N?")){
 		ele.src = source;
	 	return;
	}

	new Ajax.Request(phpself,{
		method:'post',
		parameters: "a=delete&id="+id+"&branch_id="+branch_id+"&active="+active,
	    evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onSuccess: function(m){
			//alert($('curr_tab').value);
			if($('curr_tab').value == 3){
				if(active == 1){ // is activate
					$('inac_msg'+id+"_"+branch_id).hide();
					ele.src = "/ui/rejected.png";
					ele.setAttribute("onclick","delete_sn_items('"+id+"', '"+branch_id+"', '0', this)");
				}else{ // is deactivate
					$('inac_msg'+id+"_"+branch_id).show();
					ele.src = "/ui/act.png";
					ele.setAttribute("onclick","delete_sn_items('"+id+"', '"+branch_id+"', '1', this)");
				}
				document.f_a.elements['active['+id+']['+branch_id+']'].value = active;
            }else{
				Element.remove('sn_row'+id+"_"+branch_id);
            	reset_row();
			}
    	},
    	onComplete: function(m){
    		var serial_no_items = $('serial_no_items').getElementsByTagName('input');
			var sn_total_rows = serial_no_items.length;

			if(sn_total_rows == 0){
				$('upd_btn').style.display = "none";
				new Insertion.Bottom($('serial_no_items'), "<tr id=\"empty_row\"><td colspan=\"11\" align=\"center\">-- No record --</td></tr>");
			}
		}
	});
}

function add_sn_items(){
	var have_item = '';
	var branch_id = '';
	var existed_list = false;
	var sold_list = false;

	if(document.f_sn.sn_choice[0].checked == true && !document.f_sn.serial_no_list.value){
		alert("There is no Serial No to add.");
		return false;
	}else if(document.f_sn.sn_choice[1].checked == true){
		var sn_from = document.f_sn.sn_from.value.trim();
		var sn_to = document.f_sn.sn_to.value.trim();
		if(!sn_from || !sn_to){
			alert("Please set S/N range From and To.");
			return false;
		}else if(sn_from > sn_to){
			alert("Invalid S/N range From and To.");
			return false;
		}
	}

	if(!confirm("Are you sure want to add below S/N?")) return false;

	if(document.f_a.branch_id) branch_id = document.f_a.branch_id.value;

	new Ajax.Request(phpself, {
		method:'post',
		parameters: Form.serialize(document.f_sn)+'&a=add&sku_item_id='+document.f_a.sku_item_id.value+'&sku_item_code='+document.f_a.sku_item_code.value+'&branch_id='+branch_id+"&tab="+document.f_a.tab.value,
		evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onSuccess: function(m){
			eval("var json = "+m.responseText);
			for(var tr_key in json){
				if(json[tr_key]['rowdata']){
					if($('empty_row')) $('empty_row').remove();
				
					if(json[tr_key]['is_update'] && $("sn_row"+json[tr_key]['upd_id']+"_"+json[tr_key]['upd_branch_id'])){
						$("sn_row"+json[tr_key]['upd_id']+"_"+json[tr_key]['upd_branch_id']).update(json[tr_key]['rowdata']);
					}else new Insertion.Bottom($('serial_no_items'),json[tr_key]['rowdata']);
				}

				if(json[tr_key]['existed_sn']){
					$('err').style.display = "";
					$("dup_sn").update(json[tr_key]['existed_sn']);
					existed_list = true; 
				}

				if(json[tr_key]['sn_sold']){
					$('err').style.display = "";
					$("sn_sold").update(json[tr_key]['sn_sold']);
					sold_list = true;
				}
				if(json[tr_key]['sn_diff_branch']){
					$('err').style.display = "";
					$("sn_diff_branch").update(json[tr_key]['sn_diff_branch']);
					diff_branch_list = true;
				}
			}
		},
		onComplete: function(m){
			if(!sold_list) $('sold_sn_msg').style.display = "none";
			else $('sold_sn_msg').style.display = "";
			if(!existed_list) $('dup_sn_msg').style.display = "none";
			else $('dup_sn_msg').style.display = "";
			if(!diff_branch_list) $('diff_branch_sn_msg').style.display = "none";
			else $('diff_branch_sn_msg').style.display = "";

			reset_row();
			$("upd_btn").show();
			obj.disabled = false;
		}
	});

	document.f_sn.serial_no_list.value = "";
	$('linecount').update(0);
	curtain_clicked();
	/*new Ajax.Updater('serial_no_div', phpself, {
		method:'post',
		parameters: Form.serialize(document.f_sn)+'&a=add&sku_item_id='+document.f_a.sku_item_id.value+'&sku_item_code='+document.f_a.sku_item_code.value+'&branch_id='+branch_id,
		evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onSuccess: function(m){
			curtain_clicked();
		}

	});*/
}

function save_sn_items(){
	var err_msg = '';
	var item_count = 0;

	var serial_no_items = $('serial_no_items').getElementsByTagName('input');
	var sn_total_rows = serial_no_items.length;

	if(sn_total_rows > 0){
		$A(serial_no_items).each(
			function(r,idx){
				if(r.name.indexOf("serial_no")==0){
					item_count++;
					if(!r.value){
						err_msg += "Row ["+item_count+"] having empty S/N. \n";
					}
				}
			}
		);
	}

	if(err_msg){
		alert("You have encountered below: \n\n"+err_msg);
		return false;
	}else{
		if(!confirm("Are you sure want to update the following S/N item(s)?")) return false;
	}

	new Ajax.Request(phpself,{
		method:'post',
		parameters: Form.serialize(document.f_a)+"&a=save",
	    evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onSuccess: function(m){
			//alert($('curr_tab').value);
			if(m.responseText){
				$('err').style.display = "";
				$('dup_sn_msg').style.display = "";
				$('sold_sn_msg').style.display = "none";
				$("dup_sn").update(m.responseText);
			}else{
				alert("Saved Successfully");
				window.location = phpself;
			}
    	}
	});
}

function curtain_clicked(){
	hidediv('add_sn');
	document.f_sn.serial_no_list.value = "";
	$('linecount').update(0);
	document.f_sn.sn_from.value = "";
	document.f_sn.sn_to.value = "";
	document.f_sn.remark.value = "";
	curtain(false);
}

function clear_dtl(){
	$('serial_no_div').innerHTML = '';
	document.f_a.sku_item_id.value = '';
	document.f_a.sku_item_code.value = '';
}

function disableEnterKey(e){
     var key;

     if(window.event) key = window.event.keyCode;	//IE
     else key = e.which;	//firefox

     if(key == 13) return false;
     else return true;
}

function list_sel(s)
{	
	$$('.tab a').each(function(e)
	{
		e.className = '';
	});
	
	$('lst'+s).className = 'active';
	find_items(undefined, s);
	//$(s).style.display = '';
	//$(s).className='tabcontent active';
}

function search_input_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		list_sel(3);
	}
}

function toggle_sn_choice(obj){
	if(obj == undefined) return;
	
	if(obj.value == 1){ // it is check on insert by list
		$("tbody_sn_by_range").hide();
		$("tr_sn_list").show();
	}else{ // it is check on insert by range
		$("tbody_sn_by_range").show();
		$("tr_sn_list").hide();
	}
}

{/literal}
</script>

<iframe width=1 height=1 style="visibility:hidden" id=test></iframe>

<div id="add_sn" style="background:#fff;border:3px solid #000;width:300px;position:absolute; padding:10px; display:none;z-index:20000;">
	<div class="small" style="position:absolute; right:10px;">
		<a href="javascript:void(curtain_clicked())"><img src=ui/closewin.png border=0 align=absmiddle></a>
	</div>
	<div class="alert alert-primary rounded mx-3">
		<b>Important:</b><br>
	* To insert different S/N, press "Enter" to proceed next row.<br />
	* To use insert S/N by range, the S/N MUST in numberic format.
	</div>
	
	<form name="f_sn" method="post">
		<hr>
		<b class="form-label">Insert S/N by <input type="radio" name="sn_choice" value="1" onclick="toggle_sn_choice(this);" checked> List <input type="radio" name="sn_choice" value="2" onclick="toggle_sn_choice(this);"> Range</b><br />
		<table width="100%">
			<tr id="tr_sn_list">
				<td colspan="2">
					<textarea class="form-control" name="serial_no_list" id="serial_no_list" cols="30" rows="{$sn_rows}" wrap="off"></textarea>
					<br />&nbsp;&nbsp;<i><span id="linecount">0</span> Item(s)</i>
				</td>
			</tr>
			<tbody id="tbody_sn_by_range" style="display:none;">
				<tr>
					<td width="30" ><b class="form-label">From</b></td>
					<td><input class="form-control" type="text" name="sn_from" size="15" onchange="mi(this);" /></td>
				</tr>
				<tr>
					<td><b class="form-label">To</b></td>
					<td><input class="form-control" type="text" name="sn_to" size="15" onchange="mi(this);" /></td>
				</tr>
			</tbody>
			<tr>
				<td><b class="form-label">Remarks</b></td>
				<td><textarea class="form-control" type="text" name="remark" cols="22" rows="3" /></textarea></td>
			</tr>
			<tr align="center">
				<td colspan="2">
					<input type="button" class="btn btn-primary" value="Add" onclick="add_sn_items(this);">
					<input type="button" class="btn btn-danger" value="Back" onclick="curtain_clicked();">
				</td>
			</tr>
		</table>
	</form>
</div>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class=stdframe style="background:#fff;">
<div id=history_popup style="padding:5px;border:1px solid #000;overflow:hidden;width:300px;height:300px;position:absolute;background:#fff;display:none;">
<div style="text-align:right"><img src="/ui/closewin.png" onclick="Element.hide('history_popup')"></div>
<div id=history_popup_content></div>
</div>

		<form name="f_a" method="post">
			<div class="card mx-3">
				<div class="card-body">
			<input type="hidden" name="a" value="search">
			<table>
			<tr>
				
					{if $BRANCH_CODE eq 'HQ'}
					<div class="row">
						<div class="col-md-6">
				
								<b class="form-label">Located Branch</b>
						<select class="form-control" name="branch_id" onKeyPress="return disableEnterKey(event);" onchange="if(document.f_a.sku_item_id.value) find_items(); ">
							<option value="">-- All --</option>
							{foreach from=$branch_list item=r}
								<option value="{$r.id}" {if $smarty.request.branch_id eq $r.id} selected {/if}>{$r.code}</option>
								{if $smarty.request.branch eq $r.id}
									{assign var=bcode value=$r.code}
								{/if}
							{/foreach}
						</select>
							
						</div>
						<div class="col-md-6">
							
								<b class="form-label">Serial No</b> <input class="form-control" type="text" name="sn_filter" value="{$form.sn_filter}">
							
						</div>
					
					</div>
				{else}
					<td><b>Serial No</b></td>
					<td><input type="text" name="sn_filter" value="{$form.sn_filter}"></td>
				{/if}
				
			</tr>
			<tr>
				<th align="left" class="form-label mt-3">Search SKU</th>
				<td {if $BRANCH_CODE eq 'HQ'}colspan="3"{/if}>
					<input name="sku_item_id" size=3 type=hidden>
					<input name="sku_item_code" size=13 type=hidden>
					<input class="form-control mt-2" id="autocomplete_sku" name="sku" size=50 onclick="this.select()" onchange="clear_dtl();" onKeyPress="return disableEnterKey(event);" style="font-size:14px;width:500px;">
					<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td {if $BRANCH_CODE eq 'HQ'}colspan="3"{/if}>
					<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
					<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
					<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="3"> ARMS Code
					<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="4"> Description
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td colspan="2">
					<input class="btn btn-primary mt-3" type="button" value="Search" onclick="find_items(this);">
					{if file_exists('masterfile_sku_items.serial_no.import_grn_items.php')}
						<input class="btn btn-info mt-3" type="button" value="Import from GRN" onclick="window.open('masterfile_sku_items.serial_no.import_grn_items.php', '_blank');">
					{/if}
				</td>
			</tr>
			</table>
		</div>
	</div>			
			<input name="item_del_list" type="hidden">
			<div id="serial_no_div"></div>
			</form>
	
{include file="footer.tpl"}
{literal}
<script>
new Draggable('add_sn');
center_div('add_sn');
reset_sku_autocomplete();

Event.observe(serial_no_list, 'input', function(event)
{
	var l = 0;
	inpTxt = Event.element(event).value.trim();
	inpTxt.split(/\r\n|\r|\n/).each(function(s)
	{
		if (s.length > 0) l++;
	});
	$('linecount').update(l);
});
</script>
{/literal}
