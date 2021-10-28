{*
Revision History
================
4 Apr 2007 - yinsee
- 	check for $config.sku_application_require_multics to enable/disable multics code columns
- 	replace 'Multics Code' with $config.link_code_name

7/30/2007 2:27:26 PM - yinsee 
- add inventory popup

9/27/2007 12:14:42 PM gary
- added last grn cost price in listing.

11/19/2007 9:57:28 AM gary
- add do and adjustment stock.

11/23/2007 4:40:27 PM gary
- modify the layout of moving sku div.

1/16/2008 5:24:57 PM yinsee
- move get_inventory to ajax_sku_popups.php

11/18/2008 2:37:35 PM yinsee
- is_parent derive from (sku_code+'0000' == sku_item_code)
- context menu pop on top of mouse pointer

3/2/2009 6:56:00 PM jeff
- highlight inactive sku items

2/12/2009 11:41 AM Andy
- modify data shown by branch group, add branches group selection dropdown

4/22/2009 11:50:52 AM yinsee
- control view cost permission 

7/8/2009 5:50 PM Andy
- Hide cost history if user don't have privilege 'show_cost'

8/19/2009 5:13:26 PM Andy
- check sku can move or not before moving

11/4/2009 6:19:07 PM yinsee
- change parent using parent ARMS Code

5/7/2010 10:44:37 AM Andy
- Fix a bugs that cause inventory popup cannot be hide.

8/13/2010 10:09:25 AM Andy
- Add view replacement items at SKU master file.
- Add recalculate cost/inventory and resync sku at SKU master file.

11/8/2010 6:00:05 PM Alex
- add sku items active/inactive filter

11/15/2010 11:00:10 AM Alex
- add block filter

12/3/2010 5:23:30 PM Alex
- add border at block filter section

12/15/2010 2:50:18 PM Alex
- reset to previous sku parent

1/11/2011 2:03:13 PM Alex
- move blocked section to new line

3/23/2011 5:10:41 PM Andy
- Add checking privilege to allow user to view promotion history.

4/13/2011 3:11:22 PM Andy
- Change resync sku at masterfile only can use at HQ.

4/13/2011 3:40:10 PM Andy
- Change promotion history to load same as how "active promotion" load.

7/7/2011 2:01:38 PM Andy
- Change SKU Master File to load photo from ajax if found multi server mode and photo is not store at own server.

7/14/2011 11:13:05 AM Andy
- Make promotion history also can show future promotion.

7/18/2011 5:32:21 PM Justin
- Fixed the category autocomplete to show in 2 lines if too long instead require user to drag the bar to see the whole contents.

9/14/2011 9:58:17 AM Alex
- add filter for searching code

9/15/2011 11:14:13 AM Andy
- Add filter "All" for searching code.

9/22/2011 11:56:23 AM Andy
- Change masterfile SKU default will not load SKU list, will only load when user search.
- Change default select active SKU.
- Significantly increase loading speed.

9/23/2011 3:02:29 PM Andy
- Change default select active="All".

10/7/2011 4:16:23 PM Andy
- Add can "show parent sales trend".

11/14/2011 3:02:14 PM Andy
- Add default focus on "Find" input box.

9/21/2012 10:15 AM Justin
- Added to pickup sku item ID while user click on edit SKU.

12/20/2012 9:59:00 AM Fithri
- Under Sku Listing request add filter for Scale type

3/7/2013 5:26 PM Fithri
- redesign the gui & rename the label for "Item Blocked in PO" filter at sku listing

3/11/2013 6:10 PM Fithri
- fix "Item Blocked in PO" filter difference between HQ and branch

12/19/2013 11:10 AM Andy
- Fix sku photo path if got special character will not able to show in popup.

3/25/2014 2:13 PM Justin
- Modified the wording from "Color" to "Colour".

5:19 PM 4/16/2014 Justin
- Added new context menu "Set PO Re-order Qty by Branch" and ability to hide this menu while SKU is not allowed to set reorder by branch.

7/30/2015 6:05 PM Joo Chia
- Enhance to allow to filter by input tax, output tax, and inclusive tax.

4/12/2016 2:43 PM Andy
- Enhanced to able to see stock check details when view inventory by parent.

05/03/2015 15:00 Edwin
- Added new filter field: Goat Parent Child

5/4/2017 11:31 AM Qiu Ying
- Enhanced to change "Got Parent Child" to "Parent Child?"

7/3/2017 11:31 AM Andy
- Added a page to show SKU Summary.

8/7/2017 4:42 PM Andy
- Enhanced to able to view cost history details.

2017-09-21 09:45 AM Qiu Ying
- Enhanced to add a note "S. R. = Stock Reorder"

11/22/2017 4:35 PM Justin
- Enhanced to have Group by SKU checkbox and able to sum up the stock balance while it is checked.
- Enhanced to hide SKU child items while Group by SKU checkbox is checked and allow user to show it by click on expand icon.

10/22/2018 4:54 PM Justin
- Modified to load SKU type from its own table instead from SKU.

11/20/2018 3:52 PM Justin
- Enhanced to have "Sort by" and "Match word with" dropdown list options.

1/7/2019 4:48 PM Andy
- Fixed to always show latest sku in first page.

3/6/2019 2:48 PM Andy
- Enhanced to have Page Selection at the bottom of page.

5/7/2019 10:00 AM William
- Add search filter by packing uom.

7/24/2019 3:30 PM William
- Enhanced item_context_menu popup will not auto dismiss when mouse out, it will hide only when users click any other place.

8/8/2019 9:12 AM William
- Fixed bug item context menu cannot pop up when sku item inactive.

06/26/2020 Sheila 11:10 AM
- Updated button css.
*}

{include file=header.tpl}

{literal}
<style>
.inactive {
	color:#999;
}
.nowrap { white-space:nowrap;padding-right:10px; }

#div_sales_details,#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}

.input_matrix td input.ntp { /* price */
	background-color:#ff9;
}
.input_matrix td [alt="header"] { /* header */
    background-color:#9f9;
	font-weight:bold;
}
.input_matrix td input.nte {    /* entry */
	background-color:#fff;
	color:#000;
	font-weight:bold;
	border:1px solid black;
}
.input_matrix td input.ntd { /* disabled */
	background-color:#eee;
	color:#eee;
	border:none;
}
span.span_loading_actual_photo{
	padding:5px;
}
.cost_history_changed_sku_row{
	color: blue;
	font-style: italic;
}
</style>
{/literal}

<script type="text/javascript">

var LOADING = '<img src="/ui/clock.gif" />';
var category_autocompleter = null;
var php_self = '{$smarty.server.PHP_SELF}';
var curr_sort_order = new Array();
var curr_sort = new Array();
var sku_photo_sample_html = '<img width=100 height=100 align=absmiddle vspace=4 hspace=4 src="__THUMB_SRC__" border=1 style="cursor:pointer" onclick=\'popup_div("img_full", "<img width=640 src=\\"__SRC__\\" />")\' title="View" class="sku_photo" />';
var privlege_mst_po_reorder_qty_by_branch = '{$sessioninfo.privilege.MST_PO_REORDER_QTY_BY_BRANCH}';

{literal}
function SetCookie(cookieName,cookieValue,nDays) {
 var today = new Date();
 var expire = new Date();
 if (nDays==null || nDays==0) nDays=1;
 expire.setTime(today.getTime() + 3600000*24*nDays);
 document.cookie = cookieName+"="+escape(cookieValue)
                 + ";expires="+expire.toGMTString();
}

function show_child(id)
{
	// reactivate the auto-completer with child of the category
	setTimeout('category_autocompleter.options.defaultParams = "child='+id+'";category_autocompleter.activate()',250);
}

function init_autocomplete()
{
	category_autocompleter = new Ajax.Autocompleter("autocomplete_category", "autocomplete_category_choices", "ajax_autocomplete.php?a=ajax_search_category&min_level=-1", {
	afterUpdateElement: function (obj,li)
	{
	    this.defaultParams = '';
		var s = li.title.split(',');
		document.f_a.category_id.value = s[0];
		sel_category(obj,s[1]);
	}});
}

function sel_category(obj,have_child)
{
	var str = new String(obj.value);
	str.replace('<span class=sh>', '');
	str.replace('</span>', '');
	document.f_a.category_tree.value = str;
	$('str_cat_tree').innerHTML = str;
	obj.value = str.substr(str.lastIndexOf(">")+2, str.length);
}

function curtain_clicked()
{
	curtain(false);
	Element.hide('div_move');
	Element.hide('div_sku');
	Element.hide('div_size_color');
	Element.hide('div_sku_cost_history');
	if ($('div_promotion_history')!=undefined) Element.hide('div_promotion_history');
	Element.hide('div_sales_details');
	Element.hide('div_item_details');	
	$('div_branch_group').hide();
	hide_inventory_popups();
}

function curtain_sales_clicked()
{
	//curtain(false);
	curtain(true);
	Element.hide('div_move');
	Element.hide('div_sku');
	Element.hide('div_size_color');
	$('div_promotion_history').show();
	Element.hide('div_sales_details');
	$('div_branch_group').hide();
	hide_inventory_popups();
}

function curtain_promotion_clicked()
{
	curtain(false);
	Element.hide('div_move');
	Element.hide('div_sku');
	Element.hide('div_promotion_history');
	Element.hide('div_size_color');
	$('div_branch_group').hide();
	hide_inventory_popups();
}

function save_linkcode(id,sku_id)
{
	if ($('link_code['+id+']').value.trim() == '')
	{
	    alert('Field cannot be empty');
	    $('link_code['+id+']').focus();
		return;
	}
	new Ajax.Request(
		"{/literal}{$smarty.server.PHP_SELF}{literal}",
		{
		    parameters: 'a=ajax_save_linkcode&sku_id='+sku_id+'&value='+$('link_code['+id+']').value,
		    evalScripts: true,
		    onComplete: function(t) {
		        if (t.responseText != 'OK')
		        {
					alert(t.responseText);
					$('link_code['+id+']').select();
					$('link_code['+id+']').focus();
				}
				else
				    $('td['+id+']').innerHTML = $('link_code['+id+']').value;
			}
		}
	)
}

function do_move(id){
	document.f_move.sku_item_id.value=id;
	document.f_move['subm'].disabled = false;
	Element.show('div_move');
	curtain(true);
	center_div('div_move');	
	document.f_move.parent_code.select();
	document.f_move.parent_code.focus();
}

function do_submit(action){
	mi(document.f_move.parent_code);
	/*
	if(document.f_move.id.value==context_info.sku_id){
		alert('Cannot move item to same Parent SKU');
		document.f_move.id.select();
		document.f_move.id.focus();
		return;		
	}*/

	if (action == "change"){
		//Change under a sku parent

		if (document.f_move.parent_code.value <= 0){
			document.f_move.parent_code.select();
			alert('Please enter Parent ARMS Code');
			document.f_move.parent_code.select();
			document.f_move.parent_code.focus();
			return;
		}

		document.f_move['subm'].disabled = true;

		// check whether can move or not
		new Ajax.Request('masterfile_sku.php',{
			parameters:{
				a: 'ajax_check_sku_move',
				sku_item_id: document.f_move['sku_item_id'].value
			},
			onComplete: function(msg){
				if(msg.responseText=='OK'){
		            Element.hide('div_move');
					new Ajax.Updater('div_sku', 'masterfile_sku.php', {
					parameters: 'a=ajax_load_move_sku&'+Form.serialize(document.f_move),
					evalScripts: true,
					onComplete:function(){
						load_sku_detail();
					}
					});
				}else{
					alert(msg.responseText);
					document.f_move['subm'].disabled = false;
				}
			}
		});

	}else{
	    //Reset to be a sku parent
		document.f_move['reset'].disabled = true;

		new Ajax.Request('masterfile_sku.php',{
			parameters:{
				a: 'ajax_check_sku_obsolete',
				sku_item_id: document.f_move['sku_item_id'].value
			},
			onComplete: function(msg){
	            Element.hide('div_move');
			    curtain(false);

  				if(msg.responseText=='OK'){
		            document.f_move['a'].value="reset_to_parent";
					document.f_move.submit();
				}else{
					alert(msg.responseText);
					document.f_move['reset'].disabled = false;
				}
			}
		});
		
	}

}

function load_sku_detail(){
	Element.show('div_sku');
	curtain(true);
	center_div('div_sku');	
}

function do_confirm(){
	curtain_clicked();
	document.f_sku.submit();
}

var context_info;

function hide_context_menu()
{
	$('ul_menu').onmouseout = undefined;
	$('ul_menu').onmousemove = undefined;	 
	Element.hide('item_context_menu');
}

function show_context_menu(obj, sku_id, item_id, item_code)
{
	context_info = { element: obj, sku_id: sku_id, sku_item_id: item_id, code: item_code};
	$('item_context_menu').style.left = ((document.body.scrollLeft)+mx) + 'px';
	$('item_context_menu').style.top = ((document.body.scrollTop)+my-100) + 'px';
	
	// found this SKU is not allowed to set PO re-order by branch, hide menu...
	if(privlege_mst_po_reorder_qty_by_branch){
		var po_reorder_by_child = $(obj).readAttribute('po_reorder_by_child');
		if(po_reorder_by_child == 0){
			$('po_reorder_by_child').hide();
		}else $('po_reorder_by_child').show();
	}
	
	Element.show('item_context_menu');
	
	/*$('ul_menu').onmouseout = function() {
		context_info.timer = setTimeout('hide_context_menu()', 100);
	}
	
	$('ul_menu').onmousemove = function() {
		if (context_info.timer!=undefined) clearTimeout(context_info.timer);
		context_info.timer = undefined;
	}*/
	return false;
}

function show_cost_history_form(id_type,id)
{
	curtain(true);
	center_div('div_sku_cost_history');
	$('div_sku_cost_history').style.display = '';
	$('div_sku_cost_history').style.zIndex = 10000;
	show_sku_cost_history();
}

function show_promotion_history_form(id_type,id)
{
	curtain(true);
	center_div('div_promotion_history');
	$('div_promotion_history').style.display = '';
	$('div_promotion_history').style.zIndex = 10000;
	show_promotion_cost_history();
}

function show_promotion_cost_history()
{
    var bid = $('select_promo_branch').value;
  	var sku_item_id = context_info.sku_item_id;
 
  	$('div_promotion_history_list').update('<img src=/ui/clock.gif align=absmiddle> Loading...');
  	
  	new Ajax.Updater('div_promotion_history_list','promotion.php',
  	{
  	    method: 'get',
  	    parameters:{
  			a: 'ajax_get_promotions',
  			id: sku_item_id,
  			promo_bid: bid,
  			date_from: '2000-01-01',
  			date_to: '9999-12-31'
  		},
  		evalScripts:true
  	});
  	
  	/*new Ajax.Updater('div_promotion_history_list','ajax_sku_popups.php',
  	{
  	    method: 'get',
  	    parameters:{
  			a: 'promotion_history',
  			sku_item_id: sku_item_id,
  			branch_id: bid
  		},
  		evalScripts:true
  	});*/
}

function show_sku_cost_history(){
  
	var bid = $('select_branch').value;
	
	var sku_item_id = context_info.sku_item_id;
	
	$('div_sku_cost_history_list').update('<img src=/ui/clock.gif align=absmiddle> Loading...');
	
	new Ajax.Updater('div_sku_cost_history_list','ajax_sku_popups.php',
	{
	    method: 'get',
	    parameters:{
			a: 'sku_cost_history',
			sku_item_id: sku_item_id,
			branch_id: bid
		},
		evalScripts:true
	});
}

function sales_details(d,sk,br_id){
 
  curtain(true);
  center_div('div_sales_details');
  
  $('div_sales_details').show()
  $('div_promotion_history').hide();
	$('div_sales_content').update(LOADING+' Please wait...');
 
	new Ajax.Updater('div_sales_content',php_self+'?a=sales_details&date='+d+'&sku='+sk+'&brn_id='+br_id,
	{
	    method: 'post'
	});
}

function trans_detail(counter_id,cashier_id,date,pos_id)
{
  var br = document.f_b['b'].value;
  var sku = document.f_b['sku'].value;
	curtain(true);
	center_div('div_item_details');
	
  $('div_item_details').show();
	$('div_item_content').update(LOADING+' Please wait...');

	new Ajax.Updater('div_item_content',php_self,
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			counter_id: counter_id,
			pos_id: pos_id,
			cashier_id: cashier_id,
			date: date,
			br_id : br,
			sku_id :sku
		}
	});
}

function sort_reloadTable(col,grp)
{
	if (curr_sort[grp]==undefined || curr_sort[grp] != col)
	{
		curr_sort[grp] = col;
		curr_sort_order[grp] = 'asc';
	}
	else
	{
		curr_sort_order[grp] =  (curr_sort_order[grp] == 'asc' ? 'desc' : 'asc' );
	}

	SetCookie('_tbsort_'+grp, curr_sort[grp],1);
	SetCookie('_tbsort_'+grp+'_order', curr_sort_order[grp],1);

	// ajax reload
	$('span_loading').update('<img src=/ui/clock.gif align=absmiddle> Sorting in process...');
	new Ajax.Updater('div_content',php_self+'?ajax=1',{
		parameters: document.f_a.serialize(),
		method: 'post',
		evalScripts: true,
		onComplete: function(){
            $('span_loading').update('');
		}
	});
}

function showGroup(id){
	$('select_branches_group').value = id;
	document.f_a.submit();
}

function show_item_by_group(group_id,sku_item_id){
	curtain(true);
	$('div_branch_group').update(_loading_);
	$('div_branch_group').show();
	center_div('div_branch_group');
	
	var group_by_sku;
	if(document.f_a['group_by_sku'].checked == true){
		group_by_sku = 1;
	}else group_by_sku = 0;
	
	new Ajax.Updater('div_branch_group',php_self,{
		parameters: {
		    a: 'get_branch_sku',
            group_id: group_id,
            sku_item_id: sku_item_id,
			group_by_sku: group_by_sku
		},
		onComplete: function(e){
            center_div('div_branch_group');
		}
	});
}

function size_color_form(id_type,id)
{
	curtain(true);
	center_div('div_size_color');
	$('div_size_color').style.display = '';
	$('div_size_color').style.zIndex = 10000;
	size_color_matrix();
}

function size_color_matrix()
{
	var bid = $('select_branch').value;

	var sku_item_id = context_info.sku_item_id;

	$('div_size_color_matrix').update('<img src=/ui/clock.gif align=absmiddle> Loading...');

	new Ajax.Updater('div_size_color_matrix','ajax_sku_popups.php',
	{
	    method: 'get',
	    parameters:{
			a: 'size_color',
			sku_item_id: sku_item_id,
			branch_id: bid
		},
		evalScripts:true
	});
}

function show_replacement_items(sid){
    var params = {
		'sid': sid,
		'can_click_item_row': 1
	}
	replacement_item.show_popup(params);
}

function replacement_item_code_clicked(params){
	var sku_item_code = params['sku_item_code'];
	if(!sku_item_code)  return;
	
	document.f_a['search_description'].value = sku_item_code;
	document.f_a.submit();
}

function update_sku_cost_changed(sid){
	new Ajax.Request(php_self+'?a=update_sku_cost_changed&sid='+sid,{
		onComplete: function(e){
			var msg = e.responseText.trim();
			if(msg=='OK'){
				alert('Item cost and inventory has marked as not up to date. Please wait 30 minutes and check again.');
			}else{
				alert(msg);
			}
		}
	});
}

function resync_sku(sid){
    new Ajax.Request(php_self+'?a=resync_sku&sid='+sid,{
		onComplete: function(e){
			var msg = e.responseText.trim();
			if(msg=='OK'){
				alert('This sku has been add into the queue to sync, please check again later');
			}else{
				alert(msg);
			}
		}
	});
}

function show_hide_block(obj){
	if (obj.value > 0){
	    $('block').style.display = '';
	    document.f_a['block'].disabled=false;
	}else{
	    $('block').style.display = 'none';
	    document.f_a['block'].disabled=true;
	}


}

function load_sku_item_actual_photo(){
	// get all span which showing 'loading photo...'
	var span_loading_actual_photo_list = $$('#div_content span.span_loading_actual_photo');

	for(var i=0; i<span_loading_actual_photo_list.length; i++){
		var span = span_loading_actual_photo_list[i];
		var sid = $(span).id.split('-')[1];

		ajax_load_sku_item_photo(sid);
	}
}

function ajax_load_sku_item_photo(sid){
	var span = $('span_loading_actual_photo-'+sid);
	var url_to_get_photo = $(span).readAttribute('url_to_get_photo');
	
	new Ajax.Request('http_con.php', {
		parameters:{
			a: 'ajax_load_sku_item_photo',
			sku_item_id: sid,
			url_to_get_photo: url_to_get_photo,
			SKIP_CONNECT_MYSQL: 1
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};
		    var err_msg = '';

			var span = $('span_loading_actual_photo-'+sid);	// get again span
			
			if(!span)	return;	// span not found
			
		    try{
                ret = JSON.parse(str); // try decode json object
                if(ret['photo_list']){ // success
                    for(var i=0; i<ret['photo_list'].length; i++){
                    	var path = ret['photo_list'][i]; 
                    	var sku_photo_html = sku_photo_sample_html;
						var thumb_path = '/thumb.php?w=100&h=100&cache=1&img='+URLEncode(path);
						
						sku_photo_html = sku_photo_html.replace(/__SRC__/g, escape(path));
						sku_photo_html = sku_photo_html.replace(/__THUMB_SRC__/g, thumb_path);

						new Insertion.After('span_loading_actual_photo-'+sid, sku_photo_html);
						
					}
					$(span).remove();
	                return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}
			
			// failed
			$(span).remove();
			
			if($$('#div_photo_icon-'+sid+' img.sku_photo').length<=0){	// no photo found for this sku
				$('div_photo_icon-'+sid).remove();	//remove container as well
			}
		}
	});
}

function toggle_sc_details(dt){
	var div = $('div_sc_details-'+dt);
	if(!div)	return;
	
	if(div.style.display=='none'){
		div.show();
	}else{
		div.hide();
	}
}

function show_sku_summary(sid){
	window.open('masterfile_sku_summary.php?sid='+sid);
}

function toggle_cost_history_details(d, entry_row){
	var row = $('tbody_entry_row-'+d+'-'+entry_row);
	if(!row)	return;
	
	if(row.style.display == 'none'){
		row.style.display = '';
	}else{
		row.style.display = 'none';
	}
}

function toggle_sku_items(sku_id, obj){
	var all_tr = $$("#tbl_si tr.tr_sku_item_"+sku_id);
	if(obj.src.indexOf('expand')>0){
		obj.src = '/ui/collapse.gif';
		for(var i=0; i<all_tr.length; i++){
			$(all_tr[i]).show();
		}
	}else{
		obj.src = '/ui/expand.gif';
		for(var i=0; i<all_tr.length; i++){
			$(all_tr[i]).hide();
		}
	}
}

function btm_page_changed(){
	var sel_page = $('sel_page-btm');
	if(!sel_page)	return;
	
	document.f_a['s'].selectedIndex = sel_page.value;
	document.f_a.submit();
}

function filter_dropdown(){
	//get the advanced table  
	var tbl_advanced_filter = $('tbl_advanced_filter');
	var inp_show_advanced_search = $('inp_show_advanced_search');
	var advancedsearch_btn = $('advancedsearch_btn');
	var is_show = inp_show_advanced_search.value==1 ? false : true;
	
	if(is_show){
		// Show filter
		$(tbl_advanced_filter).show();
		$(advancedsearch_btn).update('Hide Advanced Filter');
		inp_show_advanced_search.value = 1;
	}else{
		// Hide filter
		$(tbl_advanced_filter).hide();
		$(advancedsearch_btn).update('Show Advanced Filter');
		inp_show_advanced_search.value = 0;
	}
}

function detect_to_hide_context_menu(){
	document.addEventListener('click', function(e){   
	  if (!e.target.closest(".tr_sku_item")){
		hide_context_menu();
	  }
	});
}
</script>
{/literal}
<!-- Replacement Iten Popup -->
{if $config.enable_replacement_items}{include file='replacement_items_popup.tpl'}{/if}

<!-- Transaction Details-->
<div id="div_sales_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="curtain_sales_clicked()" src="/ui/closewin.png" /></div>
<div id="div_sales_content">
</div>
</div>
<!-- End of Transaction Details-->
<!-- Item Details -->
<div id="div_item_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="hidediv('div_item_details');" src="/ui/closewin.png" /></div>
<div id="div_item_content">
</div>
</div>
<!-- End of Item Details-->
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>


<div id=item_context_menu style="display:none;position:absolute;">
<ul id=ul_menu class=contextmenu>
{if $sessioninfo.privilege.MST_SKU_UPDATE}
	<li> <a href="javascript:void(0)" onclick="hide_context_menu();window.open('{$smarty.server.PHP_SELF}?a=edit&id='+context_info.sku_id+'&sid='+context_info.sku_item_id);"><img src=/ui/ed.png border=0 alt="Edit"> Edit SKU</a></li>
{/if}
<li> <a href="javascript:void(0)" onclick="hide_context_menu();show_inventory('sku_id', context_info.sku_id, '');"><img src="/ui/icons/table_key.png" align=absmiddle border=0> Show Parent SKU Inventory</a></li>
<li> <a href="javascript:void(0)" onclick="hide_context_menu();show_inventory('sku_item_id',context_info.sku_item_id,'');"><img src="/ui/icons/table_key.png" align=absmiddle border=0> Show SKU Item Inventory</a></li>

<li><a href="javascript:void(0)" onclick="hide_context_menu();get_item_sales_trend(context_info.sku_item_id, 1)"><img src="/ui/icons/chart_bar.png" align="absmiddle" /> Parent Sales Trend</a></li>


<li><a href="javascript:void(0)" onclick="hide_context_menu();get_item_sales_trend(context_info.sku_item_id)"><img src=/ui/icons/chart_bar.png align=absmiddle> Item Sales Trend</a></li>

{if $sessioninfo.privilege.MST_SKU_MOVE_PARENT}
	<li> <a href="javascript:void(0)" onclick="hide_context_menu();do_move(context_info.sku_item_id, context_info.sku_id);"><img src=/ui/move.png align=absmiddle border=0> Change Parent SKU</a></li>
{/if}
{if $sessioninfo.privilege.MST_SKU_UPDATE_PRICE}
	<li> <a href="javascript:void(0)" onclick="hide_context_menu();window.open('masterfile_sku_items_price.php?a=find&sku_item_id='+context_info.sku_item_id);"><img src=/ui/icons/money_dollar.png align=absmiddle border=0>  Change SKU Items Price</a></li>
{/if}
{if $sessioninfo.privilege.SHOW_COST}
	<li> <a href="javascript:void(0)" onclick="hide_context_menu();show_cost_history_form('sku_item_id',context_info.sku_item_id);"><img src="/ui/icons/chart_curve.png" align=absmiddle border=0>  Cost History</a></li>
{/if}

{if $sessioninfo.privilege.PROMOTION_HISTORY}
<li> <a href="javascript:void(0)" onclick="hide_context_menu();show_promotion_history_form('sku_item_id',context_info.sku_item_id);"><img src="/ui/icons/chart_curve.png" align=absmiddle border=0>  Promotion History</a></li>
{/if}

<li> <a href="javascript:void(0)" onclick="hide_context_menu();size_color_form('sku_item_id',context_info.sku_item_id);"><img src="/ui/icons/chart_curve.png" align=absmiddle border=0>  Size/Colour In Matrix</a></li>

{if $config.enable_replacement_items}
    <li> <a href="javascript:void(0)" onclick="hide_context_menu();show_replacement_items(context_info.sku_item_id);"><img src="/ui/icons/page_refresh.png" align=absmiddle border=0> Show Replacement Items</a></li>
{/if}
{if $sessioninfo.level>=9999}
    <li> <a href="javascript:void(0)" onclick="hide_context_menu();update_sku_cost_changed(context_info.sku_item_id);"><img src="/ui/icons/brick_error.png" align=absmiddle border=0> Recalculate cost & inventory</a></li>
    {if $BRANCH_CODE eq 'HQ'}
    	<li> <a href="javascript:void(0)" onclick="hide_context_menu();resync_sku(context_info.sku_item_id);"><img src="/ui/icons/arrow_refresh.png" align=absmiddle border=0> Resync SKU</a></li>
    {/if}
{/if}
{if $sessioninfo.privilege.MST_PO_REORDER_QTY_BY_BRANCH}
	<li id="po_reorder_by_child"> <a href="javascript:void(0)" onclick="hide_context_menu(); window.open('masterfile_sku_items.po_reorder_qty_by_branch.php?sid='+context_info.sku_item_id+'&si_code='+context_info.code);"><img src="/ui/icons/layout_edit.png" align=absmiddle border=0> Set PO Re-order Qty by Branch</a></li>
{/if}

	{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_summary.php")}
		<li>
			<a href="javascript:void(0)" onclick="hide_context_menu();show_sku_summary(context_info.sku_item_id)"><img src="/ui/icons/chart_bar.png" align="absmiddle" /> SKU Summary</a>
		</li>
	{/if}
</ul>
</div>

<div id=div_move style="display:none;position:absolute;z-index:50000;background:#fff;border:2px solid #000;height:150px;width:300px;">
<form name=f_move method=post onSubmit="return false;">
<input type=hidden name=sku_item_id>
<input type=hidden name=a>

<h3 align=center>Change SKU Parent</h3>
<p align=center>
<b>Enter the new Parent ARMS Code to move to</b>
<input name="parent_code" size=25><br><br>
<input type=submit name="subm" value="Submit" onclick="do_submit('change');">
<input type=button value="Cancel" onclick="curtain_clicked();">
<input type=submit name="reset" value="Reset to Parent" onclick="do_submit('reset');">
</P>
</form>
</div>

<div id=div_sku style="display:none;position:absolute;z-index:50001;background:#fff;border:1px solid #000;margin: 0 0 0 0;height:500px;width:552px;overflow:auto;padding:10px;">
</div>

<div id="div_branch_group" style="display:none;position:absolute;z-index:50002;background:#fff;border:2px solid #000;padding:10px;width:550px;height:500px;overflow:auto;padding:3px;"></div>

{include file=popup.inventory_popups.tpl}
{include file=popup.sku_cost_history.tpl}
{include file=popup.promotion_history.tpl}
{include file=popup.size_color.tpl}

<div class="card mx-3">
	<div class="card-body">
		<form name=f_a class=noprint style="line-height:24px" method=get>
			<input type=hidden name=a value=find> 
			<input type="hidden" name="load" value="1" />
			<div class=stdframe >
			
				<table>
					
					<div class="row">
						<div class="col-md-4">
							<span class="form-inline"><b class="form-label">Find</b>&nbsp;
								<input  style="width: 150px;" class="form-control" name=search_description value="{$smarty.request.search_description}" size=20> <b class="form-label">&nbsp;in&nbsp;</b> 
								<select class="form-control" name="search_filter">
									<option value="">-- All --</option>
									{if $config.link_code_name}<option value="linkcode" {if $smarty.request.search_filter eq "linkcode"}selected {/if}>{$config.link_code_name}</option>{/if}
									<option value="armscode" {if $smarty.request.search_filter eq "armscode"}selected {/if}>ARMS Code</option>
									<option value="artno" {if $smarty.request.search_filter eq "artno"}selected {/if}>Artno</option>
									<option value="mcode" {if $smarty.request.search_filter eq "mcode"}selected {/if}>Mcode</option>
									<option value="description" {if $smarty.request.search_filter eq "description"}selected {/if}>Description</option>
								</select></span>
						</div>
						
						<div class="col-md-4">
							
							<span class="form-inline"><b class="form-label">&nbsp;Match Word With&nbsp;</b> 
								<select class="form-control" name="match_method">
									{foreach from=$matching_method_list key=method_type item=method_name}
										<option value="{$method_type}" {if $smarty.request.match_method eq $method_type}selected{/if}>{$method_name}</option>
									{/foreach}
								</select></span>
						</div>
					
						<div class="col-md-4">
							
							<span class="form-inline">
								&nbsp;<b class="form-label">Sort By</b>&nbsp; 
								<select class="form-control" name="sorting_type">
									{foreach from=$sorting_list key=sort_type item=sort_name}
										<option value="{$sort_type}" {if $smarty.request.sorting_type eq $sort_type}selected{/if}>{$sort_name}</option>
									{/foreach}
								</select>&nbsp;
								<select class="form-control" name="sorting_sequence">
									<option value="asc" {if $smarty.request.sorting_sequence eq 'asc'}selected{/if}>Ascending</option>&nbsp;&nbsp;&nbsp;&nbsp;
									<option value="desc" {if $smarty.request.sorting_sequence eq 'desc' or !$smarty.request.sorting_sequence}selected{/if}>Descending</option>
								</select>
							</span>
						</div>

						
					</div>	
					
				</table>
				
			<div class="badge badge-dark p-1 mt-2 mb-2">
				<a id="advancedsearch_btn" style="cursor:pointer;" onClick='filter_dropdown()'>{if $smarty.request.show_advanced_search}Hide{else}Show{/if} Advanced Filter</a>
			</div>
				<input name="show_advanced_search" id="inp_show_advanced_search" type="hidden" value="{$smarty.request.show_advanced_search}" />
				
				<table style="{if !$smarty.request.show_advanced_search}display:none;{/if}" id="tbl_advanced_filter">
					<tr>
						<td>
							<span><b class="form-label">Category</b></span>
						</td>
						<td>
							<span>
							<input type=radio name=s1 value=0 {if $smarty.request.s1==0}checked{/if} onclick="Element.hide('csel');"> All
							<input type=radio name=s1 value=1 {if $smarty.request.s1==1}checked{/if} onclick="Element.show('csel');category.focus();"> Selected
							<span id=csel style="{if $smarty.request.s1==0}display:none;{/if} white-space:normal;">
							<input readonly name=category_id size=1 value="{$smarty.request.category_id}">
							<input type=hidden name=category_tree value="{$smarty.request.category_tree}">
							<input class="form-control" id=autocomplete_category name=category value="{$smarty.request.category|default:'Enter keyword to search'}" onfocus=this.select() size=50><br>
							<div id=autocomplete_category_choices class=autocomplete style="width:600px !important"></div>
							<span id=str_cat_tree class=small style="color:#00f;margin-left:90px;">{$smarty.request.category_tree|default:''}</span>
							</span>
						</span>
						</td>
					</tr>
			
					<tr>
						<td>
							<span><b class="form-label">Brand</b></span>
						</td>
						<td>
							<span>
							{dropdown all="-- All --" name=brand_id values=$brands key='id' value='description' selected=$smarty.request.brand_id}
							</span>
						</td>
						<td>
							<span><b class="form-label mt-4">Selling Price Inclusive Tax</b></span>&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
						<td>
							<span>
							<select class="form-control" name="incl_tax_filter">
								<option value="">-- All --</option>
								<option value="yes" {if $smarty.request.incl_tax_filter eq "yes"}selected {/if}>YES</option>
								<option value="no" {if $smarty.request.incl_tax_filter eq "no"}selected {/if}>NO</option>
							</select>
							</span>
						</td>
					</tr>
					
					<tr>
						<td>
							<span><b class="form-label mb-4">Vendor</b></span>
						</td>
						<td>
							<span>{dropdown all="-- All --" name=vendor_id values=$vendors key='id' value='description' selected=$smarty.request.vendor_id}</span>&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
						<td>
							<span><b class="form-label mb-4">SKU Type</b></span>
						</td>
						<td>
							<span><select class="form-control mb-3" name="sku_type">
								<option value=''>-- All --</option>
								{foreach from=$sku_type_list item=r}
									<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected {/if}>{$r.description}</option>
								{/foreach}
							</select></span>
						</td>
					</tr>
					
					
					
					<tr>
						<td>
							<span><b class="form-label">Parent Child?</b></span>
						</td>
						<td>
							<span><select class="form-control" name="parent_child_filter">
								{foreach from=$parent_child key=k item=i}
								<option value="{$k}" {if $smarty.request.parent_child_filter eq $k}selected{/if}>{$i}</option>
								{/foreach}
							</select></span>
						</td>
						<td>	
			
						</td>
						<td>
							
						</td>
					</tr>
					
					<tr>
						<td>
							<span><b class="form-label">Scale Type</b></span>
						</td>
						<td>
							<span><select class="form-control" name="scale_type">
									<option value=''>-- All --</option>
									{foreach from=$scale_type_list key=st_value item=st_name}
										{if $st_value >= 0}
											<option value="{$st_value}" {if $smarty.request.scale_type neq '' and $smarty.request.scale_type eq $st_value}selected {/if}>{$st_name}</option>
										{/if}
									{/foreach}
							</select></span>
						</td>
						<td>
							{if $branches_group.header and BRANCH_CODE eq 'HQ'}
							<span><b class="form-label">Branch Group</b></span>
						</td>
						<td>
								<span><select class="form-control" name="branches_group" id="select_branches_group">
									<option value=''>-- All --</option>
									{foreach from=$branches_group.header item=r}
										<option value="{$r.id}" {if $smarty.request.branches_group eq $r.id}selected {/if}>{$r.code}</option>
									{/foreach}
								</select></span>
							{/if}
						</td>
					</tr>
					
					<tr>
						<td>
							<span><b class="form-label">Active</b></span>
						</td>
						<td>
							<span><select class="form-control" name="active">
								<option value="" {if $smarty.request.active eq ''}selected {/if}>-- All --</option>
								<option value="1" {if $smarty.request.active eq '1'}selected {/if}>Yes</option>
								<option value="0" {if $smarty.request.active eq '0'}selected {/if}>No</option>
							</select></span>
						</td>
						<td>
							<span><b class="form-label">Packing UOM</b></span>
						</td>
						<td>
							<span><select class="form-control" name="uom_id">
								<option value="" {if $smarty.request.uom_id eq ''}selected {/if}>-- All --</option>
								{section name=j loop=$uom}
								<option value="{$uom[j].id}" {if $smarty.request.uom_id eq $uom[j].id}selected{/if} > 
									{$uom[j].code}
								</option>
								{/section}
							</select></span>
						</td>
					</tr>
					
					
					{if $config.enable_gst}
					<tr>
						<td>
							<span><b class="form-label">Input Tax</b></span>
						</td>
						<td>
							<span><select class="form-control" name="input_tax_filter">
							<option value="">-- All --</option>
								{foreach from=$input_tax_list key=rid item=r}
								<option value="{$r.id}" {if $smarty.request.input_tax_filter eq $r.id}selected {/if}>{$r.code} ({$r.rate}%)</option>
								{/foreach}
							</select></span>
						</td>
						<td>
							<span><b class="form-label">Output Tax</b></span>
						</td>
						<td>
							<span><select class="form-control" name="output_tax_filter">
								<option value="">-- All --</option>
								{foreach from=$output_tax_list key=rid item=r}
									<option value="{$r.id}" {if $smarty.request.output_tax_filter eq $r.id}selected {/if}>{$r.code} ({$r.rate}%)</option>
								{/foreach}
							</select></span>
						</td>
					</tr>
					{/if}
					
					
					
					<tr>
						<td>
							<span><b class="form-label mt-4">Item blocked in PO</b></span>&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
						<td>
							<span>
								{if BRANCH_CODE eq 'HQ'}
									<select class="form-control" name="branch_id" onchange="show_hide_block(this)" id="branch_id">
										<option value=''>-- No Filter --</option>
										{foreach from=$branches item=r}
											<option value="{$r.id}" {if $smarty.request.branch_id eq $r.id}selected {/if}>{$r.code}</option>
										{/foreach}
									</select>
								{/if}
			
								<span id=block {if $smarty.request.branch_id eq ''}style="display:none;" {/if}>
			
								<select name="block" {if $smarty.request.branch_id eq ''} disabled {/if}>
									{if BRANCH_CODE neq 'HQ'}
									<option value="">-- No Filter --</option>
									{/if}
									<option value="1" {if $smarty.request.block eq '1'}selected {/if}>Yes</option>
									<option value="0" {if $smarty.request.block eq '0'}selected {/if}>No</option>
								</select>
			
								</span>
			
							
							</span>
						</td>
						{if $config.sku_application_require_multics}
						<td>
							<span><b class="form-label">Status</b></span>
						</td>
						<td>
							<span><select class="form-control" name=status>
								<option value=''>-- All --</option>
								<option value=0 {if $smarty.request.status eq '0'}selected{/if}>No {$config.link_code_name}</option>
								<option value=1 {if $smarty.request.status eq '1'}selected{/if}>Have {$config.link_code_name}</option>
							</select></span>
							
						</td>
						{/if}
					</tr>
					
					<tr>
						<td>
							<span><b class="form-label">Group by SKU</b></span>
						</td>
						<td>	
							<span><input style="margin: 0;" type="checkbox" name="group_by_sku" value="1" {if $smarty.request.group_by_sku}checked{/if} /></span>
						</td>
					</tr>
					
					<tr>
						<td>
							<span>
								{if $pagination}
								{$pagination}
								{/if}
							</span>
						</td>
						<td></td>
					</tr>
					
				</table>
			
				</br>
				<span >
					<input class=" btn btn-primary mt-2" name=submits type=submit value="Find">
					{if $config.link_code_name && $config.sku_application_require_multics}<input type=button value="Print for {$config.link_code_name}" onclick="window.open('{$smarty.server.PHP_SELF}?print=1&{$smarty.server.QUERY_STRING}')">{/if}
				</span>
			</div>	
			</form>
	</div>
</div>

{if $smarty.request.load}
	{if $err_msg}
		<p>{$err_msg}</p>
	{else}
		<ul>
			<li> <font color=red>*</font> = not up to date</li>
			<li> click on a row for more options.</li>
			<li> S. R. = Stock Reorder</li>
		</ul> 
		<span id="span_loading"></span>
		<div id="div_content">
			{include file='masterfile_sku.table.tpl'}
		</div>
	{/if}
	
	{if $page_max>1}
		<br /><b>Go to Page</b>
		<select id="sel_page-btm" onChange="btm_page_changed()">
			{section loop=$page_max name=ps}
				<option value="{$smarty.section.ps.index}" {if $smarty.request.s eq $smarty.section.ps.index*$size_page}selected {/if}>{$smarty.section.ps.iteration}</option>
			{/section}
		</select>
	{/if}
{/if}

{include file=footer.tpl}

<script>
init_autocomplete();
document.f_a['search_description'].focus();
detect_to_hide_context_menu();
</script>
