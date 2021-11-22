{*

9/29/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

10/12/2009 9:30:00 AM jeff
- fix search category price type filter "Unknown column 'trade_discount_code'"

12/10/2009 10:08:57 AM Andy
- change to only allow revoke if status==4 and revoke_id==0 and active==1

3/16/2010 4:06:18 PM Andy
- Make promotion able to change branch after save

4/21/2010 10:13:21 AM Andy
- Promotion branch edit only allow for HQ.

4/23/2010 11:05:03 AM Andy
- Fix promotion after approved, input box still available to edit bugs

5/10/2010 9:20:40 AM edward
- to clear value of min,qty from,qty to when control type change to limited by day or period.

7/6/2010 9:33:06 AM edward
- add print_title_in_receipt checkbox

7/19/2010 12:00:01 PM Alex
- add consignment bearing promotion discount

9/15/2010 1:35:26 PM Andy
- Fix wrong item num bugs.

10/29/2010 10:31:18 AM Alex
- fix display consignment bearing type table by add a refresh button

12/16/2010 1:59:39 PM Andy
- Add block customer to buy more if already hit promotion items limit.

12/22/2010 1:17:29 PM Andy
- Remove "revoke" button from promotion.

1/3/2011 11:54:58 AM Alex
- change function load_data and change brand_vendor to compatible for ajax call

3/22/2011 3:10:15 PM Andy
- Add show/hide overlap promotion.

3/23/2011 4:32:56 PM Alex
- check $config['use_consignment_bearing'] to show and hide consignment bearing module

4/6/2011 3:13:53 PM Andy
- Change discount promotion date format to YYYY-MM-DD.
- Add calendar popup date selection for discount promotion.
- Hide "Use Consignment Bearing Table" if promotion is not use consignment bearning.

4/28/2011 5:46:56 PM Andy
- Add checking to only allow owner or system admin to do delete and cancel promotion.

5/4/2011 5:26:10 PM Alex
- add checking for consignment bearing items if got 0 discount, 0 bearing or 0 net sales, current items not allow to be added

5/9/2011 4:58:45 PM Alex
- add checking config consignment bearing at init_autocomplete

7/8/2011 12:20:16 PM Alex
- Fix date format.

7/12/2011 12:58:39 PM Andy
- Fix show button bugs while create new promotion and save empty promotion items

7/12/2011 12:58:39 PM Alex
- add checking for promotion status while call ajax_change_brand_vendor

9/6/2011 12:01:16 PM Alex
- add discount checking in consignment bearing mode

9/12/2011 11:07:17 AM Alex
- fix branches check box when refresh

9/23/2011 2:21:51 PM Andy
- Add user can cancel whole promotion sheet if got privilege "PROMOTION_CANCEL".

12/2/2011 12:06:52 PM Andy
- Add checking for input value. (only allow number and %)

2/16/2012 6:02:37 PM Andy
- Remove unused "is_pwp" variable.
- Add can set different category reward point.
- Add promotion can set allowed member type.

7/26/2012 10:21:34 AM Justin
- Enhanced the Membership Type to show additional description if found.

8/3/2012 11:48 AM Andy
- Add can check autocomplete object before submit to add.

8/23/2012 2:39 PM Justin
- Enhanced for drop down menu of Member Reward Point to base on privilege PROMOTION_MEMBER_POINT_REWARD.
- Changed the wording "V149" into "V168".

8/30/2012 5:45 PM Justin
- Changed the privilege name from "PROMOTION_MEMBER_POINT_REWARD" into "MEMBER_POINT_REWARD_EDIT".

2/22/2013 11:57 AM Fithri
- add checkbox (include all parent/child) under add item.
- if got tick checkbox, will automatically add all the selected parent/child items
- if the same parent/child item put together, the 2nd item row color will change, until a new group sku

4/3/2013 5:50 PM Fithri
- fix bug where promotion can save and confirm without branch

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window

9/6/2013 3:40 PM Fithri
- add search item by vendor
- change brand search to autocomplete

11/14/2013 11:38 AM Fithri
- add missing indicator for compulsory field

11/26/2013 2:04 PM Justin
- Enhanced to have new function that can change setting by parent child (need config).

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

1/20/2014 2:33 PM Fithri
- fix bug discount value cannot insert percentage & decimal point

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

4/11/2014 10:53 AM Fithri
- add data collector import function at promotion module

5/26/2014 2:16 PM Fithri
- able to select item(s) to reject & must provide reason for each rejected item

8/4/2015 3:00 PM Andy
- Change the cancel promotion checking to check config.doc_reset_level.

07/04/2016 17:30 Edwin
- Enhanced on disable block normal when qty_to and limit is 0 or null.

11/30/2016 3:28 PM Andy
- Escape promotion title to fix double quotes.

4/21/2017 11:02 AM Khausalya
- Enhanced changes from RM to use config setting.

7/6/2018 12:19 PM Andy
- Enhanced import promotion to have column member discount, member price, non-member discount and non-member price.

2/19/2019 5:55 PM Andy
- Enhanced Print Promotion to use shared template.

05/07/2020 5:47 PM Sheila
- Updated button color

05/11/2020 11:06 AM Justin
- Enhanced to have "all" checkbox to select all branches.

06/30/2020 04:00 PM Sheila
- Updated button css.

07/24/2020 09:53 PM Sheila
- Updated button css.
*}

{if !$form.approval_screen}
{include file=header.tpl}
{include file='promotion.print_dialog.tpl'}
{else}
<hr noshade size=2>
{/if}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
input[disabled] {
  color:black;
  background:rgb(255,238,153);
}
input[readonly] {
  color:black;
  background:rgb(255,238,153);
}
select[disabled] {
  color:black;
  background:rgb(255,238,153);
}

.div_member_type_override > div{
	height:25px;
	vertical-align:middle;
}
</style>
<script type="text/javascript">
{/literal}
var category_autocompleter = null;
var b_id= {$sessioninfo.branch_id};
var conf_consignment_bearing='{$config.use_consignment_bearing}';
var phpself = '{$smarty.server.PHP_SELF}';
var show_confirm_msg_dialog = true;
var first_load = true;
var promotion_change_settings_by_parent_child = '{$config.promotion_change_settings_by_parent_child}';
var prv_grp_id = 0;
var curr_obj = "";
var call_own_function = 0;
var skip_dialog_function = false;
{literal}

function check_discount(obj,id,type)
{
	if (type=='select'){
		//consignment bearing side
		var cb_arr=obj.value.split(',');

        if (cb_arr[1] != ''){
			check_consignment_bearing(obj);
			if (/^member_consignment/.test(obj.name)){
				$('member_disc_p_'+id).value = '';
				$('member_disc_p_'+id).readOnly = true;
			}
			else if (/^non_consignment/.test(obj.name)){
				$('non_member_disc_p_'+id).value = '';
				$('non_member_disc_p_'+id).readOnly = true;
			}
		}
		else{
			if (/^member_consignment/.test(obj.name)){
				$('member_disc_p_'+id).readOnly = false;
			}
			else if (/^non_consignment/.test(obj.name)){
				$('non_member_disc_p_'+id).readOnly = false;
			}
		}
	}else if(type=='disc'){
		if (/%/.test(obj.value)){
			alert("Percentage discount is not allow to be used in consignment bearing mode.");
			obj.value="";
		}
	}else{
		//normal side
		obj.value = obj.value.trim();
		
		if (obj.value != '')
		{
			// check pattern
			if (/member_disc_a/.test(obj.name)){
				var ck_pattern = /^[0-9]+(\.?[0-9]{1,2})?$/;
			}else{
				var ck_pattern = /^[0-9]+(\.?[0-9]{1,2})?%?$/;
			}
			
			if (!ck_pattern.test(obj.value)) {
				alert('Invalid input');
				
				if (/^member_disc/.test(obj.name)){	// is at member column
					$('member_disc_a_'+id).value = '';
					$('member_disc_a_'+id).readOnly = false;
					$('member_disc_p_'+id).value = '';
					$('member_disc_p_'+id).readOnly = false;
				}else{	// non member column
					$('non_member_disc_a_'+id).value = '';
					$('non_member_disc_a_'+id).readOnly = false;
					$('non_member_disc_p_'+id).value = '';
					$('non_member_disc_p_'+id).readOnly = false;
				}
				
				return;
			}
			if (/^member_disc_p/.test(obj.name))
			{
				$('member_disc_a_'+id).value = '';
				$('member_disc_a_'+id).readOnly = true;
			}
			else if (/^member_disc_a/.test(obj.name))
			{
			    if ($('member_disc_p_'+id)){
					$('member_disc_p_'+id).value = '';
					$('member_disc_p_'+id).readOnly = true;
				}
				else
					$('member_consignment_'+id).disable();
			}
			else if (/^non_member_disc_p/.test(obj.name))
			{
				$('non_member_disc_a_'+id).value = '';
				$('non_member_disc_a_'+id).readOnly = true;
			}
			else if (/^non_member_disc_a/.test(obj.name))
			{
			    if ($('non_member_disc_p_'+id)){
					$('non_member_disc_p_'+id).value = '';
					$('non_member_disc_p_'+id).readOnly = true;
				}
				else
					$('non_consignment_'+id).disable();

			}
	/*
			if (/%/.test(obj.value) && /member_disc_p/.test(obj.name))
			{
				if (obj.value.replace('%','') > 100)
				{
					alert('cannot more than 100%');
				}
			}
			else if (/member_disc_p/.test(obj.name))
			{
				if (parseFloat(obj.value) > parseFloat($('selling_price_'+id).value))
				{
					alert('Discount price cannot more that selling price.');
				}
			}
	*/
		}
		else
		{
			if (/^member_disc_p/.test(obj.name))
				$('member_disc_a_'+id).readOnly = false;
			else if (/^member_disc_a/.test(obj.name)){
			    if ($('member_disc_p_'+id)){
					$('member_disc_p_'+id).readOnly = false;
				}
				else
					$('member_consignment_'+id).enable();
			}
			else if (/^non_member_disc_p/.test(obj.name))
				$('non_member_disc_a_'+id).readOnly = false;
			else if (/^non_member_disc_a/.test(obj.name)){
	            if($('non_member_disc_p_'+id)){
					$('non_member_disc_p_'+id).readOnly = false;
				}
				else
					$('non_consignment_'+id).enable();
			}
		}
	}
	
	//var field_type = obj.readAttribute('field_type');
	//alert(field_type);
	/*if(promotion_change_settings_by_parent_child && first_load == false){
		show_change_value_by_parent_dialog(obj, 1);
	}*/
	
	if(promotion_change_settings_by_parent_child && skip_dialog_function == false && first_load == false){
		var field_type = obj.readAttribute('field_type');
		var parent_tr = obj.parentNode.parentNode.parentNode;
		var curr_grp_id = parent_tr.readAttribute('group_id');
		var curr_item_id = parent_tr.readAttribute('item_id');
	
		if(curr_grp_id != prv_grp_id) show_confirm_msg_dialog = true;
	
		if(show_confirm_msg_dialog == true) show_change_value_by_parent_dialog(obj, 1);
		else{
			var field_type = obj.readAttribute('field_type');
			var parent_tr = obj.parentNode.parentNode.parentNode;
			var curr_grp_id = parent_tr.readAttribute('group_id');
			var curr_item_id = parent_tr.readAttribute('item_id');
			
			curr_obj = obj;
			change_value_by_parent();
		}
	}
}

function add_autocomplete_callback(m)
{
	var inp = $$('.member_disc_p');
	var inp2 = $$('.non_member_disc_p');
	var ret = new Array();

	if (inp[(inp.length-1)])
	{
		if (/^\d+\%/.test(inp[(inp.length-1)].value))
		ret[0] = inp[(inp.length-1)].value;
		else
		ret[0] = 0;
	}

	if (inp2[(inp2.length-1)])
	{
		if (/^\d+\%/.test(inp2[(inp2.length-1)].value))
		ret[1] = inp2[(inp2.length-1)].value;
		else
		ret[1] = 0;
	}
	return ret;
}

function check_disc(obj,type)
{
	if (type=='select'){
		//consignment bearing side
		var cb_arr=obj.value.split(',');
        if (cb_arr[1] != ''){
			check_consignment_bearing(obj);

			if (/^m_consignment/.test(obj.name)){
				$('m_disc').value = '';
				$('m_disc').readOnly = true;
			}
			else if (/^nm_consignment/.test(obj.name)){
				$('nm_disc').value = '';
				$('nm_disc').readOnly = true;
			}
		}
		else{
			if (/^m_consignment/.test(obj.name)){
				$('m_disc').readOnly = false;
			}
			else if (/^nm_consignment/.test(obj.name)){
				$('nm_disc').readOnly = false;
			}
		}
	}else if(type=='disc'){
		if (/%/.test(obj.value)){
			alert("Percentage discount is not allow to be used in consignment bearing mode.");
			obj.value="";
		}
	}else{
		if (obj.value != '')
		{
			if (obj.name == 'm_disc')
			{
				$('m_price').value = '';
				$('m_price').readOnly = true;
			}
			else if (obj.name == 'm_price')
			{
			    if ($('m_disc')){
					$('m_disc').value = '';
					$('m_disc').readOnly = true;
				}else
				    $('m_consignment').disable();
			}
			else if (obj.name == 'nm_disc')
			{
				$('nm_price').value = '';
				$('nm_price').readOnly = true;
			}
			else if (obj.name == 'nm_price')
			{
			    if ($('m_disc')){
					$('nm_disc').value = '';
					$('nm_disc').readOnly = true;
				}else
				    $('nm_consignment').disable();
			}
		}
		else
		{
			if (obj.name == 'm_disc')
				$('m_price').readOnly = false;
			else if (obj.name == 'm_price'){
			    if ($('m_disc'))
					$('m_disc').readOnly = false;
				else
				    $('m_consignment').enable();
			}
			else if (obj.name == 'nm_disc')
				$('nm_price').readOnly = false;
			else if (obj.name == 'nm_price'){
			    if ($('nm_disc'))
					$('nm_disc').readOnly = false;
				else
				    $('nm_consignment').enable();
			}
		}
	}
}

function get_brand(val,selected_id)
{

	var action = document.f_a.a.value;
	document.f_a.a.value = 'ajax_get_brand';
	var param = Form.serialize(document.f_a);
	document.f_a.a.value = action;
	$('brand_select').innerHTML = '<img src=/ui/clock.gif align=absmiddle>';

	new Ajax.Updater('brand_select',
		'promotion.php',
		{
			method:'post',
			evalScripts:true,
			parameters: param+'&show_all=1&selected='+selected_id,
/*			onComplete:function(m)
			{
				get_sku_type();

			}
*/		});

}

function get_sku_type()
{
	var action = document.f_a.a.value;
	document.f_a.a.value = 'ajax_get_sku_type';
	var param = Form.serialize(document.f_a);
	document.f_a.a.value = action;
	$('sku_type_select').innerHTML = '<img src=/ui/clock.gif align=absmiddle>';
	new Ajax.Updater('sku_type_select',
		'promotion.php',
		{
			method:'post',
			evalScripts:true,
			parameters: param+'&show_all=1',
			onComplete:function(m)
			{
				get_price_type();
			}
		});

}

function get_price_type()
{
	var action = document.f_a.a.value;
	document.f_a.a.value = 'ajax_get_price_type';
	var param = Form.serialize(document.f_a);
	document.f_a.a.value = action;
	$('price_type_select').innerHTML = '<img src=/ui/clock.gif align=absmiddle>';
	new Ajax.Updater('price_type_select',
		'promotion.php',
		{
			method:'post',
			evalScripts:true,
			parameters: param+'&show_all=1',
		});

}

function show_child(id)
{
	// reactivate the auto-completer with child of the category
	setTimeout('category_autocompleter.options.defaultParams = "child='+id+'";category_autocompleter.activate()',250);
}
function init_autocomplete()
{
	//consignment bearing module
	var bearing='';
	if (conf_consignment_bearing){
		if (document.f_a.s_consignment_bearing.checked){
			bearing="&bearing=1&sku_type=CONSIGN&dept_id="+$('sel_did').value;
	
			var r_type=$('id_r_type').value;
	
			if (r_type == 'brand')
			    bearing+="&brand_id="+$('id_bid').value;
			else if (r_type == 'vendor')
				bearing+="&vendor_id="+$('id_vid').value;
		}else{
	        bearing="&sku_type=OUTRIGHT";
		}
	}
	//==========================

	category_autocompleter = new Ajax.Autocompleter("autocomplete_category", "autocomplete_category_choices", "ajax_autocomplete.php?a=ajax_search_category&min_level=1"+bearing, {
	afterUpdateElement: function (obj,li)
	{
	    this.defaultParams = '';
		var s = li.title.split(',');
		$('category_id').value = s[0];
		sel_category(obj,s[1]);
		//get_brand($('category_id').value,'All'); //brand has changed to autocomplete
	}});
	
	vendor_autocompleter = new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", {
	afterUpdateElement: function (obj,li)
	{
	    $('vendor_id').value = li.title;
	}});
	
	brand_autocompleter = new Ajax.Autocompleter("autocomplete_brand", "autocomplete_brand_choices", "ajax_autocomplete.php?a=ajax_search_brand", {
	afterUpdateElement: function (obj,li)
	{
	    $('brand_id').value = li.title;
	}});
}

function list_sku()
{
	var action = document.f_a.a.value;
	document.f_a.a.value = 'ajax_get_sku';
	
	if ($('autocomplete_category').value == '') {
		$('category_id').value = '';
		$('str_cat_tree').innerHTML = '';
	}
	if ($('autocomplete_brand').value == '') $('brand_id').value = '';
	if ($('autocomplete_vendor').value == '') $('vendor_id').value = '';
	
	var param = Form.serialize(document.f_a);
	document.f_a.a.value = action;
	$('sku_listing').style.display = '';
	$('sku_listing').innerHTML = '<img src=/ui/clock.gif align=absmiddle>';
	
	new Ajax.Updater('sku_listing',
		'promotion.php',
		{
			method:'post',
			evalScripts:true,
			parameters: param+'&show_all=1',
			onComplete: function ()
			{
				$('sku_listing').scrollTop = 0;
			}
		});
}

function sel_category(obj,have_child)
{
	var str = new String(obj.value);
	str.replace('<span class=sh>', '');
	str.replace('</span>', '');
	$('category_tree').value = str;
	$('str_cat_tree').innerHTML = str;
	obj.value = str.substr(str.lastIndexOf(">")+2, str.length);
}

function add_category(){
	var parms;

    parms = Form.serialize(document.f_a) + '&a=ajax_add_cat_promo_row';

 	// insert new row
	new Ajax.Request("promotion.php",{
		method:'post',
		parameters: parms,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
            var tb = $('cat_promotion_items');
            var lbody;
			var xml = m.responseXML;
			if (!xml) { alert(m.responseText); return; }

			var records = xml.getElementsByTagName("record");
			var tmp_id;
			$A(records).each(
			    function(r,idx){
					var rowitem = tb.insertRow(-1);
					tmp_id=xml_getData(r, "id");
					rowitem.id = "titem"+xml_getData(r, "id").strip();
				    rowitem.innerHTML = xml_getData(r,'rowdata');
				}
			);
		},
		onComplete: function(){
		}
	});
}

function active_btn(){
	$('srefresh').style.display='';
	$('submitbtn').style.display='none';
}

function refresh_tables(){
	//check consignment table form
	if (document.f_a.s_consignment_bearing.checked){
		var missing=[];
		var r_type=$('id_r_type').value;
		var send_filter='';


		if (!$('id_dept_id').value)   missing.push("department");
		if (!r_type)    missing.push("Consignment Type");
		else{
			if (r_type == 'vendor'){
				if (!$('id_vid').value)	missing.push("vendor");
				else    send_filter='&vendor_id='+$('id_vid').value;
			}
			else if (r_type == 'brand'){
				if (!$('id_bid').value)	missing.push("brand");
				else    send_filter='&brand_id='+$('id_bid').value;
	  		}
		}
		
		if (missing.length>0){
			alert("Invalid consignment bearing table. Missing "+missing.join(', '));
			return;
		}
		else{
			new Ajax.Request("promotion.php",{
				method:'post',
				parameters: 'a=ajax_check_consignment&ajax=1&dept_id='+$('id_dept_id').value+'&r_type='+r_type+send_filter,
			    evalScripts: true,
				onFailure: function(m) {
					alert(m.responseText);
					return;
				},
				onComplete: function (m) {
					if (m.responseText == 'No'){
						alert("Data not existed for current department and type");
						return;
					}
				    else{
						document.f_a.a.value = "refresh";
						document.f_a.submit();
					}
		    	}
			});

		}
	}
	else{
		document.f_a.a.value = "refresh";
		document.f_a.submit();
	}
}

function init_calendar(sstr){
	Calendar.setup({
	    inputField     :    "dt1"+sstr,     // id of the input field
	    ifFormat       :    "%e/%m/%Y",      // format of the input field
	    button         :    "t_dt1"+sstr,  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});

	Calendar.setup({
	    inputField     :    "dt2"+sstr,     // id of the input field
	    ifFormat       :    "%e/%m/%Y",      // format of the input field
	    button         :    "t_dt2"+sstr,  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
});
}

function do_save(){
	if (check_promo())
	{
		
		center_div('wait_popup');
		curtain(true,'curtain2');
		Element.show('wait_popup');
		
		new Ajax.Request('promotion.php',{
			method: 'post',
			parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
			onComplete: function(e){
				if (e.responseText.trim() == 'OK') {
					document.f_a.a.value='save';
					document.f_a.target = "";
					document.f_a.submit();
					return;
				}
				else {
					Element.hide('wait_popup');
					curtain(false,'curtain2');
					alert(e.responseText.trim());
					return;
				}
			}
		});
	}
}

function do_copy(){
	if (confirm('Copy promotion?'))
	{
	document.f_a.a.value='copy';
	document.f_a.target = "";
	Form.enable(document.f_a);
	document.f_a.submit();
	}
}

function cancel_selected_item(){
	if (confirm('Are you sure?'))
	{
		document.f_a.a.value='cancel_selected_item';
		document.f_a.target = "";
		document.f_a.submit();
	}
}

function disable_input()
{
	var input = $$('input');
	for(i=0;i<input.length;i++)
	{
		input[i].readOnly = true;
	}
}

function do_confirm(){
	if (check_promo())
	{
		if (confirm('Finalise promotion and submit for approval?'))
		{
			
			center_div('wait_popup');
			curtain(true,'curtain2');
			Element.show('wait_popup');
			
			new Ajax.Request('promotion.php',{
				method: 'post',
				parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
				onComplete: function(e){
					if (e.responseText.trim() == 'OK') {
						document.f_a.a.value = "confirm";
						document.f_a.target = "";
						document.f_a.submit();
						return;
					}
					else {
						Element.hide('wait_popup');
						curtain(false,'curtain2');
						alert(e.responseText.trim());
						return;
					}
				}
			});
			
		}
	}
}

function do_delete(){
	if (confirm('Delete this Promotion?')){
		document.f_b.a.value='delete';
		document.f_b.target = "";
		document.f_b.submit();
	}
}

function do_cancel(){
	if (confirm('Cancel this Promotion?')){
		document.f_b.a.value='cancel';
		document.f_b.target = "";
		document.f_b.submit();
	}
}

function do_revoke(){
	if (confirm('Copy details from this Promotion to a new Promotion?'))
	{
{/literal}
		document.location='{$smarty.server.PHP_SELF}?a=revoke&id={$form.id}&branch_id={$form.branch_id}';
{literal}
	}
}

function delete_item(id){

 	if (!confirm('Remove this SKU from Promotion?')) return;
 	bid = document.f_a.branch_id.value;
	new Ajax.Request("promotion.php",{
		method:'post',
		parameters: 'a=ajax_delete_promo_row&branch_id='+bid+'&promo_items_id='+id,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
            Element.remove('promo_item_'+id);
			recolour_group();
            reset_item_row_no();
    	}
	});
}

function check_promo()
{
	var branch_checked = false;
	var branch_checkboxes = ($$('.branch_cb'));
	for(i=0;i<branch_checkboxes.length;i++) {
		if (branch_checkboxes[i].checked) {
			branch_checked = true;
			break;
		}
	}
	
	if (!branch_checked) {
		alert('Please select at least one branch');
		return false;
	}
	
	var tb = $$('tbody.tbinput');
	for(i=0;i<tb.length;i++)
	{
		var id = tb[i].id.replace('promo_item_','');
		
/*		//check consignment bearing
		if ($('member_consignment_'+id)){
			var member_cons = $('member_consignment_'+id).value.split(",");
			var non_cons = $('non_consignment_'+id).value.split(",");
			
			if ((!member_cons[1] && !$('member_disc_p_'+id).value) || (!non_cons[1] && !$('non_member_disc_p_'+id).value)){
				alert("Missing amount discount for promotion items.");
				return false;
			}
		}
*/
		if ($('member_disc_p_'+id)){
			if ($('member_disc_p_'+id).value != ''  )
			{
				if (/%/.test($('member_disc_p_'+id).value))
				{
					if ($('member_disc_p_'+id).value.replace('%','') > 100)
					{
						alert('Discount cannot more than 100%');
						$('member_disc_p_'+id).focus();
						$('member_disc_p_'+id).select();
						return false;
					}
				}
				else
				{
					if (parseFloat($('member_disc_p_'+id).value) > parseFloat($('selling_price_'+id).value))
					{
						alert('Discount price cannot more that selling price.');
						$('member_disc_p_'+id).focus();
						$('member_disc_p_'+id).select();
						return false;
					}
				}
			}
		}
		if ($('non_member_disc_p_'+id)){
			if ($('non_member_disc_p_'+id).value != '' )
			{
				if (/%/.test($('non_member_disc_p_'+id).value))
				{
					if ($('non_member_disc_p_'+id).value.replace('%','') > 100)
					{
						alert('Discount cannot more than 100%');
						$('non_member_disc_p_'+id).focus();
						$('non_member_disc_p_'+id).select();
						return false;
					}
				}
				else
				{
					if (parseFloat($('non_member_disc_p_'+id).value) > parseFloat($('selling_price_'+id).value))
					{
						alert('Discount price cannot more that selling price.');
						$('non_member_disc_p_'+id).focus();
						$('non_member_disc_p_'+id).select();
						return false;
					}
				}
			}
		}
	}
	return true;
}

function control_type_changed(ele){
	var disabled = false;
	if(ele.value>0) disabled = true;
	var parent_tr = ele.parentNode.parentNode;
	$(parent_tr).getElementsBySelector("input.with_no_control").each(function(curr){
		curr.readOnly = disabled;
		if(disabled) curr.value = '';
	});
	$(parent_tr).getElementsBySelector("input.with_control").each(function(curr){
	    if(curr.type=='checkbox'){
			curr.disabled = !disabled;
			if(curr.disabled)   curr.checked = false;
		}else{
            curr.readOnly = !disabled;
			if(!disabled) curr.value = '';
		}
	});

	if(promotion_change_settings_by_parent_child && skip_dialog_function == false){
		var field_type = ele.readAttribute('field_type');
		var parent_tr = ele.parentNode.parentNode.parentNode;
		var curr_grp_id = parent_tr.readAttribute('group_id');
		var curr_item_id = parent_tr.readAttribute('item_id');

		if(curr_grp_id != prv_grp_id) show_confirm_msg_dialog = true;
	
		if(show_confirm_msg_dialog == true) show_change_value_by_parent_dialog(ele, 1);
		else{
			var field_type = ele.readAttribute('field_type');
			var parent_tr = ele.parentNode.parentNode.parentNode;
			var curr_grp_id = parent_tr.readAttribute('group_id');
			var curr_item_id = parent_tr.readAttribute('item_id');
			
			curr_obj = ele;
			call_own_function = true;
			change_value_by_parent();
		}
	}
    
    var item_id = parent_tr.readAttribute('item_id');
    document.f_a['member_block_normal['+item_id+']'].checked = false;
    document.f_a['member_block_normal['+item_id+']'].disabled = true;
    document.f_a['block_normal['+item_id+']'].checked = false;
    document.f_a['block_normal['+item_id+']'].disabled = true;
}

function check_print_title(){
  if(chk.checked == 1)
    alert("Thank You");
  else
    alert("You didn't check it! Let me check it for you.")
    chk.checked = 1;
}

function add_autocomplete_extra(){
	recolour_group();
	reset_item_row_no();
}

function reset_item_row_no(){
	$$('#tbl_items span.item_no').each(function(ele, index){
		$(ele).update((index+1)+'.');
	});
}

//----------------------------Alex: Add Consignment Bearing-------------------->
consignment_toggle = function (obj){
 	if (obj) {
		$('consignment_table_id').show();
		$('id_consignment').value='yes';
		return true;
	}
	else {
		$('consignment_table_id').hide();
	    $('id_consignment').value='';
		return false;
	}
}

load_data = function (v){

	if (v=='vendor'){
		$('vendor').show();
		$('brand').hide();
	}
	else if (v=='brand'){
		$('vendor').hide();
		$('brand').show();
	}
	else if (v=='none'){
		$('vendor').hide();
		$('brand').hide();
	}

	$('id_r_type').value=v;

}

change_brand_vendor = function (obj){

	$('id_dept_id').value=obj.value;
    new Ajax.Request("promotion.php",{
		method:'post',
		parameters: 'a=ajax_change_brand_vendor&ajax=1&branch_id='+document.f_a['branch_id'].value+'&promo_id='+document.f_a['id'].value+'&dept_id='+obj.value+"&vendor_id="+$('id_vid').value+"&brand_id="+$('id_bid').value,
		onComplete:function(e){
		    if (e.responseText!='NO'){
				eval("var json ="+e.responseText);
				$('sel_vid').innerHTML = json['vendor'];
				$('sel_bid').innerHTML = json['brand'];
			}
		}
	});
}

function save_id(type,val){

	if (type == 'vendor')
        $('id_vid').value=val;
	else
        $('id_bid').value=val;

}

toggle_overlap_promo = function (show){
    var show_method = show ? 'show' : 'hide';

	// show/hide all overlap div
	$$('#tbl_items div.div_overlap_pi_content').invoke(show_method);
	
	// toggle image src
	$$('#tbl_items img.img_toggle_overlap_pi').each(function(ele){
		if(show_method=='show') ele.src = '/ui/collapse.gif';
		else    ele.src = '/ui/expand.gif';
	});
}

function check_consignment_bearing(obj){
  	if ((/0%,no,0,/.test(obj.value)) || (/0%,yes,0,/.test(obj.value))){
  		obj.value="0,0,no,0,";
		alert("Current setting is not allowed.");
  	}
}

function change_wholeday(obj){
	if (obj.checked){
		$('time_from_id').value="00:00";
		$('time_from_id').readOnly=true;
		$('time_to_id').value="23:59";
		$('time_to_id').readOnly=true;
	}else{
		$('time_from_id').readOnly=false;
		$('time_to_id').readOnly=false;
	}
}

function category_point_inherit_changed(){
	if(!document.f_a['category_point_inherit'])	return;
	
	if(document.f_a['category_point_inherit'].value == 'set'){
		$('div_cat_point').show();
	}else{
		$('div_cat_point').hide();
	}
}

function category_point_value_changed(inp){
	var v = inp.value.trim();
	
	if(v=='')	inp.value='';
	else{
		mf(inp,2);
		v = float(inp.value);
		if(v<=0){
			inp.value = 0;
		}
	}
}

function show_choose_member_type_popup(item_id){
	// assign item id
	document.f_member_type['item_id'].value = item_id;
	
	var selected_member_type = [];
	var is_all = false;
	// check current selected member type
	$$('#div_allowed_member_type-'+item_id+' input.allowed_member_type-'+item_id).each(function(inp){
		selected_member_type.push(inp.value);
	});
	
	// nothing is selected = allow all
	if(selected_member_type.length == 0)	is_all = true;
	
	// mark all checkbox
	document.f_member_type['all'].checked = is_all;
	
	// get all member type input
	var member_type_list = document.f_member_type['member_type[]'];
	
	// loop it and check the checkbox
	for(var i=0; i<member_type_list.length; i++){
		member_type_list[i].checked = (is_all || in_array(member_type_list[i].value, selected_member_type));
	}
	
	// show popup
	curtain(true);
	center_div($('div_choose_member_type_popup').show());
}

function check_member_type_selection(ele){
	var member_type_list = document.f_member_type['member_type[]'];
	
	if(ele.name=='all'){	// is all checkbox
		$A(member_type_list).each(function(inp){
			inp.checked = ele.checked;
		});
	}else{	// other checkbox
		var is_all = true;
		for(var i=0; i<member_type_list.length; i++){
			if(!member_type_list[i].checked){
				is_all = false;
				break;
			}
		}
		document.f_member_type['all'].checked = is_all;
	}
}

function confirm_select_member_type(){
	var is_all = document.f_member_type['all'].checked;
	var member_type_list = document.f_member_type['member_type[]'];
	var item_id = document.f_member_type['item_id'].value;
	var selected_member_type = [];
	var div_allowed_member_type = $('div_allowed_member_type-'+item_id);
	copy_member_across_parent = false;
	
	if(is_all){	// is for all member type
		$(div_allowed_member_type).update('All');
	}else{
		// check at least need 1 member type
		for(var i=0; i<member_type_list.length; i++){
			if(member_type_list[i].checked){
				selected_member_type.push(member_type_list[i].value);
			}
		}
		
		// no member type is selected
		if(selected_member_type.length<=0){
			alert('Please select at least 1 member type');
			return;
		}
		
		var input_html = $('tmp_member_type_input_div').innerHTML;
		var div_html = '';
		
		for(var i=0; i<selected_member_type.length; i++){
			if(i>0)	div_html+= ', ';
			
			var member_type = selected_member_type[i];
			var new_html = input_html;
			new_html = new_html.replace(/__item_id__/g,item_id);
			new_html = new_html.replace(/__member_type__/g,member_type);
			div_html += new_html+' '+member_type;
		}
		$(div_allowed_member_type).update(div_html);
	}
	
	if(promotion_change_settings_by_parent_child){
		var parent_tr = $('promo_item_'+item_id);
		var curr_grp_id = parent_tr.readAttribute('group_id');
		
		if(prv_grp_id != curr_grp_id) show_confirm_msg_dialog = true;

		$$('#tbl_items .group_colour').each(function(i){
			var tmp_group_id = i.readAttribute('group_id');
			var tmp_item_id = i.readAttribute('item_id');
			var other_div_allowed_member_type = $('div_allowed_member_type-'+tmp_item_id);
			//set different colour if current row is in the same group
			if (tmp_group_id == curr_grp_id && item_id != tmp_item_id) {
				if(show_confirm_msg_dialog == true){
					if(!confirm("Found having same parent and child in the list, update all?")){
						copy_member_across_parent = false;
						throw $break;
					}
				}
				
				copy_member_across_parent = true;
				throw $break;
			}
		});
		
		if(copy_member_across_parent == true) copy_member_type_across_parent(item_id, is_all);
		//show_confirm_msg_dialog = true;
	}
	
	default_curtain_clicked();
}

function copy_member_type_across_parent(item_id, is_all){
	var parent_tr = $('promo_item_'+item_id);
	var curr_grp_id = parent_tr.readAttribute('group_id');
	var member_type_list = document.f_member_type['member_type[]'];
	var selected_member_type = [];

	if(!is_all){
		// check at least need 1 member type
		for(var i=0; i<member_type_list.length; i++){
			if(member_type_list[i].checked){
				selected_member_type.push(member_type_list[i].value);
			}
		}
	}
	
	$$('#tbl_items .group_colour').each(function(i){
		var tmp_group_id = i.readAttribute('group_id');
		var tmp_item_id = i.readAttribute('item_id');
		var other_div_allowed_member_type = $('div_allowed_member_type-'+tmp_item_id);
		//set different colour if current row is in the same group
		if (tmp_group_id == curr_grp_id && item_id != tmp_item_id) {
			if(is_all){
				$(other_div_allowed_member_type).update('All');
			}else{
				var other_div_html = '';
				for(var i=0; i<selected_member_type.length; i++){
					if(i>0)	other_div_html+= ', ';

					var other_new_html = $('tmp_member_type_input_div').innerHTML;
					var member_type = selected_member_type[i];
					other_new_html = other_new_html.replace(/__item_id__/g,tmp_item_id);
					other_new_html = other_new_html.replace(/__member_type__/g,member_type);
					other_div_html += other_new_html+' '+member_type;
				}
				$(other_div_allowed_member_type).update(other_div_html);
			}
		}
	});
}

function autocomplete_multiadd_validation(opt_list){
	// loop for each <option>
	var sid_list = [];
	inp_list = document.f_a['sku_id[]'];
	if(inp_list){
		if(inp_list.length){
			for(var i=0; i<inp_list.length; i++){
				sid_list.push(inp_list[i].value);
			}
		}else{
			sid_list.push(inp_list.value);
		}
		
		for(var i=0;i<opt_list.length;i++){
			var c = opt_list[i].value.split(",");
			
			if (opt_list[i].checked){
				var sid = c[0];
				var code = opt_list[i].title;
				
				// check whether got this sku in list
				if(in_array(sid, sid_list)){
					if(!confirm(('Duplicate item found: '+code+' already in the list, continue to add?')))	return false;
				}		
			}
		}
	}
	
	return true;
}

recolour_group = function() {
	var current_group_id = '0';
	var group_id;
	var current_row;
	$$('#tbl_items .group_colour').each(function(i) {
		group_id = i.readAttribute('group_id');
		current_row = i.down('tr');
		//set different colour if current row is in the same group
		if (group_id == current_group_id) {
			current_row.setStyle({backgroundColor:'#FFFFCC'});
			current_row.observe('mouseenter',function(){this.setStyle({backgroundColor:'#FFDFCC'});}).observe('mouseleave',function(){this.setStyle({backgroundColor:'#FFFFCC'});});
		}
		else {
			current_row.setStyle({backgroundColor:''});
			current_row.observe('mouseenter',function(){this.setStyle({backgroundColor:'#FFDFCC'});}).observe('mouseleave',function(){this.setStyle({backgroundColor:''});});
		}
		current_group_id = group_id;
	});
}

function show_change_value_by_parent_dialog(obj, need_recall_function){
	if(obj == undefined) return;

	if(need_recall_function) call_own_function = 1;
	else call_own_function = 0;
	var field_type = obj.readAttribute('field_type');
	var parent_tr = obj.parentNode.parentNode.parentNode;
	var curr_grp_id = parent_tr.readAttribute('group_id');
	var curr_item_id = parent_tr.readAttribute('item_id');

    // change block normal input
    change_block_normal_appearance(curr_item_id);
    
	if(prv_grp_id != curr_grp_id){
		show_confirm_msg_dialog = false;
		var have_parent = false;
		$$('#tbl_items .group_colour').each(function(i){
			var tmp_group_id = i.readAttribute('group_id');
			var tmp_item_id = i.readAttribute('item_id');
			//set different colour if current row is in the same group
			if (tmp_group_id == curr_grp_id && curr_item_id != tmp_item_id) {
				have_parent = true;
				throw $break;
			}
		});
		
		if(have_parent == true){
			show_confirm_msg_dialog = true;
			$('ignore_change_setting_msg').checked = false;
		}else show_confirm_msg_dialog = false;
		prv_grp_id = curr_grp_id;
	}
	
	curr_obj = obj;

	if(show_confirm_msg_dialog == true){
		$('change_settings_by_sku_dialog').show();
		center_div('change_settings_by_sku_dialog');
		curtain(true);
		ask_confirm_msg_clicked();
	}else{
		change_value_by_parent();
	}
}

function change_block_normal_appearance(item_id) {
    //member normal block cheking
    var mem_qty = (document.f_a['member_qty_to['+item_id+']'].value == '') ? 0 : document.f_a['member_qty_to['+item_id+']'].value;
    var mem_limit = (document.f_a['member_limit['+item_id+']'].value == '') ? 0 : document.f_a['member_limit['+item_id+']'].value;
    
    if (mem_qty == 0 && mem_limit == 0) {
        document.f_a['member_block_normal['+item_id+']'].checked = false;
        document.f_a['member_block_normal['+item_id+']'].disabled = true;
    }else{
        document.f_a['member_block_normal['+item_id+']'].disabled = false;
    }
    
    //non_member normal block cheking
    var non_qty = (document.f_a['non_member_qty_to['+item_id+']'].value == '') ? 0 : document.f_a['non_member_qty_to['+item_id+']'].value;
    if (non_qty == 0) {
        document.f_a['block_normal['+item_id+']'].checked = false;
        document.f_a['block_normal['+item_id+']'].disabled = true;
    }else{
        document.f_a['block_normal['+item_id+']'].disabled = false;
    }
}

function ask_confirm_msg_clicked(){
	if($('ignore_change_setting_msg').checked == true){
		show_confirm_msg_dialog = false;
	}else{
		show_confirm_msg_dialog = true;
	}
}

function change_value_by_parent(){
	//if(obj == undefined) return;
	if(curr_obj == undefined) return;
	//mi(curr_obj);
	
	var field_type = curr_obj.readAttribute('field_type');
	var parent_tr = curr_obj.parentNode.parentNode.parentNode;
	var curr_grp_id = parent_tr.readAttribute('group_id');
	var curr_item_id = parent_tr.readAttribute('item_id');
	
	$$('#tbl_items .group_colour').each(function(i){
		var tmp_group_id = i.readAttribute('group_id');
		var tmp_item_id = i.readAttribute('item_id');
		var curr_field = document.f_a[field_type+"["+tmp_item_id+"]"];
		
		//set different colour if current row is in the same group
		if (tmp_group_id == curr_grp_id && curr_item_id != tmp_item_id && (curr_field.type == "select-one" || curr_field.readOnly == false) && curr_field.disabled == false) {
			if(document.f_a[field_type+"["+tmp_item_id+"]"].type == "checkbox"){
				if(document.f_a[field_type+"["+tmp_item_id+"]"].checked == false)
					document.f_a[field_type+"["+tmp_item_id+"]"].checked = true;
				else
					document.f_a[field_type+"["+tmp_item_id+"]"].checked = false;
			}else{
				document.f_a[field_type+"["+tmp_item_id+"]"].value = curr_obj.value;
			}
			var tmp = document.f_a[field_type+"["+tmp_item_id+"]"];
			if(call_own_function == 1){
				skip_dialog_function = true;
				tmp.onchange();
			}
		}
	});

	//show_confirm_msg_dialog = true;
	skip_dialog_function = false;
	default_curtain_clicked();
}

function toggle_all_branches(obj){
	var all_chx = $$('input.branch_cb');
	for(var i=0; i<all_chx.length; i++){
		if(obj.checked == true){
			all_chx[i].checked = true;
		}else{
			all_chx[i].checked = false;
		}
	}
}
</script>
{/literal}

{if $readonly}
	{assign var=allow_edit value=0}
{else}
	{assign var=allow_edit value=1}
{/if}
{assign var=time_value value=1000000000}
<div id=item_context_menu style="display:none;position:absolute;">
<ul id=ul_menu class=contextmenu>
{if $allow_edit}
<li><a href="javascript:void(0)" onclick="hide_context_menu();delete_item(context_info.id)"><img src=/ui/icons/delete.png class=clickable align=absmiddle> Delete Item form Promotion</a>
{*<li><a href="javascript:void(0)" onclick="hide_context_menu();showdiv('note'+context_info.id);$('rem'+context_info.id).focus();"><img src=/ui/note16.png align=absmiddle> Add Remark</a>
<li><a href="javascript:void(0)" onclick="hide_context_menu();showdiv('note2'+context_info.id);$('rem2'+context_info.id).focus();"><img src=/ui/inote16.png align=absmiddle> Add Internal Remark</a>
<li id=item_context_menu_foc><a href="javascript:void(0)" onclick="hide_context_menu();edit_foc(context_info.id,context_info.sku_item_id)"><img src=/ui/icons/book_edit.png align=absmiddle>  Edit FOC costing</a>
*}{/if}
{*<li><a href="javascript:void(0)" onclick="hide_context_menu();get_item_po_history(context_info.sku_item_id)"><img src=/ui/icons/clock.png align=absmiddle> PO Cost History</a>
<li><a href="javascript:void(0)" onclick="hide_context_menu();get_item_sales_trend(context_info.sku_item_id)"><img src=/ui/icons/chart_bar.png align=absmiddle> Item Sales Trend</a>
*}</ul>
</div>

<div id=wait_popup style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
	<p align=center>
		Please wait..<br /><br /><img src="ui/clock.gif" border="0" />
	</p>
</div>

<!-- print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:250px;position:absolute; padding:10px; display:none;">
<form name=fprn>
<img src=ui/print64.png hspace=10 align=left> <h3>Print Options</h3>
<input type=hidden name=load value=1>
<input type=hidden name=id value="{$form.id}">
<input type=hidden name=branch_id value="{$form.branch_id}">
<p align=center>
<font color=red>* Unsaved changes in Promotion will not be printed *</font><br>
<input type=button value="Print" onclick="print_ok()">
<input type=button value="Cancel" onclick="print_cancel()">
</p>
</form>
</div>

<!-- member type popup -->
<div id="div_choose_member_type_popup" class="curtain_popup" style="position:absolute;z-index:10000;width:200px;height:250px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_choose_member_type_popup_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Please Select Member Type</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_choose_member_type_popup_content" style="padding:2px;">
		<div style="height:190px;overflow:auto;">
			<form name="f_member_type" onSubmit="return false;">
				<input type="hidden" name="item_id" />
				<div id="tmp_member_type_input_div">
					<input type="hidden" name="allowed_member_type[__item_id__][member_type][__member_type__]" value="__member_type__" class="allowed_member_type-__item_id__" />
				</div>
				
				<input type="checkbox" name="all" value="1" onChange="check_member_type_selection(this);" /> All
				<ul style="list-style:none;">
					{foreach from=$config.membership_type key=member_type item=mtype_desc}
						{if is_numeric($member_type)}
							{assign var=mt value=$mtype_desc}
						{else}
							{assign var=mt value=$member_type}
						{/if}
						<li>
							<input type="checkbox" name="member_type[]" value="{$mt}" onChange="check_member_type_selection(this);" />
							{$mtype_desc}
						</li>
					{/foreach}
				</ul>
			</form>
		</div>
		<div align="center">
			<input type="button" value="OK" onClick="confirm_select_member_type();" />
		</div>
	</div>
</div>

<!-- change settings by parent and child dialog -->
<div id="change_settings_by_sku_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:200px;height:150px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_change_settings_by_sku_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Message</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_change_settings_by_sku_content" style="padding:2px;">
		<div style="height:90px;overflow:auto;">
			<h5>Found having same parent and child in the list, update all?</h5>
			<input type="checkbox" id="ignore_change_setting_msg" value="1" onclick="ask_confirm_msg_clicked();" /> Do not ask again
		</div>
		<div align="center">
			<input type="button" value="Yes" id="cs_by_sku_btn" onClick="change_value_by_parent();" />
			<input type="button" value="No" onClick="default_curtain_clicked();" />
		</div>
	</div>
</div>

<iframe style="visibility:hidden" width=1 height=1 name=ifprint id=ifprint></iframe>
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				Promotion{if $form.id<$time_value}(ID#{$form.id}){else}(New){/if}
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if $form.id<$time_value}
<h3 class="mx-3 text-primary mt-2">Status:
{if $form.status == 1}
	{if $form.approved}
		Fully Approved
	{else}
	In Approval Cycle
	{/if}
{elseif $form.status == 5}
	Cancelled
{elseif $form.status == 4}
	Terminated
{elseif $form.status == 3}
	In Approval Cycle (KIV)
{elseif $form.status == 2}
	Rejected
{elseif $form.status == 0}
	Draft Promotion
{/if}
{/if}
{if $form.revoke_id}(This Promotion has been revoked to Promotion ID#{$form.revoke_id} <a href="?a=open&id={$form.revoke_id}&branch_id={$form.branch_id}"><img src=ui/view.png border=0 title="Click here to open the new Promotion" align=absmiddle></a>){/if}
</h3>

{if $approval_history}
<br>
<div class="card mx-3">
	<div class="card-body">
		<div class="stdframe" >
			<h4 class="text-primary">Approval History</h4>
			{section name=i loop=$approval_history}
			<p>
			{if $approval_history[i].status==1}
				<img src=ui/approved.png width=16 height=16>
			{elseif $approval_history[i].status==2}
				<img src=ui/rejected.png width=16 height=16>
			{else}
				<img src=ui/terminated.png width=16 height=16>
			{/if}
			{$approval_history[i].timestamp} by {$approval_history[i].u}<br>
			{$approval_history[i].log}
			</p>
			{/section}
			</div>
	</div>
</div>
{/if}
<br>

{if $errm.top}
<div class="alert alert-danger mx-3 rounded">
	<div id=err><div class=errmsg><ul>
		{foreach from=$errm.top item=e}
		<li> {$e}
		{/foreach}
		</ul></div></div>
</div>
{/if}

{if $smarty.request.msg}<div style="color:blue;">{$smarty.request.msg}</div>{/if}

<div id="consignment_bearing_id" style="z-index:10000;position:absolute;left:0;top:0;display:none;width:300px;height:300px;padding:10px;border:1px solid #000; background:#fff;overflow:auto;">
</div>


<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method=post ENCTYPE="multipart/form-data">
			<input type=hidden name=id value="{$form.id}">
			<input type=hidden name=user_id value="{$form.user_id|default:$sessioninfo.id}">
			<input type=hidden name=branch_id value="{$form.branch_id|default:$sessioninfo.branch_id}">
			<input type=hidden name=a value="save">
			<input type=hidden name=approval_history_id value="{$form.approval_history_id}">
			<input type=hidden name=readonly value="{$readonly}">
			<input type=hidden name=active value="{$form.active}">
			<input type=hidden name=active value="{$form.active}">
			
			<div class="stdframe" style="background:#fff">
			<table border=0 cellspacing=0 cellpadding=4>
			<tr>
			<td><b class="form-label">Title</b></td>
			<td colspan=3><input class="form-control" name="title" value="{$form.title|escape}" size=80></td>
			</tr>
			<tr>
			<td><b class="form-label">Date<span class="text-danger" title="Required Field"> *</span></b></td>
			<td colspan=3>
				<div class="form-inline">
					<input class="form-control" name="date_from" value="{if $form.date_from>0}{$form.date_from|date_format:"%Y-%m-%d"}{else}{$smarty.now|date_format:"%Y-%m-%d"}{/if}" onclick="if(this.value)this.select();" size="10" id="inp_date_from" />
				{if $allow_edit}
					&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date" /> 
					
				{/if}
				<b class="form-label">To<span class="text-danger" title="Required Field">*&nbsp;</span></b>
			
				<input class="form-control" name="date_to" value="{if $form.date_to>0}{$form.date_to|date_format:"%Y-%m-%d"}{else}{$smarty.now|date_format:"%Y-%m-%d"}{/if}" onclick="if(this.value)this.select();" size="10" id="inp_date_to" />
				{if $allow_edit}
					&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date" /> 
				{/if}
				<b class="form-label">&nbsp;(yyyy-mm-dd)</b>
				</div>
			</td>
			</tr>
			<tr><td><b class="form-label">Time</b></td>
			<td colspan=3>
			<div class="form-inline">
				<input class="form-control" id="time_from_id" name="time_from" value="{if $form.time_from>0}{$form.time_from|date_format:"%H:%M"}{else}00:00{/if}" onclick="if(this.value)this.select();" size=10>
			<b class="form-label">&nbsp;To&nbsp;</b> 
			<input class="form-control" id="time_to_id" name="time_to" value="{if $form.time_to>0}{$form.time_to|date_format:"%H:%M"}{else}23:59{/if}" onclick="if(this.value)this.select();" size=10> <b class="form-label">&nbsp;(hh:mm)</b>
		
			<div class="form-label form-inline">
				&nbsp;&nbsp;<input type="checkbox" id="all_day_id" name="all_day" value="all_day" onclick="change_wholeday(this)"> <label for="all_day_id"><b>&nbsp;All Day</b></label>
			</div>
			</div>
			
			</td>
			</tr>
			<tr>
			<td>
				<div class="form-inline form-label">
					<b>Print title in receipt</b></td>
			<td colspan=3><input type="checkbox" name="print_title_in_receipt" value="1" {if $form.print_title_in_receipt}checked{/if}>
				</div>
			</td>
			</tr>
			<tr {if !$config.use_consignment_bearing or (($smarty.request.a eq 'refresh' || $smarty.request.id) and $form.consignment_bearing ne 'yes')} style="display:none;" {/if} >
			<td valign="top"><b class="text-primary">Use Consignment Bearing Table</b></td>
			<td>
				<input type="checkbox" name="s_consignment_bearing" onchange="consignment_toggle(this.checked);" value="yes" {if $form.consignment_bearing eq 'yes'}checked {/if} {if $smarty.request.a eq 'refresh' || $smarty.request.id}disabled {/if} >
				<b >Note:</b> Only consignment items that had selected brand / vendor trade discount table which match the consignment bearing table.
				<input id='id_consignment' type=hidden name="consignment_bearing" value="{$form.consignment_bearing}" > &nbsp;&nbsp;&nbsp;&nbsp;
				<table border=0 id="consignment_table_id" style="display:none;border:1px solid #000" >
					<tr>
						<th>Department</th>
						<td>
							<select name="s_dept_id" onchange="change_brand_vendor(this);" id="sel_did" {if $smarty.request.a eq 'refresh' || $smarty.request.id}disabled {/if}>
								<option value="">-- Please Select --</option>
								{foreach from=$departments item=dept}
									<option value={$dept.id} {if $form.dept_id eq $dept.id}selected {/if}>{$dept.description}</option>
								{/foreach}
							</select>
							<input id='id_dept_id' type=hidden name="dept_id" value="{$form.dept_id}" >
						</td>
					</tr>
					<tr>
						<th align="left">Type</th>
						<td>
							<input type=radio name="s_r_type" value="vendor" onchange="load_data(this.value)"
							{if $form.r_type eq 'vendor'}
								{assign var=selected_vb_id value=$form.vendor_id}
								checked {/if} {if $smarty.request.a eq 'refresh' || $smarty.request.id}disabled {/if}>Vendor &nbsp;&nbsp;&nbsp;&nbsp;
			
							<input type=radio name="s_r_type" value="brand" onchange="load_data(this.value)"
							{if $form.r_type eq 'brand'}
								{assign var=selected_vb_id value=$form.brand_id}
								checked {/if} {if $smarty.request.a eq 'refresh' || $smarty.request.id}disabled {/if}>Brand &nbsp;&nbsp;&nbsp;&nbsp;
			
							<input type=radio name="s_r_type" value="none" onchange="load_data(this.value)" {if $form.r_type eq 'none'}checked {/if} {if $smarty.request.a eq 'refresh' || $smarty.request.id}disabled {/if}>None &nbsp;&nbsp;&nbsp;&nbsp;
							<input id='id_r_type' type=hidden name="r_type" value='{$form.r_type}' >
						</td>
					</tr>
					<tr id=vendor style="display:none;">
						<th align="left">Vendor</th>
						<td>
							<select name="vendor_id" onchange="save_id('vendor',this.value)" id="sel_vid" {if $smarty.request.a eq 'refresh' || $smarty.request.id}disabled {/if}>
								<option value="">-- Please Select A Vendor--</option>
							</select>
							<input id='id_vid' type=hidden name="s_vendor_id" value="{if $form.s_vendor_id}{$form.s_vendor_id}{else}{$form.vendor_id}{/if}">
						</td>
					</tr>
					<tr id=brand style="display:none;" >
						<th align="left">Brand</th>
						<td>
							<select name="brd_id" onchange="save_id('brand',this.value)" id="sel_bid" {if $smarty.request.a eq 'refresh' || $smarty.request.id}disabled {/if}>
								<option value="">-- Please Select A Brand--</option>
							</select>
							<input id='id_bid' type=hidden name="s_brand_id" value='{if $form.s_brand_id}{$form.s_brand_id}{else}{$form.brand_id}{/if}'>
						</td>
					</tr>
				</table>
			</td>
			</tr>
			
			{if !$config.promotion_hide_member_options}
			<tr>
				<td valign="top">
					<div class="form-inline">
						<b class="form-label">Member Reward Point</b>
					<a href="javascript:void(alert('This feature only available at counter BETA v168.\n\nInherit: Member Type -> Member\n\nRequire privilege PROMOTION_MEMBER_POINT_REWARD_EDIT to use this.'));">
						<img src="/ui/icons/information.png" align="absmiddle" />
					</a>
					</div>
				</td>
				<td>
					{if $sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}
						<select class="form-control" name="category_point_inherit" onChange="category_point_inherit_changed();">
							{foreach from=$category_point_inherit_options key=k item=w}
								<option value="{$k}" {if $form.category_point_inherit eq $k}selected {/if}>{$w}</option>
							{/foreach}
						</select>
					{else}
						<b class="form-label">
							{foreach from=$category_point_inherit_options key=k item=w}
								{if $form.category_point_inherit eq $k}{$w}{/if}
							{/foreach}
						</b>
						<input type="hidden" name="category_point_inherit" value="{$form.category_point_inherit}">
					{/if}
					<div id="div_cat_point" style="border:0px solid black;padding:5px;{if $form.category_point_inherit ne 'set'}display:none;{/if}">
						{if $sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}
							<div class="alert alert-primary rounded">
								Please enter how many {$config.arms_currency.symbol} for each point.
							</div>
						{/if}
									
						<table class="report_table">
							<div class="mt-2">
								<tr class="header">
									<td>&nbsp;</td>
									<td>({$config.arms_currency.symbol} <b>X</b> for 1 Point)</td>
								</tr>
							</div>
							<tr>
								<td><b class="form-label">Member</b></td>
								<td>
									<input class="form-control" type="text" name="category_point_inherit_data[global]" value="{$form.category_point_inherit_data.global}" size="3" onChange="category_point_value_changed(this);" {if !$sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}readonly{/if} />
								</td>
							</tr>
							{foreach from=$config.membership_type key=member_type item=mtype_desc name=fmt}
								{if is_numeric($member_type)}
									{assign var=mt value=$mtype_desc}
								{else}
									{assign var=mt value=$member_type}
								{/if}
								{if $smarty.foreach.fmt.first}
									<tr class="header">
										<th class="form-label" colspan="2">
											Member Type (Leave Empty will follow member)
										</th>
									</tr>
								{/if}
								<tr>
									<td><b class="form-label">{$mtype_desc}</b></td>
									<td>
										<input class="form-control" type="text" name="category_point_inherit_data[{$mt}]" size="3" onChange="category_point_value_changed(this)" value="{$form.category_point_inherit_data.$mt}" {if !$sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}readonly{/if} />
									</td>
								</tr>
							{/foreach}
						</table>
					</div>
				</td>
			</tr>
			{/if}
			
			<tr>
				<td valign=top><b class="form-label">Branches Promotion</b> </td>
			{if ($form.branch_id==1 and $smarty.request.a ne 'refresh') and $form.id > 1000000000 and !$errm}
				<td>You may select multiple branches <br>
					<table class="small" border=0 id=tbl_branch>
					<div class="form-label">
						<tr>
							<td><input type="checkbox" name="all_branches" value="1" onclick="toggle_all_branches(this); active_btn();" /> All</td>
						</tr>
						{section name=i loop=$branch}
						{assign var=bid value=$branch[i].id}
						<tr>
							<td valign=top>
							<input onchange="active_btn();" type=checkbox id=dt_{$branch[i].id} name="promo_branch_id[]" value="{$branch[i].id}" class="branch branch_cb" {if is_array($form.promo_branch_id) and in_array($branch[i].id,$form.promo_branch_id)}checked{/if}>&nbsp;{$branch[i].code}
							</td>
						</tr>
					</div>
					{/section}
					</table>
			
					<div id=srefresh style="display:none; padding-top:10px">
					<input class="btn btn-primary" type=button onclick="void(refresh_tables())" value="click here to continue">
					</div>
			
				</td>
			{else}
				<td>
					<table class="small" border=0>
					{if $BRANCH_CODE eq 'HQ'}
						<tr>
							<td><input type="checkbox" name="all_branches" value="1" onclick="toggle_all_branches(this);" /> All</td>
						</tr>
					{/if}
					{section name=i loop=$branch}
						{assign var=bid value=$branch[i].id}
						{assign var=bcode value=$branch[i].code}
						{if $BRANCH_CODE eq 'HQ'}
							<tr>
								<td valign="top">
								<input type="checkbox" name="promo_branch_id[{$bid}]" type="hidden" value="{$bcode}" class="branch_cb" {if $form.promo_branch_id.$bid}checked {/if} /> {$bcode}
								</td>
							</tr>
						{else}
							{if $form.promo_branch_id.$bid}
								<tr>
									<td valign="top">
									<span style="display:none;"><input type="checkbox" name="promo_branch_id[{$bid}]" type="hidden" value="{$bcode}" class="branch_cb" checked /></span>
									{$bcode}
									</td>
								</tr>
							{/if}
						{/if}
					{/section}
					</table>
					<div id=srefresh style=" {if $smarty.request.a eq 'refresh' || $smarty.request.id} display:none; {/if} padding-top:10px">
					<input type=button onclick="void(refresh_tables())" class="btn btn-success" value="click here to continue">
					</div>
				</td>
			{/if}
			
			</tr>
			
			
			
			</table>
			
			</div><br>
			
			{if $data_collector_invalid_items}
				<div class="errmsg stdframe" style="background-color:yellow;">
					<h3 class="text-danger">The following item(s) code got error:</h3>
					<ul>
						{foreach from=$data_collector_invalid_items item=it}
							<div class="alert alert-danger mx-3">
								<li>
									Line {$it.line_no}: {$it.code} - {$it.msg}
								</li>
							</div>
						{/foreach}
					</ul>
				</div><br />
			{/if}
			
			{if ($smarty.request.a eq 'refresh' or $smarty.request.a eq 'save' or $smarty.request.a eq 'confirm') or $form.id < 1000000000}
				<div id=promo_items_list>
					{include file=promotion.new.sheet.tpl}
				</div>
			{/if}
			</form>
			
	</div>
</div>

<script type="text/javascript">
//reset_vendor_autocomplete();
//reset_brand_autocomplete();

{literal}

var cons_check=consignment_toggle(document.f_a.s_consignment_bearing.checked);

if (cons_check){
	{/literal}
	load_data('{$form.r_type}');
	change_brand_vendor(document.f_a.dept_id);
   	{literal}
}

function show_context_menu(obj, id, item_id)
{
	context_info = { element: obj, id: id, sku_item_id: item_id};
	$('item_context_menu').style.left = ((document.body.scrollLeft)+mx) + 'px';
	$('item_context_menu').style.top = ((document.body.scrollTop)+my) + 'px';
	Element.show('item_context_menu');

	$('ul_menu').onmouseout = function() {
		context_info.timer = setTimeout('hide_context_menu()', 100);
	}

	$('ul_menu').onmousemove = function() {
		if (context_info.timer!=undefined) clearTimeout(context_info.timer);
		context_info.timer = undefined;
	}
	return false;
}

function hide_context_menu()
{
	$('ul_menu').onmouseout = undefined;
	$('ul_menu').onmousemove = undefined;
	Element.hide('item_context_menu');
}

function sel_all(obj)
{
	if (obj.value=='Select All')
	{
		obj.value = 'Select None';
		bl = true;
	}
	else
	{
		obj.value = 'Select All';
		bl = false;
	}

	var input = $$('#sku_listing ul li input');
	for(i=0;i<input.length;i++)
	{
		input[i].checked = bl;
	}
}

function add_sku_listing()
{
//	$('sku_listing').style.display='none';
	var opts = $('sku_listing').getElementsByTagName('input');
	for(var i=0;i<opts.length;i++)
	{
		var c = opts[i].value.split(",");
		if (opts[i].checked) add_sku_to_list(c[0],opts[i].title);
	}

	sku_list = document.getElementById("sku_code_list");

	for (var i=0;i<sku_list.options.length;i++)
	{
		sku_list.options[i].selected=true;
	}

//	alert(Form.serialize(document.f_a).replace(/\&/g,"\n"));

	clear_sku_listing();
	if (window.add_autocomplete_callback) disc = add_autocomplete_callback();
	if (disc) d = '&d='+escape(disc.join());
   parms = Form.serialize(document.f_a) + '&a=ajax_add_promo_row'+d;

 	// insert new row
	new Ajax.Request("promotion.php",{
		method:'post',
		parameters: parms,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
			if (/^\w+/.test(m.responseText))
			alert(m.responseText);
			else{
                new Insertion.Before($('tbl_footer'),m.responseText);
                reset_item_row_no();
			}
			
		},
		onComplete: function(){
			var cbxs = $$('#sku_listing ul li input');
			for(i=0;i<cbxs.length;i++)
			{
				cbxs[i].checked = false;
			}
			sku_list.length = 0;
			recolour_group();
		}
	});
}

function clear_sku_listing(){
//	$('category_id').value= '';
	$('sku_item_id').value = '';
	$('sku_item_code').value = '';
//	$('autocomplete_category').value = '';
//	$('sku_listing').innerHTML = 'Loading...';
//	$('sku_listing').style.display='none';
	$('autocomplete_category').focus();
}

function do_print(){
	if (document.f_b.id.value == '' || document.f_b.id.value == 0){
		alert('You must SAVE the Promotion before it can be printed.');
		exit;
	}
	curtain(true);
	show_print_dialog();
}

function show_print_dialog(){
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok(){
	$('print_dialog').style.display = 'none';
	window.open('{/literal}{$smarty.server.PHP_SELF}{literal}?&a=do_print&'+Form.serialize(document.fprn));
	curtain(false);
}

function print_cancel(){
	$('print_dialog').style.display = 'none';
	curtain(false);
}

function calendar_setup(){
    Calendar.setup({
		    inputField     :    "inp_date_from",     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_date_from",  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});

		Calendar.setup({
		    inputField     :    "inp_date_to",     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_date_to",  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
	});
}
</script>
{/literal}
	<form name="f_b" method=post ENCTYPE="multipart/form-data">
		<input type=hidden name=a>
		<input type=hidden name=approval_history_id value="{$form.approval_history_id}">
		<input type=hidden name=id value="{$form.id}">
		<input type=hidden name=user_id value="{$form.user_id|default:$sessioninfo.id}">
		<input type=hidden name=branch_id value="{$form.branch_id|default:$sessioninfo.branch_id}">
		<input type=hidden name=readonly value="{$readonly}">
		<input type=hidden name=active value="{$form.active}">
	</form>

	{if $form.approval_screen}
		<form name="f_c" method=post>
			<input type=hidden name=a value="save_approval">
			<input type=hidden name=approve_comment value="">
			<input type=hidden name=id value="{$form.id}">
			<input type=hidden name=branch_id value="{$form.branch_id}">
			<input type=hidden name=approvals value="{$form.approvals}">
			<input type=hidden name=approval_history_id value="{$form.approval_history_id}">
			{if $approval_on_behalf}
			<input type="hidden" name="on_behalf_of" value="{$approval_on_behalf.on_behalf_of}" />
			<input type="hidden" name="on_behalf_by" value="{$approval_on_behalf.on_behalf_by}" />
			{/if}
			<input type="hidden" name="rejected_item_data" value="" />
		</form>
	{/if}
	
	<p id=submitbtn align=center>
	{if $form.is_approval and $form.status==1 and $form.approved==0 and $form.approval_screen}
	<input type=button value="Approve" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_approve({$form.last_approver},'discount')">
	<input type=button value="Reject" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_reject({$form.last_approver})">
	<input type=button value="Terminate" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_terminate({$form.last_approver})">
	{/if}

	{if !$form.approval_screen}
		{if $allow_edit}
		<input name=bsubmit class="btn btn-primary" type=button value="Save & Close" onclick="do_save()">
		{/if}

		{if $form.id>$time_value|| !$allow_edit}
		<input type=button class="btn btn-error" value="Close" onclick="document.location='/promotion.php'">
		{/if}

		{if ($form.user_id eq $sessioninfo.id || $sessioninfo.level>=$config.doc_reset_level || $sessioninfo.privilege.PROMOTION_CANCEL) and $sessioninfo.branch_id eq $form.branch_id and !is_new_id($form.id)}
			{if $form.approved}
				{if $form.status!=4 && $form.status!=5 && $form.status!=0 && $form.active}
					<input type=button class="btn btn-warning" value="Cancel Promotion" onclick="do_cancel()">
	
				{/if}
			{elseif ($form.active || !$form.status) && $allow_edit}
			<input type=button class="btn btn-error" value="Delete" onclick="do_delete()">
			{/if}
		{/if}

		{if $smarty.request.branch_id == $sessioninfo.branch_id and !is_new_id($form.id)}
			<input type=button class="btn btn-primary" value="Copy" onclick="do_copy()">
		{/if}

		{if $allow_edit and $form.status == 0 and $form.approved == 0}
		<input type=button class="btn btn-success" value="Confirm" onclick="do_confirm()">
		{/if}

		{if $form.status == 1 and $form.approved == 1 and $form.active == 1 and $promo_item_count > 1 and $sessioninfo.privilege.PROMOTION_CANCEL and $sessioninfo.branch_id eq $form.branch_id}
		<input class="btn btn-warning" type=button value="Cancel Selected Item(s)" onclick="cancel_selected_item();">
		{/if}

		{if $form.id<$time_value && $form.active && $form.status<=1}
			<input type=button class="btn btn-primary" value="Print{if $form.status==0 || $form.status==2} Draft{elseif !$form.approved}{/if} Promotion" onclick="PROMO_PRINT.show('{$form.branch_id}', '{$form.id}', '{$form.promo_type}', '', '{$form.str_promo_branch_id_list}', '{$form.active}', '{$form.status}', '{$form.approved}')">
		{/if}
	{/if}
	</p>


	{if $form.status <> 1 or $form.approved <> 1 or $form.active <> 1}
		{if !$allow_edit or $form.approval_screen}
			{if $config.promotion_approval_allow_reject_by_items}
				<script>
					{literal}
					Form.getElements(document.f_a).each(function(item) {
						if (!$(item).hasClassName('rejected_item')) {
							$(item).disable();
						}
					});
					{/literal}
				</script>
			{else}
				<script>Form.disable(document.f_a);</script>
			{/if}
		{/if}
	{else}
	<script>disable_input();</script>
	{/if}

	{if (!$allow_edit or $form.approval_screen or $form.approved eq 1 or $form.active ne 1) and $form.id<1000000000}
	    {*<script>Form.disable(document.f_a);</script>*}
	{/if}
<script>
{if $allow_edit}
	calendar_setup();
	{literal}
		new Draggable('div_choose_member_type_popup',{ handle: 'div_choose_member_type_popup_header'});
		new Draggable('change_settings_by_sku_dialog',{ handle: 'div_change_settings_by_sku_header'});
	{/literal}
{/if}

{if $smarty.request.a eq 'refresh' or $smarty.request.a eq 'save' or $smarty.request.a eq 'confirm' or !is_new_id($form.id)}
	$('submitbtn').show();
{else}
	$('submitbtn').hide();
{/if}
	recolour_group();
	first_load = false;
	PROMO_PRINT.initialise();
	
	{if $form.status eq 0}
		PROMO_PRINT.show_unsave_remark();
	{/if}
</script>

{include file='footer.tpl'}