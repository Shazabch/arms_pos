{*
3/21/2011 5:24:41 PM Andy
- Move all overlap promotion item script to new template.
- Add show/hide overlap promotion.

4/28/2011 5:46:56 PM Andy
- Add checking to only allow owner or system admin to do delete and cancel promotion.
- Hide "dicount by value" when choose "FOC".
- Add "Bundled Price", discount qty must more than 1.
- Add "Special FOC".
- Add discount target filter. (sku type, price type, price range)
- Add create promotion by wizard.

7/19/2011 4:22:19 PM Andy
- Fix special foc should not be able to key in qty from.
- Change discount qty : 'all items' can use 'qty from'.

10/10/2011 5:24:41 PM Andy
- Make mix and match category can search until level 10.

12/2/2011 12:09:04 PM Justin
- Fix title maxlength bugs.

2/17/2012 3:35:43 PM Andy
- Add promotion can set allowed member type.
- Add can set different category reward point.

7/26/2012 10:21:34 AM Justin
- Enhanced the Membership Type to show additional description if found.

8/23/2012 2:39 PM Justin
- Enhanced for drop down menu of Member Reward Point to base on privilege PROMOTION_MEMBER_POINT_REWARD.
- Changed the wording "V149" into "V168".

8/30/2012 5:45 PM Justin
- Changed the privilege name from "PROMOTION_MEMBER_POINT_REWARD" into "MEMBER_POINT_REWARD_EDIT".

11/7/2013 3:58 PM Andy
- Enhance to can select SKU Group as Discount Target and Condition Rule.

12/12/2013 4:47 PM Andy
- Enhance to check config "mix_and_match_allow_add_line" and allow user to add LINE from category.

1/7/2013 2:33 PM Andy
- Remove popup icon from member point reward.

3/5/2014 5:17 PM Justin
- Enhanced to have include parent & child feature.

5/26/2014 2:16 PM Fithri
- able to select item(s) to reject & must provide reason for each rejected item

6/25/2015 2:57 PM Justin
- Bug fixed on system will return javascript errors while in view mode.

8/4/2015 3:00 PM Andy
- Change the cancel promotion checking to check config.doc_reset_level and privilege 'PROMOTION_CANCEL'.

07/04/2016 11:30 Edwin
- Enhanced on show warning message when create new Mix and Match and above of the buttons

9/19/2016 09:05 Qiu Ying
- Enhanced to set selling inclusive or exclusive for bundled price

11/30/2016 3:28 PM Andy
- Escape promotion title to fix double quotes.

12/1/2016 13:35 Qiu Ying
- Fixed bug on when not selecting bundled price, error message prompt out after click on confirm button 

2/17/2017 3:12PM Justin
- Enhanced not to show the cancel buttons while the opened promotion is not created from current branch.

2/27/2017 4:57 PM Zhi Kai
- Change wording of 'General Informations' to 'General Information'.

5/3/2017 09:41 AM Qiu Ying
- Bug fixed on showing "{" in Approved Mix & Match

4/19/2017 2:22 PM Khausalya 
- Enhanced changes from RM to use config setting.

6/5/2018 3:55 PM Andy
- Fixed when selected FOC, discount value input should hide, it no hide when branch is not under gst.

2/19/2019 5:55 PM Andy
- Enhanced Print Promotion to use shared template.

4/29/2020 4:29 PM Andy
- Modified layout to compatible with new UI.

29/04/2020 06:19 PM Sheila
- Modified layout to compatible with new UI.

05/07/2020 5:47 PM Sheila
- updated button color

05/11/2020 11:06 AM Justin
- Enhanced to have "all" checkbox to select all branches.
*}

{if !$form.approval_screen}
{include file='header.tpl'}
{include file='promotion.print_dialog.tpl'}
{/if}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}
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
tr.promo_item_row:hover{
	background: #ffc;
}
.group_checked{
	border: 1px solid red;
}

.btn_open_wizard{
	border:2px outset #000;
	background-color:#091; 
	color:#fff;
	font-weight: bold;
}

#div_promotion_wizard_container_body{
	height: 530px;
	overflow-x:auto;
	overflow-y:auto;
}

.div_pw_disc_target_item{
	border:4px outset grey;
	padding:2px;
	margin: 5px;
}
{/literal}
</style>

<script type="text/javascript" language="javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var allow_edit = '{$allow_edit}';
var form_label = '{$form.label}';
var is_approval_screen = '{$form.approval_screen}';
var mix_and_match_allow_add_line = int('{$config.mix_and_match_allow_add_line}');
var first_time = int('{$form.first_time}');
var is_under_gst = int('{$is_under_gst}');

{literal}

MIX_MATCH_MAIN_MODULE = {
	branch_id: 0,
	promo_id: 0,
	form_element: undefined,
	all_items_val: -1,
	group_total_val: -2,
	customize_discount_qty_last_click_time: 0,
	initialize: function(){
	    this.form_element = document.f_a;   // assign form element
	    if(!this.form_element){
			alert('Moudule failed to load.');
			return false;
		}
		
		// save the id into object
		this.branch_id = int(this.form_element['branch_id'].value);
		this.promo_id = int(this.form_element['id'].value);
		
		if(allow_edit==1 && !is_approval_screen){  // edit mode
		    // event when user click refresh table
		    if($('btn_refresh')){
                $('btn_refresh').observe('click', function(){
					MIX_MATCH_MAIN_MODULE.refresh_page();
				});
			}
			

			// event when user click add new mix and match group
			if($('btn_new_mnm_group')){
                $('btn_new_mnm_group').observe('click', function(){
		            MIX_MATCH_MAIN_MODULE.add_new_mix_n_match_group();
				});
			}
            
			
			// initial calendar
			Calendar.setup({
				    inputField     :    "inp_date_from",     // id of the input field
				    ifFormat       :    "%Y-%m-%e",      // format of the input field
				    button         :    "img_date_from",  // trigger for the calendar (button ID)
				    align          :    "Bl",           // alignment (defaults to "Bl")
				    singleClick    :    true
				});

				Calendar.setup({
				    inputField     :    "inp_date_to",     // id of the input field
				    ifFormat       :    "%Y-%m-%e",      // format of the input field
				    button         :    "img_date_to",  // trigger for the calendar (button ID)
				    align          :    "Bl",           // alignment (defaults to "Bl")
				    singleClick    :    true
			});
			
			// initialize mix and match dialog
			MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG.initialize(this.branch_id, this.promo_id);
			
			// initialize mix and match wizard dialog
			MIX_MATCH_MAIN_WIZARD_DIALOG.initialize(this.branch_id, this.promo_id);
			
			// initial sku autocomplete popup
			SKU_AUTOCOMPLETE_POPUP.initialize();
		
			// initial category autocomplete popup
			CAT_BRAND_AUTOCOMPLETE_POPUP.initialize();
		}else{
			if (typeof promotion_approval_allow_reject_by_items != "undefined" && promotion_approval_allow_reject_by_items) {
				Form.getElements(this.form_element).each(function(item) {
					if (!$(item).hasClassName('rejected_item')) {
						$(item).disable();
					}
				});
			}
			else {
				// disable form
				Form.disable(this.form_element);
			}
		
		}
		
		if(form_label=='approved'){
			var group_selection = $(this.form_element).getElementsBySelector('input[name="group_selection[]"]');
			if(group_selection){
			    for(var i=0; i<group_selection.length; i++){
                    group_selection[i].disabled = false;
				}
			}
		}
		
		// initial print dialog
		//MIX_MATCH_MAIN_PRINT_PROMO_DIALOG.initialize();
		PROMO_PRINT.initialise();
		if(allow_edit == 1){
			PROMO_PRINT.show_unsave_remark();
		}
		
		if (first_time) {
		  curtain(true, 'curtain2');
		  center_div($('div_warning_message_dialog').show());
        }
	},
	//
	get_group_selection_checkbox: function(){

	},
	check_header: function(){
		if(!check_required_field(this.form_element))    return false;   // check all required field
		
		// check branches
		var got_branch_checked = false;
		var all_chx = $$('input.chx_branch');
		for(var i=0; i<all_chx.length; i++){
			if(all_chx[i].checked){
                got_branch_checked = true;
                break;
			}
		}
		
		// no branch is selected
		if(!got_branch_checked){
			alert('Please at least select one branch.');
			return false;
		}
		
		return true;
	},
	refresh_page: function(){
	    // validate header
		if(!this.check_header())    return false;
		
		this.form_element['a'].value = 'refresh';
		this.form_element.submit();
	},
	add_new_mix_n_match_group: function(){
	    // show loading icons
	    $('btn_new_mnm_group').disabled = true;
		$('span_adding_mnm_loading').show();
		
		// construct params
		var params = {
		    a: 'add_new_mix_n_match_group',
			branch_id: this.branch_id,
			promo_id: this.promo_id
		};
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
			    // hide the loading icon
			    $('span_adding_mnm_loading').hide();
			    // enable back the button
			    $('btn_new_mnm_group').disabled = false;
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['new_group_id']){ // success
	                    var new_group_id = int(ret['new_group_id']);
	                    // cannot create new group
						if($('div_promo_group-'+new_group_id)){
							alert('You cannot add multiple new group, please enter some data to your current group first');
							return;
						}else{
                            new Insertion.Bottom('promo_items_group_list', ret['html']);
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
	},
	delete_promo_group: function(group_id){
	    if(!group_id)   return false;
	    
	    var div_promo_group = $('div_promo_group-'+group_id);
		if(!div_promo_group)    return false;
		
		var img = $('img_delete_promo_group-'+group_id);
		if(!img)    return false;
		
		if(img.src.indexOf('clock')>=0){    // already deleting
			alert('Please wait while in progress...');
			return false;
		}
	    // aks confirmation
		if(!confirm('Are you sure?'))   return false;
		
		img.src = '/ui/clock.gif';
		
		// construct params
		var params = {
			a: 'delete_promo_group',
			branch_id: this.branch_id,
			promo_id: this.promo_id,
			group_id: group_id
		};
		
		// call ajax to delete whole group
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
			    var str = msg.responseText.trim();
    			if(str=='OK'){
                    // remove whole box
					Effect.DropOut(div_promo_group, {
						duration:0.5,
						afterFinish: function() {
							$(div_promo_group).remove();
						}
					});
				}else{
					alert(str);
				}
			}
		});
	},
	// event when user seach discount item
	search_new_promo_item: function(group_id){
        if(!group_id)   return false;
        MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG.open(group_id, 0);   // open new item
	},
	// event when user click add receipt discount
	add_new_discount_item: function(group_id, params, callback){
		if(!group_id)   return false;
		
		// is special foc discount
		if(params['disc_target_type']=='special_foc'){
			// ask user to enter description info
			var special_foc_description = (prompt('Please enter Special FOC discount description') || '').trim();
			// user not enter
			if(!special_foc_description)	return false;
			
			// this script not working in prototype >.<
			//params['disc_target_info'] = {'description': special_foc_description};
			
			// work around 
			params['disc_target_info[]'] = [
				'description',
				special_foc_description
			];
			
		}
		
		// disabled button
		var btn_add = $('btn_add_receipt_discount-'+group_id);
		var btn_search = $('btn_search_new_discount_item-'+group_id);
		
		btn_add.disabled= true;
		btn_search.disabled = true;
		// show group item loading
		var loading_icon = $('span_group_item_loading-'+group_id).show();
		
		// construct params
		params['a'] = 'ajax_add_discount_item';
		params['branch_id'] = this.branch_id;
		params['promo_id'] =  this.promo_id;
		params['date_from'] = this.form_element['date_from'].value;
		params['date_to'] = this.form_element['date_to'].value;
		if (this.form_element['global_gst_inclusive_tax'])
			params['disc_by_inclusive_tax'] = this.form_element['global_gst_inclusive_tax'].value;
		
		// call ajax
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
                // hide the loading icon
			    $(loading_icon).hide();
			    // enable back the button
			    btn_add.disabled = false;
                btn_search. disabled = false;
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    	 Insertion.Bottom('tbody_promo_group_items-'+group_id, ret['html']);
	                    MIX_MATCH_MAIN_MODULE.reset_row_no(group_id);
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
			    
			    // trigger callback function
			    if(callback)    callback();
			}
		});
	},
	// function to get group id
	get_group_id_by_ele: function(ele){
        var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='div'){
                if($(parent_ele).hasClassName('div_promo_group')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var group_id = parent_ele.id.split('-')[1];
		return group_id;
	},
	// function to get item id
	get_item_id_by_ele: function(ele){
        var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('promo_item_row')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}

		if(!parent_ele) return 0;

		var item_id = parent_ele.id.split('-')[1];
		return item_id;
	},
	// event when user click delete item
	delete_discount_item: function(item_id){
		if(!item_id)    return false;
		var tr_row = $('tr_promo_item_row-'+item_id);
		if(!tr_row) return false;
		
		if(!confirm('Are you sure to remove this item?'))   return false;
		// get group id
		var group_id = this.get_group_id_by_ele(tr_row);
		// construct params
		var params = {
			a: 'delete_discount_item',
			branch_id: this.branch_id,
			promo_id: this.promo_id,
			item_id: item_id
		}
		
		// show group item loading
		var loading_icon = $('span_group_item_loading-'+group_id).show();
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
			    var str = msg.responseText.trim();
			    
				$(loading_icon).hide();
				
				if(str=='OK'){
					$(tr_row).remove();
					MIX_MATCH_MAIN_MODULE.reset_row_no(group_id);
				}else{
					alert(str);
				}
			}
		});
		
		// delete overlap promo item as well
		if($('tr_overlap_pi-'+item_id)){
			$('tr_overlap_pi-'+item_id).remove();
		}
	},
	// function to recalculate row number
	reset_row_no: function(group_id){
	    // reset row number
		$$('#div_promo_group-'+group_id+' span.row_no').each(function(ele, i){
			$(ele).update((i+1));
		});
		
		// rebuild move sequence image up
		$$('#div_promo_group-'+group_id+' img.img_move_sequence_up').each(function(ele, i){
			if(i==0)    ele.style.visibility = 'hidden';
			else    ele.style.visibility = '';
		});
		
		// rebuild move sequence image down
		var item_count = this.get_item_count_in_group(group_id);
		$$('#div_promo_group-'+group_id+' img.img_move_sequence_down').each(function(ele, i){
			if(i==item_count-1)    ele.style.visibility = 'hidden';
			else    ele.style.visibility = '';
		});
	},
	// event when user click add new condition
	add_disc_condition: function(item_id){
		if(!item_id)    return false;
		
		// cannot find the row
		var tr_row = $('tr_promo_item_row-'+item_id);
		if(!tr_row) return false;
		// get group id
		var group_id = this.get_group_id_by_ele(tr_row);
		if(!group_id)   return false;
		
		MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG.open(group_id, item_id, 'item_disc_condition')
	},
	// event when user click add new condition by receipt
	add_disc_condition_by_receipt: function(item_id){
        if(!item_id)    return false;

		// cannot find the row
		var tr_row = $('tr_promo_item_row-'+item_id);
		if(!tr_row) return false;
		
		var group_id = this.get_group_id_by_ele(tr_row);
		if(!group_id)   return false;
		// get new condition row id
        var condition_row_num = int(this.get_new_condition_row_num(item_id));
		
		// clone receipt condition row
		var new_html = $('ul_disc_condition_receipt_sample').innerHTML;
		new_html = new_html.replace(/tmp_row_num/g,condition_row_num);
		new_html = new_html.replace(/tmp_group_id/g,group_id);
		new_html = new_html.replace(/tmp_item_id/g,item_id);
		
		// add the clone html
		new Insertion.Bottom('ul_disc_condition-'+item_id, new_html);
		// remove disabled control
		this.remove_disabled($('ul_disc_condition-'+item_id));
	},
	remove_disabled: function(container){
		$(container).getElementsBySelector("input", "select", "textarea")
		    .each(function(ele){
				ele.disabled = false;
			});
	},
	delete_disc_condition_row: function(ele){
        var parent_ele = ele
		var delete_ele = undefined;

		// get item id
		var item_id = this.get_item_id_by_ele(ele);
		// check whether can delete
		if($$('#ul_disc_condition-'+item_id+' li.is_condition_row').length<=1){
			alert('You cannot delete last condition!');
			return false;
		}
		
		while(parent_ele){    // loop parebt until it found the element
		    if(parent_ele.tagName.toLowerCase()=='li'){
                if($(parent_ele).hasClassName('is_condition_row')){    // found the ele
                    delete_ele = parent_ele;
					break;
					// break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		$(delete_ele).remove();
		
		// check condition row
		this.check_disc_condition_rule(item_id);
	},
	add_new_item_conditon_row: function(group_id, item_id, params){
		if(!group_id || !item_id || !params)    return false;
		
		var loading_icon = $('span_group_item_loading-'+group_id).show();
        var condition_row_num = int(this.get_new_condition_row_num(item_id));
         
		// construct params
		params['a'] = 'ajax_add_item_condition_row';
		params['branch_id'] = this.branch_id;
		params['promo_id'] =  this.promo_id;
		params['condition_row_num'] = condition_row_num;
		
		// call ajax
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
                // hide the loading icon
			    $(loading_icon).hide();
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    new Insertion.Bottom('ul_disc_condition-'+item_id, ret['html']);
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
	},
	check_form: function(act){
	    // validate header
		if(!this.check_header())    return false;
		
	    // check item count
	    var promo_item_row = $$('#promo_items_group_list tr.promo_item_row');
	    if(promo_item_row.length<=0){
			alert('Promotion must at least contain one item.');
			return false;
		}
		
	    // check all required field
		if(!check_required_field(this.form_element))    return false;
		
		for(var i=0; i<promo_item_row.length; i++){
			var r = promo_item_row[i]; 	
			var group_id = this.get_group_id_by_ele(r);
			var item_id = this.get_item_id_by_ele(r);
			var element_name_extend = '['+group_id+']['+item_id+']';
			
			var item_row_info = this.process_promo_item_row(r);	// process and get the row all info
			if(!item_row_info)	continue;
			
			// check discount value, if not hiding then must key in
			if(item_row_info['disc_by_value']['container'].style.display==''){	// showing
				if(item_row_info['disc_by_value']['value']<=0){
					alert('Invalid '+item_row_info['disc_by_value']['ele'].title+', cannot less than or zero.');
					item_row_info['disc_by_value']['ele'].select();
					return false;
				}
			}
			/* ======================================= */
			// check wrong discount concept
			// check discount receipt cannot more than receipt condition
			if(item_row_info['disc_by_type']['value']=='amt' && item_row_info['condition_row'].length==1){
				// get the first condition row info
				var condition_row_info = item_row_info['condition_row'][0];
				if(condition_row_info['item_type']['value']=='receipt' && condition_row_info['condition_type']['value']=='amt'){
					if(item_row_info['disc_by_value']['value'] >= condition_row_info['condition_value']['value']){
						alert('Discount amount cannot same or more than condition amount.');
						item_row_info['disc_by_value']['ele'].select();
						return false;
					}
				}
			}
			
			if (is_under_gst == 1){
				if (item_row_info['disc_by_type']['value'] == 'bundled_price' && this.form_element['disc_by_inclusive_tax'+element_name_extend].value == 'Not Set' && act == 'confirm'){
					alert('Please select either Yes or No for Selling Price Inclusive Tax');
					return false;
				}
			}
		}
		
		return true;
	},
	submit_form: function(act, skip_checking){
		if(!act)    return false;
		
		if(!skip_checking){
            // check form before submit
			if(!this.check_form(act))  return false;
		}
		
		// ask last confirmation
		if(!confirm('Are you sure?'))   return false;
		
		// checking success
		this.form_element['a'].value = act;
		// enable the form before submit
		Form.enable(this.form_element);
		this.form_element.submit();
	},
	// function to get the next condition row num
	get_new_condition_row_num: function(item_id){
		var current_max_row_num = 0;
		$$('#ul_disc_condition-'+item_id+' li.is_condition_row').each(function(li){
			var condition_row_num = int($(li).readAttribute('condition_row_num'));
			if(condition_row_num>current_max_row_num)  current_max_row_num = condition_row_num;
		});
		return current_max_row_num+1;
	},
	// function get item count in group
	get_item_count_in_group: function(group_id){
		return int($$('#div_promo_group-'+group_id+' tr.promo_item_row').length);
	},
	// function to get how many group in this promotion
	get_group_count: function(){
		return int($$('#promo_items_group_list div.div_promo_group').length);
	},
	// function to cancel selected promotion group
	cancel_selected_group: function(){
		if(form_label != 'approved')    return false;   // only available in approved mode
		
		var group_selection = $(this.form_element).getElementsBySelector('input[name="group_selection[]"]');
		if(!group_selection){
			alert('Cannot find group selection checkbox.');
			return false;
		}
		
		// check whether user got select group or not
		var cancel_group_count = 0;
		var total_group_count = this.get_group_count();
		for(var i=0; i<group_selection.length; i++){
            if(group_selection[i].checked){ // got checked
                cancel_group_count++;
			}
		}
		
		
		// no group is selected
		if(cancel_group_count<=0){
			alert('Please select at least one group to cancel.');
			return false;
		}
		
		// all group are selected to be cancel, ask user to do cancel promotion
		if(cancel_group_count>=total_group_count){
			alert('You cannot cancel all group, if you wish to do so then just cancel whole promotion.');
			return false;
		}

		this.submit_form('cancel_group', 1);
	},
	// when user tick or untick group checkbox
	group_checkbox_changed: function(group_id){
		if(!group_id)   return false;   // no group id
		
		var inp = $('inp_group_selection-'+group_id);   // cannot find the checkbox
		if(!inp)    return false;
		
		var c = inp.checked;
		if(c)   $('div_promo_group-'+group_id).addClassName('group_checked');   // checked
		else    $('div_promo_group-'+group_id).removeClassName('group_checked');    // un-checked
	},
	// function to move item sequence up or down
	move_item_sequence: function(item_id, type){
		if(!item_id || !type)   return false;
		
		var tr_row = $('tr_promo_item_row-'+item_id);
		// cannot find the row
		if(!tr_row) return false;

		var group_id = this.get_group_id_by_ele(tr_row);
		// cannot find group
		if(!group_id)   return false;
		var item_to_swap
		
		if(type=='up'){
			// find previous item
			item_to_swap = $(tr_row).previous('tr.promo_item_row');
		}else{
            // find next item
            item_to_swap = $(tr_row).next('tr.promo_item_row');
		}
		
		if(item_to_swap){
		    // swap these 2 element position
            swap_ele(tr_row, item_to_swap);
            // recalucalte row number and move sequence image
            this.reset_row_no(group_id);
            // highlight the moved element
            // got bugs after highlight, css :hover cannot function
            /*new Effect.Highlight(tr_row, {
                endcolor: "#ffffff"
			});*/
			
			var item_id_1 = this.get_item_id_by_ele(tr_row);
			this.move_overlap_promo(item_id_1);
			var item_id_2 = this.get_item_id_by_ele(item_to_swap);
			this.move_overlap_promo(item_id_2);
		}else{
			alert('Swap cannot be done');
		}
	},
	// function to do check whether item limit over group limit or not
	check_item_limit: function(item_id){
	    if(!item_id)    return false;
	    
	    var tr_row = $('tr_promo_item_row-'+item_id);
		// cannot find the row
		if(!tr_row) return false;

		var group_id = this.get_group_id_by_ele(tr_row);
		// cannot find group
		if(!group_id)   return false;
		
	    // get group limit
	    var group_limit = int(this.form_element['receipt_limit['+group_id+']'].value);
	    
	    if(!group_limit)    return false;   //group have no limit
	    // get item limit
	    var ele_item_limit = $('inp_item_limit-'+item_id);
	    if(!ele_item_limit) return false;
		var item_limit = int($('inp_item_limit-'+item_id).value);
		
		// item limit over group limit
		if(item_limit>0 && item_limit > group_limit){
		    alert('Item limit ('+item_limit+') cannot over group/receipt limit ('+group_limit+')');
            ele_item_limit.value = 0;
		}
	},
	// function to do whether group_id less than item limit or not
	check_group_limit: function(group_id){
		if(!group_id)   return false;
		
		var group_limit = int(this.form_element['receipt_limit['+group_id+']'].value);
	    if(!group_limit)    return false;   //group have no limit
	    
	    // loop all row
	    var max_item_limit = 0;
	    $$('#tbody_promo_group_items-'+group_id+' tr.promo_item_row').each(function(ele, i){
	        // get row item id
	        var item_id = MIX_MATCH_MAIN_MODULE.get_item_id_by_ele(ele);
	        // get item limit
			var item_limit = int($('inp_item_limit-'+item_id).value);
			if(item_limit > max_item_limit){
                max_item_limit = item_limit;
			}
		});
		
		// got item limit over group limit
		if(max_item_limit>group_limit){
			alert('You cannot set group/receipt limit to ('+group_limit+'), because one of your item limit is set to ('+max_item_limit+')');
			// set group limit to the item limit
			this.form_element['receipt_limit['+group_id+']'].value = max_item_limit;
			return false;
		}
	},
	// function to check user chosen "discount by" type
	check_disc_by_type: function(item_id){
		if(!item_id)    return false;
		
		var tr_row = $('tr_promo_item_row-'+item_id);
		// cannot find the row
		if(!tr_row) return false;

		var group_id = this.get_group_id_by_ele(tr_row);
		// cannot find group
		if(!group_id)   return false;
		
		// get type
		var sel_disc_by_type = this.form_element['disc_by_type['+group_id+']['+item_id+']'];
		var disc_by_type = sel_disc_by_type.value;
		
		// get row info
		var item_row_info = this.process_promo_item_row(tr_row);
		
		var hide_all_items = false;
		var hide_group_total = false;
		var hide_discount_value = false;
		var hide_opt_1 = false;
		var hide_selling_price_inclusive_tax = true;
		
		if(disc_by_type=='foc'){    // is foc
            hide_all_items = true;
            hide_group_total = true;
            hide_discount_value = true; 
		}else if (disc_by_type == 'bundled_price'){
			hide_all_items = true;
            hide_group_total = true;
            hide_opt_1 = true;
			hide_selling_price_inclusive_tax = false;
		}else if (disc_by_type == 'fixed_price'){
			hide_group_total = true;
		}
		
		if(is_under_gst){
			if(!hide_selling_price_inclusive_tax){
				$('div_disc_by_bundled_price_inclusive_tax['+group_id+']['+item_id+']').show();
			}else{
				$('div_disc_by_bundled_price_inclusive_tax['+group_id+']['+item_id+']').hide();
			}
		}		
		
		// need hide all_items
		if(hide_all_items){
			$(item_row_info['disc_by_qty']['option']['all_items']).hide();
			if(item_row_info['disc_by_qty']['value'] == this.all_items_val){
				item_row_info['disc_by_qty']['ele'].value = 1;
			}
		}else{
			$(item_row_info['disc_by_qty']['option']['all_items']).show();
		}
		
		// need hide group_total
		if(hide_group_total){
			$(item_row_info['disc_by_qty']['option']['group_total']).hide();
			if(item_row_info['disc_by_qty']['value'] == this.group_total_val){
				item_row_info['disc_by_qty']['ele'].value = 1;
			}
		}else{
			$(item_row_info['disc_by_qty']['option']['group_total']).show();
		}
		
		// need hide discount qty 1
		if(hide_opt_1){
			$(item_row_info['disc_by_qty']['option']['1']).hide();
			if(item_row_info['disc_by_qty']['value'] == 1){
				item_row_info['disc_by_qty']['ele'].value = 2;
			}
		}else{
			$(item_row_info['disc_by_qty']['option']['1']).show();
		}
		
		// need hide discount value
		if(hide_discount_value){
			$(item_row_info['disc_by_value']['container']).hide();	// hide discount value span
            item_row_info['disc_by_value']['ele'].value = '';	// put 1 so it can pass the required field checking
		}else{
			 $(item_row_info['disc_by_value']['container']).show(); // show discount value input
		}
		
		// discount qty changed
        this.discount_qty_changed(item_id);
	},
	move_overlap_promo: function(item_id){
		if(!item_id)   return false;

		var tr_row = $('tr_promo_item_row-'+item_id);
		// cannot find the row
		if(!tr_row) return false;

		var group_id = this.get_group_id_by_ele(tr_row);
		// cannot find group
		if(!group_id)   return false;
		
		// create a temporary element call "t" and put it in-front of element
		var t = tr_row.parentNode.insertBefore(document.createTextNode(''), tr_row);
		
		// move row to in-front of t
		tr_row.parentNode.insertBefore(tr_row, t);
		
		// move overlap promo row in-front of t
		var tr_overlap_pi = $('tr_overlap_pi-'+item_id);
		if(tr_overlap_pi){  // only move if element exists
            tr_overlap_pi.parentNode.insertBefore(tr_overlap_pi, t);
		}
		
		// remove "t"
		t.parentNode.removeChild(t);
	},
	// function to show/hide group overlap promotion
	toggle_group_overlap_promo: function(group_id, show){
		if(!group_id)   return;
		
		var show_method = show ? 'show' : 'hide';
		
		// show/hide all overlap div
		$$('#div_promo_group-'+group_id+' div.div_overlap_pi_content').invoke(show_method);
		
		// toggle image src
		$$('#div_promo_group-'+group_id+' img.img_toggle_overlap_pi').each(function(ele){
			if(show_method=='show') ele.src = '/ui/collapse.gif';
			else    ele.src = '/ui/expand.gif';
		});
	},
	process_promo_item_row: function(tr){
		if(!tr)	return false;	// no element given
		var group_id = this.get_group_id_by_ele(tr);
		if(!group_id)	return false;	// no group id
		var item_id = this.get_item_id_by_ele(tr);
		if(!item_id)	return false;	// no item id
		
		var element_name_extend = '['+group_id+']['+item_id+']';
		
		var ret = {};
		
		// disc_target_type : receipt, sku, category, brand, category_brand
		ret['disc_target_type'] = {};
		ret['disc_target_type']['ele'] = this.form_element['disc_target_type'+element_name_extend];
		ret['disc_target_type']['value'] = ret['disc_target_type']['ele'].value.trim();
	
		// disc_target_value : sku item id, category id, etc...
		ret['disc_target_value'] = {};
		ret['disc_target_value']['ele'] = this.form_element['disc_target_value'+element_name_extend];
		ret['disc_target_value']['value'] = ret['disc_target_value']['ele'].value.trim();
		
		// get condition info
		var li_condition_row = $$('#ul_disc_condition-'+item_id+' li.is_condition_row');
		ret['condition_row'] = [];
		for(var i=0; i<li_condition_row.length; i++){
			// get each row of info
			ret['condition_row'].push(this.process_condition_row(li_condition_row[i], group_id, item_id));
		}
		
		// disc_by_type : amt, per, fixed_price, foc
		ret['disc_by_type'] = {};
		ret['disc_by_type']['ele'] = this.form_element['disc_by_type'+element_name_extend];
		ret['disc_by_type']['value'] = ret['disc_by_type']['ele'].value.trim();
		// disc_by_value : (user enter)
		ret['disc_by_value'] = {};
		ret['disc_by_value']['container'] = $('span_disc_by_value-'+item_id);
		ret['disc_by_value']['ele'] = this.form_element['disc_by_value'+element_name_extend];
		ret['disc_by_value']['value'] = float(ret['disc_by_value']['ele'].value.trim());
		// disc_by_qty : (user select)
		ret['disc_by_qty'] = {'option':{}};
		ret['disc_by_qty']['ele'] = this.form_element['disc_by_qty'+element_name_extend];
		ret['disc_by_qty']['value'] = float(ret['disc_by_qty']['ele'].value.trim());
		
		for(var i=0; i< ret['disc_by_qty']['ele'].length; i++){
			// discount qty = all items
			var v = ret['disc_by_qty']['ele'].options[i].value;
			if(v == this.all_items_val){
				ret['disc_by_qty']['option']['all_items'] = ret['disc_by_qty']['ele'].options[i];
			}else if(v == this.group_total_val){	// group total
				ret['disc_by_qty']['option']['group_total'] = ret['disc_by_qty']['ele'].options[i];
			}else{
				// others option
				ret['disc_by_qty']['option'][v] = ret['disc_by_qty']['ele'].options[i];
			}
		}
		
		// qty_from : (user enter)
		ret['qty_from'] = {};
		ret['qty_from']['container'] = $('tr_qty_from-'+item_id);
		ret['qty_from']['ele'] = this.form_element['qty_from'+element_name_extend];
		ret['qty_from']['value'] = float(ret['qty_from']['ele'].value.trim());
		// disc_limit : (user enter)
		ret['disc_limit'] = {};
		ret['disc_limit']['container'] = $('tr_disc_limit-'+item_id);
		ret['disc_limit']['ele'] = this.form_element['disc_limit'+element_name_extend];
		ret['disc_limit']['value'] = float(ret['disc_limit']['ele'].value.trim());
		// loop_limit : user enter
		ret['loop_limit'] = {};
		ret['loop_limit']['container'] = $('tr_loop_limit-'+item_id);
		ret['loop_limit']['ele'] = this.form_element['loop_limit'+element_name_extend];
		ret['loop_limit']['value'] = int(ret['loop_limit']['ele'].value);
		
		// receipt_description : (user enter)
		ret['receipt_description'] = {};
		ret['receipt_description']['ele'] = this.form_element['receipt_description'+element_name_extend]
		ret['receipt_description']['value'] = ret['receipt_description']['ele'].value.trim();
		
		return ret;
	},
	// function to get all return condition row info in object
	process_condition_row: function(li, group_id, item_id){
		if(!li)	return false; // no element given
		if(!group_id)	group_id = this.get_group_id_by_ele(li);
		if(!group_id)	return false;	// no group id
		if(!item_id)	item_id = this.get_item_id_by_ele(li);
		if(!item_id)	return false;	// no item id
		
		var condition_row_num = $(li).readAttribute('condition_row_num');
		if(!condition_row_num)	return false;	// no condition_row_num
		
		var element_name_extend = '['+group_id+']['+item_id+']';
		var condition_row_extend = element_name_extend+'['+condition_row_num+']';
		
		var ret = {};
		// rule : every, over_equal, over
		ret['rule'] = {};
		ret['rule']['ele'] = this.form_element['disc_condition'+condition_row_extend+'[rule]'];
		ret['rule']['value'] = ret['rule']['ele'].value;
		// condition_type : amt, qty
		ret['condition_type'] = {};
		ret['condition_type']['ele'] = this.form_element['disc_condition'+condition_row_extend+'[condition_type]'];
		ret['condition_type']['value'] = ret['condition_type']['ele'].value;
		// condition_value : (user enter)
		ret['condition_value'] = {};
		ret['condition_value']['ele'] = this.form_element['disc_condition'+condition_row_extend+'[condition_value]'];
		ret['condition_value']['value'] = float(ret['condition_value']['ele'].value);
		// item_value : maybe sku item id, category id, etc...
		ret['item_value'] = {};
		ret['item_value']['ele'] = this.form_element['disc_condition'+condition_row_extend+'[item_value]'];
		ret['item_value']['value'] = ret['item_value']['ele'].value;
		// item_type : receipt, sku, category, brand, category_brand
		ret['item_type'] = {};
		ret['item_type']['ele'] = this.form_element['disc_condition'+condition_row_extend+'[item_type]'];
		ret['item_type']['value'] = ret['item_type']['ele'].value;
		
		return ret;
	},
	// function when user change discount qty
	discount_qty_changed: function(item_id){
		if(!item_id)	return false;
		
		var tr_promo_item_row = $('tr_promo_item_row-'+item_id);
		var promo_item_row_info = this.process_promo_item_row(tr_promo_item_row);
		
		 
		// special FOC
		if(promo_item_row_info['disc_target_type']['value']=='special_foc'){
			// hide row
			$(promo_item_row_info['qty_from']['container']).hide();
			// clear value
			promo_item_row_info['qty_from']['ele'].value = 0;
			return;	
		}
		
		// -1 = all items, -2 = group total
		var show_qty_from = true;
		var show_qty_limit = true;
		
		if(promo_item_row_info['disc_by_qty']['value']== this.all_items_val){
			show_qty_limit = false;
		}
		
		if(promo_item_row_info['disc_by_qty']['value']== this.group_total_val || promo_item_row_info['disc_target_type']['value']=='receipt'){	
			show_qty_from = false;
			show_qty_limit = false;
		}
		
		if(show_qty_from)	$(promo_item_row_info['qty_from']['container']).show();
		else{
			// hide row
			$(promo_item_row_info['qty_from']['container']).hide();
			// clear value
			promo_item_row_info['qty_from']['ele'].value = 0;
		}
		
		if(show_qty_limit){
			$(promo_item_row_info['disc_limit']['container']).show();
		}else{
			$(promo_item_row_info['disc_limit']['container']).hide();
			// clear value
			promo_item_row_info['disc_limit']['ele'].value = 0;
		}
	},	
	// function to check discount condition rule
	check_disc_condition_rule: function(item_id){
		if(!item_id)	return false;	// no id
		
		var tr_promo_item_row = $('tr_promo_item_row-'+item_id);
		if(!tr_promo_item_row)	return false;	// no element
		
		var promo_item_row_info = this.process_promo_item_row(tr_promo_item_row);
		var got_every = false;
		
		for(var i=0; i < promo_item_row_info['condition_row'].length; i++){
			var condition_row = promo_item_row_info['condition_row'][i];
			if(condition_row['rule']['value']=='every'){	// found every
				got_every = true;
				break;
			}
		}
		
		if(got_every){
			// show loop limit
			$(promo_item_row_info['loop_limit']['container']).show();
		}else{
			// hide loop limit
			$(promo_item_row_info['loop_limit']['container']).hide();
			promo_item_row_info['loop_limit']['ele'].value = '';
		}
	},
	// function to check whether user try to key in customize discount qty
	check_enter_customize_discount_qty: function(item_id){
		var curr_click_time = int((new Date()).getTime());	// current time
		var last_click_time = int(this.customize_discount_qty_last_click_time);	// get last click time
		this.customize_discount_qty_last_click_time = curr_click_time;	// reset last click time
		
		if(last_click_time<=0 || curr_click_time - last_click_time > 200 )	return;	// not double click
		
		// let user enter the qty they want
		var disc_qty = int((prompt('Please enter the qty you want') || '').trim());
		if(!disc_qty)	return false;
		
		// check qty valid or not
		if(disc_qty<0){
			alert('Qty cannot less than zero');
			return false;
		}
		
		var tr_promo_item_row = $('tr_promo_item_row-'+item_id);
		var promo_item_row_info = this.process_promo_item_row(tr_promo_item_row);
		var curr_disc_qty = promo_item_row_info['disc_by_qty']['value'];
		
		if(curr_disc_qty == disc_qty)	return false;	// same as choosen qty
		
		// check whether this qty already exists
		if(promo_item_row_info['disc_by_qty']['option'][disc_qty]){
			// the element is exists
			
			// check whether can use it or not
			if(promo_item_row_info['disc_by_qty']['option'][disc_qty].style.display!=''){
				alert('The qty you enter is not allow to use. please try other qty.');
				return false;
			}
		}else{
			// the element is not exists
			
			// create new <option> element
			var new_opt = document.createElement('option');
			// set value
			new_opt.value = disc_qty;
			// set description
			$(new_opt).innerHTML = disc_qty;
			// append to the <select>
			promo_item_row_info['disc_by_qty']['ele'].appendChild(new_opt);
		}
		
		// switch to select this element
		promo_item_row_info['disc_by_qty']['ele'].value = disc_qty;
		
		// fire the onChange function
		this.discount_qty_changed(item_id);
	},
	// function when user change for member/non member
	change_for_member: function(group_id,ele){
		if(!group_id)	return false;
		
		var inp_for_member = this.form_element['for_member['+group_id+']'];
		var inp_for_non_member = this.form_element['for_non_member['+group_id+']'];
		var got_member = false;
		var all_checked = false;
		if(inp_for_non_member.checked){	// if enable for non member, member must also enable
			//inp_for_member.checked = true;
		}
		
		// is change member checkbox
		if(ele.name.indexOf('for_member[')>=0){
			$$('#div_promo_group-'+group_id+' input.inp_for_member_type-'+group_id).each(function(inp){
				inp.checked = ele.checked;
			});
			if(ele.checked)	got_member = true;
		}else if(ele.name.indexOf('for_member_type[')>=0){
			// is change on member type
			all_checked = true;
			
			// get member type input list
			var inp_for_member_type = $$('#div_promo_group-'+group_id+' input.inp_for_member_type-'+group_id);
			
			// loop to check whether user hv tick all member type
			for(var i=0; i<inp_for_member_type.length; i++){
				if(!inp_for_member_type[i].checked){
					all_checked = false;
				}else	got_member = true;
			}
			
			inp_for_member.checked = all_checked;
		}
		
		if(got_member){	// got for member
			$('tbody_member_settings-'+group_id).show();
		}else{
			// not for member
			$('tbody_member_settings-'+group_id).hide();
			this.form_element['control_type['+group_id+']'].value = 0;
		}
	},
	category_point_inherit_changed: function(){
		if(!this.form_element['category_point_inherit'])	return;
	
		if(this.form_element['category_point_inherit'].value == 'set'){
			$('div_cat_point').show();
		}else{
			$('div_cat_point').hide();
		}
	},
	category_point_value_changed: function(inp){
		if(!inp)	return;
		
		var v = inp.value.trim();
	
		if(v=='')	inp.value='';
		else{
			mf(inp,2);
			v = float(inp.value);
			if(v<=0){
				inp.value = 0;
			}
		}
	},
	item_category_point_inherit_changed: function(group_id){
		var sel = this.form_element['item_category_point_inherit_data['+group_id+'][inherit]'];
		var div_container = $('div_item_cat_point-'+group_id);
		
		if(!sel || !div_container)	return;
		
		if(sel.value=='set'){
			div_container.show();
		}else{
			div_container.hide();
		}
	},
	
	toggle_all_branches: function(obj){
		var all_chx = $$('input.chx_branch');
		for(var i=0; i<all_chx.length; i++){
			if(obj.checked == true){
				all_chx[i].checked = true;
			}else{
				all_chx[i].checked = false;
			}
		}
	}
}

MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG = {
	branch_id: 0,
	promo_id: 0,
	group_id: 0,
	item_id: 0,
	use_for: 'add_promo_row',
	form_element: undefined,
	div_content: undefined,
	div_dialog: undefined,
	item_autocomplete: undefined,
	inp_disc_target_autocomplete: undefined,
	cat_autocomplete: undefined,
	selected_cat_id: 0,
	brand_autocomplete: undefined,
	selected_brand_id: '',
	initialize: function(branch_id, promo_id){
		this.branch_id = branch_id;
		this.promo_id = promo_id;
		this.form_element = document.f_choose_item_type;
		if(!this.form_element){
			alert('Promotion module failed to initialize.');
			return false;
		}
		// make dialog draggable
		new Draggable('div_mnm_choose_item_type_dialog',{ handle: 'div_mnm_choose_item_type_dialog_header'});
		// store the content div
		this.div_content = $('div_mnm_choose_item_type_dialog_content');
		this.div_dialog = $('div_mnm_choose_item_type_dialog');
		this.span_header = $('span_mnm_choose_item_type_dialog_header');
		
		// store autocomplete input filed
		this.inp_disc_target_autocomplete = $('inp_disc_target_autocomplete');
		
		// initial autocomplete for sku
		this.reset_item_autocomplete();
		
		// initial autocomplete for category
		this.reset_category_autocomplete();
		$('inp_search_cat_autocomplete').observe('click', function(){
            MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG.reset_category_autocomplete();
		});
		
		// initial autocomplete for brand
		this.reset_brand_autocomplete();
		$('inp_search_brand_autocomplete').observe('click', function(){
            MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG.reset_brand_autocomplete();
		});
		
		// event when user change search item type
		$(this.form_element).getElementsBySelector('input[name="search_type"]')
		.each(function(ele){
			$(ele).observe('change', function(){
                MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG.reset_item_autocomplete();
			})
		});
		
		// event when user click add search item
		$('inp_add_disc_target_autocomplete').observe('click', function(){
            MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG.add_autocomplete_item();
		});
		
		// event when user click "add combination"
		$('inp_add_cat_brand_autocomplete').observe('click', function(){
            MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG.add_combination();
		});
		
		// event when user click add sku group
		if($('inp_choose_sku_group_id')){
			$('inp_choose_sku_group_id').observe('click', function(){
	            MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG.add_sku_group();
			});
		}
		
	},
	// event to open popup dialog
	open: function(group_id, item_id, use_for){
	    if(!group_id)   return false;
	    
	    // check target container first
        var tbl = $('tbody_promo_group_items-'+group_id);
        if(!tbl){
			alert('Invalid action, target group cannot be found.')
			return false;
		}

		// store group id
		this.group_id = group_id;
		
		if(!item_id)    item_id = 0;
		this.item_id = item_id;
		this.use_for = 'add_promo_row';
		$('tbody_choose_item_type_additional_filter').show();
		
		if(use_for) this.use_for = use_for;
		var title = 'Choose Promotion Item Type';
		if(this.use_for=='item_disc_condition'){
			title = "Choose Item Discount Condition Type";
			$('tbody_choose_item_type_additional_filter').hide();
		}
		$(this.span_header).update(title);
		
		// reset sku group
		if($('sel_choose_sku_group_id')){
			$('sel_choose_sku_group_id').selectedIndex = 0;
		}
		
		// reset additional filter status
		this.reset_additional_filter();
		// show dialog
		this.show();
		
		// clear all old search data
		this.reset_item_autocomplete();
		this.reset_category_autocomplete();
		this.reset_brand_autocomplete();
		
		// move focus to automcomplete box
		this.inp_disc_target_autocomplete.focus();
	},
	show: function(){
        // show dialog
		curtain(true);
		center_div($(this.div_dialog).show());
	},
	// reset category autocomplete
	reset_category_autocomplete: function(){
	    this.selected_cat_id = 0;
	    $('inp_search_cat_autocomplete').value = '';
	    
	    var cat_params = {
	    	a: 'ajax_search_category',
			'no_findcat_expand': 1,
			'max_level': 10,
		};
		if(mix_and_match_allow_add_line){
			cat_params['min_level'] = 0;
			cat_params['skip_dept_filter'] = 1;
		}
		
	    if(!this.cat_autocomplete){
            var params = $H(cat_params).toQueryString();

	        this.cat_autocomplete = new Ajax.Autocompleter("inp_search_cat_autocomplete", "div_search_cat_autocomplete_choices", 'ajax_autocomplete.php', {
		        parameters: params,
				paramName: "category",
				indicator: 'span_disc_target_autocomplete_loading',
				afterUpdateElement: function (obj, li) {
				    s = li.title.split(",");

		            if (s[0]==''){
				        obj.value='';
				        return;
				    }

					MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG.selected_cat_id = s[0];
				}
			});
		}
	},
	// reset brand autocomplete
	reset_brand_autocomplete: function(){
        this.selected_brand_id = '';
		$('inp_search_brand_autocomplete').value = '';
		
	    if(!this.brand_autocomplete){
            var params = $H({
				a: 'ajax_search_brand'
			}).toQueryString();

	        this.brand_autocomplete = new Ajax.Autocompleter("inp_search_brand_autocomplete", "div_search_brand_autocomplete_choices", 'ajax_autocomplete.php', {
		        parameters: params,
				paramName: "brand",
				indicator: 'span_disc_target_autocomplete_loading',
				afterUpdateElement: function (obj, li) {
				    s = li.title.split(",");

		            if (s[0]==''){
				        obj.value='';
				        return;
				    }

					MIX_MATCH_MAIN_CHOOSE_ITEM_TYPE_DIALOG.selected_brand_id = s[0];
				}
			});
		}
	},
	// event when user change autocomplete search type
	reset_item_autocomplete: function(){
	    this.reset_autocomplete_field();
	    // construct params
	    var search_type = getRadioValue(this.form_element['search_type']);
	    var search_target = '';
	    var paramName = 'value';
	    var inp_disc_target_autocomplete = this.inp_disc_target_autocomplete;
	    
	    if(search_type=='mcode'||search_type=='sku_item_code'||search_type=='artno'||search_type=='sku_descripton'){
	        // sku search
	        search_target = 'sku';
	        var t = 0;
	        if(search_type=='mcode')    t = 1;
	        else if(search_type=='sku_item_code') t =3;
	        else if(search_type=='artno') t =2;
	        else t = 4;
	        
            var params = $H({
				a: 'ajax_search_sku',
				type: t
			}).toQueryString();
		}else if(search_type=='brand'){
		    // brand search
			search_target = 'brand';
			var params = $H({
				a: 'ajax_search_brand'
			}).toQueryString();
			paramName = 'brand';
		}else if(search_type=='category'){
			// category search
			search_target = 'category';
			var params = $H({
				a: 'ajax_search_category',
				max_level: 10,
				no_findcat_expand: 1
			}).toQueryString();
			paramName = 'category';
		}
		var inp_disc_target_type = this.form_element['disc_target_type'];
		var inp_disc_target_value = this.form_element['disc_target_value'];
	    // focus to the autocomplete field
		inp_disc_target_autocomplete.focus();
		// change search target type
		inp_disc_target_type.value = search_target;
		
	    if(this.item_autocomplete){
            this.item_autocomplete.options.defaultParams = params;
            this.item_autocomplete.options.paramName = paramName;
		}else{
            this.item_autocomplete = new Ajax.Autocompleter("inp_disc_target_autocomplete", "div_disc_target_autocomplete_choices", 'ajax_autocomplete.php', {
            parameters: params,
			paramName: "value",
			indicator: 'span_disc_target_autocomplete_loading',
			afterUpdateElement: function (obj, li) {
			    s = li.title.split(",");

                if (s[0]==''){
			        obj.value='';
			        return;
			    }

				inp_disc_target_value.value = s[0];
			}});
			
		}
	},
	// function to reset and clear all autocomplete field
	reset_autocomplete_field: function(){
		this.form_element['disc_target_type'].value = '';
		this.form_element['disc_target_value'].value = '';
		this.inp_disc_target_autocomplete.value = '';
	},
	// event when user click add search item
	add_autocomplete_item: function(){
		var disc_target_value = this.form_element['disc_target_value'].value;
		var disc_target_type = this.form_element['disc_target_type'].value;
		var include_parent_child = 0;
		if(this.form_element['include_parent_child'].checked == true) include_parent_child = 1;
		var group_id =  this.group_id;
		var item_id = this.item_id;
		
		if(!disc_target_type || !disc_target_value){
			alert('Please search and select item first.');
			this.inp_disc_target_autocomplete.focus();
			return false;
		}
		
		// construct params
		var params = {
		    group_id: group_id,
		    item_id: item_id,
			disc_target_type: disc_target_type,
			disc_target_value: disc_target_value,
			include_parent_child: include_parent_child
		}
		
		// hide popup
		default_curtain_clicked();
		
		if(this.use_for=='add_promo_row'){
			params['disc_target_sku_type'] = this.form_element['disc_target_sku_type'].value;
			params['disc_target_price_type'] = this.form_element['disc_target_price_type'].value;
			params['disc_target_price_range_from'] = this.form_element['disc_target_price_range_from'].value;
			params['disc_target_price_range_to'] = this.form_element['disc_target_price_range_to'].value;
			
            MIX_MATCH_MAIN_MODULE.add_new_discount_item(group_id, params);
		}else if(this.use_for=='item_disc_condition'){
            MIX_MATCH_MAIN_MODULE.add_new_item_conditon_row(group_id, item_id, params);
		}
	},
	// event when user click "add combination"
	add_combination: function(){
		var cat_id = int(this.selected_cat_id);
		var brand_id = this.selected_brand_id;
		
		if(!cat_id && brand_id==''){
			alert('Please search category or brand first.');
			return false;
		}
		
		var disc_target_type = '';
		var disc_target_value = '';
		
		// only cat
		if(cat_id && brand_id==''){
			if(!confirm('You only select category, continue?'))  return false;
			disc_target_type = 'category';
			disc_target_value = cat_id;
		}
		// only brand
		if(brand_id!='' && !cat_id){
			if(!confirm('You only select brand, continue?'))  return false;
			disc_target_type = 'brand';
			disc_target_value = brand_id;
		}
		
		// cat + brand
		if(brand_id!='' && cat_id){
            disc_target_type = 'category_brand';
            disc_target_value = (cat_id*100000) + int(brand_id);
		}
		
		// if got price range filter
		var disc_target_price_range_from = float(this.form_element['disc_target_price_range_from'].value);
		var disc_target_price_range_to = float(this.form_element['disc_target_price_range_to'].value);
		
		if(disc_target_price_range_from < 0 || disc_target_price_range_to < 0){
			alert('Price range cannot less than zero');
			return false;
		}else{
			if(disc_target_price_range_from > 0 && disc_target_price_range_to > 0 && disc_target_price_range_from > disc_target_price_range_to){
				alert('Price range "from" cannot more than "to".');
				return false;
			}
		}
		
		this.form_element['disc_target_value'].value = disc_target_value;
		this.form_element['disc_target_type'].value = disc_target_type;
		this.add_autocomplete_item();
	},
	// function to reset additional filter dropdown
	reset_additional_filter: function(){
		this.form_element['disc_target_sku_type'].value = '';
		this.form_element['disc_target_price_type'].value = '';
		this.form_element['disc_target_price_range_from'].value = '';
		this.form_element['disc_target_price_range_to'].value = '';
	},
	// event when user click add sku group
	add_sku_group: function(){
		var sku_group_id2 = $('sel_choose_sku_group_id').value;
		if(!sku_group_id2){
			alert('Please select SKU Group first.');
			return false;
		}
		
		this.form_element['disc_target_value'].value = sku_group_id2;
		this.form_element['disc_target_type'].value = 'sku_group';
		this.add_autocomplete_item();
	}
}

/*MIX_MATCH_MAIN_PRINT_PROMO_DIALOG = {
    div_dialog: undefined,
    form_element: undefined,
	initialize: function(){
	    // store dialog
		this.div_dialog = $('div_print_promotion_dialog');
		if(!this.div_dialog){
			alert('Promotion printing function corrupt: cannot found printing dialog!');
			return false;
		}
		
		// store form
		this.form_element = document.f_print;
		if(!this.form_element){
            alert('Promotion printing function corrupt: cannot found form element!');
			return false;
		}
		
		// initial event
		// when user click cancel print
		if(this.form_element['print_cancel']){
            $(this.form_element['print_cancel']).observe('click', function(){
	            MIX_MATCH_MAIN_PRINT_PROMO_DIALOG.close();
			});
		}
		
		// when user click print ok
		if(this.form_element['print_ok']){
            $(this.form_element['print_ok']).observe('click', function(){
	            MIX_MATCH_MAIN_PRINT_PROMO_DIALOG.start_print();
			});
		}
		
	},
	// function to show print promotion dialog
	open: function(){
	    curtain(true);
		center_div($(this.div_dialog).show());
	},
	// function to close dialog
	close: function(){
		default_curtain_clicked();
	},
	// function when user click print ok
	start_print: function(){
		//if(!confirm('Confirm print?'))  return false;
		
		// submit the form
		this.form_element.submit();
		
		// close dialog
		this.close();
	}
}*/

MIX_MATCH_MAIN_WIZARD_DIALOG = {
	branch_id: 0,
	promo_id: 0,
	group_id: 0,
	item_id: 0,
	pwid: 0,
	form_element: undefined,
	div_content: undefined,
	div_dialog: undefined,
	span_header: undefined,
	btn_back: undefined,
	btn_next: undefined,
	curr_screen: 'main', // discount_target_screen
	curr_row_num: '',
	open_autocomplete_for: '',
	curr_condition_row_num: '',
	screen_ready: true,
	promo_generating: false,
	initialize: function(branch_id, promo_id){
		this.branch_id = branch_id;
		this.promo_id = promo_id;
		this.form_element = document.f_pw;
		if(!this.form_element){
			alert('Promotion module failed to initialize.');
			return false;
		}
		
		// make dialog draggable
		new Draggable('div_mnm_wizard_dialog',{ handle: 'div_mnm_wizard_dialog_header'});
		// store the content div
		this.div_content = $('div_mnm_wizard_dialog_content');
		this.div_dialog = $('div_mnm_wizard_dialog');
		this.span_header = $('span_mnm_wizard_dialog_header');
		
		this.btn_back = $('btn_promotion_wizard_back_screen');
		this.btn_next = $('btn_promotion_wizard_next_screen');
	},
	// event to open popup dialog
	open: function(group_id){
	    if(!group_id)   return false;
	    
	    // check target container first
        var tbl = $('tbody_promo_group_items-'+group_id);
        if(!tbl){
			alert('Invalid action, target group cannot be found.')
			return false;
		}

		// store group id
		this.group_id = group_id;
		
		// show the dialog
		this.reset();
		this.show();
		
		this.load_main_page();
	},
	load_main_page: function(){
		// load the main screen
		this.load_screen({'screen':'main'});
	},
	// reset all variable
	reset: function(){
		this.pwid = 0;
		this.curr_screen = 'main';
		this.curr_row_num = '';
		this.promo_generating = false;
		$(this.form_element).enable();
	},
	// function to show the dialog
	show: function(){
        // show dialog
		curtain(true, 'curtain2');	// use curtain2, so it wont auto close if user click on background
		center_div($(this.div_dialog).show());
	},
	// function to close the dialog
	close: function(){
		$(this.div_dialog).hide();
		curtain(false, 'curtain2');
	},
	// function to handle when user try to close this dialog
	close_clicked: function(){
		if(this.promo_generating){
			alert('Please wait while the process is still running...');
			return false;
		}
		
		// check if this is not the first page
		if(this.curr_screen != 'main'){
			if(!confirm('Are you sure to close? Any unsave data will lose.')){
				return false;
			}
		}
		
		// close category autocomplete
		CAT_BRAND_AUTOCOMPLETE_POPUP.close();
		// close sku autocomplete 
		SKU_AUTOCOMPLETE_POPUP.close();
		
		this.close();
	},
	// function to load the screen
	load_screen: function(params){
		if(!this.screen_ready){
			alert('Please wait, the screen is still loading...');
			return false;
		}
		
		if(!params || !params['screen']){
			$(this.div_content).update('Invalid Page');
		}
		this.curr_screen = params['screen'];
		
		var div_show = '';
		
		//$(this.btn_next).show();
		
		switch(this.curr_screen){
			case 'main':
				div_show = 'div_promotion_wizard_type_list';
				$(this.btn_back).hide();
				$(this.btn_next).update('Next &gt;').disabled = false;
				break;
			case 'discount_target_screen':
				div_show = 'div_promotion_wizard_disc_target_screen';
				$(this.btn_back).show();
				$(this.btn_next).update('Done &gt;').disabled = false;
				break;
			default:
				alert('Invalid Screen!');
				return false;
				break;
		}
		
		params['a'] = 'load_wizard_screen';
		params['branch_id'] = this.branch_id;
		params['group_id'] = this.group_id;
		params['promo_id'] = this.promo_id;
		params['pwid'] = this.pwid;
		$(this.div_content).getElementsBySelector("div.div_wizard_screen").invoke('hide');
		
		// mark screen os loading
		this.screen_ready = false;
		
		$(div_show).update(_loading_).show();
		new Ajax.Updater(div_show, phpself, {
			parameters: params,
			onComplete: function(){
				// mark the screen as ready
				MIX_MATCH_MAIN_WIZARD_DIALOG.screen_ready = true;
			}
		});
	},
	// function to check what pwid user is selecting
	get_selected_pwid: function(){
		// get the input list
		var inp_list = $('div_promotion_wizard_type_list').getElementsBySelector('input[name="promotion_wizard_id"]');
		// get the selected ID
		var pwid = getRadioValue(inp_list);
		
		return int(pwid);
	},
	// function to check selected promotion wizard
	check_promotion_wizard_type: function(){
		// get pwid
		var pwid = this.get_selected_pwid();
		
		// remove li selected color
		$$('#div_promotion_wizard_type_list li.li_pwid').invoke('removeClassName', 'li_pw_selected');
		
		if(!pwid)	return false;
		
		// add li selected color
		$('li_pwid-'+pwid).addClassName('li_pw_selected');
		
		// hide all the description
		$$('#div_promotion_wizard_description_list div.div_promotion_wizard_description').invoke('hide');
		
		// show the selected description
		$('div_promotion_wizard_description-'+pwid).show();
	},
	// function to handle when user click next page
	next_page: function(){
		var curr_screen = this.curr_screen;
		
		switch(curr_screen){
			case 'main':	// currently at first screen
				this.main_screen_to_next();
				break;
			case 'discount_target_screen':	// currently at discount screen
				this.discount_target_screen_to_next();
				break;
			default:
				alert('Invalid Screen Navigation!');
				this.close();
				break;
		}
	},
	// function to handle when user click back page
	back_page: function(){
		var curr_screen = this.curr_screen;
		
		// close the sku autocomplet dialog
		SKU_AUTOCOMPLETE_POPUP.close();
		// close category autocomplete
		CAT_BRAND_AUTOCOMPLETE_POPUP.close();
		
		switch(curr_screen){
			case 'discount_target_screen':	// currently at discount target screen
				this.discount_target_screen_to_back();
				break;
			case 'disc_condition_screen':
				this.disc_condition_screen_to_back();	// currently at  discount condition
				break;
			default:
				alert('Invalid Screen Navigation!');
				this.close();
				break;
		}
	},
	// function to handle user navigate from main screen to next screen
	main_screen_to_next: function(){
		// get pwid
		var pwid = this.get_selected_pwid();
		
		if(!pwid){
			alert('Please select a promotion type.');
			return false;
		}
		
		// assign pwid to object
		this.pwid = pwid;
		
		var params = {};
		params['screen'] = 'discount_target_screen';
		
		this.load_screen(params);
	},
	// function when user click back at discount target screen
	discount_target_screen_to_back: function(){
		// load main page
		this.load_main_page();
	},
	// function to show sku popup
	show_search_sku: function(row_num, open_autocomplete_for, condition_row_num){
		if(!row_num){
			alert('Invalid Item');
			return false;
		}
		
		if(!open_autocomplete_for)	this.open_autocomplete_for = 'discount_target';
		else	this.open_autocomplete_for = open_autocomplete_for;
		this.curr_condition_row_num = condition_row_num;
		
		this.curr_row_num = row_num;
		SKU_AUTOCOMPLETE_POPUP.open('promotion_wizard');
	},
	// function callback by sku autocomplete dialog
	add_sku: function(sid){
		if(!sid){	// no sku item id is given
			alert('Invalid SKU');
			return false;
		}
		
		this.add_disc_target_info(sid, 'sku');
	},
	// when user press next on discount target screen
	discount_target_screen_to_next: function(){
		// check promotion item
		
		// get all disc target
		var div_pw_disc_target_item_list = this.get_div_pw_disc_target_item_list();
		
		for(var i=0; i<div_pw_disc_target_item_list.length; i++){
			// get row num
			var row_num = this.get_row_num_by_ele(div_pw_disc_target_item_list[i]);
			// get row info
			var pw_item_row_info = this.process_item_row_info(row_num);
			
			if(pw_item_row_info['disc_target_type']['ele'].value != 'brand' && pw_item_row_info['disc_target_type']['ele'].value !='receipt' && pw_item_row_info['disc_target_type']['ele'].value != 'special_foc'){
				// check disc target value
				if(!pw_item_row_info['disc_target_value']['ele'].value){
					alert('Please select discount target for condition '+(i+1));
					return false;
				}
			}
			
			
			
			// loop condition row
			for(var j=0; j<pw_item_row_info['condition_row'].length; j++){
				var condition_row = pw_item_row_info['condition_row'][j];
				if(!condition_row){
					continue;
				}
				if(condition_row['item_type']['ele'].value != 'brand' && condition_row['item_type']['ele'].value != 'receipt' && condition_row['item_type']['ele'].value != 'special_foc'){
					// check item value
					if(!condition_row['item_value']['ele'].value){
						alert('Please select discount qualified '+(j+1)+' for condition '+(i+1));
						return false;
					}
				}
				
				
			}
		}
		
		// all OK
		if(!confirm('Click OK to confirm to generate this promotion.'))	return false;
		var THIS = this;
		
		// mark current stage
		this.promo_generating = true;
		// hide the back btn
		$(this.btn_back).hide();
		// change the next btn word
		$(this.btn_next).update("<img src='/ui/clock.gif' align='absbottom' /> Generating...").disabled = true;
		
		var q = $(this.form_element).serialize();
		var group_id = this.group_id;
		
		var params = {
			'a': 'ajax_generate_promo_by_promo_wizard',
			branch_id: this.branch_id,
			group_id: group_id,
			promo_id: this.promo_id
		}
		q += '&'+$H(params).toQueryString();
		// disable form to be edit
		$(this.form_element).disable();
		
		new Ajax.Request(phpself, {
			parameters: q,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

				// mark current stage
				THIS.promo_generating = false;
				// hide the back btn
				$(THIS.btn_back).show();
				// change the next btn word
				$(THIS.btn_next).update("Done &gt;").disabled = false;
				// enable form
				$(THIS.form_element).enable();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                	// append html
	                	new Insertion.Bottom('tbody_promo_group_items-'+group_id, ret['html']);
	                	// reset row num
	                    MIX_MATCH_MAIN_MODULE.reset_row_no(group_id);
	                    
	                    // got group setting
	                    if(ret['group_setting']){
							if(int(ret['group_setting']['receipt_limit'])>0){
								MIX_MATCH_MAIN_MODULE.form_element['receipt_limit['+group_id+']'].value = int(ret['group_setting']['receipt_limit']);
							}
						}
	                    // close dialog
	                    THIS.close();
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
	},
	// user click back add discount condition screen
	disc_condition_screen_to_back: function(){
		var disc_target_type = this.form_element['disc_target_type'].value;
		var disc_target_value = this.form_element['disc_target_value'].value;
		var disc_by_type = this.form_element['disc_by_type'].value;
		
		var params = {};
		params['disc_target_type'] = disc_target_type;
		params['disc_target_value'] = disc_target_value;
		params['disc_by_type'] = disc_by_type;
		params['screen'] = 'discount_target_screen';
		
		this.load_screen(params);
	},
	// function to get all promotion wizard row info
	process_item_row_info: function(row_num){
		var ret = {};
		if(!row_num)	return ret;		
		
		// SOME HIDDEN VALUE
		ret['allow_edit_condition_item_type'] = {};
		ret['allow_edit_condition_item_type']['ele'] = this.form_element['allow_edit_condition_item_type['+row_num+']'];
		
		// Discount Target
		ret['disc_target_type'] = {};
		ret['disc_target_type']['ele'] = this.form_element['disc_target_type['+row_num+']'];
		
		// Discount Target Value
		ret['disc_target_value'] = {};
		ret['disc_target_value']['ele'] = this.form_element['disc_target_value['+row_num+']'];
		
		// Discount Target Category Value
		ret['disc_target_cat_value'] = {};
		ret['disc_target_cat_value']['ele'] = this.form_element['disc_target_cat_value['+row_num+']'];
		
		// Discount Target Brand Value
		ret['disc_target_brand_value'] = {};
		ret['disc_target_brand_value']['ele'] = this.form_element['disc_target_brand_value['+row_num+']'];
		
		// Discount Target Info
		ret['disc_target_info'] = {};
		ret['disc_target_info']['ele'] = $('div_pw_disc_target_info-'+row_num);
		
		// Discount by Type
		ret['disc_by_type'] = {};
		ret['disc_by_type']['ele'] = this.form_element['disc_by_type['+row_num+']'];
		
		// Discount by Value
		ret['disc_by_value'] = {};
		ret['disc_by_value']['ele'] = this.form_element['disc_by_value['+row_num+']'];
		
		// Discount by Qty
		ret['disc_by_qty'] = {};
		ret['disc_by_qty']['ele'] = this.form_element['disc_by_qty['+row_num+']'];
		
		// Qty From
		ret['qty_from'] = {};
		ret['qty_from']['ele'] = this.form_element['qty_from['+row_num+']'];
		
		// Discount Condition 
		// get condition info
		var div_condition_row = $$('#div_pw_disc_condition-'+row_num+' div.div_pw_disc_condition');
		ret['condition_row'] = [];
		for(var i=0; i<div_condition_row.length; i++){
			// get each row of info
			ret['condition_row'].push(this.process_condition_row(div_condition_row[i], row_num));
		}
		
		return ret;
	},
	// function to process condition row
	process_condition_row: function(div, row_num){
		if(!div)	return false; // no element given
		if(!row_num)	return false;	// no group id
		
		var condition_row_num = $(div).readAttribute('condition_row_num');
		if(!condition_row_num)	return false;	// no condition_row_num
		
		var element_name_extend = '['+row_num+']';
		var condition_row_extend = element_name_extend+'['+condition_row_num+']';
		
		var ret = {};
		ret['condition_row_num'] = condition_row_num;
		
		// rule : every, over_equal, over
		ret['rule'] = {};
		ret['rule']['ele'] = this.form_element['disc_condition'+condition_row_extend+'[rule]'];
		// condition_type : amt, qty
		ret['condition_type'] = {};
		ret['condition_type']['ele'] = this.form_element['disc_condition'+condition_row_extend+'[condition_type]'];
		// condition_value : (user enter)
		ret['condition_value'] = {};
		ret['condition_value']['ele'] = this.form_element['disc_condition'+condition_row_extend+'[condition_value]'];
		// item_value : maybe sku item id, category id, etc...
		ret['item_value'] = {};
		ret['item_value']['ele'] = this.form_element['disc_condition'+condition_row_extend+'[item_value]'];
		// item_type : receipt, sku, category, brand, category_brand
		ret['item_type'] = {};
		ret['item_type']['ele'] = this.form_element['disc_condition'+condition_row_extend+'[item_type]'];
		
		ret['item_info'] = {};
		ret['item_info']['ele'] = $('div_pw_disc_condition_item_info-'+row_num+'-'+condition_row_num)
		
		return ret;	
	},
	// function to get the user wanted condition row
	get_condition_row_by_row_num: function(condition_row, row_num){
		if(!condition_row)	return false;
		
		for(var i=0; i < condition_row.length; i++){
			if(condition_row[i]['condition_row_num'] == row_num)	return condition_row[i];
		}
	},
	// function to show search category popup
	show_search_cat_brand: function(row_num, type, open_autocomplete_for, condition_row_num){
		if(!row_num){
			alert('Invalid Item');
			return false;
		}
		
		if(!open_autocomplete_for)	this.open_autocomplete_for = 'discount_target';
		else	this.open_autocomplete_for = open_autocomplete_for;
		this.curr_condition_row_num = condition_row_num;
		
		this.curr_row_num = row_num;
		
		CAT_BRAND_AUTOCOMPLETE_POPUP.open('promotion_wizard', type);
	},
	// function when user click add category
	add_disc_target_info: function(disc_target_value, disc_target_type){
		if(!disc_target_type){
			alert('Invalid Type')
		}
		if(disc_target_type!='brand' && !disc_target_value){	// no value id is given
			alert('Invalid Value');
			return false;
		}
		// get the current selected row
		var row_num = this.curr_row_num;
		
		// get all the row element
		var pw_item_row_info = this.process_item_row_info(row_num);
		// cant find the row element
		if(!pw_item_row_info){
			alert('Invalid Promotion Item Row.');
			return false;
		}
		
		var div_pw_disc_target_item_list = this.get_div_pw_disc_target_item_list();
		
		// store own object
		var THIS = this;
		
		// check what is the current screen
		switch(this.open_autocomplete_for){
			case 'discount_target':				
				// assign variable
				pw_item_row_info['disc_target_value']['ele'].value = disc_target_value;
				pw_item_row_info['disc_target_type']['ele'].value = disc_target_type;
				
				var include_parent_child = 0;
				if(disc_target_type == "sku"){
					if(document.f_sku_autocomplete['wizard_include_parent_child'].checked == true){
						include_parent_child = 1;
					}
				}
				
				// change disc target info loading
				$(pw_item_row_info['disc_target_info']['ele']).update(_loading_);
				
				// call ajax to load sku info
				new Ajax.Request(phpself, {
					parameters:{
						a: 'ajax_show_disc_target_item_info',
						disc_target_value: disc_target_value,
						disc_target_type: disc_target_type,
						include_parent_child: include_parent_child
					},
					onComplete: function(msg){						
						var str = msg.responseText.trim();
						var ret = {};
					    var err_msg = '';
		
					    try{
			                ret = JSON.parse(str); // try decode json object
			                if(ret['ok'] && ret['html']){ // success
			                	// update all info
								for(var i=0; i<div_pw_disc_target_item_list.length; i++){
									var tmp_row_num = THIS.get_row_num_by_ele(div_pw_disc_target_item_list[i]);
									THIS.update_discount_target(tmp_row_num, disc_target_type, disc_target_value, ret['html'])
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
				})
				break;
			case 'disc_condition':
				// get current selected condition row
				var condition_row_num = this.curr_condition_row_num;
				var condition_row = this.get_condition_row_by_row_num(pw_item_row_info['condition_row'], condition_row_num);
				if(!condition_row){
					alert('The selected conditon row cannot be found.');
					return false;
				}
				// assign variable
				condition_row['item_value']['ele'].value = disc_target_value;
				condition_row['item_type']['ele'].value = disc_target_type;				
				
				// change disc target info loading
				$(condition_row['item_info']['ele']).update(_loading_);
				
				// call ajax to load sku info
				new Ajax.Request(phpself, {
					parameters:{
						a: 'ajax_show_disc_target_item_info',
						disc_target_value: disc_target_value,
						disc_target_type: disc_target_type
					},
					onComplete: function(e){
						eval("var json = "+e.responseText);
						
						if(!json['html']){
							alert('Failed to add item.');
							return false;
						}
						
						// update html to the container
						$(condition_row['item_info']['ele']).update(json['html']);
						// show the DIV
						$(condition_row['item_info']['ele']).show();
					}
				})
				break;
			default:
				alert('Unhandled Error!');
				return false;
				break;
		}
		// close cat brand autocomplete
		CAT_BRAND_AUTOCOMPLETE_POPUP.close();
		
		// close sku autocomplete
		SKU_AUTOCOMPLETE_POPUP.close();
	},
	// function to get all the discount target DIV
	get_div_pw_disc_target_item_list: function(){
		return $$('#div_mnm_wizard_dialog_content div.div_pw_disc_target_item');
	},
	// function to delete row num
	delete_disc_target: function(row_num){
		if(!row_num || row_num == 1){	// no row num or row num = 1
			alert('Invalid Item');
			return false;
		}
		
		// check row exists
		var div_pw_disc_target_item = $('div_pw_disc_target_item-'+row_num);
		if(!div_pw_disc_target_item){
			alert('the item row cannot be found!');
			return false;
		}
		
		// get all the div item
		var div_pw_disc_target_item_list = this.get_div_pw_disc_target_item_list();
		
		// only 1 div left, cannot delete
		if(div_pw_disc_target_item_list.length <= 1){
			alert('You cannot delete last item.');
			return false;
		}
		
		if(!confirm('Are you sure?'))	return false;
		
		// remove the row
		var THIS = this;
		Effect.DropOut(div_pw_disc_target_item, {
			duration:0.5,
			afterFinish: function() {
				$(div_pw_disc_target_item).remove();
				// reset item no
				THIS.reset_pw_disc_target_item_no();
			}
		});
	},
	// function to reset the item no
	reset_pw_disc_target_item_no: function(){
		var span_pw_disc_target_item_no_list = $$('#div_mnm_wizard_dialog_content span.span_pw_disc_target_item_no');
		for(var i=0; i<span_pw_disc_target_item_no_list.length; i++){
			$(span_pw_disc_target_item_no_list[i]).update(i+1);
		}
	},
	// function to get the largest row num
	get_max_disc_target_item_row_num: function(){
		// get all DIV
		var max_row_num = 0;
		var div_pw_disc_target_item_list = this.get_div_pw_disc_target_item_list();
		
		for(var i=0; i<div_pw_disc_target_item_list.length; i++){
			var row_num = this.get_row_num_by_ele(div_pw_disc_target_item_list[i]);
			if(row_num>max_row_num)	max_row_num = row_num;
		}
		return max_row_num;
	},
	// function to add discount target
	add_disc_target_item: function(){
		var pwid = this.pwid;
		if(!pwid){
			alert('Invalid Promotion Type');
			return false;
		}
		
		var btn_pw_add_disc_item_div = $('btn_pw_add_disc_item_div');
		var max_row_num = this.get_max_disc_target_item_row_num();
		var THIS = this;
		
		// disable the add button
		$(btn_pw_add_disc_item_div).disabled = true;
		
		// div_pw_disc_target_item_list
		new Ajax.Request(phpself, {
			parameters:{
				a: 'ajax_pw_add_disc_target_item',
				pwid: pwid,
				max_row_num: max_row_num
			},
			onComplete: function(msg){
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                	// append to the list
						new Insertion.Bottom('div_pw_disc_target_item_list', ret['html']);
						
						// reset item no
						THIS.reset_pw_disc_target_item_no();
						
						// check whether need to clone disc target or condition
						THIS.check_and_clone_disc_target_and_condition();
						
						// enable back the button
						btn_pw_add_disc_item_div.disabled = false;
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
	},
	// function to update all element when discount target is selected
	update_discount_target: function(row_num, disc_target_type, disc_target_value, disc_target_info){
		// get the current selected row
		if(!row_num){
			alert('Invalid Item Row');
			return false;
		}
		
		// get all the row element
		var pw_item_row_info = this.process_item_row_info(row_num);
		// cant find the row element
		if(!pw_item_row_info){
			alert('Invalid Promotion Item Row.');
			return false;
		}
		
		// discount target value
		pw_item_row_info['disc_target_value']['ele'].value = disc_target_value;
		// discount target type
		pw_item_row_info['disc_target_type']['ele'].value = disc_target_type;
		// discount target info
		var disc_target_info_html = disc_target_info.replace(/tmp_row_num/g,row_num);
		$(pw_item_row_info['disc_target_info']['ele']).update(disc_target_info_html).show();
		
		// this discount cannot edit condition, so there are same as disc target
		if(!pw_item_row_info['allow_edit_condition_item_type']['ele'].value){
			// loop condition row
			for(var i=0; i<pw_item_row_info['condition_row'].length; i++){
				var condition_row = pw_item_row_info['condition_row'][i];
				if(!condition_row){
					alert('The conditon row cannot be found.');
					return false;
				}
				// assign variable
				condition_row['item_value']['ele'].value = disc_target_value;
				condition_row['item_type']['ele'].value = disc_target_type;	
				
				// update html to the container
				$(condition_row['item_info']['ele']).update(disc_target_info_html).show();
			}
			
		}
	},
	get_row_num_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='div'){
                if($(parent_ele).hasClassName('div_pw_disc_target_item')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var row_num = parent_ele.id.split('-')[1];
		return row_num;
	},
	check_and_clone_disc_target_and_condition: function(){
		// get the first row
		var pw_item_row_info = this.process_item_row_info(1);
		
		var disc_target_value = pw_item_row_info['disc_target_value']['ele'].value;
		var disc_target_type = pw_item_row_info['disc_target_type']['ele'].value;
		var disc_target_info = pw_item_row_info['disc_target_info']['ele'].innerHTML.trim();
		//var allow_edit_condition_item_type = pw_item_row_info['allow_edit_condition_item_type']['ele'].value;
		var div_pw_disc_target_item_list = this.get_div_pw_disc_target_item_list();
		
		if(pw_item_row_info['disc_target_info']['ele'].style.display!='none'){
			for(var i=1; i<div_pw_disc_target_item_list.length; i++){
				var row_num = this.get_row_num_by_ele(div_pw_disc_target_item_list[i]);
				this.update_discount_target(row_num, disc_target_type, disc_target_value, disc_target_info)
			}
		}
	}
}

SKU_AUTOCOMPLETE_POPUP = {
	div_content: undefined,
	div_dialog: undefined,
	sku_item_id_ele: undefined,
	search_ele: undefined,
	open_by: '',
	initialize: function(){
		
		/*this.form_element = document.f_choose_item_type;
		if(!this.form_element){
			alert('Promotion module failed to initialize.');
			return false;
		}*/
		// make dialog draggable
		new Draggable('div_sku_autocomplete_dialog',{ handle: 'div_sku_autocomplete_dialog_header'});
		// store the content div
		this.div_content = $('div_sku_autocomplete_dialog_content');
		this.div_dialog = $('div_sku_autocomplete_dialog');
		
		// store the element
		this.sku_item_id_ele = $('sku_item_id');
		this.search_ele = $('autocomplete_sku');
		
		// initial the sku autocomplete
		reset_sku_autocomplete();
	},
	// function to reset all element and variable
	reset: function(){
		// reset value
		this.sku_item_id_ele.value = '';
		this.search_ele.value = '';
		this.open_by = '';
	},
	// function call to to everything before show dialog 
	open: function(open_by){
		if(!open_by){
			alert('Invalid Call by module.');
			return false;
		}
		// reset all selected value
		this.reset();
		
		// assign open_by
		this.open_by = open_by;
		// show the popup
		this.show();
		// focus user to the search input
		this.search_ele.focus();
	},
	// function to show the dialog
	show: function(){
		center_div($(this.div_dialog).show());
	},
	// function to close the dialog
	close: function(){
		$(this.div_dialog).hide();
	},
	// user click add item
	add: function(){
		// get sku item id
		var sid = this.sku_item_id_ele.value;
		if(!sid){
			alert('Please select an SKU first.');
			$(this.search_ele).select();
			return false;
		}
		
		// check what module open this dialog and decide how to callback
		switch(this.open_by){
			case 'promotion_wizard':	// called by promotion wizard
				MIX_MATCH_MAIN_WIZARD_DIALOG.add_sku(sid);
				break;
			default:	// unknown caller
				alert('Unhandled Module Function!');
				return false;
				break;
		}
	}
}

CAT_BRAND_AUTOCOMPLETE_POPUP = {
	div_content: undefined,
	div_dialog: undefined,
	category_id_ele: undefined,
	search_ele: undefined,
	form_element: undefined,
	div_cat_autocomplete: undefined,
	div_brand_autocomplete: undefined,
	inp_pw_add_cat_brand_combination: undefined,
	open_by: '',
	CAT_AUTOCOMPLETE_MAIN: undefined,
	BRAND_AUTOCOMPLETE_MAIN: undefined,
	initialize: function(){
		
		this.form_element = document.f_cat_brand_autocomplete;
		if(!this.form_element){
			alert('Promotion module failed to initialize.');
			return false;
		}
		// make dialog draggable
		new Draggable('div_cat_brand_autocomplete_dialog',{ handle: 'div_cat_brand_autocomplete_dialog_header'});
		// store the content div
		this.div_content = $('div_cat_brand_autocomplete_dialog_content');
		this.div_dialog = $('div_cat_brand_autocomplete_dialog');
		
		this.div_cat_autocomplete = $('div_cat_autocomplete');
		this.div_brand_autocomplete = $('div_brand_autocomplete');
		this.inp_pw_add_cat_brand_combination = $('inp_pw_add_cat_brand_combination');
		
		// cat autocomplete
		var cat_params = {
			'no_findcat_expand': 1,
			'max_level': 10,
		};
		if(mix_and_match_allow_add_line){
			cat_params['min_level'] = 0;
			cat_params['skip_dept_filter'] = 1;
		}
		
		this.CAT_AUTOCOMPLETE_MAIN = CAT_AUTOCOMPLETE_MAIN_2.initialize(cat_params, function(cat_id){
			CAT_BRAND_AUTOCOMPLETE_POPUP.add_cat_or_brand(cat_id, 'category');
		});
		
		// brand autocomplete
		this.BRAND_AUTOCOMPLETE_MAIN = BRAND_AUTOCOMPLETE_MAIN_2.initialize({
		},
		function(brand_id){
			CAT_BRAND_AUTOCOMPLETE_POPUP.add_cat_or_brand(brand_id, 'brand');
		});
		
	},
	// function to reset all element and variable
	reset: function(){
		// reset value
		this.CAT_AUTOCOMPLETE_MAIN.reset();
		this.BRAND_AUTOCOMPLETE_MAIN.reset();
		this.open_by = '';
	},
	// function call to to everything before show dialog 
	open: function(open_by, type){
		if(!open_by){
			alert('Invalid Call by module.');
			return false;
		}
		// reset all selected value
		this.reset();
		
		// assign open_by
		this.open_by = open_by;
		
		$(this.div_cat_autocomplete).hide();
		$(this.div_brand_autocomplete).hide();
		$(this.inp_pw_add_cat_brand_combination).hide();
		
		if(type == 'category' || type == 'category_brand'){
			$(this.div_cat_autocomplete).show();
			// only add category
			if(type == 'category'){
				// show the "add" button
				this.CAT_AUTOCOMPLETE_MAIN.show_add_button(1);
			}
		}
		if(type == 'brand' || type == 'category_brand'){
			// only add brand
			$(this.div_brand_autocomplete).show();
			if(type == 'brand'){
				// show the "add" button
				this.BRAND_AUTOCOMPLETE_MAIN.show_add_button(1);
			}
		}
		if(type == 'category_brand'){
			// add category brand
			
			// hide the "add" button
			this.CAT_AUTOCOMPLETE_MAIN.show_add_button(0);
			this.BRAND_AUTOCOMPLETE_MAIN.show_add_button(0);
			
			$(this.inp_pw_add_cat_brand_combination).show();
		}
		
		// show the popup
		this.show();
		// focus user to the search input
		this.focus_search_ele(type);
	},
	// function to focus user to the search input 
	focus_search_ele: function(type){
		if(type == 'category' || type == 'category_brand'){
			this.CAT_AUTOCOMPLETE_MAIN.select_search_box();
		}else{
			this.BRAND_AUTOCOMPLETE_MAIN.select_search_box();
		}
	},
	// function to show the dialog
	show: function(){
		center_div($(this.div_dialog).show());
	},
	// function to close the dialog
	close: function(){
		$(this.div_dialog).hide();
	},
	// callback function when user click add category
	add_cat_or_brand: function(disc_target_value, disc_target_type){
		if(disc_target_type=='category'){
			if(!disc_target_value){
				alert('Please select a category first.');
				// focus user to the search input
				this.focus_search_ele();
				return false;
			}
		}else if(disc_target_type == 'brand'){
		
		}else if(!disc_target_value){
			alert('Invalid Value');
			return false;
		}
		
		
		// check what module open this dialog and decide how to callback
		switch(this.open_by){
			case 'promotion_wizard':	// called by promotion wizard
				MIX_MATCH_MAIN_WIZARD_DIALOG.add_disc_target_info(disc_target_value, disc_target_type);
				break;
			default:	// unknown caller
				alert('Unhandled Module Function!');
				return false;
				break;
		}
	},
	// when user click "Add combination" for category + brand
	add_cat_brand_combination: function(){
		var cat_id = this.CAT_AUTOCOMPLETE_MAIN.get_selected_cat_id();
		var brand_id = this.BRAND_AUTOCOMPLETE_MAIN.get_selected_brand_id();
		
		if(!cat_id){
			alert('Please select category.');
			this.CAT_AUTOCOMPLETE_MAIN.select_search_box();
			return false;
		}

		/*if(!brand_id){	// brand id can accept empty as un-brand
			alert('Please select brand.');
			this.BRAND_AUTOCOMPLETE_MAIN.select_search_box();
			return false;
		}*/
		
		var disc_target_value = (cat_id*100000) + int(brand_id);
		MIX_MATCH_MAIN_WIZARD_DIALOG.add_disc_target_info(disc_target_value, 'category_brand');
	}
}

function search_new_promo_item(group_id){
    MIX_MATCH_MAIN_MODULE.search_new_promo_item(group_id);
}

function delete_promo_group(group_id){
    MIX_MATCH_MAIN_MODULE.delete_promo_group(group_id);
}

function add_new_receipt_discount_item(group_id){
	var params = {
        group_id: group_id,
        disc_target_type: 'receipt'
	};
    MIX_MATCH_MAIN_MODULE.add_new_discount_item(group_id, params);
}

function add_new_special_foc_item(group_id){
	var params = {
        group_id: group_id,
        disc_target_type: 'special_foc'
	};
    MIX_MATCH_MAIN_MODULE.add_new_discount_item(group_id, params);
}

function delete_discount_item(item_id){
    MIX_MATCH_MAIN_MODULE.delete_discount_item(item_id);
}

function add_disc_condition(item_id){
    MIX_MATCH_MAIN_MODULE.add_disc_condition(item_id);
}

function add_disc_condition_by_receipt(item_id){
    MIX_MATCH_MAIN_MODULE.add_disc_condition_by_receipt(item_id);
}

function delete_disc_condition_row(ele){
    MIX_MATCH_MAIN_MODULE.delete_disc_condition_row(ele);
}

function do_save(){
    MIX_MATCH_MAIN_MODULE.submit_form('save');
}

function do_delete(){
    MIX_MATCH_MAIN_MODULE.submit_form('delete', 1);
}

function do_confirm(){
    MIX_MATCH_MAIN_MODULE.submit_form('confirm');
}

function do_cancel(){
    MIX_MATCH_MAIN_MODULE.submit_form('cancel', 1);
}

function do_copy(){
    MIX_MATCH_MAIN_MODULE.submit_form('copy_promotion');
}

function cancel_selected_group(){
    MIX_MATCH_MAIN_MODULE.cancel_selected_group();
}

//function do_print(){
//    MIX_MATCH_MAIN_PRINT_PROMO_DIALOG.open();
//}

function group_checkbox_changed(group_id){
    MIX_MATCH_MAIN_MODULE.group_checkbox_changed(group_id);
}

function move_sequence_up(item_id){
    MIX_MATCH_MAIN_MODULE.move_item_sequence(item_id, 'up');
}

function move_sequence_down(item_id){
    MIX_MATCH_MAIN_MODULE.move_item_sequence(item_id, 'down');
}

function check_item_limit(item_id){
    MIX_MATCH_MAIN_MODULE.check_item_limit(item_id);
}

function check_group_limit(group_id){
    MIX_MATCH_MAIN_MODULE.check_group_limit(group_id);
}

function check_disc_by_type(item_id){
    MIX_MATCH_MAIN_MODULE.check_disc_by_type(item_id);
}

// SKU AUTOCOMPLETE DIALOG add
function add_autocomplete(){
	SKU_AUTOCOMPLETE_POPUP.add();
}

function category_point_inherit_changed(){
	MIX_MATCH_MAIN_MODULE.category_point_inherit_changed();
}

function category_point_value_changed(inp){
	MIX_MATCH_MAIN_MODULE.category_point_value_changed(inp);
}

function item_category_point_inherit_changed(group_id){
	MIX_MATCH_MAIN_MODULE.item_category_point_inherit_changed(group_id);
}
{/literal}
</script>


<!-- MIX AND MATCH DIALOG -->

<div id="div_mnm_choose_item_type_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:700px;height:380px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_mnm_choose_item_type_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;" id="span_mnm_choose_item_type_dialog_header">Choose Promotion Item Type</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_mnm_choose_item_type_dialog_content" style="padding:2px;">
		{include file='promotion.mix_n_match.open.choose_item_type_dialog.tpl'}
	</div>
</div>
<!-- End of MIX AND MATCH DIALOG -->

<!-- print dialog -->
{*<div id="div_print_promotion_dialog" class="curtain_popup" style="background:#fff;border:3px solid #000;width:250px;position:absolute; padding:10px; display:none;z-index:10000;">
	<form name="f_print" target="_blank">
		<img src="ui/print64.png" hspace="10" align="left"> <h3>Print Options</h3>
		<input type="hidden" name="a" value="print_promotion" />
		<input type="hidden" name="id" value="{$form.id}" />
		<input type="hidden" name="branch_id" value="{$form.branch_id}" />
		<p align="center">
			<font color="red">* Unsaved changes in Promotion will not be printed *</font><br>
			<input type="button" name="print_ok" value="Print" />
			<input type="button" name="print_cancel" value="Cancel" />
		</p>
	</form>
</div>*}
<!-- end of print dialog -->

<!-- condition receipt sample -->
<ul id="ul_disc_condition_receipt_sample" style="display:none;">
    {include file='promotion.mix_n_match.open.promo_item_row.disc_condition.tpl' disabled=1 is_receipt_row=1}
</ul>
<!-- end of condition receipt sample -->

<!-- WIZARD DIALOG -->
<div id="div_mnm_wizard_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:800px;height:650px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_mnm_wizard_dialog_header  ml-2" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;" id="span_mnm_wizard_dialog_header">Promotion Wizard</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" cla align="absmiddle" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.close_clicked();" class="clickable mr-2 mt-1"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<form name="f_pw" onSubmit="return false;">
		<div id="div_mnm_wizard_dialog_content" style="padding:2px;">
			{include file='promotion.mix_n_match.open.wizard_dialog.main.tpl'}
		</div>
	</form>
</div>
<!-- End of WIZARD DIALOG -->

<!-- SKU AUTOCOMPLETE DIALOG -->
<div id="div_sku_autocomplete_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:750px;height:150px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_sku_autocomplete_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Search SKU</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="SKU_AUTOCOMPLETE_POPUP.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sku_autocomplete_dialog_content" style="padding:2px;">
		<form name="f_sku_autocomplete" onSubmit="return false;">
			{include file='sku_items_autocomplete.tpl' parent_form='document.f_sku_autocomplete'}
			<div style="display:none;"><input type="checkbox" name="wizard_include_parent_child" value="1" /> Include Parent & Child</div>
		</form>
	</div>
</div>
<!-- End of SKU AUTOCOMPLETE  DIALOG -->

<!-- CAT/BRAND AUTOCOMPLETE DIALOG -->
<div id="div_cat_brand_autocomplete_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:750px;height:150px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_cat_brand_autocomplete_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Search Category / Brand</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="CAT_BRAND_AUTOCOMPLETE_POPUP.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_cat_brand_autocomplete_dialog_content" style="padding:2px;">
		<form name="f_cat_brand_autocomplete" onSubmit="return false;">
			<div id="div_cat_autocomplete">
				{include file='category_autocomplete2.tpl' parent_form='document.f_cat_brand_autocomplete' ext='_2'}
			</div>
			<div id="div_brand_autocomplete">
				{include file='brand_autocomplete.tpl' parent_form='document.f_cat_brand_autocomplete' ext='_2'}
			</div>
			<input type="button" id="inp_pw_add_cat_brand_combination" value="Add Combination" onClick="CAT_BRAND_AUTOCOMPLETE_POPUP.add_cat_brand_combination();" />
		</form>
	</div>
</div>
<!-- End of CAT/BRAND AUTOCOMPLETE  DIALOG -->

<!-- WARNING MESSAGE DIALOG -->

<div id="div_warning_message_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:680px;height:375px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;padding:0;">
  <div style="border:2px ridge #CE0000;color:rgb(255, 255, 255);background-color:#CE0000;padding:2px;cursor:default;">
	<span style="float:left;font-weight: bold" class="ml-1">Warning</span>
    <span style="float:right;" class="mr-1 mt-1">
      <img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();curtain(false, 'curtain2');" class="clickable"/>
    </span>
	<div style="clear:both;"></div>
  </div>
  <div style="padding: 20px;text-align:justify;font-weight: bold">
  <b class="text-danger">Dear user,</b><br>
  This mix and match feature will be able to output many types of promotions base on your own configurations.<br>
  You may use our wizard to guide you or start creating your own.<br><br>
  <b class="text-danger">PLEASE NOTE</b> that you should only launch the mix and match promotion created by you after have done through testing and found the outcome of what you have created is indeed the kind of promotion that you want to your customers.<br><br>
  <b class="text-primary">ARMS Software International Sdn Bhd</b> shall not be liable for any disappointment/failure of any mix and match promotion created and tested by the user.<br><br>
  <div style="text-align: center"><button class="btn btn-primary" onClick="default_curtain_clicked();curtain(false, 'curtain2');">OK</button></div>
  </div>
</div>
<!-- End of WARNING MESSAGE DIALOG -->
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				Mix and Match Promotion {if !is_new_id($form.id)}(ID#{$form.id}){else}(New){/if}
			</h4>
			<h5 class="content-title mb-0 my-auto ml-4 text-primary">
				Status:
{if $form.label eq 'approved'}
	Fully Approved
{elseif $form.label eq 'waiting_approve'}
	In Approval Cycle
{elseif $form.label eq 'cancelled'}
	Cancelled
{elseif $form.label eq 'terminated'}
	Terminated
{elseif $form.label eq 'rejected'}
	Rejected
{else}
	Draft
{/if}
			</h5>
			<span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{include file=approval_history.tpl}

{if $err}
<div><div class="errmsg"><ul>
{foreach from=$err item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}

{if $form.approval_screen}
	<form name="f_c" method=post>
		<input type=hidden name=a value="save_approval">
		<input type=hidden name=approve_comment value="">
		<input type=hidden name=id value="{$form.id}">
		<input type=hidden name=branch_id value="{$form.branch_id}">
		<input type=hidden name=approvals value="{$form.approvals}">
		<input type=hidden name=approval_history_id value="{$form.approval_history_id}">
		<input type="hidden" name="rejected_item_data" value="" />
	</form>
{/if}


<form name="f_a" method="post" onSubmit="return false;">
<div class="card mx-3">
	<div class="card-body">
		<div class="stdframe">
			<h4 class="form-label">General Information</h4>
			
			{if $errm.top}
				<div class="alert alert-danger mx-3 rounded">
					<div id="err"><div class="errmsg"><ul>
						{foreach from=$errm.top item=e}
						<li> {$e}
						{/foreach}
					</ul></div></div>
				</div>
			{/if}
			
			<input type="hidden" name="a" value="save" />
			<input type="hidden" name="branch_id" value="{$form.branch_id}" />
			<input type="hidden" name="id" value="{$form.id}" />
			<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
			<input type="hidden" name="reason" />
			{if $is_under_gst}
				<input type="hidden" name="global_gst_inclusive_tax" value="{$global_gst_settings.inclusive_tax}" />
			{/if}
			<table border="0" cellspacing="0" cellpadding="4">
				<tr>
					<td><b class="form-label">Title</b></td>
					<td><input  type="text" name="title" maxlength="200" size="80" value="{$form.title|escape}" class="required form-control" title="Title" /></td>
				</tr>
				<tr>
					<td><b class="form-label">Date</b></td>
					<td>
					<div class="form-inline">
						<input type="text" name="date_from" id="inp_date_from" size="12" value="{$form.date_from|default:$smarty.now|date_format:"%Y-%m-%d"}" class="required form-control" title="Date From" />
						{if $allow_edit}
							&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date" />
						{/if}
						<b class="form-label">&nbsp;to&nbsp; </b>
						<input type="text" name="date_to" id="inp_date_to" size="12" value="{$form.date_to|default:$smarty.now|date_format:"%Y-%m-%d"}" class="required form-control" title="Date To" />
						{if $allow_edit}
							&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date" />
						{/if}
					</div>
					</td>
				</tr>
				<tr>
					<td><b class="form-label">Time</b></td>
					<td>
						<div class="form-inline">
							<input type="text" name="time_from"  value="{if $form.time_from>0}{$form.time_from|date_format:"%H:%M"}{else}00:00{/if}" size="10" class="required form-control" title="Time From" />
						<b class="form-label">&nbsp;to&nbsp; </b>
						<input type="text" name="time_to"  value="{if $form.time_to>0}{$form.time_to|date_format:"%H:%M"}{else}23:59{/if}" size="10" class="required form-control" title="Time To" /> <b class="form-label">&nbsp;(hh:mm)</b>
						</div>
					</td>
				</tr>
				
				{if !$config.promotion_hide_member_options}
					<tr>
						<td valign="top"><b class="form-label">Member Reward Point</b></td>
						<td>
							{if $sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}
								<select class="form-control" name="category_point_inherit" onChange="category_point_inherit_changed();">
									{foreach from=$category_point_inherit_options key=k item=w}
										<option value="{$k}" {if $form.category_point_inherit eq $k}selected {/if}>{$w}</option>
									{/foreach}
								</select>
							{else}
								<b>
									{foreach from=$category_point_inherit_options key=k item=w}
										{if $form.category_point_inherit eq $k}{$w}{/if}
									{/foreach}
								</b>
								<input type="hidden" name="category_point_inherit" value="{$form.category_point_inherit}">
							{/if}
					
							<div id="div_cat_point" style="padding:5px;{if $form.category_point_inherit ne 'set'}display:none;{/if}">
								{if $sessioninfo.privilege.MEMBER_POINT_REWARD_EDIT}
									<b class="form-label">Please enter how many {$config.arms_currency.symbol} for each point.</b>
								{/if}
								
								<table class="report_table">
									<tr class="header">
										<td>&nbsp;</td>
										<td>({$config.arms_currency.symbol} <b>X</b> for 1 Point)</td>
									</tr>
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
												<th colspan="2" class="form-label">
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
					<td valign="top"><b class="form-label">Branches</b></td>
					<td>
						{if ($form.branch_id==1 and $smarty.request.a ne 'refresh') and $form.id > 1000000000}
							<b class="form-label">
								You may select multiple branches
							</b> <br>
							<table class="small" border="0" id="tbl_branch">
								<tr>
									<td><input type="checkbox" name="all_branches" value="1" onclick="MIX_MATCH_MAIN_MODULE.toggle_all_branches(this);" /> All</td>
								</tr>
								{foreach from=$branches key=bid item=b}
									<tr>
										<td valign="top">
										<input type="checkbox" class="chx_branch" name="promo_branch_id[{$bid}]" value="{$b.code}" {if $form.promo_branch_id.$bid}checked {/if} /> {$b.code}
										</td>
									</tr>
								{/foreach}
							</table>
						{else}
							<table class="small" border="0">
							{if $BRANCH_CODE eq 'HQ'}
								<tr>
									<td><input type="checkbox" name="all_branches" value="1" onclick="MIX_MATCH_MAIN_MODULE.toggle_all_branches(this);" /> All</td>
								</tr>
							{/if}
							{foreach from=$branches key=bid item=b}
								{if $BRANCH_CODE eq 'HQ'}
									<tr>
										<td valign="top">
										<input type="checkbox" class="chx_branch" name="promo_branch_id[{$bid}]" type="hidden" value="{$b.code}" {if $form.promo_branch_id.$bid}checked {/if} /> {$b.code}
										</td>
									</tr>
								{else}
									{if $form.promo_branch_id.$bid or $form.branch_id eq $bid}
										<tr>
											<td valign="top">
												<span style="display:none;">
													<input type="checkbox" class="chx_branch" name="promo_branch_id[{$bid}]" type="hidden" value="{$bcode}" checked />
												</span>
												{$b.code}
											</td>
										</tr>
									{/if}
								{/if}
							{/foreach}
							</table>
						{/if}
					</td>
				</tr>
			</table>
			
			<div id="div_refresh" style="{if $smarty.request.a eq 'refresh' || $smarty.request.id} display:none; {/if} padding-top:10px">
				<input class="btn btn-primary" id="btn_refresh" type="button" value="click here to continue" />
			</div>
			</div>
	</div>
</div>
{if $smarty.request.a eq 'refresh' or !is_new_id($form.id) or $err}
	<div id="promo_items_group_list">
		{if $items}
		    {foreach from=$items.group_list key=gid item=promo_group}
		    	{include file='promotion.mix_n_match.open.group.tpl' group_id=$gid}
		    {/foreach}
	    {else}
	        {include file='promotion.mix_n_match.open.group.tpl' group_id=1 is_first=1}
	    {/if}
	</div>
{/if}
</form>

{if $form.is_approval and $form.status==1 and $form.approved==0 and $form.approval_screen}
	<p align="center">
		<input type=button value="Approve" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_approve({$form.last_approver},'mix&match')">
		<input type=button value="Reject" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_reject({$form.last_approver})">
		<input type=button value="Terminate" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_terminate({$form.last_approver})">
	</p>
{/if}
	
{if !$form.approval_screen}
    {if $allow_edit and !$form.first_time}
        <p>
	        <button id="btn_new_mnm_group" class="btn btn-primary mx-3"> <img src="/ui/add.png" border="0" align="absmiddle" /> Add New Mix and Match Group</button>
	        <span id="span_adding_mnm_loading" style="display:none;background: yellow;padding:2px;">
				<img src="/ui/clock.gif" align="absmiddle" /> Loading...
			</span>
		</p>
    {/if}
    
	<!-- Warning Message -->
	<div class="alert alert-danger mx-3 rounded">
	  <b class="text-danger">Dear user,</b><br><br>
	  This mix and match feature will be able to output many types of promotions base on your own configurations.<br>
	  You may use our wizard to guide you or start creating your own.<br><br>
	  <b class="alert-danger">PLEASE NOTE</b> that you should only launch the mix and match promotion created by you after you have done thorough testing and found what you have created is indeed what you want to launch to your customers.<br><br>
	  ARMS Software International Sdn Bhd shall not be liable for any disappointment/failure of any mix and match promotion created and tested by the user.
	</div>
	<!-- end of Warning Message -->
	
    <p align="center">
        {if $allow_edit and (!$form.status or $form.status eq 2) and !$form.approved and !$form.first_time}
			<input class="btn btn-primary" type="button" value="Save & Close" onclick="do_save();" />
		{/if}
		
        {if is_new_id($form.id) || !$allow_edit}
		<input class="btn btn-danger" type=button value="Close" onclick="document.location='/promotion.php'" />
		{/if}

		{if !$form.first_time}
			{if ($form.user_id eq $sessioninfo.id || $sessioninfo.level>=$config.doc_reset_level || $sessioninfo.privilege.PROMOTION_CANCEL) and $sessioninfo.branch_id eq $form.branch_id and !is_new_id($form.id)}
				{if $form.approved}
					{if $form.status!=4 && $form.status!=5 && $form.status!=0 && $form.active}
						<input class="btn btn-warning" type=button value="Cancel Promotion" onclick="do_cancel();" />
	
					{/if}
				{elseif ($form.active || !$form.status) && $allow_edit}
					<input class="btn btn-danger" type=button value="Delete" onclick="do_delete()" />
				{/if}
			{/if}

	        {if $form.branch_id == $sessioninfo.branch_id and !is_new_id($form.id)}
				<input class="btn btn-primary" type="button" value="Copy" onclick="do_copy();" />
			{/if}

			{if $allow_edit and $form.status == 0 and $form.approved == 0}
			<input class="btn btn-success" type=button value="Confirm" onclick="do_confirm();" />
			{/if}

			{if $form.status == 1 and $form.approved == 1 and $form.active == 1 and $sessioninfo.privilege.PROMOTION_CANCEL and $sessioninfo.branch_id eq $form.branch_id and !is_new_id($form.id)}
			<input class="btn btn-warning" type=button value="Cancel Selected Group(s)" onclick="cancel_selected_group();" />
			{/if}
			
			{if !is_new_id($form.id) && $form.active && $form.status<=1}
			<input class="btn btn-primary" type="button" value="Print{if !$form.status} Draft{elseif !$form.approved} Proforma{/if} Promotion" onclick="PROMO_PRINT.show('{$form.branch_id}', '{$form.id}', '{$form.promo_type}', '', '{$form.str_promo_branch_id_list}', '{$form.active}', '{$form.status}', '{$form.approved}');" />
			{/if}
		{/if}
    </p>
{/if}


{if !$form.approval_screen}{include file='footer.tpl'}{/if}

<script>

{literal}
	MIX_MATCH_MAIN_MODULE.initialize();
{/literal}
</script>
