{*
1/30/2012 4:24:28 PM Alex
- move the matched results to same div with searching
- combine search and match list into 1 div

12/24/2012 10:23 AM Justin
- Changed the capture from "Click on the row to edit SKU Items" to "Click on Barcode to edit SKU Item".

5/9/2013 1:40 PM Fithri
- bugfix - if got multiple same code is invalid, only replace 1 will cause the module to show all verified, but it is actually still got other un-verify

8/28/2013 2:48 PM Justin
- Enhanced the invalid SKU to list by index key instead of barcode in order to prevent special characters that causes javascript errors.

2/3/2015 3:24 PM Andy
- Enhance to able to change GST when verify invalid sku.

7/13/2016 5:25 PM Andy
- Enhanced to able to show multiple original gst type.

12/19/2016 5:38 PM Andy
- Fixed a bug where special character will cause item cannot be verify.

4/20/2017 4:25 PM Khausalya 
- Enhanced changes from RM to use config setting. 

7/3/2017 1:58 PM Justin
- Bug fixed on transaction details popup having layout display issue.

06/30/2020 04:43 PM Sheila
- Updated button css.

10/15/2020 8:30 PM William
- Enhanced to add new tax checking.
*}
{include file=header.tpl}
{if !$no_header_footer}
<script type="text/javascript">
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var phpself = '{$smarty.server.PHP_SELF}';
var pos_items_id = '';
var barcode = '';
var description = '';
var price_unit= '';
var view_only = '{$view_only}';
var window_top=0;
var window_left=0;
var mousex=0;
var mousey=0;
var sku=[];
var enable_gst = int('{$config.enable_gst}');
var enable_tax = int('{$config.enable_tax}');
var selected_date = '{$smarty.request.date_select}';
</script>

{literal}
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

function init_calendar(){
	Calendar.setup({
	    inputField     :    "date_select",     // id of the input field
	    ifFormat       :    "%Y-%m-%d",      // format of the input field
	    button         :    "t_date_select",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
}

function curtain_clicked()
{
	curtain(false);
	hidediv('div_item_details');
}

function mouseX(evt) {
	var evt=window.event;
	if (!evt)	return null;
	if (evt.pageX) return evt.pageX;
	else if (evt.clientX)
	   return evt.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
	else return null;
}
function mouseY(evt) {
	var evt=window.event;
	if (!evt)	return null;

	if (evt.pageY) return evt.pageY;
	else if (evt.clientY)
	   return evt.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	else return null;
}

function trans_detail(counter_id,date,pos_id,branch_id)
{
	curtain(true);
	center_div('div_item_details');
    $('div_item_details').show();
	$('div_item_content').update(_loading_+' Please wait...');

	new Ajax.Updater('div_item_content','counter_collection.php',
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			counter_id: counter_id,
			pos_id: pos_id,
			branch_id: branch_id,
			date: date
		}
	});
}

function check_selected_remaining(type){
	var num=0;
	$$("."+type).each(function(ele,index){
		if (type == 'remaining'){
			if(!$(ele).checked)	num++;
		}else{
			if($(ele).checked)	num++;
		}		
	});
	if ($("no_"+type+"_id"))	$("no_"+type+"_id").innerHTML=num;
}

function add_autocomplete(){
	check_and_replace(false);
}

function multiple_add_autocomplete(){
	check_and_replace(true);
}

function ajax_get_more_info(sku_item_id){
	
	$('info_loading_id').update(_loading_);
	
	var params = {
		'selected_date': selected_date,
		sku_item_id: sku_item_id
	}
	
	new Ajax.Request(phpself+'?a=ajax_get_sku_items', {
		parameters:params,
		onComplete: function(e){
			var msg = e.responseText.trim();
			if(msg=='no'){  // no match found
				output='No match found.';
			}else{
				output=msg;
			}
			eval("sku = "+msg);
			if (!sku['sku_item_code'])	sku['sku_item_code']='-';
			if (!sku['artno'])	sku['artno']='-';
			if (!sku['mcode'])	sku['mcode']='-';
			if (!sku['link_code'])	sku['link_code']='-';
			if (!sku['receipt_description'])	sku['receipt_description']='-';
			if (!sku['cost_price'])	sku['cost_price']='-';
			if (!sku['selling_price'])	sku['selling_price']='-';

			if(enable_gst || enable_tax){
				if(int(sku['is_under_gst'])){
					$('div_replace_sku_gst_info').show();
					
					$('sel_replace_sku_gst').selectedIndex = 0;
					if(sku['gst_info']['id']){
						$('sel_replace_sku_gst').value = sku['gst_info']['id'];
					}
				}else{
					$('div_replace_sku_gst_info').hide();
				}
			}
			
			$('info_description').update(sku['receipt_description']);
			$('info_sku_item_code').update(sku['sku_item_code']);
			$('info_artno').update(sku['artno']);
			$('info_mcode').update(sku['mcode']);
			$('info_link_code').update(sku['link_code']);
			$('info_cost').update(round(sku['cost_price'],global_cost_decimal_points));
			$('info_price').update(round2(sku['selling_price']));
			$('info_loading_id').update("");
			$('more_info_id').show();
		}
	});
}

function check_and_replace(replace_all){
	if (!$('sku_item_id').value){
		alert("Invalid item. Please enter a SKU item");
		return;
	}
	
	replace_row(sku,replace_all);
	hide_search_div();
	/*
	var param_str = Form.serialize(document.f_a)+"&sku_item_id="+$('sku_item_id').value;

	new Ajax.Request(phpself+'?a=ajax_get_sku_items', {
		parameters:param_str,
		onComplete: function(e){
			var msg = e.responseText.trim();
			if(msg=='no'){  // no match found
				alert('No match found.');
			}else{
				eval("sku = "+msg);
				

				replace_row(sku,replace_all);
			}
			$('search_sku_id').hide();
		}
	});
	*/
}

function hide_search_div(){
	$('search_sku_id').hide();
	$('more_info_id').hide();
}


function ajax_get_match_items(row_id, bc,desc,pu){
	//console.log("row_id: "+row_id);
	
	reset_sku_autocomplete();
	$('more_info_id').hide();
	
	// update ori gst info
	if(enable_gst || enable_tax){
		$('span_sku_ori_gst_info').update('');
		var sku_ori_gst_info ='';
		$$('#tr_row-'+row_id+' input.inp_old_gst_key-'+row_id).each(function(inp){
			var gst_info = inp.value.split('-');
			if(sku_ori_gst_info)	sku_ori_gst_info += ', ';
			sku_ori_gst_info += gst_info[0]+'@'+gst_info[1]+'%';
			
		});
		$('span_sku_ori_gst_info').update(sku_ori_gst_info);
	}
	
	var param_str = Form.serialize(document.f_a)+"&barcode="+bc;
	$('match_loading_id').update(_loading_);
	new Ajax.Request(phpself+'?a=ajax_get_match_items', {
		parameters:param_str,
		onComplete: function(e){
			var msg = e.responseText.trim();
			if(msg=='no'){  // no match found
				output='No match found.';
			}else{
				output=msg;
			}
			var ori_barcode = $$('#tr_row-'+row_id+' input.tmp_ori_barcode')[0].value;
			$('barcode_cls').update(ori_barcode);
			$('description_cls').update(desc);
			$('price_unit_cls').update(pu);
			$('match_loading_id').update("");
			$('match_items_id').update(output);
		}
	});
}

function replace_row(sku,replace_all){
	var row_id = $("row_id_"+barcode+"_"+price_unit).value;

	// gst
	var new_sku_gst_id = 0;
	var new_sku_gst_indicator = '';
	var new_sku_gst_rate = 0;
	if((enable_gst || enable_tax) && $('div_replace_sku_gst_info').style.display==''){
		var sel_replace_sku_gst = $('sel_replace_sku_gst');
		new_sku_gst_id = sel_replace_sku_gst.value;
		var opt = sel_replace_sku_gst.options[sel_replace_sku_gst.selectedIndex];
		new_sku_gst_indicator = opt.readAttribute('indicator_receipt');
		new_sku_gst_rate = opt.readAttribute('gst_rate');
	}
	
	if (replace_all){
		var code_obj=$$('.org_code');

		$(code_obj).each(function(ele,index){
			var tmp_barcode = ele.readAttribute('barcode');
			
			if(tmp_barcode == barcode){
				var row_id = ele.readAttribute('row_id');
				
				$(ele).value=sku['id'];
				$(ele).checked=true;
				$(ele).enable();
				change_background(ele);
				
				//get row element
				var tr = $(ele).parentNode.parentNode;
				$(tr).getElementsByClassName('receipt_'+barcode)[0].update(sku['receipt_description']);
				$(tr).getElementsByClassName('sku_item_code_'+barcode)[0].update(sku['sku_item_code']);
				$(tr).getElementsByClassName('mcode_'+barcode)[0].update(sku['mcode']);
				$(tr).getElementsByClassName('linkcode_'+barcode)[0].update(sku['link_code']);
				$(tr).getElementsByClassName('selling_'+barcode)[0].update(round2(sku['selling_price']));
				
				var img_ele = $(tr).getElementsByClassName('img_'+barcode)[0];
				$(img_ele).src="ui/notify_sku_reject.png";
				$(img_ele).title="Revert";
				$(img_ele).onclick=revert_func;
				$(img_ele).addClassName("set_target");
				
				$(tr).getElementsByClassName('title_'+barcode)[0].update("<a onclick='revert_func_with_element(this)'>Revert</a>");
				$(tr).getElementsByClassName('match_'+barcode)[0].checked=true;
				$(tr).getElementsByClassName('verify_user_'+barcode)[0].update("-");
				$(tr).getElementsByClassName('verify_timestamp_'+barcode)[0].update("-");
				
				// gst
				if((enable_gst || enable_tax) && new_sku_gst_id > 0){
					document.f_c['new_sku_gst_info['+row_id+'][gst_id]'].value = new_sku_gst_id;
					
					if($('span_sku_revert_gst_info-'+row_id).innerHTML == ''){
						// keep for later revert
						$('span_sku_revert_gst_info-'+row_id).update($('span_sku_new_gst_info-'+row_id).innerHTML);
					}
					$('span_sku_new_gst_info-'+row_id).update(new_sku_gst_indicator+'@'+new_sku_gst_rate+'%');
				}
			}
		});
	}else{
		var ele_id=row_id+"_"+price_unit;
		$('code_'+ele_id).value=sku['id'];
		$('code_'+ele_id).checked=true;
		$('code_'+ele_id).enable();
		$('pos_items_id_'+ele_id).checked=true;
		change_background($('code_'+ele_id));
		$('receipt_'+ele_id).update(sku['receipt_description']);
	 	$('sku_item_code_'+ele_id).innerHTML=sku['sku_item_code'];
		$('mcode_'+ele_id).innerHTML=sku['mcode'];
		$('linkcode_'+ele_id).innerHTML=sku['link_code'];
		$('selling_'+ele_id).innerHTML=round2(sku['selling_price']);
		$('img_'+ele_id).src="ui/notify_sku_reject.png";
		$('img_'+ele_id).title="Revert";
		$('img_'+ele_id).onclick=revert_func;
		$('img_'+ele_id).addClassName("set_target");
		$('title_'+ele_id).update("<a onclick='revert_func_with_element(this)'>Revert</a>");
		$('match_'+ele_id).checked=true;
		$('verify_user_'+ele_id).update("-");
		$('verify_timestamp_'+ele_id).update("-");
		
		// gst
		if((enable_gst || enable_tax) && new_sku_gst_id > 0){
			document.f_c['new_sku_gst_info['+row_id+'][gst_id]'].value = new_sku_gst_id;
			
			if($('span_sku_revert_gst_info-'+row_id).innerHTML == ''){
				// keep for later revert
				$('span_sku_revert_gst_info-'+row_id).update($('span_sku_new_gst_info-'+row_id).innerHTML);
			}
			$('span_sku_new_gst_info-'+row_id).update(new_sku_gst_indicator+'@'+new_sku_gst_rate+'%');
		}
	}


	check_selected_remaining('selected');
	check_selected_remaining('remaining');
}

var revert_func=function (){
	revert_func_with_element(this);
};

function revert_func_with_element(ele){
	var tr;
	//check if is image
	if (ele.src){
		tr=ele.parentNode.parentNode;
	}else{
		tr=ele.parentNode.parentNode.parentNode;
	}

	var barcode = $(tr).getElementsByClassName("tmp_barcode")[0].value;
	var price_unit=$(tr).getElementsByClassName("price_unit")[0].innerHTML;
	var row_id = $("row_id_"+barcode+"_"+price_unit).value;

	//get tmp data

	sku['tmp_code']=$(tr).getElementsByClassName("tmp_code")[0].value;
	sku['tmp_receipt_description'] = $(tr).getElementsByClassName("tmp_receipt_description")[0].value;
	sku['tmp_sku_item_code'] = $(tr).getElementsByClassName("tmp_sku_item_code")[0].value;
	sku['tmp_mcode'] = $(tr).getElementsByClassName("tmp_mcode")[0].value;
	sku['tmp_link_code'] = $(tr).getElementsByClassName("tmp_link_code")[0].value;
	sku['tmp_org_selling_price'] = $(tr).getElementsByClassName("tmp_org_selling_price")[0].value;
	sku['tmp_verify_user'] = $(tr).getElementsByClassName("tmp_verify_user")[0].value;
	sku['tmp_has_partially_verified'] = $(tr).getElementsByClassName("tmp_has_partially_verified")[0].value;
	sku['tmp_verify_timestamp'] = $(tr).getElementsByClassName("tmp_verify_timestamp")[0].value;
	
	var ele_id=row_id+"_"+price_unit;
	$('code_'+ele_id).value=sku['tmp_code'];
	if (sku['tmp_verify_user'] != '-'){
		$('code_'+ele_id).disable();
		if (sku['tmp_has_partially_verified'] == '1') {
			$('img_'+ele_id).src="ui/approved_grey.png";
			$('img_'+ele_id).title="Partially Verified";
			$('title_'+ele_id).update("Partially Verified");
		}
		else {
			$('img_'+ele_id).src="ui/approved.png";
			$('img_'+ele_id).title="Verified";
			$('title_'+ele_id).update("Verified");
		}
	}else if (sku['tmp_code']){
		$('code_'+ele_id).enable();
		$('img_'+ele_id).src="ui/icons/cog.png";
		$('img_'+ele_id).title="Auto-match";
		$('title_'+ele_id).update("Auto-match");
	}else{
		$('code_'+ele_id).enable();
		$('img_'+ele_id).src="ui/cancel.png";
		$('img_'+ele_id).title="No-matched";
		$('img_'+ele_id).removeClassName("set_target");
		$('title_'+ele_id).update("No-matched");
	}

	$('code_'+ele_id).checked=false;
	$('pos_items_id_'+ele_id).checked=false;
	$('match_'+ele_id).checked=false;
	change_background($('code_'+ele_id),true);
	$('receipt_'+ele_id).update(sku['tmp_receipt_description']);
	$('sku_item_code_'+ele_id).update(sku['tmp_sku_item_code']);
	$('mcode_'+ele_id).update(sku['tmp_mcode']);
	$('linkcode_'+ele_id).update(sku['tmp_link_code']);
	$('selling_'+ele_id).update(sku['tmp_org_selling_price']);
	$('verify_user_'+ele_id).update(sku['tmp_verify_user']);
	$('verify_timestamp_'+ele_id).update(sku['tmp_verify_timestamp']);

	// gst
	if(enable_gst || enable_tax){
		document.f_c['new_sku_gst_info['+row_id+']'].value = 0;
		$('span_sku_new_gst_info-'+row_id).update($('span_sku_revert_gst_info-'+row_id).innerHTML);
	}
	check_selected_remaining('selected');
	check_selected_remaining('remaining');
}

function change_background(ele,no_msg){

	var arr = ele.id.split("_");
	var bc = arr[1];
	var pu = arr[2];

	if (!ele.value && !no_msg){
		ele.checked=false;
		alert("Invalid to select. Empty real SKU item data.");
	}else{
		if (ele.checked){
			$(ele).parentNode.parentNode.addClassName('manual');
		}else{
			$(ele).parentNode.parentNode.removeClassName('manual');
		}
	}

	$('pos_items_id_'+bc+"_"+pu).checked=ele.checked;

}

function set_pointer(ele){
	var tr = $(ele).parentNode;
	barcode = $(tr).getElementsByClassName("tmp_barcode")[0].value;
	description = $(tr).getElementsByClassName("description")[0].innerHTML;
	price_unit = $(tr).getElementsByClassName("price_unit")[0].innerHTML;
}

function check_form(){
	var param_str = Form.serialize(document.f_c)+'&a=ajax_check_code';
	$('loading_id').update(_loading_);
	
	new Ajax.Request(phpself, {
		parameters:param_str,
		onComplete: function(e){
			$('loading_id').update("");
			var msg = e.responseText.trim();
			if(msg!='ok'){  // no match found
				alert(msg);
				return;
			}else{
				if (confirm("Are you sure to update this?"))	document.f_c.submit();
				return;
			}
		}
	});

	return false;
}

function replace_with_match_sku(ele,replace_all){
	var tr=$(ele).parentNode.parentNode;

	var items=[];
	
	items['id']=$(tr).getElementsByClassName("items_id")[0].value;
	items['receipt_description']=$(tr).getElementsByClassName("items_receipt_description")[0].innerHTML;		
	items['sku_item_code']=$(tr).getElementsByClassName("items_sku_item_code")[0].innerHTML;
	items['mcode']=$(tr).getElementsByClassName("items_mcode")[0].innerHTML;
	items['link_code']=$(tr).getElementsByClassName("items_link_code")[0].innerHTML;
	items['selling_price']=$(tr).getElementsByClassName("items_selling_price")[0].innerHTML;
	replace_row(items,replace_all);
	$('search_sku_id').hide();
}

function toggle_transaction(bc, selling){
	var ele = $("toggle_"+bc+"_"+selling); 
	var img_ele =$("expand_"+bc+"_"+selling);
	if (ele.style.display == ''){
		img_ele.src="/ui/expand.gif";
		ele.hide();
	}else{
		img_ele.src="/ui/collapse.gif";
		ele.show();
	}
	
	 
}

function generate_serial(sid, row_id, selling, serial_num){
	if(!confirm('Are you sure to generate this serial number?'))	return false;
	
	var img_generate_serial = $('img_generate_serial-'+row_id+'-'+selling+'-'+serial_num);
	if(!img_generate_serial)	return false;
	var ori_src = img_generate_serial.src;
	
	if(img_generate_serial.src.indexOf('clock')>=0)	return false;
	
	if(!serial_num){
		alert('Invalid Serial Number.');
		return false;
	}
	var inp_pos_items_id = $('pos_items_id_'+row_id+'_'+selling);
	
	if(!inp_pos_items_id){
		alert('Item element not found.');
		return false;
	}
	
	img_generate_serial.src = '/ui/clock.gif';
	
	var branch_id = document.f_c['branch_id'].value;
	var date_select = document.f_c['date_select'].value;
	
	var params = {
		a: 'ajax_generate_serial_num',
		branch_id: branch_id,
		date_select: date_select,
		sid: sid,
		pos_items_id_info: inp_pos_items_id.value,
		serial_num: serial_num
	}
	
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(msg){
			// insert the html at the div bottom
			var str = msg.responseText.trim();
			var ret = {};
		    var err_msg = '';

		    try{
                ret = JSON.parse(str); // try decode json object
                if(ret['ok']){ // success
                	$(img_generate_serial).remove();
                	$('span_serial_added-'+barcode+'-'+selling+'-'+serial_num).show();
                    //new Insertion.After(pi_item_row, ret['html']);
                    
	                return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

			if(err_msg.trim()=='')	err_msg = 'Unknown Error Occur';
		    // prompt the error
		    alert(err_msg);
		    img_generate_serial.src = ori_src;
		}
	});
}
</script>

<style>
#report_tbl tr.hover:hover{
	background-color:#ccffff;
}

.set_target{
	cursor:pointer;
}

#report_tbl tr.highlight{
	background-color:#ccffcc;
}

#report_tbl tr.manual {
	background-color:#fcffcf;
}

#report_tbl tr.auto {
	background-color:#eceece;
}

#report_tbl tr td.show_dlg{
	background-color:#cccccc;
}

#report_tbl tr.counter{
	background-color:#aaaaff;
}

table, table *{
	white-space: nowrap;
}

.match:hover{
	background-color:#ccffff;
}

a{
	cursor:pointer;
}

#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	width:750px;
	height:450px;
	position:absolute;
	z-index:10000;
}

.span_serial_added{
	color: red;
}
</style>
{/literal}
{/if}
<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}

<!-- Item Details -->
<div id="div_item_details" style="display:none;width:750px;height:450px;">
	<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
	<div id="div_item_content">
	</div>
</div>
<!-- End of Item Details-->

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li><font color='red'><b>{$e}</b></font></li>
{/foreach}
</ul>
{/if}

{if $smarty.request.success}
<ul class=err>
<li><font color='green'><b>Update POS successful.</b></font></li>
</ul>
{/if}

<form name="f_a" method="GET" class="form">
<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />

<b>Select Date</b> <input id="date_select" name="date_select" value="{$smarty.request.date_select}" size=10 readonly > <img align=absbottom src="ui/calendar.gif" id="t_date_select" style="cursor: pointer;" title="Select Date" />
&nbsp;&nbsp;&nbsp;&nbsp;
<button class="btn btn-primary" name="a" value="refresh_data">Refresh</button>
<span id='loading_id'></span>
{/if}
</form>
<form name="f_b" method=post>
	<div id="search_sku_id" style="position:absolute;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
		<div id="div_search_sku_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;"><b>Replace SKU</b></span>
	    <span style="float: right;">
	        <a href="javascript:void(hide_search_div())"><img border="0" align="absmiddle" src="ui/closewin.png"></a>
	    </span>
   		<div style="clear:both;"></div>
	    </div>
		<div style="padding:2px;">
			<p>
				<b style="font-size:16px;color:red;">Target</b>&nbsp;&nbsp; 
				<b>Barcode:</b> <span id="barcode_cls"></span>, 
				<b>Description: </b><span id="description_cls"></span>, 
				<b>Price per unit</b> <span id="price_unit_cls"></span>
				{if $config.enable_gst || $config.enable_tax}
					, <b>Tax:</b> <span id="span_sku_ori_gst_info"></span>
				{/if}
				<span id="match_loading_id"></span>
			</p>
			
			<b>Matched SKU Item(s) List</b>		
			<div id="match_items_id">
			{include file="pos.invalid_sku.extra.tpl"}
			</div>
			<br>		
			<fieldset>
				{include file="sku_items_autocomplete.tpl" show_more_info=1 _add_value="Replace" _multiple_add_value="Replace All" parent_form="document.f_b"}
				
				<div id="more_info_id" style="display:none;">
					<div id="div_replace_sku_gst_info">
						<b>Tax:</b>
						<select id="sel_replace_sku_gst">
							{foreach from=$gst_list item=r}
								<option value="{$r.id}" indicator_receipt="{$r.indicator_receipt}" gst_code="{$r.code}"
								gst_rate="{$r.rate}">{$r.indicator_receipt}@{$r.rate}% (Code: {$r.code})</option>
							{/foreach}
						</select>
					</div>
					
					<b>SKU Item info</b><span id="info_loading_id"></span><br />
					<table class="report_table">
						<tr class="header">
							<th>Receipt Description</th>
							<th>ARMS Code</th>
							<th>Art No</th>
							<th>Manufacture Code</th>
							<th>Link Code</th>
							<th>Cost ({$config.arms_currency.symbol}) per unit</th>
							<th>Price ({$config.arms_currency.symbol}) per unit</th>
						</tr>
						<tr>
							<td id="info_description"></td>
							<td id="info_sku_item_code"></td>
							<td id="info_artno"></td>
							<td id="info_mcode"></td>
							<td id="info_link_code"></td>
							<td id="info_cost" class="r"></td>
							<td id="info_price" class="r"></td>
						</tr>
					</table>
				</div>
			</fieldset>
		</div>
	</div>
</form>
{*
<div id="match_sku_id" style="position:absolute;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_match_sku_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;"><b>SKU Matched List</b></span>
    <span style="float: right;">
        <a href="javascript:void(hidediv('match_sku_id'))"><img border="0" align="absmiddle" src="ui/closewin.png"></a>
    </span>
 		<div style="clear:both;"></div>
    </div>
	<div style="padding:2px;">
		<p>
			<b>Target Receipt No:</b> <span id="match_receipt_no_cls"></span><b>, Barcode:</b> <span id="match_barcode_cls"></span>
			<span id="match_loading_id"></span>
		</p>
		<div id="match_items_id"></div>
	</div>
</div>

*}

	<img width='16' src="ui/approved.png" title="Verified" align="absmiddle" /> = Verified SKU &nbsp;&nbsp;
	<img width='16' src="ui/approved_grey.png" title="Partially Verified" align="absmiddle" /> = Partially Verified SKU &nbsp;&nbsp;
	<img width='16' src="ui/icons/cog.png" title="Verified" align="absmiddle" /> = Auto-match SKU &nbsp;&nbsp;
	<img width='16' src="ui/cancel.png" title="Unmatched" align="absmiddle" /> = Invalid SKU &nbsp;&nbsp;
	<img width='16' src="ui/notify_sku_reject.png" title="Revert" align="absmiddle" /> = Click to revert to default setting<br />
	{if !$view_only}
		* Click on Barcode to edit SKU Item
	{/if}
	<br /><br />
{include file="pos.invalid_sku.table.tpl"}

{if !$no_header_footer}
{literal}
<script>

init_calendar();
check_selected_remaining("selected");
check_selected_remaining("remaining");
new Draggable('search_sku_id',{ handle: 'div_search_sku_header'});
//new Draggable('match_sku_id',{ handle: 'div_match_sku_header'});

if (!view_only){
	$$('#report_tbl tr td.set_target').each(function(ele,index){
		$(ele).observe("click",function(){
			set_pointer(ele);
		});
	});
	
	$$('.change_sid').each(function(ele,index){
		$(ele).observe("click",function(){
			if ($("search_sku_id").style.display == 'none'){
				$("search_sku_id").show();
				mousex=mx;
				window_left=(document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
				$("search_sku_id").style.left=mx+window_left+'px';
				mousey=my;
				window_top=(document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
				$("search_sku_id").style.top=my+window_top+'px';
			
				$('sku_item_id').value='';
				$('autocomplete_sku').value='';
				$('autocomplete_sku').focus();
			}
			var row_id = $("row_id_"+barcode+"_"+price_unit).value;
			ajax_get_match_items(row_id, barcode,description,price_unit);
		});
	});
/*
	var previous_effect;
	Event.observe(window, 'scroll', function() { 
		if ($("search_sku_id").style.display != 'none'){
			var vertical_position = 0;
			if (pageYOffset)//usual
			  vertical_position = pageYOffset;
			else if (document.documentElement.clientHeight)//ie
			  vertical_position = document.documentElement.scrollTop;
			else if (document.body)//ie quirks
			  vertical_position = document.body.scrollTop;
			
			//$("search_sku_id").style.top = (vertical_position + mousey) + 'px';
			
			if (previous_effect){
				previous_effect.finishOn=true;
				previous_effect=null;
			}
			previous_effect = new Effect.Move($("search_sku_id"), { x: $("search_sku_id").style.left, y:(vertical_position + mousey) , mode: 'absolute'});
			
		}
	}); 
*/
	reset_sku_autocomplete();
}	
</script>
{/literal}
{include file=footer.tpl}
{/if}
