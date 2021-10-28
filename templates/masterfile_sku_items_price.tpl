{*
3/7/2008 4:10:45 PM - yinsee
- when HQ price changed, all branches follow. (WS request)

05/20/2008 11:15:50 AM - yinsee 
- only use discount code with percentage
 
1/5/2009 10:10:38 AM yinsee
- add qprice

3/2/2009 6:26:00 PM jeff
- filter in-active sku_items

1/28/2011 5:09:11 PM Alex
- add show qprice history and new show_more_result() function
- change use get_price_history() function to use price_history instead of price_history()

4/4/2011 2:20:00 PM Justin
- replaced the submit function from mprice to call Ajax instead of form submit.
- simplified the mprice when submitting and successfully updated the price, there is no form reload anymore.
- added the feature to call "update status" to show in the status area instead of using form reload.
- added the locking of update price button and show indicator for user as database is in progress of updating the price.

4/21/2011 10:40:12 AM Justin
- Added active promotion to take future promotion instead of current promotion only.

5/19/2011 11:18:10 AM Andy
- Hide Qprice and Active Promotion when found is consignment module.
- Add region price type and selling price, included mprice.

6/13/2011 2:27:03 PM Andy
- Fix when edit Qprice at HQ it cannot copy to branch.

6/22/2011 11:07:17 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

9/15/2011 4:24:00 PM Andy
- Add checkbox for FOC item, user must checked checkbox instead of leaving zero for FOC item.

9/22/2011 11:12:36 AM Andy
- Add checking to not allow negative selling price

10/25/2011 2:30:30 PM Andy
- Add checking to only show FOC checkbox if the SKU got allow FOC in masterfile.
- Prompt user to confirm whether want to replace HQ selling price/price type to all branches.

11/14/2011 2:18:12 PM Andy
- Add checking if found config.sku_hq_change_price_no_need_prompt will automatically copy selling price/price type to all branches without prompt user for confirmation.

3/27/2012 4:11:33 PM Andy
- Add can click on region title to change price by branch in region.

7/24/2012 5:52:34 PM Justin
- Added new feature that can auto copy price by column.
- Added new feature to auto copy price while user change price in sub branch.
- Enhanced to replace all mprice type with current changed price while user chosen to replace price by row by column.

8/6/2012 11:42:23 AM Justin
- Enhanced to have new config "sku_hq_change_price_by_column" control when auto update by column.

8/15/2012 11:29 AM Andy
- Enhance to can copy selling price cross sku based on same UOM, need config sku_change_price_always_apply_to_same_uom.

8/15/2012 2:48 PM Andy
- Change to copy selling price based on uom fraction instrad of uom id.

8/16/2012 3:22 PM Andy
- Add need same artno only can copy price.

10/8/2012 1:58:00 PM Fithri
- SKU change price block bom type "package"

1/23/2013 5:25 PM Justin
- Enhanced to auto add additional selling price if found any.

2/1/2013 11:55 AM Fithri
- add checkbox to enable update price
- tick to enable change price
- have a checkbox to tick whole row or column
- click into region change price, show a region price and its related control
- if change region price, all branches in region price will change

3/15/2013 10:15 AM Fithri
- fix the column messed up & all merge together if too many brnach
- fix "check all" checkbox if dont have member type

3/18/2013 5:36 PM Fithri
-bugfix : fix Multi Qty Price bug when copy from HQ

3/19/2013 1:55 PM Fithri
- add one checkbox to toggle update all price

4/8/2013 4:05 PM Andy
- Fix sku search description cannot have special character.

7/8/2013 4:01 PM Fithri
- turn off html autocomplete

6/6/2013 2:08 PM Justin
- Enhanced the negative price checking to accept multiple qprice.

7/16/2014 1:18 PM Justin
- Enhanced to have GP, GP(%) and Variance calculation.

7/17/2014 5:41 PM Justin
- Enhanced to have GP value calculation.

9/25/2014 2:39 PM Justin
- Enhanced to have GST information.

10/29/2014 9:55 AM Justin
- Bug fixed on system will not copy regular selling price to GST selling price when found it is 0% gst tax rate.

2/4/2015 9:29 AM Justin
- Bug fixed on having javascript errors while GST is not turned on.

3/11/2015 5:37 PM Andy
- Enhanced to store the checkbox FOC value into database.
- Change to not auto zerolise selling price when user tick FOC checkbox.

09-Mar-2016 09:40 Edwin
- Enhanced on checking login status before update sku price

4/18/2017 11:39 AM Qiu Ying
- Enhanced to add a remark in Change SKU Items Price

11/17/2017 4:31 PM Justin
- Enhanced to have Scan barcode feature and allow user to choose an item when matches more than 1 result.

6/20/2018 4:06 PM Andy
- Fixed to Scan GRN Barcoder to check config.enable_grn_barcoder.

11/9/2018 9:03 AM Justin
- Enlarged the price history popup window to have more spaces for new info.

06/26/2020 Sheila 11:34 AM
- Updated table background.

11/13/2020 4:15 PM Andy
- Added "Recommended Selling Price" (RSP) feature.

12/10/2020 12:34 PM Andy
- Fixed auto calculate RSP Discount bug.

12/23/2020 11:00 AM Andy
- Fixed copy selling price to branch bug when no use rsp.

12/28/2020 4:27 PM Andy
- Fixed bug on copy selling price by column.
*}

{include file=header.tpl}
{literal}
<style>
.inactive {
	color:#999;
}
.hidden {
	display: none;
}

.childhidden{
	display: none;
}

.bottom{
	position:absolute;
	bottom:0px;
	left:100px;
}

.region_header{
	background-color: #cf3;
}
.showborder{
	border-width:0px;
	border-style:solid;
}
</style>
{/literal}
<script type="text/javascript">
// update autocompleter parameters when vendor_id or department_id changed
var sku_autocomplete = undefined;
var consignment_modules = int('{$config.consignment_modules}');
var sku_use_region_price = int('{$config.sku_use_region_price}');
var sku_always_show_trade_discount = int('{$config.sku_always_show_trade_discount}');
var phpself = '{$smarty.server.PHP_SELF}';
var sku_hq_change_price_no_need_prompt = int('{$config.sku_hq_change_price_no_need_prompt}');
var sku_hq_change_price_by_column = int('{$config.sku_hq_change_price_by_column}');
var curr_branch_id = '{$sessioninfo.branch_id}';
var sku_change_price_always_apply_to_same_uom = int('{$config.sku_change_price_always_apply_to_same_uom}');
var show_cost = int('{$sessioninfo.privilege.SHOW_COST}');

{if $gst_settings}
var is_gst_active = true;
{else}
var is_gst_active = false;
{/if}

var branch_asp_list = [];
{foreach from=$branch_asp_list key=bid item=r}
	branch_asp_list['{$bid}'] = [];
	branch_asp_list['{$bid}'].push('{$r.additional_sp}');
{/foreach}

var region_asp_list = [];
{foreach from=$region_asp_list key=region_code item=r}
	region_asp_list['{$region_code}'] = [];
	region_asp_list['{$region_code}'].push('{$r.additional_sp}');
{/foreach}
{literal}

var item_last_price = {};

var _timeout_autocomplete_ = false;

function reset_sku_autocomplete()
{
	//var param_str = "a=ajax_search_sku&dept_id={/literal}{$form.department_id}{literal}&type="+getRadioValue(document.f_a.search_type);
	var param_str = "a=ajax_search_sku&type="+getRadioValue(document.f_a.search_type)+"&block_bom_package=1";
	if (sku_autocomplete != undefined)
	{
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else
	{
		/*
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_a.sku_item_id.value = s[0];
			document.f_a.sku_item_code.value = s[1];
			
		}});
		*/
		
		$('autocomplete_sku').onkeyup = function(k){
			if(k.keyCode==27) //escape
			{
				clear_autocomplete();
				return;
			}else if($('autocomplete_sku').value.trim() == "") $('autocomplete_sku_choices').hide();

			if (_timeout_autocomplete_!=false) clearTimeout(_timeout_autocomplete_);
			_timeout_autocomplete_ = false;
			
			val = this.value.trim();
			if (val<=0) return;
			_timeout_autocomplete_ = setTimeout('do_autocomplete()',500);
		};
		/*
		*/
		clear_autocomplete();
		
	}
	//clear_autocomplete();
}

function do_autocomplete(){

	//var param_str = "a=ajax_search_sku&type="+getRadioValue(document.f_a.search_type)+"&value="+$('autocomplete_sku').value+"&block_bom_package=1";
	var params = {
		'a': 'ajax_search_sku',
		'type': getRadioValue(document.f_a.search_type),
		'value': $('autocomplete_sku').value,
		'block_bom_package': 1
	};
	new Ajax.Request('ajax_autocomplete.php?', {
		parameters: params,
		onComplete: function(e){
			$('autocomplete_sku_choices').scrollTop = 0;
			$('autocomplete_sku_choices').innerHTML = e.responseText;
			$('autocomplete_sku_choices').show();
		}
	});
	
}

function set_search_value(li){
	var c = li.title.split(",");
	document.f_a.sku_item_id.value = c[0];
	document.f_a.sku_item_code.value = c[1];
	document.f_a.autocomplete_sku.value = c[2];
	$('autocomplete_sku_choices').hide();
	$('autocomplete_sku').focus();
}

function clear_autocomplete(){
	document.f_a.sku_item_id.value = '';
	document.f_a.sku_item_code.value = '';
	$('autocomplete_sku').value = '';
	$('autocomplete_sku').focus();
	$('autocomplete_sku_choices').hide();
}

function copy_to_branch(el,name, item_id, bid)
{
	// automatic change price by row
	var mprice_type = $(el).readAttribute('mprice_type');
	var update_by_row = false;
	var update_by_col = false;
	var artno = $('inp_artno-'+item_id).value.trim();
	//if (el.value != '0.00') el.parentNode.getElementsBySelector('[title="FOC"]').each(function(r){r.checked = false});
	if(mprice_type) update_gp(item_id, mprice_type, bid);
	
	var use_rsp = int(document.f_p['use_rsp['+item_id+']'].value);
	
	if(bid == 1 || $(el).readAttribute('copy_from_region') == '1'){
		if(!sku_hq_change_price_no_need_prompt){
			if(confirm('Do you want to automatically copy change made by HQ to all branches?')){
				update_by_row = true;
			}else return false;
		}else update_by_row = true;
		
		if(mprice_type){	// normal, mprice copy method
			var parent_tr = el.parentNode.parentNode.parentNode.parentNode;
			if ($(el).readAttribute('copy_from_region') == '1') parent_tr = parent_tr.parentNode;
			// update selling by branch
			var all_inp = $(parent_tr).getElementsBySelector('input.branch_selling[mprice_type="'+mprice_type+'"]');
			for(var i=0; i<all_inp.length; i++){
				if(all_inp[i]==el)	continue;	// skip own
				if(all_inp[i].readOnly)	continue;	// cannot be edit
				var splt_id = all_inp[i].id.split("-");
				var tmp_bid = splt_id[3];
				if(branch_asp_list[tmp_bid] != undefined && branch_asp_list[tmp_bid] > 0) all_inp[i].value = round(float(el.value) + float(branch_asp_list[tmp_bid]), 2); // found it is having additional selling price
				else all_inp[i].value = el.value; // clone value to all branch
				
				// RSP Discount
				
				if(use_rsp){
					document.f_p['rsp_discount['+item_id+']['+tmp_bid+']'].value = document.f_p['rsp_discount['+item_id+']['+bid+']'].value;
				}
				
				check_foc_cb(all_inp[i]);
			}

			// update selling by region
			var all_inp = $(parent_tr).getElementsBySelector('input.region_selling[mprice_type="'+mprice_type+'"]');
			for(var i=0; i<all_inp.length; i++){
				if(all_inp[i]==el)	continue;	// skip own
				if(all_inp[i].readOnly)	continue;	// cannot be edit
				var splt_id = all_inp[i].id.split("-");
				var tmp_rc = splt_id[3];
				if(region_asp_list[tmp_rc] != undefined && region_asp_list[tmp_rc] > 0) all_inp[i].value = round(float(el.value) + float(region_asp_list[tmp_rc]), 2); // found it is having additional selling price
				else all_inp[i].value = el.value; // clone value to all branch
				check_foc_cb(all_inp[i]);
			}
			
		}else{	// qprice copy method
			var obj = el.parentNode.parentNode.getElementsByTagName('input');
			var check = new RegExp('^'+name.replace(/\[/g,'\\[').replace(/\]/g,'\\]'));
			for (i=0;i<obj.length; i++)
			{
				if (el.name != obj[i].name && !obj[i].hasClassName("minqty"))
				{
					obj[i].value = el.value;
				}
			}
		}
	}
	
	if(mprice_type == "normal" && sku_hq_change_price_by_column){ // normal, mprice copy method
		// automatic change price by column
		if(!sku_hq_change_price_no_need_prompt){
			var extra_comment = "";
			if(update_by_row) extra_comment = " to all other branches";
			if(confirm('Do you want to automatically copy change to all different Mprice'+extra_comment+'?'))	update_by_col = true;
		}

		if(update_by_col){
			var parent_tr = el.parentNode.parentNode.parentNode.parentNode;
			var all_inp;
			if(update_by_row) all_inp = $(parent_tr).getElementsByClassName('inp_mprice_'+item_id);
			else all_inp = $(parent_tr).getElementsByClassName('inp_mprice_'+item_id+'_'+bid);
	
			for(var i=0; i<all_inp.length; i++){
				var curr_mprice_type = $(all_inp[i]).readAttribute('mprice_type');
				if(mprice_type==curr_mprice_type)	continue;	// skip own
				if(all_inp[i].readOnly)	continue;	// cannot be edit
				all_inp[i].value = el.value;	// clone value to all branch
				
				var splt_id = all_inp[i].id.split("-");
				calculate_gst(splt_id[2], splt_id[1], splt_id[3], all_inp[i]);
				update_gp(splt_id[2], splt_id[1], splt_id[3]);
			}
		}		
	}
	
	// copy cross item based on same UOM
	if(mprice_type && sku_change_price_always_apply_to_same_uom){
		// get this item uom id
		var uom_id = $('inp_packing_uom_id-'+item_id).value;
		var uom_fraction = $('inp_packing_uom_fraction-'+item_id).value;
		
		// get all items with same uom id
		var tr_with_same_uom_list = $$('#mprice tr.tr_mprice_row_by_uom_fraction-'+uom_fraction);
		
		for(var i=0; i<tr_with_same_uom_list.length; i++){
			var tmp_sid = tr_with_same_uom_list[i].id.split("-")[1];
			
			if(tmp_sid==item_id)	continue;	// no need touch same item
			
			var tmp_artno = $('inp_artno-'+tmp_sid).value.trim();

			if(tmp_artno != artno)	continue;	// no need update if diff artno
			
			// copy by row for this sku			
			if(update_by_row){
				var all_inp = $(tr_with_same_uom_list[i]).getElementsBySelector('input[mprice_type="'+mprice_type+'"]');
				for(var j=0; j<all_inp.length; j++){
					if(all_inp[j]==el)	continue;	// skip own	
					if(all_inp[j].readOnly)	continue;	// cannot be edit
					all_inp[j].value = el.value;	// clone value to all branch
					
					var splt_id = all_inp[j].id.split("-");
					calculate_gst(splt_id[2], splt_id[1], splt_id[3], all_inp[j]);
					update_gp(splt_id[2], splt_id[1], splt_id[3]);
				}
			}
			
			
			// copy by column
			if(update_by_col){
				if(mprice_type == "normal" && sku_hq_change_price_by_column){ // normal, mprice copy method
					if(update_by_row) all_inp = $(tr_with_same_uom_list[i]).getElementsByClassName('inp_mprice_'+tmp_sid);
					else all_inp = $(tr_with_same_uom_list[i]).getElementsByClassName('inp_mprice_'+tmp_sid+'_'+bid);
					
					for(var j=0; j<all_inp.length; j++){
						if(all_inp[j]==el)	continue;	// skip own	
						if(all_inp[j].readOnly)	continue;	// cannot be edit
						all_inp[j].value = el.value;	// clone value to all branch
						
						var splt_id = all_inp[j].id.split("-");
						calculate_gst(splt_id[2], splt_id[1], splt_id[3], all_inp[j]);
						update_gp(splt_id[2], splt_id[1], splt_id[3]);
					}
				}
			}			
		}
	}
}

function show_more_result(){
	var row=0;

	$$("#history_id .hidden").each(function(ele,index){
		
		if (!ele.hasClassName("hidden"))   return false;

		if (row <= 2){
			
			ele.removeClassName('hidden');

			var child = ele.next();
			while (child && child.hasClassName("childhidden")){
				child.removeClassName('childhidden');
                child = child.next();
			}

		}else{
			return false;
		}

		row++;
	});
	if ($$('#history_id .hidden') == '')   $('show_result_id').hide();
}

function get_price_history(element,id,bid,branch_code,type)
{
	if (type==undefined){
		type='';
		price_title='Normal';
	}else if (type=='qprice'){
        price_title='Multi Quantity';
	}else{
        price_title=type.substr(0,1).toUpperCase() + type.substr(1);
	}
	//change pop up title
	$('change_title').update(branch_code+" "+price_title+" Price History");

	if ($('price_history').style.display=='none')
	{
		// move to position if hidden
		Position.clone(element,$('price_history'),{setHeight: false, setWidth:false, offsetLeft:20});
		Element.show('price_history');
	}
	// loading
	$('price_history_content').innerHTML = _loading_;
	new Ajax.Updater('price_history_content','/masterfile_sku_items_price.php?a=history&id='+id+'&branch_id='+bid+'&type='+type,{evalScripts:true});
}

function update_price(){
	// check got zero price but no foc
	if(!check_price('price'))	return false;
	
 	if (!confirm('Are you sure want to update Selling Price?')) return;

	$('loading_area').innerHTML = " Updating, Please Wait... <img src='/ui/clock.gif'>";
	document.f_p.upd.disabled = true;
    
    ajax_request('masterfile_sku_items_price.php',{
		method:'post',
		parameters: Form.serialize(document.f_p)+'&a=change_price',
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m){
			eval("var json = "+m.responseText);
			for(var msg in json){
				if(json[msg]['err']) alert("You're have encountered below:\n\n"+json[msg]['err']);
				else if(json[msg]['status_msg']){
					$('status_msg').innerHTML = "<br><h5>Update status</h5>"+json[msg]['status_msg'];
					alert("Update Completed");
				}
			}
    	},
    	onComplete: function (m){
			document.f_p.upd.disabled = false;
			$('loading_area').innerHTML = '';
		}
	});
}

function show_region_branch_price_type_selling(sku_item_id, region_code, mprice_type){
	curtain(true);
	$('div_region_price_popup_content').update(_loading_);
	center_div($('div_region_price_popup').show());
	
	new Ajax.Updater('div_region_price_popup_content', phpself, {
		parameters:{
			a: 'ajax_show_region_branch_price_type_selling',
			sku_item_id: sku_item_id,
			region_code: region_code,
			mprice_type: mprice_type
		}
	});
}

function get_region_price_history(element,sku_item_id,region_code,region_code_name,mprice_type){
	if (!mprice_type){
		mprice_type='normal';
		price_title='Normal';
	}else{
        price_title= mprice_type.substr(0,1).toUpperCase() + mprice_type.substr(1);
	}
	//change pop up title
	$('change_title').update(region_code_name+" "+price_title+" Price History");

	if ($('price_history').style.display=='none')
	{
		// move to position if hidden
		Position.clone(element,$('price_history'),{setHeight: false, setWidth:false, offsetLeft:20});
		Element.show('price_history');
	}
	// loading
	$('price_history_content').update(_loading_);
	new Ajax.Updater('price_history_content',phpself,{
		parameters:{
			a: 'ajax_get_region_price_history',
			sku_item_id: sku_item_id,
			region_code: region_code,
			mprice_type: mprice_type
		},
		evalScripts:true
	});
}

function curtain_clicked(){
	$('div_region_price_popup').hide();
	$('div_si_list').hide();
	curtain(false);
}

function item_foc_changed(mprice_type, sid, bid){
	var bid_str = bid ? '['+bid+']' : '';
	
	// get checkbox element
	var inp_chx = document.f_p['item_foc['+mprice_type+']['+sid+']'+bid_str];
	
	// check whehther is foc
	var is_foc = inp_chx.checked;
	
	// get target price element
	var tmp_name = '';
	if(mprice_type == 'normal'){
		tmp_name = 'price['+sid+']'+bid_str;
	}else{
		tmp_name = 'mprice['+sid+']['+mprice_type+']'+bid_str;
	}
	
	var inp_price = document.f_p[tmp_name];
	if(is_foc){
		if (inp_price.readOnly) {
			alert("Please check 'Update' box to make this item editable");
			inp_chx.checked = false;
			return;
		}
		//item_last_price[tmp_name] = inp_price.value; // store last price
		//inp_price.value = '0.00';
		//inp_price.readOnly = true;
		 
	}else{
		if (inp_price.readOnly) {
			alert("Please check 'Update' box to make this item editable");
			inp_chx.checked = true;
			return;
		}
		//inp_price.readOnly = false;
		//if(item_last_price[tmp_name])	inp_price.value = item_last_price[tmp_name];
	}
	
	// clone to branch
	if(bid == 1){	// it is HQ changed
		if(!sku_hq_change_price_no_need_prompt){
			if(!confirm('Do you want to automatically copy change made by HQ to all branches?'))	return false;
		}
		// get parent <tr>
		var parent_tr = inp_chx.parentNode.parentNode.parentNode.parentNode.parentNode;
		
		// get all FOC checkbox, include region
		var all_inp = $(parent_tr).getElementsBySelector('input.chx_item_foc[mprice_type="'+mprice_type+'"]','input.chx_region_item_foc[mprice_type="'+mprice_type+'"]');
		for(var i=0; i<all_inp.length; i++){	// loop all checkbox
			if(all_inp[i]==inp_chx) {
				//toggle_check_all_row2(inp_chx.checked,mprice_type,sid);
				continue;	// skip own
			}
			
			var ids = all_inp[i].id.split('-');	// get ?ID
			var tmp_mprice_type = ids[1];
			var tmp_sid = ids[2];
			var tmp_bid = ids[3];
			var tmp_bid_str = tmp_bid ? '['+tmp_bid+']' : '';	// bid or region code
			
			// construct price element name
			if(all_inp[i].hasClassName('chx_region_item_foc')){	// region
				if(tmp_mprice_type == 'normal'){
					tmp_name = 'region_price['+tmp_sid+']'+tmp_bid_str;
				}else{
					tmp_name = 'region_mprice['+tmp_sid+']['+tmp_mprice_type+']'+tmp_bid_str;
				}	
			}else{	// normal branch
				if(tmp_mprice_type == 'normal'){
					tmp_name = 'price['+tmp_sid+']'+tmp_bid_str;
				}else{
					tmp_name = 'mprice['+tmp_sid+']['+tmp_mprice_type+']'+tmp_bid_str;
				}
			}
			
			
			// get price element
			var tmp_inp_price = document.f_p[tmp_name];
			
			// checked/unchecked checkbox
			all_inp[i].checked = is_foc;
			
			// set FOC
			if(is_foc){
				//tmp_inp_price.readOnly = true;
				//item_last_price[tmp_name] = tmp_inp_price.value; // store last price
				//tmp_inp_price.value = '0.00';
				//tmp_inp_price.readOnly = true;
			}else{
				//tmp_inp_price.readOnly = true;
				//if(item_last_price[tmp_name])	tmp_inp_price.value = item_last_price[tmp_name];
			}

			if(all_inp[i].hasClassName('chx_region_item_foc')) calculate_region_gst(tmp_sid, tmp_mprice_type, tmp_bid);
			else calculate_gst(tmp_sid, tmp_mprice_type, tmp_bid);
			update_gp(tmp_sid, tmp_mprice_type, tmp_bid);
		}
	}
	
	calculate_gst(sid, mprice_type, bid);
	update_gp(sid, mprice_type, bid);
}

function region_item_foc_changed(mprice_type, sid, region_code){
	var region_code_str = region_code ? '['+region_code+']' : '';
	
	// get checkbox element
	if (document.f_p['ro_region_item_foc['+mprice_type+']['+sid+']'+region_code_str] != undefined) {
		var region_only = true;
		var inp_chx = document.f_p['ro_region_item_foc['+mprice_type+']['+sid+']'+region_code_str];
	}
	else {
		var region_only = false;
		var inp_chx = document.f_p['region_item_foc['+mprice_type+']['+sid+']'+region_code_str];
	}
	
	// check whehther is foc
	var is_foc = inp_chx.checked;
	
	// get target price element
	var tmp_name = '';
	if(mprice_type == 'normal'){
		if (region_only) tmp_name = 'ro_region_price['+sid+']'+region_code_str;
		else tmp_name = 'region_price['+sid+']'+region_code_str;
	}else{
		if (region_only) tmp_name = 'ro_region_mprice['+sid+']['+mprice_type+']'+region_code_str;
		else tmp_name = 'region_mprice['+sid+']['+mprice_type+']'+region_code_str;
	}
	
	var inp_price = document.f_p[tmp_name];
	if(is_foc){
		//inp_price.readOnly = true;
		if (inp_price.readOnly) {
			alert("Please check 'Update' box to make this item editable");
			inp_chx.checked = false;
			return;
		}
		//if (item_last_price[tmp_name] == undefined) item_last_price[tmp_name] = inp_price.value; // store last price
		//inp_price.value = '0.00';
	}else{
		if (inp_price.readOnly) {
			alert("Please check 'Update' box to make this item editable");
			inp_chx.checked = true;
			return;
		}
		//inp_price.readOnly = false;
		//if(item_last_price[tmp_name])	inp_price.value = item_last_price[tmp_name];
	}
	
	if (region_only) {
		
		if(!confirm('Do you want to automatically copy change branches?'))	return false;
		
		$$('.chx_item_foc').each(function (r) {
			r.checked = is_foc;
		});
		
		$$('.chx_item_edit-normal-'+sid).each(function (r) {
			r.checked = is_foc;
		});
		
		var all_normal = $(document.f_p).getElementsBySelector('input[mprice_type="'+mprice_type+'"]');
		
		for(var i=0; i<all_normal.length; i++){
			if (all_normal[i].type == 'checkbox') continue;
			if (is_foc) {
				if (all_normal[i] != inp_price) {
					if (item_last_price[all_normal[i].name] == undefined) item_last_price[all_normal[i].name] = all_normal[i].value;
				}
				all_normal[i].value = '0.00';
				all_normal[i].readOnly = false;
			}
			else {
				if (item_last_price[all_normal[i].name]) all_normal[i].value = item_last_price[all_normal[i].name];
				//all_normal[i].readOnly = true;
			}
			
			var ids = all_normal[i].id.split('-');
			calculate_region_gst(ids[2], ids[1], ids[3]);
			update_gp(ids[2], ids[1], ids[3]);
		}
	}
	
	calculate_region_gst(sid, mprice_type, region_code);
	update_gp(sid, mprice_type, region_code);
}

function check_price(type){
	if(type!='price' && type!='qprice'){
		alert('Invalid Checking Type');
		return false;
	}
	
	if(type=='qprice'){	// qprice
		var all_inp_qprice = $(document.f_q).getElementsBySelector('input.inp_qprice');
		
		for(var i=0; i<all_inp_qprice.length; i++){
			var inp_qprice = all_inp_qprice[i];
			var price = float(inp_qprice.value);
			
			if(price<0){	// found negative
				alert('Selling Price cannot be negative.');
				inp_qprice.focus();
				return false;
			}
		}
	}else{	// multiple price, included region
		// currently only check normal, mprice no need FOC checkbox
		var all_inp_price = $(document.f_p).getElementsBySelector('input.inp_price','input.inp_region_price');
		
		for(var i=0; i<all_inp_price.length; i++){
			var inp_price = all_inp_price[i];
			var price = float(inp_price.value);
			if(price>0)	continue;	// nothing to do if the price is valid
			
			var is_region = $(inp_price).hasClassName('inp_region_price');
			var ids = inp_price.id.split('-');
			var mprice_type = ids[1];
			var sid = ids[2];
			var bid = ids[3];	// can be region code as well
			var bid_str = bid ? '-'+bid : '';
			
			var tmp_name = '';
			
			 
			if(price<0){	// found negative
				alert('Selling Price cannot be negative.');
				inp_price.focus();
				return false;
			}else{
				if(mprice_type!='normal')	continue;
				// check got tick FOC or not
				if(is_region){
					tmp_name = 'region_item_foc-'+mprice_type+'-'+sid+bid_str;
				}else{
					tmp_name = 'item_foc-'+mprice_type+'-'+sid+bid_str;
				}
				
				// get FOC checkbox
				var inp_foc = $(tmp_name);
				if(!inp_foc.checked){
					alert('Selling Price cannot be zero if not FOC item');
					inp_price.focus();
					return false;
				}
			}
		}
	}
	
	
	return true;
}

function copy_price_type_to_branch(ele){
	var ptype = ele.value;
	
	if(!sku_hq_change_price_no_need_prompt){
		if(!confirm('Do you want to automatically change all branches price type to '+ptype+'?'))	return false;
	}
	
	var all_sel = $(document.f_p).getElementsBySelector("select.sel_price_type");
	
	for(var i=0; i<all_sel.length; i++){
		all_sel[i].value = ptype;
	}
}

function change_price_by_region(region_code){
	if(!document.f_p)	return;
	var sid = document.f_p['sku_item_id'].value;
	if(!sid || region_code=='')	return;
	
	window.location = '?a=find&sku_item_id='+sid+'&show_by_region_code='+region_code;
}

function self_click_edit(obj) {
	var ids = obj.id.split('-');
	var type = ids[1];
	var sid = ids[2];
	var bid = ids[3];
	
	if ($(obj).readAttribute('is_region_price')){
		$('inp_region_price-'+ids[1]+'-'+ids[2]+'-'+ids[3]).readOnly = !obj.checked;
		if(is_gst_active) $('inp_region_gst_price-'+ids[1]+'-'+ids[2]+'-'+ids[3]).readOnly = !obj.checked;
	}else{
		$('inp_price-'+ids[1]+'-'+ids[2]+'-'+ids[3]).readOnly = !obj.checked;
		if(is_gst_active) $('inp_gst_price-'+ids[1]+'-'+ids[2]+'-'+ids[3]).readOnly = !obj.checked;
		
		if(type == 'normal'){
			// RSP Discount
			if(document.f_p['rsp_discount['+sid+']['+bid+']']){
				document.f_p['rsp_discount['+sid+']['+bid+']'].readOnly = !obj.checked;
			}
		}
		
	}
}

function self_click_edit2(obj) {
	var ids = obj.id.split('-');
	$('inp_price-'+ids[1]+'-'+ids[2]).readOnly = !obj.checked;
	if(is_gst_active) $('inp_gst_price-'+ids[1]+'-'+ids[2]).readOnly = !obj.checked;
}

function toggle_check_all_row(ch,ptype,id) {
	$$('.chx_item_edit-'+ptype+'-'+id).each(function (r) {
		r.checked = ch;
		self_click_edit(r);
	});
}

function toggle_check_all_row2(ch,id) {
	$$('.cb-'+id).each(function (r) {
		r.checked = ch;
		self_click_edit(r);
	});
}

function toggle_check_all_column(ch,id,bid) {
	$$('.col_item-'+id+'-'+bid).each(function (r) {
		r.checked = ch;
		self_click_edit(r);
	});
}

function toggle_check_all_column2(ch,type) {
	$$('.cb_col-'+type).each(function (r) {
		r.checked = ch;
		self_click_edit(r);
	});
}

function toggle_check_all_col_row(ch) {
	$$('.cb_check_all').each(function (r) {r.checked = ch;});
	$$('.cb_all_col').each(function (r) {r.checked = ch;});
	$$('.cb_all_col2').each(function (r) {r.checked = ch;});
	$$('.cb_all_row').each(function (r) {r.checked = ch;});
	$$('.cb_all_row2').each(function (r) {r.checked = ch;});
	$$('.branch_selling').each(function (r) {r.readOnly = !ch;});
	$$('.region_selling').each(function (r) {r.readOnly = !ch;});
	if(is_gst_active) $$('.region_gst_selling').each(function (r) {r.readOnly = !ch;});
	$$('.inp_price').each(function (r) {r.readOnly = !ch;});
	if(is_gst_active) $$('.inp_gst_price').each(function (r) {r.readOnly = !ch;});
	$$('.cb_update_price').each(function (r) {r.checked = ch;});
	
	// RSP Discount
	$$('.inp_rsp_discount').each(function (r) {r.readOnly = !ch;});
}

function toggle_check_all_col_row_item(id,ch) {
	$$('.cb_all_col_'+id).each(function (r) {r.checked = ch;});
	$$('.cb_all_col2_'+id).each(function (r) {r.checked = ch;});
	$$('.cb_all_row_'+id).each(function (r) {r.checked = ch;});
	$$('.cb_all_row2_'+id).each(function (r) {r.checked = ch;});
	$$('.inp_mprice_'+id).each(function (r) {r.readOnly = !ch;});
	$$('.inp_gst_mprice_'+id).each(function (r) {r.readOnly = !ch;});
	$$('.inp_price_'+id).each(function (r) {r.readOnly = !ch;});
	if(is_gst_active) $$('.inp_gst_price_'+id).each(function (r) {r.readOnly = !ch;});
	$$('.inp_region_price_'+id).each(function (r) {r.readOnly = !ch;});
	if(is_gst_active) $$('.inp_region_gst_price_'+id).each(function (r) {r.readOnly = !ch;});
	$$('.cb_mprice_'+id).each(function (r) {r.checked = ch;});
	$$('.cb-'+id).each(function (r) {r.checked = ch;});
	$$('.cb_item_'+id).each(function (r) {r.checked = ch;});
	
	// RSP Discount
	$$('.inp_rsp_discount').each(function (r) {r.readOnly = !ch;});
}

function check_foc_cb(el) {
	if (el.value != '0.00') el.parentNode.getElementsBySelector('[title="FOC"]').each(function(r){r.checked = false});
	var splt_id = el.id.split("-");
	if ($(el).readAttribute('is_region_price'))
		calculate_region_gst(splt_id[2], splt_id[1], splt_id[3], el);
	else calculate_gst(splt_id[2], splt_id[1], splt_id[3], el);
	update_gp(splt_id[2], splt_id[1], splt_id[3]);
}

function update_gp(sid, type, bid){

	if(show_cost == 0) return false;
	// current gross profit amt
	var gp = round(document.f_p["curr_price["+sid+"]["+type+"]["+bid+"]"].value - document.f_p["cost_price["+sid+"]["+type+"]["+bid+"]"].value, 4);
    
    // current gross profit percent
    var grossp = 0
    if(document.f_p["curr_price["+sid+"]["+type+"]["+bid+"]"].value!=0){
		grossp = float(gp/document.f_p["curr_price["+sid+"]["+type+"]["+bid+"]"].value)*100;
	}
	//current GP val
	 var gp_val = 0;
	if($("new_gpv_"+sid+"_"+type+"_"+bid) != undefined) gp_val = round(gp * document.f_p['stock_bal['+sid+']['+bid+']'].value, 2);
	
	var inclusive_tax = "";
	var gst_rate = "";
	if(is_gst_active){
		inclusive_tax = document.f_p["inclusive_tax["+sid+"]"].value;
		gst_rate = document.f_p["gst_rate["+sid+"]"].value;
	}

	if(type == "normal"){
		if(document.f_p["price["+sid+"]["+bid+"]"] != undefined){
			if(is_gst_active && inclusive_tax == "yes" && gst_rate > 0) var selling_price = document.f_p["gst_price["+sid+"]["+bid+"]"].value;
			else var selling_price = document.f_p["price["+sid+"]["+bid+"]"].value;
		}
		else if(document.f_p["region_price["+sid+"]["+bid+"]"] != undefined){
			if(is_gst_active && inclusive_tax == "yes" && gst_rate > 0) var selling_price = document.f_p["region_gst_price["+sid+"]["+bid+"]"].value;
			else var selling_price = document.f_p["region_price["+sid+"]["+bid+"]"].value;
		}
		else if(document.f_p["ro_region_price["+sid+"]["+bid+"]"] != undefined){
			if(is_gst_active && inclusive_tax == "yes" && gst_rate > 0) var selling_price = document.f_p["ro_region_gst_price["+sid+"]["+bid+"]"].value;
			else var selling_price = document.f_p["ro_region_price["+sid+"]["+bid+"]"].value;
		}
		else return false;
	}else{
		if(document.f_p["mprice["+sid+"]["+type+"]["+bid+"]"] != undefined){
			if(is_gst_active && inclusive_tax == "yes" && gst_rate > 0) var selling_price = document.f_p["gst_mprice["+sid+"]["+type+"]["+bid+"]"].value;
			else var selling_price = document.f_p["mprice["+sid+"]["+type+"]["+bid+"]"].value;
		}
		else if(document.f_p["region_mprice["+sid+"]["+type+"]["+bid+"]"] != undefined){
			if(is_gst_active && inclusive_tax == "yes" && gst_rate > 0) var selling_price = document.f_p["region_gst_mprice["+sid+"]["+type+"]["+bid+"]"].value;
			else var selling_price = document.f_p["region_mprice["+sid+"]["+type+"]["+bid+"]"].value;
		}
		else if(document.f_p["ro_region_mprice["+sid+"]["+type+"]["+bid+"]"] != undefined){
			if(is_gst_active && inclusive_tax == "yes" && gst_rate > 0) var selling_price = document.f_p["ro_region_gst_mprice["+sid+"]["+type+"]["+bid+"]"].value;
			else var selling_price = document.f_p["ro_region_mprice["+sid+"]["+type+"]["+bid+"]"].value;
		}
		else return false;
	}
	
	if(selling_price != 0 && round(document.f_p["curr_price["+sid+"]["+type+"]["+bid+"]"].value,2) != round(selling_price,2)){
		// calculate GP
		var new_gp = round(selling_price - document.f_p["cost_price["+sid+"]["+type+"]["+bid+"]"].value, 4);
		$("new_gp_"+sid+"_"+type+"_"+bid).update(new_gp);

		// calculate GP%
		var new_grossp = 0
		if(selling_price!=0){
			new_grossp = float(new_gp/selling_price)*100;
			$("new_gpp_"+sid+"_"+type+"_"+bid).update(round(new_grossp, 2));
		}
		
		// calculate GP value
		var new_gp_val = 0;
		var gpv_var = 0;
		if($("new_gpv_"+sid+"_"+type+"_"+bid) != undefined){
			var new_gp_val = round(new_gp * document.f_p['stock_bal['+sid+']['+bid+']'].value, 2);
			$("new_gpv_"+sid+"_"+type+"_"+bid).update(round(new_gp_val, 2));
			var gpv_var = round(new_gp_val - gp_val, 2);
		}
		
		// calculate the GP variance
		var gp_var = round(new_gp - gp, 4);
		var gpp_var = round(gp_var / selling_price * 100, 2);
		/*if(new_gp < 0){
			$("new_gp_"+sid+"_"+type+"_"+bid).setStyle({
				color: 'red'
			});
			$("new_gpp_"+sid+"_"+type+"_"+bid).setStyle({
				color: 'red'
			});
		}else{
			$("new_gp_"+sid+"_"+type+"_"+bid).setStyle({
				color: 'green'
			});
			$("new_gpp_"+sid+"_"+type+"_"+bid).setStyle({
				color: 'green'
			});		
		}*/
		
		if(gp_var < 0){
			$("gp_var_"+sid+"_"+type+"_"+bid).setStyle({
				color: 'red'
			});
			$("gpp_var_"+sid+"_"+type+"_"+bid).setStyle({
				color: 'red'
			});
		}else{
			$("gp_var_"+sid+"_"+type+"_"+bid).setStyle({
				color: 'green'
			});
			$("gpp_var_"+sid+"_"+type+"_"+bid).setStyle({
				color: 'green'
			});
		}

		if($("new_gpv_"+sid+"_"+type+"_"+bid) != undefined){
			/*if(new_gp_val > 0){
				$("new_gpv_"+sid+"_"+type+"_"+bid).setStyle({
					color: 'green'
				});
			}else{
				$("new_gpv_"+sid+"_"+type+"_"+bid).setStyle({
					color: 'red'
				});			
			}*/
			if(gpv_var > 0){
				$("gpv_var_"+sid+"_"+type+"_"+bid).setStyle({
					color: 'green'
				});
			}else{
				$("gpv_var_"+sid+"_"+type+"_"+bid).setStyle({
					color: 'red'
				});
			}
			
			$("gpv_var_"+sid+"_"+type+"_"+bid).update(gpv_var);
		}
		$("gp_var_"+sid+"_"+type+"_"+bid).update(gp_var);
		$("gpp_var_"+sid+"_"+type+"_"+bid).update(gpp_var);
	}else{
		$("new_gp_"+sid+"_"+type+"_"+bid).update("");
		$("new_gpp_"+sid+"_"+type+"_"+bid).update("");
		
		$("gp_var_"+sid+"_"+type+"_"+bid).update("");
		$("gpp_var_"+sid+"_"+type+"_"+bid).update("");
		
		if($("gpv_var_"+sid+"_"+type+"_"+bid) != undefined){
			$("new_gpv_"+sid+"_"+type+"_"+bid).update("");
			$("gpv_var_"+sid+"_"+type+"_"+bid).update("");
		}

	}
}


function calculate_gst(id, type, bid, obj){
	if(!is_gst_active || inclusive_tax == "inherit") return;
	var inclusive_tax = document.f_p["inclusive_tax["+id+"]"].value;
	var gst_rate = float(document.f_p["gst_rate["+id+"]"].value);
	var use_rsp = int(document.f_p['use_rsp['+id+']'].value);
	
	if(type == "normal"){
		// calculate selling price after/before GST
		if(obj != undefined && obj.name == "gst_price["+id+"]["+bid+"]"){ // found user changing GST selling price
			// calculate gst amount
			var gst_selling_price = float(obj.value);
			
			if (inclusive_tax=='no') {
				var selling_price=(gst_selling_price*100)/(100+gst_rate);
				var gst_amt=float(selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
				var selling_price=float(gst_selling_price+gst_amt);
			}

			document.f_p["price["+id+"]["+bid+"]"].value = round(selling_price, 2);
			
			// RSP
			if(use_rsp){
				item_selling_price_changed(id, bid);
			}
		}else{
			var selling_price = float(document.f_p["price["+id+"]["+bid+"]"].value);
			
			if (inclusive_tax=='yes') {
				var gst_selling_price=(selling_price*100)/(100+gst_rate);
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(selling_price) * gst_rate / 100;
				var gst_selling_price=float(selling_price+gst_amt);
			}
			
			document.f_p["gst_price["+id+"]["+bid+"]"].value=round(gst_selling_price,2);
		}
		
		document.f_p["gst_amount["+id+"]["+bid+"]"].value = round(gst_amt, 2);
	}else{
		// calculate selling price after/before GST
		if(obj != undefined && obj.name == "gst_mprice["+id+"]["+type+"]["+bid+"]"){ // found user changing GST selling price
			// calculate gst amount
			var gst_selling_price = float(obj.value);
			
			if (inclusive_tax=='no') {
				var selling_price=(gst_selling_price*100)/(100+gst_rate);
				var gst_amt=float(selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
				var selling_price=float(gst_selling_price+gst_amt);
			}

			document.f_p["mprice["+id+"]["+type+"]["+bid+"]"].value = round(selling_price, 2);
		}else{
			var selling_price = float(document.f_p["mprice["+id+"]["+type+"]["+bid+"]"].value);
			
			if (inclusive_tax=='yes') {
				var gst_selling_price=(selling_price*100)/(100+gst_rate);
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(selling_price) * gst_rate / 100;
				var gst_selling_price=float(selling_price+gst_amt);
			}
			
			document.f_p["gst_mprice["+id+"]["+type+"]["+bid+"]"].value=round(gst_selling_price,2);
		}
		
		document.f_p["gst_amount["+id+"]["+type+"]["+bid+"]"].value = round(gst_amt, 2);
	}
}

function calculate_region_gst(id, type, bid, obj){
	if(!is_gst_active || inclusive_tax == "inherit") return;
	
	var inclusive_tax = document.f_p["inclusive_tax["+id+"]"].value;
	var gst_rate = float(document.f_p["gst_rate["+id+"]"].value);
	
	if(type == "normal"){
		// calculate selling price after/before GST
		if(obj != undefined && obj.name == "region_gst_price["+id+"]["+bid+"]"){ // found user changing GST selling price
			// calculate gst amount
			var gst_selling_price = float(obj.value);
			
			if (inclusive_tax=='no') {
				var selling_price=(gst_selling_price*100)/(100+gst_rate);
				var gst_amt=float(selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
				var selling_price=float(gst_selling_price+gst_amt);
			}

			document.f_p["region_price["+id+"]["+bid+"]"].value = round(selling_price, 2);
		}else{
			var selling_price = float(document.f_p["region_price["+id+"]["+bid+"]"].value);
			
			if (inclusive_tax=='yes') {
				var gst_selling_price=(selling_price*100)/(100+gst_rate);
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(selling_price) * gst_rate / 100;
				var gst_selling_price=float(selling_price+gst_amt);
			}
			
			document.f_p["region_gst_price["+id+"]["+bid+"]"].value=round(gst_selling_price,2);
		}
		
		document.f_p["region_gst_amount["+id+"]["+bid+"]"].value = round(gst_amt, 2);
	}else{
		// calculate selling price after/before GST
		if(obj != undefined && obj.name == "region_gst_mprice["+id+"]["+type+"]["+bid+"]"){ // found user changing GST selling price
			// calculate gst amount
			var gst_selling_price = float(obj.value);
			
			if (inclusive_tax=='no') {
				var selling_price=(gst_selling_price*100)/(100+gst_rate);
				var gst_amt=float(selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
				var selling_price=float(gst_selling_price+gst_amt);
			}

			document.f_p["region_mprice["+id+"]["+type+"]["+bid+"]"].value = round(selling_price, 2);
		}else{
			var selling_price = float(document.f_p["region_mprice["+id+"]["+type+"]["+bid+"]"].value);
			
			if (inclusive_tax=='yes') {
				var gst_selling_price=(selling_price*100)/(100+gst_rate);
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(selling_price) * gst_rate / 100;
				var gst_selling_price=float(selling_price+gst_amt);
			}
			
			document.f_p["region_gst_mprice["+id+"]["+type+"]["+bid+"]"].value=round(gst_selling_price,2);
		}
		
		document.f_p["region_gst_amount["+id+"]["+type+"]["+bid+"]"].value = round(gst_amt, 2);
	}
}

function search_sku_item(){
	if(document.f_a['sku_item_id'].value == "") return false;

	document.f_a.submit();
}

function ajax_search_barcode_item(grn_barcode){
	if(grn_barcode.trim() == "") return false;

	// construct params
	var params = {
		a: 'ajax_search_barcode_item',
		grn_barcode: grn_barcode,
		grn_barcode_type: document.f_a['grn_barcode_type'].value
	};
	
	// call ajax
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
					if(ret['html']){
						$('div_si_list_content').update(ret['html']);
						$('div_si_list').show();
						center_div('div_si_list');
						curtain(true);
					}else{
						document.f_a['sku_item_id'].value = ret['sku_item_id'];
						document.f_a['sku_item_code'].value = ret['sku_item_code'];
						search_sku_item();
					}
					return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

			// prompt the error
			alert(err_msg);
		}
	});
}

function si_list_item_clicked(sid, sku_item_code){
	document.f_a['sku_item_id'].value = sid;
	document.f_a['sku_item_code'].value = sku_item_code;
	curtain_clicked();
	search_sku_item();
}

function item_gst_price_changed(sid, bid){
	var inp = document.f_p['gst_price['+sid+']['+bid+']'];

	// round 2
	inp.value = round2(inp.value);
	
	// calculate gst
	calculate_gst(sid, 'normal', bid, inp);
	
	//if(bid == curr_branch_id){
		// Copy Price to Branch
		copy_to_branch($('inp_price-normal-'+sid+'-'+bid), 'price['+sid+']['+bid+']', sid, bid);
	//}
}

function item_selling_price_changed(sid, bid, no_need_update_rsp_discount){
	if(!no_need_update_rsp_discount)	no_need_update_rsp_discount = false;
	
	var inp = document.f_p['price['+sid+']['+bid+']'];

	// round 2
	inp.value = round2(inp.value);
	
	var use_rsp = int(document.f_p['use_rsp['+sid+']'].value);
	if(use_rsp && !no_need_update_rsp_discount){
		var rsp_price = float(document.f_p['rsp_price['+sid+']'].value);
		if(inp.value > rsp_price){
			alert('Your Selling Price ('+round2(inp.value)+') is more than RSP ('+round2(rsp_price)+')\nSelling Price will be auto adjust to '+round2(rsp_price));
			inp.value = round2(rsp_price);
		}
		
		// Calculate RSP
		calculate_rsp(sid, bid, 'rsp_discount');
	}
	
	// calculate gst
	calculate_gst(sid, 'normal', bid, inp);
	
	//if(bid == curr_branch_id){
		// Copy Price to Branch
		copy_to_branch(inp, 'price['+sid+']['+bid+']', sid, bid);
	//}
}

function rsp_discount_changed(sid, bid){
	var inp_rsp_discount = document.f_p['rsp_discount['+sid+']['+bid+']'];
	var discount_pattern = validate_discount_format(inp_rsp_discount.value);
	
	if(inp_rsp_discount.value != '' && discount_pattern == ''){
		alert('Invalid Discount Pattern');
	}
	inp_rsp_discount.value = discount_pattern;
	
	// Calculate RSP
	calculate_rsp(sid, bid, 'selling_price');
}

function calculate_rsp(sid, bid, target_input){
	// RSP
	var rsp_price = float(document.f_p['rsp_price['+sid+']'].value);
	var inp_rsp_discount = document.f_p['rsp_discount['+sid+']['+bid+']'];
	var inp_selling_price = document.f_p['price['+sid+']['+bid+']'];
	
	if(target_input == 'selling_price'){
		// Get RSP Discount
		rsp_discount_amt = float(get_discount_amt(rsp_price, inp_rsp_discount.value));
		
		// Calculate Selling Price by using RSP - RSP Discount
		var selling_price = float(rsp_price - rsp_discount_amt);
		inp_selling_price.value = round2(selling_price);
		
		// Trigger change selling price event
		item_selling_price_changed(sid, bid, true);
	}else if(target_input == 'rsp_discount'){
		// Get Selling Price
		var selling_price = float(inp_selling_price.value);
		
		// Calculate RSP Discount by using RSP - Selling Price
		var rsp_discount = round(float(rsp_price - selling_price), 2);
		inp_rsp_discount.value = rsp_discount;
	}
}

</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>


<div id="div_si_list" style="padding:5px;border:1px solid #000;overflow:hidden;width:600px;height:500px;position:absolute;background:#fff;display:none;z-index:10000;">
	<div style="text-align:right"><img src="/ui/closewin.png" onclick="curtain_clicked();"></div>
	<div id="div_si_list_content" style="max-height:500px;overflow:auto;"></div>
</div>

<div id="history_popup" style="padding:5px;border:1px solid #000;overflow:hidden;width:300px;height:300px;position:absolute;background:#fff;display:none;">
	<div style="text-align:right"><img src="/ui/closewin.png" onclick="Element.hide('history_popup')"></div>
	<div id="history_popup_content" style="max-height:280px;overflow:auto;"></div>
</div>

<div id="div_region_price_popup" style="padding:0px;border:1px solid #000;overflow:hidden;width:700px;height:520px;position:absolute;background:#fff;display:none;z-index:10000;">
	<div id="div_region_price_popup_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
		<span id="change_title" style="float:left;">Region Price Type Information</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_region_price_popup_content" style="max-height:480px;overflow:auto;"></div>
</div>

<div id="price_history" style="padding:0px;border:1px solid #000;overflow:hidden;width:480px;height:320px;position:absolute;background:#fff;display:none;z-index:10001;">
	<div id="price_history_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
		<span id="change_title" style="float:left;">Price History</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="Element.hide('price_history')" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
<div id="price_history_content" style="height:290px;overflow:auto;"></div>
</div>


<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method="post">
			<input type="hidden" name="a" value="find" onsubmit="return false;">
			<table>
				<th align="left">Search SKU</th>
				<td>
					<input name="sku_item_id" size="3" type="hidden">
					<input name="sku_item_code" size="13" type="hidden">
					<input class="form-control" id="autocomplete_sku" name="sku" size="50" onclick="this.select()" style="font-size:14px;width:500px;" autocomplete="off" onkeypress="if(event.keyCode==13) search_sku_item();">
					<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
				</td>
				<td><input class="btn btn-primary" type="button" value="Find" onclick="search_sku_item();"></td>
			</tr><tr>
				<td>&nbsp;</td>
				<td>
					<input onchange="reset_sku_autocomplete()" type="radio" name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
					<input onchange="reset_sku_autocomplete()" type="radio" name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
					<input onchange="reset_sku_autocomplete()" type="radio" name="search_type" value="3"> ARMS Code
					<input onchange="reset_sku_autocomplete()" type="radio" name="search_type" value="4"> Description
				</td>
			</tr>
			
			<tr>
				<th align="left">Scan Barcode</th>
				<td>
					<div class="form-inline">
						<input class="form-control" id="grn_barcode" name="grn_barcode" onkeypress="if(event.keyCode==13) ajax_search_barcode_item(this.value);">
					&nbsp;&nbsp; <input class="btn btn-primary" type="button" value="Find" onclick="ajax_search_barcode_item(document.f_a['grn_barcode'].value);"></td>
					</div>
			</tr>
			
			<tr>
				<td>&nbsp;</td>
				<td>
					{if $config.enable_grn_barcoder}
						<input type="radio" name="grn_barcode_type" value="0" onChange="grn_barcode_type_changed();" /> GRN Barcoder &nbsp;&nbsp;&nbsp;&nbsp;
					{/if}
					<input type="radio" name="grn_barcode_type" value="1" checked onChange="grn_barcode_type_changed();" /> ARMS Code / MCode / Art.No / {$config.link_code_name} &nbsp;&nbsp;&nbsp;&nbsp;
				</td>
			</tr>
			
			</table>
			</form>
	</div>
</div>

{if $smarty.request.a}
{if !$items}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$smarty.request.code} not found</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<div class="alert alert-primary rounded mx-3">
	Note: Check that you have access to the correct departments.
</div>

{else}
<br>

<div class="alert alert-primary rounded mx-3">
	<ul>
		{if $config.sku_multiple_selling_price}
			<li> Mprice {if !$config.consignment_modules}or Qprice{/if} set to 0 will use normal price.</li>
		{/if}
		{if $config.sku_change_price_always_apply_to_same_uom}
			<li> Change selling price will automatically apply to all SKU with same UOM and ArtNo.</li>
		{/if}
		<li>Please key in unit price for Multi Quantity Price Setting </li>
	</ul>
</div>

<br>

{if $BRANCH_CODE eq 'HQ' and $config.sku_use_region_price and $config.masterfile_branch_region and $show_by_region_code}
	<p>
		<a href="?a=find&sku_item_id={$smarty.request.sku_item_id}">All Branches</a> > ({$show_by_region_code}) {$config.masterfile_branch_region.$show_by_region_code.name} 
	</p>
{/if}

<div class="tab row mx-3 mb-2" style="white-space:nowrap; background:">

<!-- Selling Price-->
<a href="javascript:list_sel(1,'mprice')" id=lst1 class="btn btn-outline-primary btn-rounded">Selling Price</a>

{if !$config.consignment_modules}
	<!-- QPrice -->
	{if $config.sku_multiple_quantity_price}
&nbsp;&nbsp;	<a href="javascript:list_sel(2,'qprice')" id=lst2 class="btn btn-outline-primary btn-rounded">Multi Quantity Price</a>
	{/if}
	
	<!-- Active Promotion -->
	&nbsp;&nbsp;<a href="javascript:list_sel(3,'promo')" id=lst3 class="btn btn-outline-primary btn-rounded">Active Promotions</a>
{/if}

</div>
<div class="card mx-3">
	<div class="card-body">
		<div style="">
			<!-- Selling price and mprice -->
			<div id=mprice class=tabcontent>
				{include file=masterfile_sku_items_price.mprice.tpl}
			</div>
			
			<!-- Qprice -->
			{if $config.sku_multiple_quantity_price}
				<div id=qprice class=tabcontent style="display:none">
				{include file=masterfile_sku_items_price.qprice.tpl}
				</div>
			{/if}
			
			<!-- Active Promotion -->
			<input type=hidden id=item_ids value="{section loop=$items name=i}{$items[i].id},{/section}">
			<div id=promo class=tabcontent style="display:none">
			</div>
		</div>
	</div>
</div>
{/if}
<div id="status_msg"></div>
{/if}

{include file=footer.tpl}
{literal}
<script>
function list_sel(n,s)
{
	var i;
	for(i=0;i<=3;i++)
	{
		if ($('lst'+i)!=undefined)
		{
			if (i==n)
			{
			    $('lst'+i).addClassName('selected');
			}
			else
			{
			    $('lst'+i).removeClassName('selected');
			}
		}
	}
	$$('.tabcontent').each(function(e) {
		e.style.display = 'none';
	});
	$(s).style.display = '';
	
	if (n==3)
	{
		$('promo').innerHTML = _loading_;
		new Ajax.Updater('promo', '/promotion.php?a=ajax_get_promotions&id='+$('item_ids').value+'&date_to=9999-12-31&template=masterfile_sku_items.promotions.tpl');
	}
}
reset_sku_autocomplete();
new Draggable('price_history', {handle: 'price_history_header'});
new Draggable('div_region_price_popup', {handle: 'div_region_price_popup_header'});
</script>
{/literal}

{if $config.sku_change_price_default_check_all}
<script>
toggle_check_all_col_row(true);
</script>
{/if}
