{*
4/12/2011 3:18:48 PM Andy
- Add can choose PO to which branch for HQ.
- Add new re-order filter type: check item got DO in given date range.
- Add filter "last 30 days sales" and "sales in date range" can choose sales from other branches (multiple), only available to HQ.

6/21/2011 10:54:19 AM Andy
- Allow report to search higher level category.
- Add nowrap class for branch checkbox selection.

9/6/2011 2:29:12 PM Andy
- Add can pre-generate stock reorder SKU by branch,vendor and dept using cron.
- Add report can load data by using pregen SKU.

9/8/2011 3:48:10 PM Andy
- Add sorting for SKU.

9/14/2011 2:55:47 PM Andy
- Add IBT, Delivery and Cancellation Date for report.

10/12/2011 2:42:33 PM Andy
- Add partial delivery.

10/12/2011 2:52:42 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

6/28/2012 PM Andy
- Add new reorder type which calculate by vendor grace period.
- Add pregen reorder list can define reorder type by using config.

6/7/2013 11:17 AM Justin
- Bug fixed on branch list show out of screen.

10/7/2013 5:35 PM Fithri
- Allow user to key in PO date when generate PO

4/30/2014 10:26 AM Justin
- Bug fixed on view pending PO is not working.
- Bug fixed on if there is more than 2 vendors having notifications for Vendor Stock Reorder, system only show one vendor.

10/1/2014 10:30 AM Fithri
- combine two option together, 'Item got sales between date range' and 'Item got DO between date range' (POS + DO - Stock Balance - PO)

7/27/2015 10:35 AM Andy
- Added the branch code in the stock balance column.

8/27/2015 10:00 AM Justin
- Bug fixed on Suggested PO Qty will become invalid while reorder more than thousand qty.

12/14/2015 1:27 PM DingRen
- add Include inactive Vendor filter

6/22/2016 11:30 AM Andy
- Enhanced to able to generate to DO.

6/28/2016 3:38 PM Andy
- Enhanced to send po items to purchase agreement page for editing, if no purchase agreement, it will direct generate to po.

9/27/2016 5:47 PM Andy
- Enhanced to fix firefox connection reset issue.

10/24/2016 11:56 AM Andy
- Fix suggested po qty bugs.
- Added suggested po in pcs.

2/20/2017 3:47 PM Andy
- Add sub-title "(Stock Balance As Per Login Branch)".

5/10/2017 11:40 AM Andy
- Add "Reorder by Branch" feature.
- Enhanced to hide "PO Reorder Qty" column if reorder type is not "less_than_po_reorder_min".

6/14/2017 11:34 AM Andy
- Enhanced the calculation to include uncheckout gra.

6/20/2017 4:22 PM Andy
- Change the word "Generate PO" to "Generate".
- When reorder by branch and select generate to DO, need disabled the own login branch.

6/29/2017 10:00 AM Andy
- Added generate to DO Request (Branch Only).

10/27/2017 4:23 PM Andy
- Enhanced to able to generate DO by vendor.

11/1/2017 5:32 PM Andy
- Enhanced to always show HQ Stock Balance when reorder by branch.
- Enhanced to able to select branch by branch group.

3/30/2018 4:13PM HockLee
- Export PO to csv format.

5/30/2019 1:15 PM William
- Added new filter Order By MOQ.
- Added new warning stock balance is less than reorder max or moq.

06/24/2020 02:54 PM Sheila
- Updated button css
*}

{if !$header_printed}
{include file='header.tpl'}
{/if}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}
.negative{
	color: red;
	font-weight: bold;
}
{/literal}
</style>

<script type="text/javascript">
var phpself = '{$smarty.request.PHP_SELF}';
var using_pregen_sku = int('{if $data.pregen_sku_data}1{else}0{/if}');
var got_do_request = int('{$got_do_request}');
var got_expect_do_date = int('{$got_expect_do_date}');
var submitted_reorder_bid = [];
{if $data.reorder_bid}
	{foreach from=$data.reorder_bid item=bid}
		submitted_reorder_bid.push('{$bid}');
	{/foreach}
{/if}
{literal}
var STOCK_REORDER = {
	f_vendor: undefined,
    category_autocompleter: undefined,
    close_curtain2_btn: "<input type='button' value='Close' onClick='close_curtain2();' />",
	initialize: function(){
		// initial category autocomplete
		this.init_category_autocomplete();
		this.f_vendor = document.f_vendor;
		
		// initial calendar
		this.init_calendar();
		
		new Draggable('div_pending_po_dialog',{ handle: 'div_pending_po_dialog_header'});
		new Draggable('div_generate_po_dialog',{ handle: 'div_generate_po_dialog_header'});
		
		if(using_pregen_sku){	// alert user if got user open it b4
			if($('inp_open_by_user_u'))	alert('This pregen SKU list has been first opened by '+$('inp_open_by_user_u').value);
		}
	},
	init_category_autocomplete: function(){
	    // intialize category autocompleter
	    var param_str = "ajax_autocomplete.php?a=ajax_search_category";
        this.category_autocompleter = new Ajax.Autocompleter("autocomplete_category", "autocomplete_category_choices", param_str,
		{
			afterUpdateElement: function (obj,li)
			{
			    this.defaultParams = '';
				var s = li.title.split(',');
				document.f_a.category_id.value = s[0];

				var str = new String(obj.value);
				str.replace('<span class=sh>', '');
				str.replace('</span>', '');
				document.f_a.category_tree.value = str;
				$('str_cat_tree').innerHTML = str;
				obj.value = str.substr(str.lastIndexOf(">")+2, str.length);
			},
			indicator: 'span_autocomplete_loading'
		});
		
		// event when user focus to category
		$('autocomplete_category').observe('focus', function(){
            STOCK_REORDER.category_autocompleter.options.defaultParams='';
            this.select();
            var default_text = $(this).readAttribute('default_text').trim();
            var curr_text = $(this).value.trim();
            
            if(curr_text==default_text) $(this).value='';
		})
		// event when user out focus the input
		.observe('blur', function(){
            var default_text = $(this).readAttribute('default_text').trim();
            var curr_text = $(this).value.trim();
            
            if(curr_text=='')   $(this).value = default_text;
		});
	},
	// function initial all calendar
	init_calendar: function(){
	    // sales date from
        Calendar.setup({
			inputField     :    "inp_date_range_from",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_date_range_from",
			align          :    "Bl",
			singleClick    :    true
		});
		
		// sales date to
		Calendar.setup({
			inputField     :    "inp_date_range_to",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_date_range_to",
			align          :    "Bl",
			singleClick    :    true
		});
		
		if(this.f_vendor){
			// PO Date
			Calendar.setup({
				inputField     :    "inp_po_date",
				ifFormat       :    "%Y-%m-%d",
				button         :    "img_po_date",
				align          :    "Bl",
				singleClick    :    true
			});
			
			// Delivery Date
			Calendar.setup({
				inputField     :    "inp_delivery_date",
				ifFormat       :    "%Y-%m-%d",
				button         :    "img_delivery_date",
				align          :    "Bl",
				singleClick    :    true
			});
			
			// Cancel Date
			Calendar.setup({
				inputField     :    "inp_cancel_date",
				ifFormat       :    "%Y-%m-%d",
				button         :    "img_cancel_date",
				align          :    "Bl",
				singleClick    :    true
			});
			
			// DO Date
			Calendar.setup({
				inputField     :    "inp_do_date",
				ifFormat       :    "%Y-%m-%d",
				button         :    "img_do_date",
				align          :    "Bl",
				singleClick    :    true
			});
			
			if(got_do_request){
				if(got_expect_do_date){
					// Export DO Date
					Calendar.setup({
						inputField     :    "inp_expect_do_date",
						ifFormat       :    "%Y-%m-%d",
						button         :    "img_expect_do_date",
						align          :    "Bl",
						singleClick    :    true
					});
				}
			}
		}
	},
	// event when user click find sub category
	show_cat_child: function(cat_id){
        setTimeout("STOCK_REORDER.category_autocompleter.options.defaultParams = 'child="+cat_id+"';STOCK_REORDER.category_autocompleter.activate();",250);
	},
	// function to show SKU pending PO
	show_pending_po: function(sid, po_reorder_by_child, reorder_by_branch, show_bid){
		reorder_by_branch = reorder_by_branch == undefined ? 0 : reorder_by_branch;
		show_bid = show_bid == undefined ? 0 : show_bid;
		
	    curtain(true);
	    $('div_pending_po_dialog_content').update(_loading_);
		center_div($('div_pending_po_dialog').show());
		
		if (document.f_a['incl_not_approved'].checked) var incl_not_approved = 'on';
		else var incl_not_approved = '';
		new Ajax.Updater('div_pending_po_dialog_content', phpself+'?a=ajax_show_pending_po',{
		    parameters:{
				sid: sid,
				po_reorder_by_child: po_reorder_by_child,
				incl_not_approved: incl_not_approved,
				reorder_by_branch: reorder_by_branch,
				show_bid: show_bid,
				"submitted_reorder_bid[]": submitted_reorder_bid
			},
			evalScripts: true
		});
	},
	// function to toggle checked/un-checked generate po for sku in vendor group
	toggle_vendor_generate_po: function(vendor_id){
	    if(!vendor_id)  return; // invalid id
	    
	    var c = $('chx_toggle_vendor_generate_po-'+vendor_id).checked;
	    
		$$('#div_vendor-'+vendor_id+' input.chx_generate_po[type="checkbox"]').each(function(ele){
			$(ele).checked = c;
		});
	},
	// function to drop whole vendor group
	remove_vendor: function(vendor_id){
		if(!vendor_id)  return; // invalid vendor id
		
		if(!confirm('Are you sure?'))   return false;
		
		var div_vendor = $('div_vendor-'+vendor_id);
		Effect.DropOut(div_vendor, {
			duration:0.5,
			afterFinish: function() {
				$(div_vendor).remove();
			}
		});
	},
	generate_po: function(vendor_id){
		// check delivery and cancellation date
		var po_date = strtotime(this.f_vendor['po_date'].value.trim());
		var delivery_date = strtotime(this.f_vendor['delivery_date'].value.trim());
		var cancel_date = strtotime(this.f_vendor['cancel_date'].value.trim());
		var reorder_by_branch = int(this.f_vendor['reorder_by_branch'].value);
		
		if(!po_date){
			alert('Please select PO date.');
			this.f_vendor['po_date'].focus();
			return false;
		}	
		if(!delivery_date){
			alert('Please select delivery date.');
			this.f_vendor['delivery_date'].focus();
			return false;
		}	
		if(!cancel_date){
			alert('Please select cancellation date.');
			this.f_vendor['cancel_date'].focus();
			return false;
		}
		if(cancel_date<=delivery_date){
			alert('Cancellation date cannot same or early than Delivery date.');
			this.f_vendor['cancel_date'].focus();
			return false;
		}
		if(!reorder_by_branch){
			if(!this.f_vendor['po_branch_id'].value){
				alert('Please select Delivery Branch.');
				this.f_vendor['po_branch_id'].focus();
				return false;
			}
		}else{
			var delivery_branch_count = 0;
			$(this.f_vendor).getElementsBySelector('input[name="po_deliver_to[]"]').each(function(inp){
				if(inp.checked){
					delivery_branch_count++;
				}
			});
			if(delivery_branch_count<=0){
				alert('Please select at least one Delivery Branch.');
				return false;
			}
		}
        vendor_id = int(vendor_id); // escape integer
        var checked_chx = this.get_checked_items(vendor_id);
        
        // remove highligh color
        $$('#f_vendor tr.tr_sku').invoke('removeClassName', 'highlight_row');
        
        /*if(vendor_id){  // generate single PO
            var container = $('div_vendor-'+vendor_id);
		}else{  // generate po for all vendor
		    var container = $('f_vendor');
		}
		
		// get all checked input
		$(container).getElementsBySelector('input.chx_generate_po[type="checkbox"]').each(function(ele){
			if(ele.checked) checked_chx.push(ele);
		});*/
		
		if(checked_chx.length<=0){  // no item is selected
			alert('Please select at least 1 item to generate PO.');
			return;
		}
		
		// check duplicate
		if(!vendor_id){
            for(var i=0; i<checked_chx.length; i++){
				var chx = checked_chx[i];
				var parent_tr = chx.parentNode.parentNode;
				var sku_id = $(parent_tr).readAttribute('sku_id').trim();

				var sku_id_tr = this.get_selected_sku_tr(sku_id, checked_chx);
				if(sku_id_tr.length>1){
					for(var j=0; j<sku_id_tr.length; j++){
	                    $(sku_id_tr[j]).addClassName('highlight_row');
					}
					$(sku_id_tr[0]).scrollTo();
					alert('There is an item listed in more than 1 vendor, please only select for 1 vendor');
					return false;
				}
			}
		}
		
		if(!confirm('Are you sure?'))   return false;
		
        curtain(true, 'curtain2');
		$('span_generate_po_title').update('Generate PO');
        center_div($('div_generate_po_dialog').show());
        $('div_generate_po_dialog_content').update('Generating PO...<br />'+_loading_);
        
        var close_curtain2_btn = this.close_curtain2_btn;
        new Ajax.Request(phpself+'?a=ajax_generate_po', {
            method: 'post',
			parameters: $('f_vendor').serialize()+'&selected_vendor_id='+vendor_id,
			onComplete: function(msg){
                var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_generate_po_dialog_content').update(ret['html']+'<br>'+close_curtain2_btn);
	                    if(ret['po_generated'])	alert('PO Generate Successfully.');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    close_curtain2();
			    alert(err_msg);
			}
		});
	},

	export_po: function(vendor_id){		
        vendor_id = int(vendor_id); // escape integer
        var checked_chx = this.get_checked_items(vendor_id);
        
        // remove highligh color
        $$('#f_vendor tr.tr_sku').invoke('removeClassName', 'highlight_row');
        
		if(checked_chx.length<=0){  // no item is selected
			alert('Please select at least 1 item to export PO.');
			return;
		}
		
		// check duplicate
		if(!vendor_id){
            for(var i=0; i<checked_chx.length; i++){
				var chx = checked_chx[i];
				var parent_tr = chx.parentNode.parentNode;
				var sku_id = $(parent_tr).readAttribute('sku_id').trim();

				var sku_id_tr = this.get_selected_sku_tr(sku_id, checked_chx);
				if(sku_id_tr.length>1){
					for(var j=0; j<sku_id_tr.length; j++){
	                    $(sku_id_tr[j]).addClassName('highlight_row');
					}
					$(sku_id_tr[0]).scrollTo();
					alert('There is an item listed in more than 1 vendor, please only select for 1 vendor');
					return false;
				}
			}
		}
		
		if(!confirm('Export to CSV?\n\nPlease ensure the item has Mcode and/or Art Number.'))   return false;
		
        curtain(true, 'curtain2');
		$('span_generate_po_title').update('Export PO');
        center_div($('div_generate_po_dialog').show());
        $('div_generate_po_dialog_content').update('Exporting PO...<br />'+_loading_);
        
        var close_curtain2_btn = this.close_curtain2_btn;
        new Ajax.Request(phpself, {
            method: 'post',
			parameters: $('f_vendor').serialize()+'&a=export_po&selected_vendor_id='+vendor_id,
			onComplete: function(msg){
                var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_generate_po_dialog_content').update(ret['html']+'<br>'+close_curtain2_btn);
	                    if(ret['po_exported'])	alert('PO Export Successfully.');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    close_curtain2();
			    alert(err_msg);
			}
		});
	},
	
	// function to get selected sku row
	get_selected_sku_tr: function(checke_sku_id, checked_chx){
	    var sku_id_tr = [];
	    
        for(var i=0; i<checked_chx.length; i++){
			var chx = checked_chx[i];
			var parent_tr = chx.parentNode.parentNode;
			var sku_id = $(parent_tr).readAttribute('sku_id').trim();
			if(checke_sku_id==sku_id)   sku_id_tr.push(parent_tr);
		}
		return sku_id_tr;
	},
	// event when user click submit form
	submit_form: function(){
	    // check reorder type
	    var reorder_type = document.f_a['reorder_type'].value;
	    if(reorder_type==''){
			alert('Please select re-order type.');
			return false;
		}else{
			if(reorder_type=='sales_range' || reorder_type=='sales_range_plus_do'){
				if(document.f_a['date_range_from'].value.trim()==''){
					alert('Please select sales date from.');
					return false;
				}
				if(document.f_a['date_range_to'].value.trim()==''){
					alert('Please select sales date to.');
					return false;
				}
			}else if(reorder_type == 'less_than_po_reorder_min'){
				if(document.f_a['reorder_by_branch']){
					document.f_a['reorder_by_branch'].checked = false;
					document.f_a['show_reorder_details_by_branch'].checked = false;
				}
			}
		}
		
		// check category
		if(document.f_a['category_id'].value==''){
			alert('Please select category.');
			return false;
		}
		
		// check branch list
		if($('div_reorder_branch_list').style.display==''){	// branch list is showing
			var got_select_bid = false;
			var chx_reorder_bid = $$("#div_reorder_branch_list input.chx_reorder_bid");
			for(var i=0; i<chx_reorder_bid.length; i++){
				if(chx_reorder_bid[i].checked){
					got_select_bid = true;
					break;
				}
			}

			if(chx_reorder_bid.length>1 && !got_select_bid){
				alert('Please select at least one branch.');
				return false;
			}
		}
		
		document.f_a.submit();
	},
	// function to check reorder type
	check_reorder_type: function(){
		var reorder_type = document.f_a['reorder_type'].value;
		var show_reorder_branch_list = false;
		var show_reorder_moq_checkbox = false;
		// span for date range
		if(reorder_type=='sales_range' || reorder_type=='sales_range_plus_do' || reorder_type=='do_range'){
			$('span_reorder_date_range').show();
		}else{
			$('span_reorder_date_range').hide();
		}
		
		// span for branch list
		if(reorder_type=='less_than_sales' || reorder_type=='sales_range' || reorder_type=='sales_range_plus_do' || reorder_type=='less_then_grace_period'){
			show_reorder_branch_list = true;
		}
		
		if(show_reorder_branch_list){
			$('div_reorder_branch_list').show();
		}else{
			$('div_reorder_branch_list').hide();
		}
		
		//span for MOQ 
		if(reorder_type=='less_than_po_reorder_min'){
			show_reorder_moq_checkbox = true;
		}
		
		if(show_reorder_moq_checkbox){
			$('span_reorder_moq_checkbox').show();
		}else{
			$('span_reorder_moq_checkbox').hide();
		}
	},
	// function to toggle branch list checkbox
	toggle_reorder_branch_list: function(){
        var chx_toggle_reorder_branch_list = $('chx_toggle_reorder_branch_list');

		if(!chx_toggle_reorder_branch_list)    return; // element not found

		var c = chx_toggle_reorder_branch_list.checked;
		$$("#div_reorder_branch_list input.chx_reorder_bid").each(function(chx){
			chx.checked = c;
		});
	},
	// function when user change sorting option
	change_sort_by: function (ele){
		if(ele.value=='')   $('span_sort_order').hide();
		else    $('span_sort_order').show();
	},
	// function when user change generate type
	generate_type_changed: function(){
		var t = this.f_vendor['generate_type'].value;
		
		$$('#div_generate_details div.div_generate_details').invoke('hide');
		$$('input.inp_generate').invoke('hide');
		$('div_info-'+t).show();
		$$('input.inp_generate-'+t).invoke('show');
	},
	// function when user click genereate do
	generate_do: function(vendor_id){
		// checking
		var do_date = strtotime(this.f_vendor['do_date'].value.trim());
		var reorder_by_branch = int(this.f_vendor['reorder_by_branch'].value);
		
		if(!do_date){
			alert('Please select DO date.');
			this.f_vendor['do_date'].focus();
			return false;
		}
		
		if(!reorder_by_branch){
			if(!this.f_vendor['do_branch_id'].value){
				alert('Please select Delivery Branch.');
				this.f_vendor['do_branch_id'].focus();
				return false;
			}
		}else{
			var delivery_branch_count = 0;
			$(this.f_vendor).getElementsBySelector('input[name="do_deliver_to[]"]').each(function(inp){
				if(inp.checked){
					delivery_branch_count++;
				}
			});
			if(delivery_branch_count<=0){
				alert('Please select at least one Delivery Branch.');
				return false;
			}
		}
		
		vendor_id = int(vendor_id); // escape integer
        var checked_chx = this.get_checked_items(vendor_id);
		
		if(checked_chx.length<=0){  // no item is selected
			alert('Please select at least 1 item to generate.');
			return;
		}
		
		if(!confirm('Are you sure?'))   return false;
		
        curtain(true, 'curtain2');
		$('span_generate_po_title').update('Generate DO');
        center_div($('div_generate_po_dialog').show());
        $('div_generate_po_dialog_content').update('Generating DO...<br />'+_loading_);
        
        var THIS = this;
        new Ajax.Request(phpself, {
            method: 'post',
			parameters: $('f_vendor').serialize()+'&a=ajax_generate_do&selected_vendor_id='+vendor_id,
			onComplete: function(msg){
                var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_generate_po_dialog_content').update(ret['html']+'<br><br>'+THIS.close_curtain2_btn);
	                    alert('DO Generate Successfully.');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    close_curtain2();
			    alert(err_msg);
			}
		});
	},
	// function to show SKU pending PO
	show_pending_do: function(sid, po_reorder_by_child, reorder_by_branch, show_bid){
		reorder_by_branch = reorder_by_branch == undefined ? 0 : reorder_by_branch;
		show_bid = show_bid == undefined ? 0 : show_bid;
		
	    curtain(true);
	    $('div_pending_do_dialog_content').update(_loading_);
		center_div($('div_pending_do_dialog').show());
		
		if (document.f_a['incl_not_approved'].checked) var incl_not_approved = 'on';
		else var incl_not_approved = '';
		new Ajax.Updater('div_pending_do_dialog_content', phpself+'?a=ajax_show_pending_do',{
		    parameters:{
				sid: sid,
				po_reorder_by_child: po_reorder_by_child,
				incl_not_approved: incl_not_approved,
				reorder_by_branch: reorder_by_branch,
				show_bid: show_bid,
				"submitted_reorder_bid[]": submitted_reorder_bid
			},
			evalScripts: true
		});
	},
	// function when user change reorder by branch
	reorder_by_branch_changed: function(){
		if(document.f_a['reorder_by_branch'].checked){
			document.f_a['show_reorder_details_by_branch'].disabled = false;
		}else{
			document.f_a['show_reorder_details_by_branch'].disabled = true;
			document.f_a['show_reorder_details_by_branch'].checked = false;
		}
	},
	// function to show uncheckout gra
	show_pending_gra: function(sid, po_reorder_by_child, reorder_by_branch, show_bid){
		reorder_by_branch = reorder_by_branch == undefined ? 0 : reorder_by_branch;
		show_bid = show_bid == undefined ? 0 : show_bid;
		
	    curtain(true);
	    $('div_pending_gra_dialog_content').update(_loading_);
		center_div($('div_pending_gra_dialog').show());
		
		new Ajax.Updater('div_pending_gra_dialog_content', phpself+'?a=ajax_show_pending_gra',{
		    parameters:{
				sid: sid,
				po_reorder_by_child: po_reorder_by_child,
				reorder_by_branch: reorder_by_branch,
				show_bid: show_bid,
				"submitted_reorder_bid[]": submitted_reorder_bid
			},
			evalScripts: true
		});
	},
	// function to count how many item checked
	get_checked_items: function(vendor_id){
		var checked_chx = [];
                
        if(vendor_id){  // single vendor
            var container = $('div_vendor-'+vendor_id);
		}else{  // generate po for all vendor
		    var container = $('f_vendor');
		}
		
		// get all checked input
		$(container).getElementsBySelector('input.chx_generate_po[type="checkbox"]').each(function(ele){
			if(ele.checked) checked_chx.push(ele);
		});
		
		return checked_chx;
	},
	// function when user click generate do request
	generate_do_request: function(){
		// checking
		if(got_expect_do_date){
			var expect_do_date = strtotime(this.f_vendor['expect_do_date'].value.trim());
		
			if(!expect_do_date){
				alert('Please select Expected Delivery Date.');
				this.f_vendor['expect_do_date'].focus();
				return false;
			}
		}
		
		if(!this.f_vendor['request_branch_id'].value){
			alert('Please Choose Supply Branch.');
			this.f_vendor['request_branch_id'].focus();
			return false;
		}
		
		if(this.get_checked_items().length<=0){  // no item is selected
			alert('Please select at least 1 item to generate.');
			return;
		}
		
		if(!confirm('Are you sure?'))   return false;
		
        curtain(true, 'curtain2');
		$('span_generate_po_title').update('Generate DO Request');
        center_div($('div_generate_po_dialog').show());
        $('div_generate_po_dialog_content').update('Generating DO Request...<br />'+_loading_);
        
        var THIS = this;
        new Ajax.Request(phpself, {
            method: 'post',
			parameters: $('f_vendor').serialize()+'&a=ajax_generate_do_request',
			onComplete: function(msg){
                var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_generate_po_dialog_content').update(ret['html']+'<br><br>'+THIS.close_curtain2_btn);
	                    alert('DO Request Generate Successfully.');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    close_curtain2();
			    alert(err_msg);
			}
		});
	},
	show_pending_do_request: function(sid, po_reorder_by_child){
	    curtain(true);
	    $('div_pending_do_request_dialog_content').update(_loading_);
		center_div($('div_pending_do_request_dialog').show());
		
		new Ajax.Updater('div_pending_do_request_dialog_content', phpself+'?a=ajax_show_pending_do_request',{
		    parameters:{
				sid: sid,
				po_reorder_by_child: po_reorder_by_child
			},
			evalScripts: true
		});
	},
	check_branch_by_group: function(){
		var sl_brn_grp = $('sel_brn_grp');
		var sel_grp_val = sl_brn_grp.value;
		
		if (sel_grp_val){
			var sel_brn_list = sel_grp_val.split(',');
			
			for (i=0, len=sel_brn_list.length; i<len; i++){	
				if (!$('reorder_bid-'+sel_brn_list[i]).checked){
					$('reorder_bid-'+sel_brn_list[i]).checked = true;
				}
			}
		} else {
			var all_brn = $(document.f_a).getElementsBySelector("input.chx_reorder_bid");
			
			for (i=0, len=all_brn.length; i<len; i++){
				if (!all_brn[i].checked){
					all_brn[i].checked = true;
				}
			}
		}
	},
	uncheck_branch_by_group: function(){
		var sl_brn_grp = $('sel_brn_grp');
		var sel_grp_val = sl_brn_grp.value;
		
		if (sel_grp_val){
			var sel_brn_list = sel_grp_val.split(',');
		
			for (i=0, len=sel_brn_list.length; i<len; i++){
				if ($('reorder_bid-'+sel_brn_list[i]).checked){
					$('reorder_bid-'+sel_brn_list[i]).checked = false;
				}	
			}
		} else {
			var all_brn = $(document.f_a).getElementsBySelector("input.chx_reorder_bid");
			
			for (i=0, len=all_brn.length; i<len; i++){
				if (all_brn[i].checked){
					all_brn[i].checked = false;
				}
			}
		}
	}
}

function show_child(cat_id){
	STOCK_REORDER.show_cat_child(cat_id);
}

function close_curtain2(){
    curtain(false, 'curtain2');
	$('div_generate_po_dialog').hide();
}

{/literal}
</script>

<!-- Pending PO dialog -->
<div id="div_pending_po_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_pending_po_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Pending PO List</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_pending_po_dialog_content" style="padding:2px;height:400px;overflow-y:auto;"></div>
</div>
<!-- End of Pending PO dialog -->

<!-- Pending DO dialog -->
<div id="div_pending_do_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_pending_do_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Pending DO List</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_pending_do_dialog_content" style="padding:2px;height:400px;overflow-y:auto;"></div>
</div>
<!-- End of Pending DO dialog -->

<!-- Generate PO dialog -->
<div id="div_generate_po_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:400px;height:200px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_generate_po_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;" id="span_generate_po_title">Generate PO</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_generate_po_dialog_content" style="padding:2px;height:170px;overflow-y:auto;"></div>
</div>
<!-- End of Generate PO dialog -->

<!-- Pending GRA dialog -->
<div id="div_pending_gra_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_pending_gra_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Uncheckout GRA</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_pending_gra_dialog_content" style="padding:2px;height:400px;overflow-y:auto;"></div>
</div>
<!-- End of Pending GRA dialog -->

{if $got_do_request}
	<!-- Pending DO Request dialog -->
	<div id="div_pending_do_request_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:700px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
		<div id="div_pending_do_request_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">DO Request</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
		</div>
		<div id="div_pending_do_request_dialog_content" style="padding:2px;height:400px;overflow-y:auto;"></div>
	</div>
	<!-- End of Pending DO Request dialog -->
{/if}

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}
				
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h5 class="content-title mb-0 my-auto ml-4 text-primary">
				(Stock Balance As Per Login Branch)
			</h5><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>



{if $err}
    <ul>
        {foreach from=$err item=e}
            <div class="alert alert-danger">
				<div class="alert alert-danger rounded mx-3">
					<li>{$e}</li>
				</div>
			</div>
        {/foreach}
    </ul>
{/if}
<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" class="stdframe" onSubmit="return false;" method="post" action="{$smarty.server.PHP_SELF}">
			<input type="hidden" name="load_report" value="1" />
		
				<div class="row">
				<div class="col-md-4">
					<b class="form-label">Vendor: </b>
					{dropdown name='vendor_id' key=id value=description values=$vendors selected=$smarty.request.vendor_id all='-- All --'}
				</div>
			
				<div class="col-md-4">
					<b class="form-label">Brand: </b>
				{dropdown name='brand_id' key=id value=description values=$brands selected=$smarty.request.brand_id all='-- All --'}
				</div>
		
				<div class="col-md-4">
					<b class="form-label">Sort by</b>
				<select class="form-control" name="sort_by" onChange="STOCK_REORDER.change_sort_by(this);">
					<option value="">--</option>
					{foreach from=$sort_option key=k item=desc}
						<option value="{$k}" {if $smarty.request.sort_by eq $k}selected {/if}>{$desc}</option>
					{/foreach}
				</select>
		
					<span id="span_sort_order" style="{if !$smarty.request.sort_by}display:none;{/if}">
						<select class="form-control" name="sort_order">
							<option value="asc" {if $smarty.request.sort_order eq 'asc'}selected {/if}>Ascending</option>
							<option value="desc" {if $smarty.request.sort_order eq 'desc'}selected {/if}>Descending</option>
						</select>
						</span>
				</div>
				</div>
			
			<b class="form-label mt-2">Re-order Type: </b>
			<select class="form-control" name="reorder_type" onChange="STOCK_REORDER.check_reorder_type();">
				<option value="">-- Please Select --</option>
				{foreach from=$reorder_type_list key=t item=lbl}
				<option value="{$t}" {if $smarty.request.reorder_type eq $t}selected {/if}>{$lbl}</option>
				{/foreach}
			</select>
			<label><input class="mt-2" type="checkbox" name="incl_not_approved" {if $smarty.request.incl_not_approved or !$smarty.request.load_report}checked{/if} />&nbsp;Include Saved & Pending Approval PO/DO</label>
		
			<span id="span_reorder_moq_checkbox" style="{if ($smarty.request.reorder_type ne 'less_than_po_reorder_min') or $smarty.request.reorder_type eq ''}display:none;{/if}">
			<label><input type="checkbox" name="order_by_moq" {if $smarty.request.order_by_moq}checked{/if} />Order By MOQ <span style="color:blue;white-space:nowrap">(If set)</span></label>
	
			</span>
			
			
			<!-- SPAN span_reorder_date_range -->
			<span id="span_reorder_date_range" style="{if ($smarty.request.reorder_type ne 'sales_range' and $smarty.request.reorder_type ne 'sales_range_plus_do' and $smarty.request.reorder_type ne 'do_range') or $smarty.request.reorder_type eq ''}display:none;{/if}">
				<div class="form-inline">
					<b class="form-label">From</b>&nbsp;&nbsp;
				<input class="form-control" name="date_range_from" id="inp_date_range_from" size="10" maxlength="10"  value="{$smarty.request.date_range_from|default:$smarty.now-604800|date_format:"%Y-%m-%d"}" />
				&nbsp;
				<img align="absmiddle" src="ui/calendar.gif" id="img_date_range_from" style="cursor: pointer;" title="Select Date" />
				&nbsp;&nbsp;&nbsp;<b class="form-label">To</b>&nbsp;&nbsp;
				<input class="form-control" name="date_range_to" id="inp_date_range_to" size="10" maxlength="10"  value="{$smarty.request.date_range_to|default:$smarty.now|date_format:"%Y-%m-%d"}" />
				<img align="absmiddle" src="ui/calendar.gif" id="img_date_range_to" style="cursor: pointer;" title="Select Date" />
				</div>
			
			</span>
			
			<!-- DIV div_reorder_branch_list -->
			<div id="div_reorder_branch_list" style="{if $smarty.request.reorder_type ne 'less_than_sales' and $smarty.request.reorder_type ne 'sales_range' and $smarty.request.reorder_type ne 'sales_range_plus_do' and $smarty.request.reorder_type ne 'less_then_grace_period'}display:none;{/if}">
				{if $BRANCH_CODE eq 'HQ'}
					<p>
						<table cellpadding="0" cellspacing="0">
							<tr>
								<td width="100">
									<b class="form-label fs-09 mt-2">Branch: </b>
								</td>
								<td>
								<div class="form-inline mt-2">
									<span class="fs-09">Select by Branch Group: </span>
									&nbsp;&nbsp;<select class="form-control" name="sel_brn_grp" id="sel_brn_grp" >
										<option value="" >-- All --</option>
										{foreach from=$branches_group_list item=r}
											<option value="{$r.grp_items}" >{$r.code} - {$r.description}</option>
										{/foreach}
									</select>&nbsp;&nbsp;
									<input type="button" class="btn btn-success"  value="Select " onclick="STOCK_REORDER.check_branch_by_group();" />&nbsp;
									<input type="button" class="btn btn-danger"  value="De-select" onclick="STOCK_REORDER.uncheck_branch_by_group();" /><br /><br />
								</div>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>
									<div class="mt-2 p-2" style="width:100%;max-height:200px;border:1px solid #ddd;overflow:auto;">
										{*<input type="checkbox" onChange="STOCK_REORDER.toggle_reorder_branch_list();" id="chx_toggle_reorder_branch_list" /> All &nbsp;&nbsp;&nbsp;*}
										<ul style="list-style:none;">
										{foreach from=$branches key=bid item=b}
											<li>
												<span class="nowrap">
													<input type="checkbox" class="chx_reorder_bid" name="reorder_bid[{$bid}]" value="1" {if $smarty.request.reorder_bid.$bid}checked {/if} id="reorder_bid-{$bid}" />
												{$b.code}&nbsp;&nbsp;&nbsp;
												</span>
											</li>
										{/foreach}
										</ul>
									</div>
								</td>
							</tr>
						</table>
						
						
					</p>
					<p>
					<div class="form-inline form-label">
						<b >Reorder by Branch?</b>
						&nbsp;<input type="checkbox" name="reorder_by_branch" value="1" {if $smarty.request.reorder_by_branch}checked {/if} onChange="STOCK_REORDER.reorder_by_branch_changed();" />&nbsp; Yes 
						&nbsp;<input type="checkbox" name="show_reorder_details_by_branch" value="1" {if $smarty.request.reorder_by_branch}{if $smarty.request.show_reorder_details_by_branch}checked {/if}{else}disabled {/if} />&nbsp; Show Details by Branch
					</div>
					</p>
				{/if}
			</div>
			
			<p>
				
				<div class="form-inline">
				<b class="form-label">Category: </b>
				&nbsp;&nbsp;	<input class="form-control" readonly name="category_id" size="1" value="{$smarty.request.category_id}" />
				<input type="hidden" name="category_tree" value="{$smarty.request.category_tree}" />
				&nbsp;<input class="form-control" id="autocomplete_category" name="category" value="{$smarty.request.category|default:'Enter keyword to search'}" size="50" default_text="Enter keyword to search" />
			</div>	
				<span style="white-space:nowrap" class="fs-08 text-primary"><br>
					 Please use deeper level category for better report speed,<br> &nbsp;&nbsp;report will take longer time to process if using higher level category.)</span>
				<br>
				<span id="span_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
				<div id="autocomplete_category_choices" class="autocomplete" style="width:600px !important;display:none;"></div>
				
				<span id="str_cat_tree" class="small" style="color:#00f;margin-left:90px;">{$smarty.request.category_tree|default:''}</span>
				
				</p>
			<input type="button" class="btn btn-primary fs-08" value="Show Report" onClick="STOCK_REORDER.submit_form();" />
			&nbsp;<input type="checkbox" name="by_last_vendor" value="1" {if $smarty.request.by_last_vendor}checked {/if} /> last vendor only
			&nbsp;<input type="checkbox" name="inc_inactive_vendor" value="1" {if $smarty.request.inc_inactive_vendor}checked {/if} /> Include inactive Vendor
		</form>
		
	</div>
</div>
{if $smarty.request.load_report and !$err}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

	{if !$data.vendor_data}-- No Data --{else}
	    <form name="f_vendor" method="post" id="f_vendor">
	    <input type="hidden" name="a" value="ajax_generate_po" />
	    <input type="hidden" name="category_id" value="{$smarty.request.category_id}" />
	    <input type="hidden" name="reorder_by_branch" value="{$data.reorder_by_branch}" />
	    
	    {if $data.pregen_sku_data}
	    	<div class="alert alert-primary mx-3 rounded" >
	    		<ul>
	    			<li>Please note that you are under pregen mode and the SKU list may be in-accurate.</li>
					<li>The SKU list was pre-generated at {$data.pregen_sku_data.added}.</li>
					{if $data.pregen_sku_data.open_by_user_u}
						<li>This pregen SKU list has been first open by <b>{$data.pregen_sku_data.open_by_user_u}.</b>
							<input type="hidden" value="{$data.pregen_sku_data.open_by_user_u}" id="inp_open_by_user_u" />
						</li>
						
					{/if}
	    		</ul>
	    	</div><br />
	    {/if}
	    <div class="card mx-3">
			<div class="card-body">
		<div class="stdframe form-inline">
			<b class="form-label">Generate to: </b>
			&nbsp;<select class="form-control" name="generate_type" onChange="STOCK_REORDER.generate_type_changed();">
				<option value="po">PO</option>
				{if $BRANCH_CODE eq 'HQ' and $smarty.request.reorder_type ne 'less_then_grace_period'}
					<option value="do">DO</option>
				{/if}
				{if $got_do_request}
					<option value="do_request">DO Request</option>
				{/if}
			</select>
		</div>
		
		
				<div id="div_generate_details">
					<div class="stdframe div_generate_details" id="div_info-po">
						<h4 class="form-label">PO Information</h4>
						<table>
							
							<tr>
								<td><b class="form-label">Delivery Branch:</b></td>
								<td>
									{if $BRANCH_CODE eq 'HQ'}
										{if !$data.reorder_by_branch}
											<select class="form-control" name="po_branch_id">
												{foreach from=$branches key=bid item=b}
													<option value="{$bid}" {if $bid eq $sessioninfo.branch_id}selected {/if}>{$b.code} - {$b.description}</option>
												{/foreach}
											</select>
										{else}
											{foreach from=$data.reorder_bid item=bid}
												<input type="checkbox" name="po_deliver_to[]" value="{$bid}" checked /> {$branches.$bid.code} &nbsp;&nbsp;&nbsp;&nbsp;
											{/foreach}
										{/if}
									{else}
										{$BRANCH_CODE}
										<input type="hidden" name="po_branch_id" value="{$sessioninfo.branch_id}" />
									{/if}
								</td>
							</tr>
		
							{if $config.po_enable_ibt}
								<tr>
									<div class="form-label form-inline">
										<td><b>IBT</b></td>
									<td><input type="checkbox" name="is_ibt" value="1" /></td>
									</div>
								</tr>
							{/if}
							<tr>
								<div class="form-label form-inline">
									<td><b class="form-label">Partial Delivery</b></td>
								<td><input type="checkbox" name="partial_delivery" value="1" /></td>
								</div>
							</tr>
							<tr>
								<td><b class="form-label">PO Date</b></td>
								<td>
									<div class="form-inline">
										<input class="form-control" name="po_date" id="inp_po_date" size="23" maxlength="23"  value="{$smarty.request.po_date|date_format:"%Y-%m-%d"}" readonly />
									&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_po_date" style="cursor: pointer;" title="Select PO Date" />
									</div>
								</td>
							</tr>
							<tr>
								<td><b class="form-label">Delivery Date</b></td>
								<td>
									<div class="form-inline">
										<input class="form-control" name="delivery_date" id="inp_delivery_date" size="23" maxlength="23"  value="{$smarty.request.delivery_date|date_format:"%Y-%m-%d"}" readonly />
									&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_delivery_date" style="cursor: pointer;" title="Select Deliver Date" />
									</div>
								</td>
							</tr>
							<tr>
								<td><b class="form-label">Cancellation Date</b></td>
								<td>
									<div class="form-inline">
										<input class="form-control" name="cancel_date" id="inp_cancel_date" size="23" maxlength="23"  value="{$smarty.request.cancel_date|date_format:"%Y-%m-%d"}" readonly />
									&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_cancel_date" style="cursor: pointer;" title="Select Cancellation Date" />
									</div>
								</td>
							</tr>
						</table>
					</div>
					
					<div class="stdframe div_generate_details" id="div_info-do" style="display:none;">
						<h4 class="form-label">DO Information</h4>
						<table>
							<tr>
								<td><b class="form-label">Deliver To: </b></td>
								<td>
									{if !$data.reorder_by_branch}
										<select class="form-control" name="do_branch_id">
											{foreach from=$branches key=bid item=b}
												{if $bid ne $sessioninfo.branch_id}
													<option value="{$bid}">{$b.code} - {$b.description}</option>
												{/if}
											{/foreach}
										</select>
									{else}
										{foreach from=$data.reorder_bid item=bid}
											<input type="checkbox" name="do_deliver_to[]" value="{$bid}" {if $bid ne $sessioninfo.branch_id}checked{else}disabled{/if} /> {$branches.$bid.code} &nbsp;&nbsp;&nbsp;&nbsp;
										{/foreach}
									{/if}
								</td>
							</tr>
							<tr>
								<td><b class="form-label">DO Date</b></td>
								<td>
									<div class="form-inline">
										<input class="form-control" name="do_date" id="inp_do_date" size="23" maxlength="23"  value="{$smarty.request.do_date|date_format:"%Y-%m-%d"}" readonly />
									&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_do_date" style="cursor: pointer;" title="Select DO Date" />
									</div>
								</td>
							</tr>
						</table>
						<div class="alert alert-primary rounded mt-3">
							* Multiple rows of same sku will be combine into 1 sku row.
						</div>
					</div>
					
					{if $got_do_request}
						<div class="stdframe div_generate_details" id="div_info-do_request" style="display:none;">
							<h4 class="form-label">DO Request Information</h4>
							<table>
								<tr>
									<td><b class="form-label">Choose Supply Branch: </b></td>
									<td>
										<select class="form-control" name="request_branch_id">
											{foreach from=$branches key=bid item=b}
												{if $bid ne $sessioninfo.branch_id}
													<option value="{$bid}" {if $bid eq 1}selected {/if}>{$b.code} - {$b.description}</option>
												{/if}
											{/foreach}
										</select>
									</td>
								</tr>
								{if $got_expect_do_date}
									<tr>
										<td><b class="form-label">Expected Delivery Date</b></td>
										<td>
											<div class="form-inline">
												<input class="form-control" name="expect_do_date" id="inp_expect_do_date" size="10" maxlength="10"  value="{$default_expect_do_date}" readonly />
											&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_expect_do_date" style="cursor: pointer;" title="Select Date" />
											</div>
										</td>
									</tr>
								{/if}
							</table>
							<div class="alert alert-primary rounded mt-2">
								* Multiple rows of same sku will be combine into 1 sku row.
							</div>
						</div>
					{/if}
				</div>
			</div>
		</div>
	    
	    {if $smarty.request.reorder_type eq 'do_range' or $smarty.request.reorder_type eq 'sales_range_plus_do'}
	        {assign var=show_do_range value=1}
	    {/if}
	    {foreach from=$data.vendor_data key=vendor_id item=vendors}
	        <div id="div_vendor-{$vendor_id}" class="stdframe" style="position:relative;">
	            <div style="position:absolute;right:5px;">
					<img src="/ui/del.png" title="Remove this vendor from generate PO" class="clickable" onClick="STOCK_REORDER.remove_vendor('{$vendor_id}');" />
				</div>
	
	            <h3 class="text-primary mx-3">{$vendors.info.code} - {$vendors.info.description} {if !$vendors.info.active}(Inactived){/if}
	            	{if $smarty.request.reorder_type eq 'less_then_grace_period'}
		            	<br />
		            	Grace Period: {$vendors.info.grace_period}
	            	{/if}
	            </h3>
	            
	            <div class="card mx-3">
					<div class="card-body">
						<div class="table-reponsive">
							<table width="100%" class="report_table">
								<thead class="bg-gray-100">
									<tr class="header">
										<th width="20" rowspan="2">#</th>
										<th width="30" align="center"  rowspan="2">
											Generate<br />
											<input id="chx_toggle_vendor_generate_po-{$vendor_id}" type="checkbox" onChange="STOCK_REORDER.toggle_vendor_generate_po('{$vendor_id}');" checked />
										</th>
										<th rowspan="2">ARMS Code</th>
										<th rowspan="2">Art.No</th>
										<th rowspan="2">Mcode</th>
										<th rowspan="2">{$config.link_code_name|default:'Link Code'}</th>
										<th rowspan="2">Description</th>
										
										{if $data.reorder_by_branch}
											<th rowspan="2">Stock Balance<br />HQ</th>
										{/if}
										
										{* GRN in Last 30 Days *}
										{if !$data.show_reorder_details_by_branch}
											<th rowspan="2">
												GRN in last 30 days<br />
												{if !$data.reorder_by_branch}
													({$BRANCH_CODE})
												{else}
													(Reorder Branches)
												{/if}
											</th>
										{else}
											<th colspan="{$data.reorder_branch_count}">GRN in<br>last 30 days</th>
										{/if}
										
										{* Stock Balance *}
										{if !$data.show_reorder_details_by_branch}
											<th rowspan="2">
												Stock Balance <br />
												{if !$data.reorder_by_branch}
													({$BRANCH_CODE})
												{else}
													(Reorder Branches)
												{/if}
											</th>
										{else}
											<th colspan="{$data.reorder_branch_count}">Stock Balance</th>
										{/if}
										
										{* Sales in last 30 days *}
										{if $data.show_reorder_details_by_branch and ($smarty.request.reorder_type eq 'less_than_sales' or $smarty.request.reorder_type eq 'less_then_grace_period')}
											<th colspan="{$data.reorder_branch_count}">Sales in<br>last 30 days</th>
										{else}
											<th rowspan="2">Sales in<br>last 30 days</th>
										{/if}
										
										{* Sales in Range *}
										{if $smarty.request.reorder_type eq 'sales_range' or $smarty.request.reorder_type eq 'sales_range_plus_do'}
											{if !$data.show_reorder_details_by_branch}
												<th rowspan="2">Sales in<br>range</th>
											{else}
												<th colspan="{$data.reorder_branch_count}">Sales in<br>range</th>
											{/if}
										{/if}
										
										{* PO Reorder Qty *}
										{if $smarty.request.reorder_type eq 'less_than_po_reorder_min'}
											<th colspan="3">PO Reorder Qty</th>
										{/if}
										
										{* Pending PO Qty *}
										<th {if !$data.show_reorder_details_by_branch}rowspan="2"{else}colspan="{$data.reorder_branch_count}"{/if}>Pending PO Qty</th>
										
										{* Pending DO Qty *}
										<th {if !$data.show_reorder_details_by_branch}rowspan="2"{else}colspan="{$data.reorder_branch_count}"{/if}>
											Pending DO Qty [<a href="javascript:void(alert('- Transfer DO only.'))">?</a>]
										</th>
										
										{* Uncheckout GRA *}
										<th {if !$data.show_reorder_details_by_branch}rowspan="2"{else}colspan="{$data.reorder_branch_count}"{/if}>Uncheckout GRA Qty</th>
										
										{* DO Request *}
										{if $BRANCH_CODE ne 'HQ'}
											<th rowspan="2">Current DO Request Qty</th>
										{/if}
										
										{* DO Sales Qty in Range *}
										{if $show_do_range}
											<th {if !$data.show_reorder_details_by_branch}rowspan="2"{else}colspan="{$data.reorder_branch_count}"{/if}>DO Sales Qty in range</th>
										{/if}
										
										{* Suggest PO in Pcs *}
										<th {if !$data.show_reorder_details_by_branch}rowspan="2"{else}colspan="{$data.reorder_branch_count}"{/if}>Suggest PO in Pcs</th>
										
										{* Suggest PO in Ctn *}
										{assign var=cols value=1}
										{if !$data.show_reorder_details_by_branch}
											{assign var=cols value=$cols+1}
										{else}
											{assign var=cols value=$cols+$data.reorder_branch_count}
										{/if}
										<th colspan="{$cols}">Suggest PO in Ctn
											{if $smarty.request.reorder_type eq 'less_then_grace_period'}
												[<a href="javascript:void(alert('Calculation method: (Sales in last 30 days / 30) x (grace period + 1) - stock balance - pending po qty'))">?</a>]
											{/if}
										</th>
									</tr>
									<tr class="header">
										{* GRN in Last 30 Days *}
										{if $data.show_reorder_details_by_branch}
											{foreach from=$data.reorder_bid item=bid}
												<th>{$branches.$bid.code}</th>
											{/foreach}
										{/if}
										
										{* Stock Balance *}
										{if $data.show_reorder_details_by_branch}
											{foreach from=$data.reorder_bid item=bid}
												<th>{$branches.$bid.code}</th>
											{/foreach}
										{/if}
										
										{* Sales in last 30 days *}
										{if $data.show_reorder_details_by_branch and ($smarty.request.reorder_type eq 'less_than_sales' or $smarty.request.reorder_type eq 'less_then_grace_period')}
											{foreach from=$data.reorder_bid item=bid}
												<th>{$branches.$bid.code}</th>
											{/foreach}
										{/if}
										
										{* Sales in Range *}
										{if $smarty.request.reorder_type eq 'sales_range' or $smarty.request.reorder_type eq 'sales_range_plus_do'}
											{if $data.show_reorder_details_by_branch}
												{foreach from=$data.reorder_bid item=bid}
													<th>{$branches.$bid.code}</th>
												{/foreach}
											{/if}
										{/if}
										
										{* PO Reorder Qty *}
										{if $smarty.request.reorder_type eq 'less_than_po_reorder_min'}
											<th>Min</th>
											<th>Max</th>
											<th>MOQ</th>
										{/if}
										
										{* Pending PO Qty *}
										{if $data.show_reorder_details_by_branch}
											{foreach from=$data.reorder_bid item=bid}
												<th>{$branches.$bid.code}</th>
											{/foreach}
										{/if}
										
										{* Pending DO Qty *}
										{if $data.show_reorder_details_by_branch}
											{foreach from=$data.reorder_bid item=bid}
												<th>{$branches.$bid.code}</th>
											{/foreach}
										{/if}
										
										{* Uncheckout GRA *}
										{if $data.show_reorder_details_by_branch}
											{foreach from=$data.reorder_bid item=bid}
												<th>{$branches.$bid.code}</th>
											{/foreach}
										{/if}
										
										{* DO Sales Qty in Range *}
										{if $show_do_range}
											{if $data.show_reorder_details_by_branch}
												{foreach from=$data.reorder_bid item=bid}
													<th>{$branches.$bid.code}</th>
												{/foreach}
											{/if}
										{/if}
										
										{* Suggest PO in Pcs *}
										{if $data.show_reorder_details_by_branch}
											{foreach from=$data.reorder_bid item=bid}
												<th>{$branches.$bid.code}</th>
											{/foreach}
										{/if}
										
										{* Suggest PO in Ctn *}
										<th>UOM</th>
										{if !$data.show_reorder_details_by_branch}
											<th>Ctn</th>
										{else}
											{foreach from=$data.reorder_bid item=bid}
												<th>{$branches.$bid.code}</th>
											{/foreach}
										{/if}
										
									</tr>
								</thead>
								{foreach from=$vendors.sku_item item=sku_id name=fv}
									{assign var=sku_info value=$data.sku_data.$sku_id.info}
									<tbody class="fs-08">
										<tr sku_id="{$sku_id}" class="tr_sku">
											<td><span class="row_no">{$smarty.foreach.fv.iteration}</span>.</td>
											<td align="center">
												<input type="checkbox" name="vendor_sku[{$vendor_id}][{$sku_id}][generate_po]" class="chx_generate_po" value="1" checked />
											</td>
											<td>{$sku_info.sku_item_code}</td>
											<td>{$sku_info.artno|default:'-'}</td>
											<td>{$sku_info.mcode|default:'-'}</td>
											<td>{$sku_info.link_code|default:'-'}</td>
											<td>{$sku_info.description|default:'-'}</td>
											
											{assign var=zero value=0}
											{if $data.reorder_by_branch}
												<td class="r">
												{$sku_info.hq_stock|qty_nf|ifzero:'-'}
												</td>
											{/if}
											
											{* GRN in Last 30 Days *}
											{if !$data.reorder_by_branch}
												<td class="r">{$sku_info.l30d_grn|qty_nf|ifzero:'-'}</td>
											{else}
												{if $data.show_reorder_details_by_branch}
													{foreach from=$data.reorder_bid item=bid}
														<td class="r">{$sku_info.l30d_grn_by_branch.$bid|qty_nf|ifzero:'-'}</td>
													{/foreach}
												{else}
													<td class="r">{$sku_info.l30d_grn_by_branch.total|qty_nf|ifzero:'-'}</td>
												{/if}
											{/if}
											
											{* Stock Balance *}
											{if !$data.reorder_by_branch}
												<td class="r {if $sku_info.qty<0}negative{/if}">
												{if $sku_info.po_reorder_moq eq '-'  or $sku_info.po_reorder_moq eq '0'} 
													{if $sku_info.qty <0 and (abs($sku_info.qty) > $sku_info.po_reorder_qty_max )}
														<a title="Stock Balance is less than Reorder Max or MOQ."><img src="/ui/messages.gif"></a>
													{/if}
												{else}
													{if $sku_info.qty <0 and (abs($sku_info.qty) > $sku_info.po_reorder_moq or abs($sku_info.qty) > $sku_info.po_reorder_qty_max )}
														<a title="Stock Balance is less than Reorder Max or MOQ."><img src="/ui/messages.gif"></a>
													{/if}
												{/if}
												{$sku_info.qty|qty_nf}</td>
											{else}
											
												{if $data.show_reorder_details_by_branch}
													{foreach from=$data.reorder_bid item=bid}
														<td class="r {if $sku_info.stock_by_branch.$bid < 0}negative{/if}">
														{if $sku_info.po_reorder_moq eq '-'  or $sku_info.po_reorder_moq eq '0'}
															{if $sku_info.stock_by_branch.$bid < 0 and (abs($sku_info.stock_by_branch.$bid) > $sku_info.po_reorder_qty_max) }
																<a title="Stock Balance is less than Reorder Max or MOQ."><img src="/ui/messages.gif"></a>
															{/if}
														{else}
															{if $sku_info.stock_by_branch.$bid<0 and (abs($sku_info.stock_by_branch.$bid) > $sku_info.po_reorder_moq or abs($sku_info.stock_by_branch.$bid) > $sku_info.po_reorder_qty_max )}
																<a title="Stock Balance is less than Reorder Max or MOQ."><img src="/ui/messages.gif"></a>
															{/if}
														{/if}
														{$sku_info.stock_by_branch.$bid|qty_nf}</td>
													{/foreach}
												{else}
													<td class="r {if $sku_info.stock_by_branch.total < 0}negative{/if}">
													{if $sku_info.po_reorder_moq eq '-' or $sku_info.po_reorder_moq eq '0'}
														{if $sku_info.stock_by_branch.total <0 and (abs($sku_info.stock_by_branch.total) > $sku_info.po_reorder_qty_max )}
														<a title="Stock Balance is less than Reorder Max or MOQ."><img src="/ui/messages.gif"></a>
														{/if}
													{else}
														{if $sku_info.stock_by_branch.total <0 and (abs($sku_info.stock_by_branch.total) > $sku_info.po_reorder_moq or abs($sku_info.stock_by_branch.total) > $sku_info.po_reorder_qty_max )}
														<a title="Stock Balance is less than Reorder Max or MOQ."><img src="/ui/messages.gif"></a>
														{/if}
													{/if}
													{$sku_info.stock_by_branch.total|qty_nf}</td>
												{/if}
											{/if}
											
											{* Sales in last 30 days *}
											{if $data.show_reorder_details_by_branch and ($smarty.request.reorder_type eq 'less_than_sales' or $smarty.request.reorder_type eq 'less_then_grace_period')}
												{foreach from=$data.reorder_bid item=bid}
													<td class="r">{$sku_info.l30d_pos_by_branch.$bid|qty_nf|ifzero:'-'}</td>
												{/foreach}
											{else}
												<td class="r">{$sku_info.l30d_pos|qty_nf|ifzero:'-'}</td>
											{/if}
											
											{* Sales in Range *}
											{if $smarty.request.reorder_type eq 'sales_range' or $smarty.request.reorder_type eq 'sales_range_plus_do'}
												{if $data.show_reorder_details_by_branch}
													{foreach from=$data.reorder_bid item=bid}
														<td class="r">{$sku_info.sales_range_qty_by_branch.$bid|qty_nf}</td>
													{/foreach}
												{else}
													<td class="r">{$sku_info.sales_range_qty|qty_nf|ifzero:'-'}</td>
												{/if}
											{/if}
											
											{* PO Reorder Qty *}
											{if $smarty.request.reorder_type eq 'less_than_po_reorder_min'}
												<td class="r">{$sku_info.po_reorder_qty_min|qty_nf|default:'0'|ifzero:'-'}</td>
												<td class="r">{$sku_info.po_reorder_qty_max|qty_nf|default:'0'|ifzero:'-'}</td>
												<td class="r">{$sku_info.po_reorder_moq|qty_nf|default:'0'|ifzero:'-'}
												</td>
											{/if}
											
											{* Pending PO Qty *}
											{if !$data.reorder_by_branch}
												<td class="r">
													{if !$sku_info.po_qty}-{else}
														<a href="javascript:void(STOCK_REORDER.show_pending_po('{$sku_id}', '{$sku_info.po_reorder_by_child}'));">
															{$sku_info.po_qty|qty_nf}
														</a>
													{/if}
												</td>
											{else}
												{if $data.show_reorder_details_by_branch}
													{foreach from=$data.reorder_bid item=bid}
														<td class="r">
															{if !$sku_info.po_qty_by_branch.$bid}-{else}
																<a href="javascript:void(STOCK_REORDER.show_pending_po('{$sku_id}', '{$sku_info.po_reorder_by_child}', 1, '{$bid}'));">
																	{$sku_info.po_qty_by_branch.$bid|qty_nf}
																</a>
															{/if}
														</td>
													{/foreach}
												{else}
													<td class="r">
														{if !$sku_info.po_qty_by_branch.total}-{else}
															<a href="javascript:void(STOCK_REORDER.show_pending_po('{$sku_id}', '{$sku_info.po_reorder_by_child}', 1, 0));">
																{$sku_info.po_qty_by_branch.total|qty_nf}
															</a>
														{/if}
													</td>
												{/if}
											{/if}
											
											{* Pending DO Qty *}
											{if !$data.reorder_by_branch}
												<td class="r">
													{if !$sku_info.do_qty}-
													{else}
														<a href="javascript:void(STOCK_REORDER.show_pending_do('{$sku_id}', '{$sku_info.po_reorder_by_child}'));">
															{$sku_info.do_qty|qty_nf}
														</a>
													{/if}
												</td>
											{else}
												{if $data.show_reorder_details_by_branch}
													{foreach from=$data.reorder_bid item=bid}
														<td class="r">
															{if !$sku_info.do_qty_by_branch.$bid}-{else}
																<a href="javascript:void(STOCK_REORDER.show_pending_do('{$sku_id}', '{$sku_info.po_reorder_by_child}', 1, '{$bid}'));">
																	{$sku_info.do_qty_by_branch.$bid|qty_nf}
																</a>
															{/if}
														</td>
													{/foreach}
												{else}
													<td class="r">
														{if !$sku_info.do_qty_by_branch.total}-{else}
															<a href="javascript:void(STOCK_REORDER.show_pending_do('{$sku_id}', '{$sku_info.po_reorder_by_child}', 1, 0));">
																{$sku_info.do_qty_by_branch.total|qty_nf}
															</a>
														{/if}
													</td>
												{/if}
											{/if}
											
											{* Uncheckout GRA *}
											{if !$data.reorder_by_branch}
												<td class="r">
													{if !$sku_info.gra_qty}-
													{else}
														<a href="javascript:void(STOCK_REORDER.show_pending_gra('{$sku_id}', '{$sku_info.po_reorder_by_child}'));">
															{$sku_info.gra_qty|qty_nf}
														</a>
													{/if}
												</td>
											{else}
												{if $data.show_reorder_details_by_branch}
													{foreach from=$data.reorder_bid item=bid}
														<td class="r">
															{if !$sku_info.gra_qty_by_branch.$bid}-{else}
																<a href="javascript:void(STOCK_REORDER.show_pending_gra('{$sku_id}', '{$sku_info.po_reorder_by_child}', 1, '{$bid}'));">
																	{$sku_info.gra_qty_by_branch.$bid|qty_nf}
																</a>
															{/if}
														</td>
													{/foreach}
												{else}
													<td class="r">
														{if !$sku_info.gra_qty_by_branch.total}-{else}
															<a href="javascript:void(STOCK_REORDER.show_pending_gra('{$sku_id}', '{$sku_info.po_reorder_by_child}', 1, 0));">
																{$sku_info.gra_qty_by_branch.total|qty_nf}
															</a>
														{/if}
													</td>
												{/if}
											{/if}
											
											{* DO Request *}
											{if $BRANCH_CODE ne 'HQ'}
												<td class="r">
													{if !$sku_info.do_request_qty}-
													{else}
														<a href="javascript:void(STOCK_REORDER.show_pending_do_request('{$sku_id}', '{$sku_info.po_reorder_by_child}'));">
															{$sku_info.do_request_qty|qty_nf}
														</a>
													{/if}
												</td>
											{/if}
											
											{* DO Sales Qty in Range *}
											{if $show_do_range}
												{if $data.show_reorder_details_by_branch}
													{foreach from=$data.reorder_bid item=bid}
														<td class="r">{$sku_info.do_range_qty_by_branch.$bid|qty_nf}</td>
													{/foreach}
												{else}
													<td class="r">{$sku_info.do_range_qty|qty_nf|ifzero:'-'}</td>
												{/if}
											{/if}
											
											{* Suggest PO in Pcs *}
											{if !$data.show_reorder_details_by_branch}
												{if !$data.reorder_by_branch}
													{assign var=suggest_po_qty value=$sku_info.suggest_po_qty}
													{if $data.use_vendor_po_data}
														{assign var=suggest_po_qty value=$sku_info.suggest_po_qty_by_vendor.$vendor_id}
													{/if}
												{else}
													{assign var=suggest_po_qty value=$sku_info.suggest_po_qty_by_branch.total}
													{if $data.use_vendor_po_data}
														{assign var=suggest_po_qty value=$sku_info.suggest_po_qty_by_vendor_by_branch.$vendor_id.total}
													{/if}
												{/if}
												<td class="r">
													{$suggest_po_qty|qty_nf|ifzero:'-'}
													<input type="hidden" name="vendor_sku[{$vendor_id}][{$sku_id}][suggest_po_qty]" value="{$suggest_po_qty}" />
													{if $data.reorder_by_branch}
														{foreach from=$data.reorder_bid item=bid}
															{assign var=suggest_po_qty value=$sku_info.suggest_po_qty_by_branch.$bid}
															{if $data.use_vendor_po_data}
																{assign var=suggest_po_qty value=$sku_info.suggest_po_qty_by_vendor_by_branch.$vendor_id.$bid}
															{/if}
															<input type="hidden" name="vendor_sku[{$vendor_id}][{$sku_id}][suggest_po_qty_by_branch][{$bid}]" value="{$suggest_po_qty}" />
															
														{/foreach}
													{/if}
												</td>
											{else}
												{foreach from=$data.reorder_bid item=bid}
													{assign var=suggest_po_qty value=$sku_info.suggest_po_qty_by_branch.$bid}
													{if $data.use_vendor_po_data}
														{assign var=suggest_po_qty value=$sku_info.suggest_po_qty_by_vendor_by_branch.$vendor_id.$bid}
													{/if}
													<td class="r">
														{$suggest_po_qty|qty_nf|ifzero:'-'}
														<input type="hidden" name="vendor_sku[{$vendor_id}][{$sku_id}][suggest_po_qty_by_branch][{$bid}]" value="{$suggest_po_qty}" />
													</td>
												{/foreach}
											{/if}
											
											{* Suggest PO in Ctn *}
											<td>{$sku_info.po_uom_code}
												<input type="hidden" name="vendor_sku[{$vendor_id}][{$sku_id}][po_uom_id]" value="{$sku_info.po_uom_id}" />
												<input type="hidden" name="vendor_sku[{$vendor_id}][{$sku_id}][po_uom_fraction]" value="{$sku_info.po_uom_fraction}" />
											</td>
											
											{if !$data.show_reorder_details_by_branch}
												{if !$data.reorder_by_branch}
													{assign var=suggest_po_ctn value=$sku_info.suggest_po_ctn}
													{if $data.use_vendor_po_data}
														{assign var=suggest_po_ctn value=$sku_info.suggest_po_ctn_by_vendor.$vendor_id}
													{/if}
												{else}
													{assign var=suggest_po_ctn value=$sku_info.suggest_po_ctn_by_branch.total}
													{if $data.use_vendor_po_data}
														{assign var=suggest_po_ctn value=$sku_info.suggest_po_ctn_by_vendor_by_branch.$vendor_id.total}
													{/if}
												{/if}
												<td class="r">
													{$suggest_po_ctn|qty_nf|ifzero:'-'}
													<input type="hidden" name="vendor_sku[{$vendor_id}][{$sku_id}][suggest_po_ctn]" value="{$suggest_po_ctn}" />
												</td>
											{else}
												{foreach from=$data.reorder_bid item=bid}
													{assign var=suggest_po_ctn value=$sku_info.suggest_po_ctn_by_branch.$bid}
													{if $data.use_vendor_po_data}
														{assign var=suggest_po_ctn value=$sku_info.suggest_po_ctn_by_vendor_by_branch.$vendor_id.$bid}
													{/if}
													<td class="r">
														{$suggest_po_ctn|qty_nf|ifzero:'-'}
														<input type="hidden" name="vendor_sku[{$vendor_id}][{$sku_id}][suggest_po_ctn_by_branch][{$bid}]" value="{$suggest_po_ctn}" />
													</td>
												{/foreach}
											{/if}
											
										</tr>
									</tbody>
								{/foreach}
							</table><br />
						</div>
					</div>
				</div>
	            <div class="mx-3">
					<input type="button" value="Generate PO"  onClick="STOCK_REORDER.generate_po('{$vendor_id}');" class=" btn btn-warning inp_generate inp_generate-po" />
	            {if $config.enable_reorder_integration}
	            <input type="button" value="Export to CSV"  onClick="STOCK_REORDER.export_po('{$vendor_id}')" class="btn btn-info inp_export inp_export-po" />
	            {/if}
	            <input type="button" value="Generate DO" style="display:none;" class="btn btn-warning" onClick="STOCK_REORDER.generate_do('{$vendor_id}');" class="inp_generate inp_generate-do" />
				</div>
	        </div>
	    {/foreach}
	    </form>
	    <p align="center">
	    	<input type="button" value="Generate All PO"  onClick="STOCK_REORDER.generate_po();" class="btn btn-success inp_generate inp_generate-po" />
	    	{if $config.enable_reorder_integration}
	    	<input type="button" value="Export All to CSV" class="btn btn-primary" onClick="STOCK_REORDER.export_po();" class="inp_export inp_export-po" />
	    	{/if}
	    	<input type="button" value="Generate DO" style="display:none;" onClick="STOCK_REORDER.generate_do();" class="btn btn-warning inp_generate inp_generate-do" />
			{if $got_do_request}
				<input type="button" value="Generate DO Request" style="font:bold 20px Arial; background-color:#f90; color:#fff;display:none;" onClick="STOCK_REORDER.generate_do_request();" class="inp_generate inp_generate-do_request" />
			{/if}
	    </p>
	{/if}
{/if}

<script>STOCK_REORDER.initialize();</script>

{include file='footer.tpl'}
