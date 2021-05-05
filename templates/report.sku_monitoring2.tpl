{*
9/1/2010 2:40:43 PM Andy
- Change calculate holding cost base on bank interest rate.
- Add can use config to control start calculation holding cost after how many months.

9/14/2010 3:03:54 PM Andy
- Fix wrong bank interest calculation.

9/17/2010 3:26:56 PM Andy
- Add sku ending date notice.
- Make sku span use lighter color if already have repeat.
- Make all monthly data closed in default.

9/23/2010 10:12:52 AM Andy
- Add column tooltips.
- Change to do not show the "end at..." remark for the item which does not have repeat until the current date.
- Fix IBT row show empty branch description.

10/6/2010 5:50:44 PM Andy
- Fix sometime cannot show full tooltip due to double quotes problem.

11/17/2010 2:13:54 PM Andy
- Add feature to let user choose whether use report server or not.
- Add flush and send output to client browser every 20 items.
- Change (End at date) to (Carry to date)

11/26/2010 11:06:24 AM Andy
- Separate items by table to reduce browser load speed. (can adjust by config)

12/24/2010 10:58:41 AM Andy
- Add few extra column "Proposal", some of it are editable and will be directly save once user finish edit.
- Those column and edited data will also show in export excel.
- Add checkbox to let user only show got proposal data SKU.

12/30/2010 2:51:00 PM Andy
- Change to hide all monthly column by default.
- Add a control box to allow user to show/hide multiple monthly column.

1/28/2011 2:57:38 PM Andy
- Change the available branch checkbox layout.

6/13/2011 9:58:32 AM Andy
- Add "Actual Profit Group Amount".
- Change "Average Per Unit (Amt)" cost and selling formula.
- Remove "Profit & Sales Weighted average".

10/17/2011 3:08:43 PM Alex
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

4/19/2017 9:21 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/22/2018 10:00 pm Kuan Yeh
- Bug fixed of logo shown on excel export  

06/30/2020 02:42 PM Sheila
- Updated button css.

*}

{include file='header.tpl'}

{if !$no_header_footer}
<style>
{literal}
.err{
	color: red;
}
.group_proposed{
	background-color:rgb(204,153,255);
}
.branch_last{
	background-color: rgb(255,255,153);
}
.avg{
    background-color:#cfc;
}
.variances{
    background-color:#00ffff;
}
.d1{
	background-color:#00FF00;
}
.d2{
	background-color:#CC99FF;
}
.row1{
	background-color: #ff0;
}
.unused_col{
	background-color: #afafaf;
}
.branch_summary{
	background-color: #fc0;
}
.in_qty{
    background-color: #cfc;
}
.sales_qty{
    background-color: #ff9;
}
.ibt_qty{
    background-color: #f9c;
}
.gra_qty{
    background-color: #f60;
}
.total_out_qty{
    background-color: #fc0;
}
.adj_qty{
    background-color: #ff8080;
}
.balance_qty{
    background-color: #c0c0c0;
}
.sales_amt{
    background-color: #f77;
}
.holding_cost{
    background-color: #f9c;
}
.actual_profit{
    background-color: #9cf;
}
.markup{
    background-color: #ff9;
}
.markdown{
    background-color: #f9c;
}
.discount{
    background-color: #f60;
}
.variance_amt{
    background-color: rgb(255,255,153);
}
.left_div{
    width:350px;
	overflow:hidden;
	float:left;
}
.right_div{
    float:left;
	overflow-y:hidden;
	overflow-x:auto;
}
.repeated_sku{
	opacity:0.3;
}
.col_proposal{
	background: #cff;
}
.editable{
	background: yellow;
}
.ajax_saving{
	background: red;
}
.summary{
	background-color: #fcf;
}
.col_sp1{
	background-color: #9f3;
}
.col_sp2{
	background-color: #fc0;
}
{/literal}
</style>

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var BRANCH_CODE = '{$BRANCH_CODE}'
var smg = '{$smarty.request.sku_monitoring_group_id}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
{literal}

EDITABLE_FIELD_MODULE = {
    div_edit_popup:undefined,
    inp_edit_text: undefined,
    div_selected_popup: undefined,
	selected_obj: undefined,
	sku_monitoring_group_id: 0,
	year: 0,
	month: 0,
	need_save: true,
	initialize: function(){
		this.div_edit_popup = $('div_edit_popup');
		this.inp_edit_text = $('inp_edit_text');
		
		if(!this.div_edit_popup || !this.inp_edit_text){
			alert('Direct Edit module failed to start up.');
			return false;
		}
		
		if(!smg)    return false;   // no group is selected
		
		var smg_arr = smg.split(",");
		this.sku_monitoring_group_id = smg_arr[0];
		this.year = smg_arr[1];
		this.month = smg_arr[2];
		
		
		// event when user lost focus on the edit text
		$(this.inp_edit_text).observe('blur', function(){
            EDITABLE_FIELD_MODULE.save();
		}).observe('keypress', function(event){ // check user key press
            EDITABLE_FIELD_MODULE.check_edit_text_keypress(event);
		})
		
		// get all editable <td>
		$$('table.report_table td.editable').each(function(td){
			$(td).observe('click', function(){
                EDITABLE_FIELD_MODULE.start_edit(this);
			})
		});
		
		document.onmouseup = function(){
            EDITABLE_FIELD_MODULE.check_document_clicked();
		}
	},
	// function to reset all status
	reset_element_status: function(){
		this.need_save = true;
	},
	// function to save the change to server
	save: function(){
		// check whether need to save first
		if(!this.selected_obj || !this.need_save)  return false;   // no object is selected
		
		var old_value = float(this.selected_obj.innerHTML.replace(/^&nbsp;/,''));
		var new_value = float(this.inp_edit_text.value.replace(/^&nbsp;/,''));
		
		if(old_value==new_value){
            // close edit dialog
			this.close_edit();
			return false;   // same value, no need save
		}    
		
		// get all information
		var id_arr = this.selected_obj.id.split('-');
		var colname = id_arr[0];
		var bid = id_arr[1];
		var sid = id_arr[2];
		var obj = this.selected_obj;
		
		// construct params
		if(colname.indexOf('qty')>=0)   new_value = float(round(new_value, global_qty_decimal_points));
		else    new_value = round2(new_value);
		
		$(obj).addClassName('ajax_saving').update(new_value);
		var params = {
			a: 'ajax_update_proposal_data',
			colname: colname,
			bid: bid,
			sid: sid,
			v: new_value,
			sku_monitoring_group_id: this.sku_monitoring_group_id,
			year: this.year,
			month: this.month
		};
		
		// call ajax to save
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				$(obj).removeClassName('ajax_saving');
				
				if(str=='OK'){  // save success
                    EDITABLE_FIELD_MODULE.recalc_row(bid, sid, colname);
				}else{  // save failed
					alert(str);
					$(obj).update(old_value);  // roll back to old value
				}
			}
		});
		
		// close edit dialog
		this.close_edit();
	},
	// function to check what user press in the editable text
	check_edit_text_keypress: function(event){
		if (event == undefined) event = window.event;
		var kc = event.keyCode;
		
		if(kc==13){  // enter
			this.save();
		}else if(kc==27){    // escape
		    this.close_edit();
		    
		}
		event.stopPropagation();
	},
	start_edit: function(ele){
		var ele_id = ele.id;
		if(!ele_id) return false;   // no id
		
		this.reset_element_status();    // reset element status
		
		this.selected_obj = ele;    // mark selected obj
		
		// copy the value to the input text
		this.inp_edit_text.value = float(this.selected_obj.innerHTML.replace(/^&nbsp;/,''));
		
		Position.clone(this.selected_obj, this.div_edit_popup); // clone position for div
		Position.clone(this.selected_obj, this.inp_edit_text); // clone position for input text
		$(this.div_edit_popup).show();
		$(this.inp_edit_text).select();
	},
	// function to close the editable popup
	close_edit: function(){
	    this.selected_obj = undefined;
        $(this.div_edit_popup).hide();
	},
	// event when user click on document other side
	check_document_clicked: function(){
		if(this.selected_obj){  // if got editing field
			this.save();    // save the field before move to new action
			this.close_edit();
		}
	},
	// function to recalculate the proposal data
	recalc_row: function(bid, sid, colname){
		var hq_cost = float($('hq_cost-'+bid).innerHTML);
		var branch_last_selling = float($('branch_last_selling-'+bid+'-'+sid).innerHTML.replace(/^&nbsp;/,''));
		
		// Branch summary
	    var branch_summary_sales_amt = float($('branch_summary_sales_amt-'+bid+'-'+sid).innerHTML);
	    var branch_summary_cost_of_goods_sold = float($('branch_summary_cost_of_goods_sold-'+bid+'-'+sid).innerHTML);
	    
		// Proposal
	    var existing_branch_profit_per = float($('existing_branch_profit_per-'+bid+'-'+sid).innerHTML.replace(/^&nbsp;/,''));
	    var existing_group_profit_per = float($('existing_group_profit_per-'+bid+'-'+sid).innerHTML.replace(/^&nbsp;/,''));
	    var existing_sales_per = float($('existing_sales_per-'+bid+'-'+sid).innerHTML.replace(/^&nbsp;/,''));
	    var existing_sales_pcs = float($('existing_sales_pcs-'+bid+'-'+sid).innerHTML);
	    var bal_qty = float($('bal_qty-'+bid+'-'+sid).innerHTML.replace(/^&nbsp;/,''));
		var p1_sales_qty = float($('p1_sales_qty-'+bid+'-'+sid).innerHTML.replace(/^&nbsp;/,''));
		var p1_sales_per = 0;
		var p1_selling_price = float($('p1_selling_price-'+bid+'-'+sid).innerHTML.replace(/^&nbsp;/,''));
		var p2_selling_price = float($('p2_selling_price-'+bid+'-'+sid).innerHTML.replace(/^&nbsp;/,''));
		
		// IN
		var total_in = float($('total_in-'+bid+'-'+sid).innerHTML.replace(/^&nbsp;/,''));
		
		// value to be recalculate
		// proposal
		var p2_sales_qty = 0;
		var p2_sales_per = 0;
		var proposal_1_profit = 0;
		var proposal_2_profit = 0;
		
		// SP1
		var sp1_sales_at_sp1 = 0;
		var sp1_cost_sales_at_sp1 = 0;
		var sp1_profit_for_sp1 = 0;
		var sp1_total_sales_plus_sp1 = 0;
		var sp1_total_cost_plus_sp1 = 0;
		var sp1_net_profit_sp1 = 0;
		var sp1_profit_per_sp1 = 0;
		
		// SP2
		var sp2_sales_at_sp2 = 0;
		var sp2_cost_sales_sp2 = 0;
		var sp2_profit_sp2 = 0;
		var sp2_total_sales_sp1sp2 = 0;
		var sp2_total_cost_sp1sp2 = 0;
		var sp2_net_profit_sp1sp2 = 0;
		var sp2_profit_per_sp1sp2 = 0;
		
		var temp = 0;
		var temp2 = 0;
		// start calculate
		//alert(p1_sales_qty);
		// Proposal
		if(existing_sales_pcs)	p1_sales_per = float(round2(existing_sales_per*p1_sales_qty/existing_sales_pcs));
		p2_sales_qty = round2(bal_qty-p1_sales_qty);
		if(existing_sales_pcs)    p2_sales_per = float(round2(existing_sales_per*p2_sales_qty/existing_sales_pcs));
		
		// SP1
		// sales at sp1
		temp = branch_last_selling*hq_cost;
		if(temp)	sp1_sales_at_sp1 = float(round2(branch_summary_sales_amt*p1_sales_per*p1_selling_price/temp));
		
		// Cost of sales at SP1
		if(existing_sales_per)	sp1_cost_sales_at_sp1 = float(round2(branch_summary_cost_of_goods_sold*p1_sales_per/existing_sales_per));
		
		// Profit for SP1
		sp1_profit_for_sp1 = float(round2(sp1_sales_at_sp1 - sp1_cost_sales_at_sp1));
		
		// Total sales plus SP1
		sp1_total_sales_plus_sp1 = float(round2(sp1_sales_at_sp1+branch_summary_sales_amt));
		
		// Total Cost plus SP1
		sp1_total_cost_plus_sp1 = float(round2(branch_summary_cost_of_goods_sold - sp1_total_sales_plus_sp1));
		
		// Net Profit after SP1
		sp1_net_profit_sp1 = float(round2(sp1_total_sales_plus_sp1 - sp1_total_cost_plus_sp1));
		
		// Profit % after SP1
		if(sp1_total_sales_plus_sp1)	sp1_profit_per_sp1 = float(round2(sp1_net_profit_sp1/sp1_total_sales_plus_sp1));
		
		// SP2
		// Sales at SP2
		temp = branch_last_selling*hq_cost;
		if(temp)	sp2_sales_at_sp2 = float(round2(branch_summary_sales_amt*p2_sales_per*p2_selling_price/temp)); 
		
		// Cost of sales at SP2
		if(existing_sales_per)	sp2_cost_sales_sp2 = float(round2(branch_summary_cost_of_goods_sold*p2_sales_per/existing_sales_per));
		
		// Profit for SP2
		sp2_profit_sp2 = float(round2(sp2_sales_at_sp2 - sp2_cost_sales_sp2));
		
		// Total Sales (SP1+SP2)
		sp2_total_sales_sp1sp2 = float(round2(sp1_total_sales_plus_sp1 + sp2_sales_at_sp2));
		
		// Total COST (SP1+SP2)
		sp2_total_cost_sp1sp2 = float(round2(sp1_total_cost_plus_sp1 + sp2_cost_sales_sp2));
		
		// Net profit (SP1+SP2)
		sp2_net_profit_sp1sp2 = float(round2(sp2_total_sales_sp1sp2 - sp2_total_cost_sp1sp2));
		
		// Profit (%) (SP1+SP2)
		if(sp2_total_sales_sp1sp2)	sp2_profit_per_sp1sp2 = float(round2(sp2_net_profit_sp1sp2/sp2_total_sales_sp1sp2));
		
		// Proposal 1 
		proposal_1_profit = sp1_profit_per_sp1;
		
		// Proposal 2
		proposal_2_profit = sp2_profit_per_sp1sp2;
		
		// update all values
		// Proposal
		$('p1_sales_per-'+bid+'-'+sid).update(round2(p1_sales_per));
		$('p2_sales_qty-'+bid+'-'+sid).update(float(round(p2_sales_qty,global_qty_decimal_points)));
		$('p2_sales_per-'+bid+'-'+sid).update(round2(p2_sales_per));
		
		// SP1
		$('sp1_sales_at_sp1-'+bid+'-'+sid).update(round2(sp1_sales_at_sp1));
		$('sp1_cost_sales_at_sp1-'+bid+'-'+sid).update(round2(sp1_cost_sales_at_sp1));
		$('sp1_profit_for_sp1-'+bid+'-'+sid).update(round2(sp1_profit_for_sp1));
		$('sp1_total_sales_plus_sp1-'+bid+'-'+sid).update(round2(sp1_total_sales_plus_sp1));
		$('sp1_total_cost_plus_sp1-'+bid+'-'+sid).update(round2(sp1_total_cost_plus_sp1));
		$('sp1_net_profit_sp1-'+bid+'-'+sid).update(round2(sp1_net_profit_sp1));
		$('sp1_profit_per_sp1-'+bid+'-'+sid).update(round2(sp1_profit_per_sp1));
		
		// SP2
		$('sp2_sales_at_sp2-'+bid+'-'+sid).update(round2(sp2_sales_at_sp2));
		$('sp2_cost_sales_sp2-'+bid+'-'+sid).update(round2(sp2_cost_sales_sp2));
		$('sp2_profit_sp2-'+bid+'-'+sid).update(round2(sp2_profit_sp2));
		$('sp2_total_sales_sp1sp2-'+bid+'-'+sid).update(round2(sp2_total_sales_sp1sp2));
		$('sp2_total_cost_sp1sp2-'+bid+'-'+sid).update(round2(sp2_total_cost_sp1sp2));
		$('sp2_net_profit_sp1sp2-'+bid+'-'+sid).update(round2(sp2_net_profit_sp1sp2));
		$('sp2_profit_per_sp1sp2-'+bid+'-'+sid).update(round2(sp2_profit_per_sp1sp2));
		
		// Proposal 1
		$('proposal_1_profit-'+bid+'-'+sid).update(round2(proposal_1_profit));
		
		// Proposal 2
		$('proposal_2_profit-'+bid+'-'+sid).update(round2(proposal_2_profit));
	}
};

function toggle_branch_all(ele){
 	var c = ele.checked;
	$$('#f_a input.chx_branch').each(function(inp){
		$(inp).checked = c;
	});
}

function reload_sku_group(){
	var dept_id = document.f_a['dept_id'].value;
	var user_id = document.f_a['user_id'].value;

	$('td_sku_group').update(_loading_);
	new Ajax.Updater('td_sku_group', phpself+'?a=load_sku_monitoring_group', {
		parameters: {
            dept_id: dept_id,
            user_id: user_id
		},
		onComplete: function(e){

		}
	});
}

function submit_form(t){
	document.f_a['show_type'].value = t;

	if(BRANCH_CODE=='HQ'){
	    var branch_checked = false;
		var all_chx = $$('#f_a input.chx_branch');
		for(var i=0; i<all_chx.length; i++){
			if(all_chx[i].checked){
	            branch_checked = true;
	            break;
			}
		}
		if(!branch_checked){
			alert('Please select at least one branch.');return;
		}
	}

	if(!document.f_a['sku_monitoring_group_id'])   return;

	var sg = document.f_a['sku_monitoring_group_id'].value;
	if(!sg){
		alert('Please select SKU Monitoring Group.');return;
	}
	document.f_a.submit();
}

function sku_monitoring_group_changed(){
	var sel = document.f_a['sku_monitoring_group_id'];
    var sg = sel.value;
    for(var i=0; i<sel.length; i++){
		if(sel.options[i].value==sg){
			document.f_a['date_from'].value = $(sel.options[i]).readAttribute('date_from');
			break;
		}
	}
	if(document.f_a['date_from'].value=='0000-00-00')   document.f_a['date_from'].value = '';
}

function toggle_branch_sku(sid, ele){
	var tbd = $('tbody_branch_sku,'+sid);

	if($(tbd).getStyle('display')=='none'){
        $(tbd).show();
        $(ele).src = '/ui/collapse.gif';
	}
	else{
	    $(tbd).hide();
        $(ele).src = '/ui/expand.gif';
	}
}

function toggle_month_col(col_class, max_col_count, img){
	var parent_table = img.parentNode.parentNode.parentNode.parentNode.parentNode;
 	
	var type = 'hide';
	if(img.src.indexOf('expand')>=0)	type = 'show';
    
    $(parent_table).getElementsBySelector('[col_class="'+col_class+'"]').each(function(ele){
	    if(type=='hide'){
            if($(ele).hasClassName('td_month_space')){
				$(ele).show();
			}else{
	            $(ele).hide();
			}
		}else{
            if($(ele).hasClassName('td_month_space')){
				$(ele).hide();
			}else{
	            $(ele).show();
			}
		}
	});

	if(type=='hide'){
		img.src = 'ui/expand.gif';
		$('th_mon_'+col_class).colSpan = 1;
	}else{
        img.src = 'ui/collapse.gif';
		$('th_mon_'+col_class).colSpan = max_col_count;
	}
	
}

function toggle_item_row(sid, img){
	var type = 'hide';
	if(img.src.indexOf('expand')>=0)    type = 'show';

	if(type=='show'){
        $('tbody_item_branch_list_'+sid).show();
        $(img).src = 'ui/collapse.gif';
	}else{
        $('tbody_item_branch_list_'+sid).hide();
        $(img).src = 'ui/expand.gif';
	}

}

function view_batch_sku_details(){
	var smg_value = document.f_a['sku_monitoring_group_id'].value;
	if(!smg_value){
		alert('Please select a batch first.');
		return;
	}
	
	curtain(true);
	center_div($('div_group_batch_items_details').show());
	$('div_group_batch_items_details_content').update(_loading_);
	
	new Ajax.Updater('div_group_batch_items_details_content', phpself, {
		parameters:{
			'a': 'ajax_load_batch_items_details',
			'smg_value': smg_value
		}
	});
}

function refresh_monthly_col(){
	// get all monthly input
	var all_inp = document.f_m['show_month[]'];
	
	var selected_ym_str = '';
	for(var i=0; i<all_inp.length; i++){    // loop all input
	    var col_class = 'col_'+all_inp[i].value;
	    var need_change = false;
	    var change_to = 'hide';
	    
	    // check header first
		if(all_inp[i].checked){ // show
            if($('th_mon_'+col_class).style.display=='none'){
                need_change = true;
                change_to = 'show';
			}
		}else{  // hide
            if($('th_mon_'+col_class).style.display==''){
                need_change = true;
                change_to = 'hide';
            }
		}
		
		if(need_change){
            // hide/show header
			$$('table.report_table thead th[col_class="'+col_class+'"]').invoke(change_to);
			// hide/show items
			$$('table.report_table tr.tr_item_row td[col_class="'+col_class+'"]').invoke(change_to);
		}
	}
	
	// loop all monthly column
	/*$$('table.report_table th[col_class]', 'table.report_table td[col_class]').each(function(ele, i){
		var col_class = $(ele).readAttribute('col_class');
		var str_to_chk = '['+col_class+']';
		if(selected_ym_str.indexOf(str_to_chk)>=0)  $(ele).show();
		else    $(ele).hide();
	});*/
	//alert($$('table.report_table th[col_class]').length);
	//alert($$('table.report_table td[col_class]').length);
}
{/literal}
</script>
{/if}

<div id="div_edit_popup" style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;">
	<input id="inp_edit_text" size="5" style="text-align:right;" />
</div>

<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}
    <!-- SKU Monitroing Group items Details -->
	<div id="div_group_batch_items_details" class="curtain_popup" style="position:absolute;z-index:10001;width:450px;height:350px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
		<div id="div_group_batch_items_details_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">SKU Monitoring Group Batch Item List</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
		</div>
		<div id="div_group_batch_items_details_content" style="padding:2px;"></div>
	</div>
	<!-- End of SKU Monitroing Group items Details -->

	<form method="post" name="f_a" id="f_a" class="form" onsubmit="return false;">
	    <input type="hidden" name="load_report" value="1" />
	    <input type="hidden" name="show_type" value="report" />
		{if $BRANCH_CODE eq 'HQ'}
			<b>Branch</b>
			<input type="checkbox" onChange="toggle_branch_all(this);" /> All
			{foreach from=$branches key=bid item=b}
			    <input type="checkbox" class="chx_branch" name="branch_id[]" value="{$bid}" {if is_array($smarty.request.branch_id)}{if in_array($bid, $smarty.request.branch_id)}checked {/if}{/if} /> {$b.code}
			{/foreach}
		{/if}
		<fieldset style="width:400px;">
		    <legend><b>SKU Group Selection</b></legend>
		    <table width="100%">
		    <tr>
		    	<td><b>Filter by Department</b></td>
				<td>
					<select name="dept_id" onChange="reload_sku_group();">
				        <option value="">-- All --</option>
				        {foreach from=$dept key=did item=r}
				            <option value="{$did}" {if $smarty.request.dept_id eq $did}selected {/if}>{$r.description}</option>
				        {/foreach}
				    </select>
		        </td>
		    </tr>
		    <tr>
		    <td><b>Filter by User</b></td>
		    <td>
			    <select name="user_id" onChange="reload_sku_group();">
			        <option value="">-- All --</option>
			        {foreach from=$user_list key=uid item=r}
			            <option value="{$uid}" {if $smarty.request.user_id eq $uid}selected {/if}>{$r.u}</option>
			        {/foreach}
			    </select>
		    </td>
		    </tr>
            <tr>
				<td><b>SKU Monitoring Group</b></td>
				<td id="td_sku_group" nowrap>
					{include file='report.sku_monitoring2.sku_monitoring_group.tpl'}
				</td>
			</tr>
			<tr>
			    <td><b>Date From</b></td>
				<td>
					<input size="10" type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly />
				</td>
			</tr>
		    </table>
		</fieldset>
		<p>
		    <button class="btn btn-primary" onClick="submit_form('report');">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL}
				<button class="btn btn-primary" onClick="submit_form('excel');">{#OUTPUT_EXCEL#}</button>
			{/if}
			<input type="checkbox" name="use_report_server" value="1" {if $smarty.request.use_report_server or !$smarty.request.load_report}checked {/if} />
			Use report server &nbsp;
			<input type="checkbox" name="only_show_proposal_sku" value="1" {if $smarty.request.only_show_proposal_sku}checked {/if} /> Only show got proposal sku
		</p>
	</form>
{/if}


{if !$data}
	{if $smarty.request.load_report}
		<p>-- No Data --</p>
	{/if}
{else}
	<h1>{$report_title}</h1>
	
	{if !$no_header_footer}
	<form name="f_m" onSubmit="return false;">
		<fieldset style="width:700px;">
		    <legend><b>Available Month</b></legend>
			<ul>
				{foreach from=$date_label key=date_key item=dk}
				    <li style="float:left;margin-right:20px;width:100px;"><input type="checkbox" name="show_month[]" value="{$dk.y}_{$dk.m}" /> {$months[$dk.m]}-{$dk.y}</li>
				{/foreach}
			</ul>
			<br style="clear:both;" />
			<input type="button" value="Refresh" onClick="refresh_monthly_col();" />
		</fieldset>
	</form>
	<br />
	{/if}
	
    {assign var=cols_for_a_month value=35}
    {capture assign=report_header}
	<table width="100%" class="report_table">
	    <thead>
         <tr>
	        <th colspan="8">Particulars</th>
	        {foreach from=$date_label key=date_key item=dk}
	            {assign var=col_class value="col_`$dk.y`_`$dk.m`"}
	            <th colspan="{$cols_for_a_month}" class="{cycle values="d1,d2"}" id="th_mon_{$col_class}" style="display:none;" col_class="{$col_class}">
					{*
					<div style="float:left;">
						<img src="ui/expand.gif" align="absmiddle" title="show/Hide" border="0" onClick="toggle_month_col('{$col_class}','{$cols_for_a_month}', this);" class="clickable" />
					</div>
					*}
					{$months[$dk.m]}-{$dk.y}
				</th>
	        {/foreach}
	        <th colspan="{$cols_for_a_month}" class="{cycle values="d1,d2"} col_total">Total</th>
	        <th colspan="8" class="branch_summary">Branch Summary</th>
	        <th colspan="14" class="summary">Summary</th>
	        <th colspan="14" class="col_proposal">Proposal</th>
	        <th colspan="7" class="col_sp1">SP 1</th>
	        <th colspan="7" class="col_sp2">SP 2</th>
	    </tr>
	    <tr>
	        <th rowspan="3">ARMS Code</th>
	        <th rowspan="3">Description</th>
	        <th colspan="3" class="group_proposed">Group Proposed</th>
	        <th colspan="3" class="branch_last">Branch</th>
	        {foreach from=$date_label key=date_key item=dk}
	            {assign var=col_class value="col_`$dk.y`_`$dk.m`"}
	            <th colspan="3" class="in_qty" col_class="{$col_class}" style="display:none;">In (Qty)</th>
	            <th colspan="9" col_class="{$col_class}" style="display:none;">Out (Qty)</th>
	            <th colspan="2" col_class="{$col_class}" style="display:none;">Stock Take (Qty)</th>
	            <th colspan="2" col_class="{$col_class}" style="display:none;">Balance Stock (Qty)</th>
	            <th colspan="4" col_class="{$col_class}" style="display:none;">Total Sales (Amt)</th>
	            <th colspan="2" col_class="{$col_class}" style="display:none;">Holding Cost</th>
	            <th colspan="4" col_class="{$col_class}" style="display:none;">Actual Profit</th>
	            <th colspan="2" col_class="{$col_class}" style="display:none;">Average Per Unit (Amt)</th>
	            <th colspan="7" class="variances" col_class="{$col_class}" style="display:none;">Variance (Qty & Amt)</th>
	        {/foreach}
	        <th colspan="3" class="col_total in_qty">In (Qty)</th>
            <th colspan="9" class="col_total">Out (Qty)</th>
            <th colspan="2" class="col_total">Stock Take (Qty)</th>
            <th colspan="2" class="col_total">Balance Stock (Qty)</th>
            <th colspan="4" class="col_total">Total Sales (Amt)</th>
            <th colspan="2" class="col_total">Holding Cost</th>
            <th colspan="4" class="col_total">Actual Profit</th>
            <th colspan="2" class="col_total">Average Per Unit (Amt)</th>
            <th colspan="7" class="col_total variances">Variance (Qty & Amt)</th>
            <th colspan="3" class="branch_summary">Amount</th>
            <th colspan="2" class="branch_summary">Profit</th>
            <th rowspan="2" class="branch_summary">Closing Stock (Amt)</th>
            <th rowspan="2" class="branch_summary">% sold on QTY</th>
            <!-- th rowspan="2" class="branch_summary">Profit & Sales Weighted average</th -->
            <th rowspan="2" class="branch_summary">Sales - Purchase (Amt)</th>

			<!-- Summary-->
			<th rowspan="3" class="summary">Selling</th>
			<th colspan="2" class="summary">Cost</th>
			<th colspan="2" class="summary">Profit ({$config.arms_currency.symbol})</th>
			<th colspan="2" class="summary">Profit (%)</th>
			<th rowspan="2" class="summary">Holding<br />Cost</th>
			<th colspan="2" class="summary">Total Cost</th>
			<th colspan="2" class="summary">Actual Profit ({$config.arms_currency.symbol})</th>
			<th colspan="2" class="summary">Actual Profit (%)</th>
			
			<!-- Proposal -->
			<th rowspan="2" class="col_proposal">Existing Branch profit (%)</th>
            <th rowspan="2" class="col_proposal">Existing Group profit (%)</th>
            <th colspan="2" class="col_proposal">Existing Sales</th>
            <th rowspan="2" class="col_proposal">Balance quantity</th>
            <th rowspan="2" class="col_proposal">Last selling price</th>
            <th colspan="4" class="col_proposal">Proposal 1</th>
            <th colspan="4" class="col_proposal">Proposal 2</th>
            
            <!-- SP 1 -->
            <th class="col_sp1" rowspan="2">Sales at SP1</th>
            <th class="col_sp1" rowspan="2">Cost of sales at SP1</th>
            <th class="col_sp1" rowspan="2">Profit for SP1</th>
            <th class="col_sp1" rowspan="2">Total Sales (plus SP1)</th>
            <th class="col_sp1" rowspan="2">Total COST (plus SP1)</th>
            <th class="col_sp1" rowspan="2">Net profit after SP1</th>
            <th class="col_sp1" rowspan="2">Profit (%) after SP1</th>
            
            
            <!-- SP 2 -->
            <th class="col_sp2" rowspan="2">Sales at SP2</th>
            <th class="col_sp2" rowspan="2">Cost of sales at SP2</th>
            <th class="col_sp2" rowspan="2">Profit for SP2</th>
            <th class="col_sp2" rowspan="2">Total Sales (SP1+SP2)</th>
            <th class="col_sp2" rowspan="2">Total COST (SP1+SP2)</th>
            <th class="col_sp2" rowspan="2">Net profit (SP1+SP2)</th>
            <th class="col_sp2" rowspan="2">Profit (%) (SP1+SP2)</th>
	    </tr>
	    <tr>
	        <th rowspan="2" class="group_proposed">Selling</th>
	        <th rowspan="2" class="group_proposed">HQ Cost</th>
	        <th rowspan="2" class="group_proposed">Proposed GP%</th>
	        <th rowspan="2" class="branch_last">Last Selling</th>
	        <th rowspan="2" class="branch_last">Branch Cost</th>
	        <th rowspan="2" class="branch_last">GP%</th>
	        {foreach from=$date_label key=date_key item=dk}
	            {assign var=col_class value="col_`$dk.y`_`$dk.m`"}
	            <th rowspan="2" class="in_qty" col_class="{$col_class}" style="display:none;">Opening</th>
	            <th rowspan="2" class="in_qty" col_class="{$col_class}" style="display:none;">In</th>
	            <th rowspan="2" class="in_qty" col_class="{$col_class}" style="display:none;">Total</th>
	            <th colspan="2" class="sales_qty" col_class="{$col_class}" style="display:none;">Sales</th>
	            <th colspan="3" class="ibt_qty" col_class="{$col_class}" style="display:none;">IBT</th>
	            <th colspan="2" class="gra_qty" col_class="{$col_class}" style="display:none;">GRA</th>
	            <th colspan="2" class="total_out_qty" col_class="{$col_class}" style="display:none;">Total Out</th>
	            <th rowspan="2" class="adj_qty" col_class="{$col_class}" style="display:none;">Adj (+/-)</th>
	            <th rowspan="2" class="adj_qty" col_class="{$col_class}" style="display:none;">%</th>
	            <th rowspan="2" class="balance_qty" col_class="{$col_class}" style="display:none;">Qty</th>
	            <th rowspan="2" class="balance_qty" col_class="{$col_class}" style="display:none;">%</th>
	            <th rowspan="2" class="sales_amt" col_class="{$col_class}" style="display:none;">Cost</th>
	            <th rowspan="2" class="sales_amt" col_class="{$col_class}" style="display:none;">Selling</th>
	            <th colspan="2" class="sales_amt" col_class="{$col_class}" style="display:none;">Profit</th>
	            <th rowspan="2" class="holding_cost" col_class="{$col_class}" style="display:none;">Amt</th>
	            <th rowspan="2" class="holding_cost" col_class="{$col_class}" style="display:none;">% On Profit</th>
	            <th colspan="2" class="actual_profit" col_class="{$col_class}" style="display:none;">Branch</th>
	            <th colspan="2" class="actual_profit" col_class="{$col_class}" style="display:none;">Group</th>
	            <th rowspan="2" class="avg" col_class="{$col_class}" style="display:none;">Cost</th>
	            <th rowspan="2" class="avg" col_class="{$col_class}" style="display:none;">Selling</th>
	            <th colspan="2" class="markup" col_class="{$col_class}" style="display:none;">Mark Up</th>
	            <th colspan="2" class="markdown" col_class="{$col_class}" style="display:none;">Mark Down</th>
	            <th colspan="2" class="discount" col_class="{$col_class}" style="display:none;">Discount</th>
	            <th rowspan="2" class="variance_amt" col_class="{$col_class}" style="display:none;">Amount (+/-)</th>
			{/foreach}
			<th rowspan="2" class="col_total in_qty">Opening</th>
            <th rowspan="2" class="col_total in_qty">In</th>
            <th rowspan="2" class="col_total in_qty">Total</th>
            <th colspan="2" class="col_total sales_qty">Sales</th>
            <th colspan="3" class="col_total ibt_qty">IBT</th>
            <th colspan="2" class="col_total gra_qty">GRA</th>
            <th colspan="2" class="col_total total_out_qty">Total Out</th>
            <th rowspan="2" class="col_total adj_qty">Adj (+/-)</th>
            <th rowspan="2" class="col_total adj_qty">%</th>
            <th rowspan="2" class="col_total balance_qty">Qty</th>
            <th rowspan="2" class="col_total balance_qty">%</th>
            <th rowspan="2" class="col_total sales_amt">Cost</th>
            <th rowspan="2" class="col_total sales_amt">Selling</th>
            <th colspan="2" class="col_total sales_amt">Profit</th>
            <th rowspan="2" class="col_total holding_cost">Amt</th>
            <th rowspan="2" class="col_total holding_cost">% On Profit</th>
            <th colspan="2" class="col_total actual_profit">Branch</th>
            <th colspan="2" class="col_total actual_profit">Group</th>
            <th rowspan="2" class="col_total avg">Cost</th>
            <th rowspan="2" class="col_total avg">Selling</th>
            <th colspan="2" class="col_total markup">Mark Up</th>
            <th colspan="2" class="col_total markdown">Mark Down</th>
            <th colspan="2" class="col_total discount">Mark Discount</th>
            <th rowspan="2" class="col_total variance_amt">Amount (+/-)</th>
            <th class="branch_summary">Opening Balance & In Stock</th>
            <th class="branch_summary">Sales</th>
            <th class="branch_summary">Cost of Goods Sold </th>
            <th class="branch_summary">Amount</th>
            <th class="branch_summary">%</th>
            
            <!-- Summary -->
            <th class="summary">Branch</th>
            <th class="summary">HQ</th>
            <th class="summary">Branch</th>
            <th class="summary">HQ</th>
            <th class="summary">Branch</th>
            <th class="summary">HQ</th>
            <th class="summary">Branch</th>
            <th class="summary">HQ</th>
            <th class="summary">Branch</th>
            <th class="summary">HQ</th>
            <th class="summary">Branch</th>
            <th class="summary">HQ</th>
            
            <!-- Proposal -->
            <th class="col_proposal">%</th>
            <th class="col_proposal">Pcs</th>
            <th class="col_proposal">Sales Qty</th>
            <th class="col_proposal">Sales %</th>
            <th class="col_proposal">Selling Price</th>
            <th class="col_proposal">Profit %</th>
            <th class="col_proposal">Sales Qty</th>
            <th class="col_proposal">Sales %</th>
            <th class="col_proposal">Selling Price</th>
            <th class="col_proposal">Profit %</th>
	    </tr>
	    <tr>
	        {foreach from=$date_label key=date_key item=dk}
	            {assign var=col_class value="col_`$dk.y`_`$dk.m`"}
	            <th class="sales_qty" col_class="{$col_class}" style="display:none;">Qty</th>
	            <th class="sales_qty" col_class="{$col_class}" style="display:none;">%</th>
	            <th class="ibt_qty" col_class="{$col_class}" style="display:none;">Qty</th>
	            <th class="ibt_qty" col_class="{$col_class}" style="display:none;">Adj</th>
	            <th class="ibt_qty" col_class="{$col_class}" style="display:none;">%</th>
	            <th class="gra_qty" col_class="{$col_class}" style="display:none;">Qty</th>
	            <th class="gra_qty" col_class="{$col_class}" style="display:none;">%</th>
	            <th class="total_out_qty" col_class="{$col_class}" style="display:none;">Qty</th>
	            <th class="total_out_qty" col_class="{$col_class}" style="display:none;">%</th>
	            <th class="sales_amt" col_class="{$col_class}" style="display:none;">Amt</th>
	            <th class="sales_amt" col_class="{$col_class}" style="display:none;">%</th>
	            <th class="actual_profit" col_class="{$col_class}" style="display:none;">Amt</th>
	            <th class="actual_profit" col_class="{$col_class}" style="display:none;">%</th>
	            <th class="actual_profit" col_class="{$col_class}" style="display:none;">Amt</th>
	            <th class="actual_profit" col_class="{$col_class}" style="display:none;">%</th>
	            <th class="markup" col_class="{$col_class}" style="display:none;">Qty</th>
	            <th class="markup" col_class="{$col_class}" style="display:none;">Amt</th>
	            <th class="markdown" col_class="{$col_class}" style="display:none;">Qty</th>
	            <th class="markdown" col_class="{$col_class}" style="display:none;">Amt</th>
	            <th class="discount" col_class="{$col_class}" style="display:none;">Qty</th>
	            <th class="discount" col_class="{$col_class}" style="display:none;">Amt</th>
			{/foreach}
			<th class="col_total sales_qty">Qty</th>
            <th class="col_total sales_qty">%</th>
            <th class="col_total ibt_qty">Qty</th>
            <th class="col_total ibt_qty">Adj</th>
            <th class="col_total ibt_qty">%</th>
            <th class="col_total gra_qty">Qty</th>
            <th class="col_total gra_qty">%</th>
            <th class="col_total total_out_qty">Qty</th>
            <th class="col_total total_out_qty">%</th>
            <th class="col_total sales_amt">Amt</th>
            <th class="col_total sales_amt">%</th>
            <th class="col_total actual_profit">Amt</th>
            <th class="col_total actual_profit">%</th>
            <th class="col_total actual_profit">Amt</th>
            <th class="col_total actual_profit">%</th>
            <th class="col_total markup">Qty</th>
            <th class="col_total markup">Amt</th>
            <th class="col_total markdown">Qty</th>
            <th class="col_total markdown">Amt</th>
            <th class="col_total discount">Qty</th>
            <th class="col_total discount">Amt</th>
            <th class="branch_summary">A</th>
            <th class="branch_summary">B</th>
            <th class="branch_summary">C</th>
            <th class="branch_summary">D = B-C</th>
            <th class="branch_summary">E = (D/B)*100</th>
            <th class="branch_summary">F = A-C</th>
            <th class="branch_summary">G</th>
            <!-- th class="branch_summary">H = E*G</th -->
            <th class="branch_summary">I</th>
            
            <!-- Summary -->
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            <th class="summary">&nbsp;</th>
            
            <!-- Proposal -->
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">J = D/A</th>
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">&nbsp;</th>
            <th class="col_proposal">&nbsp;</th>
            
            <!-- SP1 -->
            <th class="col_sp1">&nbsp;</th>
            <th class="col_sp1">&nbsp;</th>
            <th class="col_sp1">&nbsp;</th>
            <th class="col_sp1">&nbsp;</th>
            <th class="col_sp1">&nbsp;</th>
            <th class="col_sp1">&nbsp;</th>
            <th class="col_sp1">&nbsp;</th>
            
            <!-- SP2 -->
            <th class="col_sp2">&nbsp;</th>
            <th class="col_sp2">&nbsp;</th>
            <th class="col_sp2">&nbsp;</th>
            <th class="col_sp2">&nbsp;</th>
            <th class="col_sp2">&nbsp;</th>
            <th class="col_sp2">&nbsp;</th>
            <th class="col_sp2">&nbsp;</th>
	    </tr>
		</thead>
		{/capture}
	    
	    <!-- Total -->
	    {assign var=total_holding_cost value=0}
	    
	    
	    {foreach from=$sku_info key=sid item=sku_item name=f_sku}
	        {if $smarty.foreach.f_sku.index%$rows_per_table eq 0}
	            {$report_header}
			{/if}
	        <tr class="tr_item_row">
	            <td nowrap>
	                {if $BRANCH_CODE eq 'HQ'}
	                 	{if !$no_header_footer} 
							<img src="ui/expand.gif" align="absmiddle" title="Show/Hide" class="clickable" id="img_item_row_{$sid}" onClick="toggle_item_row('{$sid}', this);" />
						{/if}
	                {/if}
					{$sku_item.sku_item_code}
				</td>
	            <td nowrap>{$sku_item.description}
	                {if $sku_item.max_report_date|date_format:'%Y-%m-%d' ne $smarty.now|date_format:'%Y-%m-%d'}
	                    {assign var=max_report_time value=$sku_item.max_report_date|strtotime}
	                    {assign var=max_report_time value=$max_report_time+86400}
						<span class="small" style="color:blue;">(Carry to {$max_report_time|date_format:'%b'} {$max_report_time|date_format:'%Y'})</span>
					{/if}
				</td>

	            <!-- Group Proposed -->
	            <td class="r group_proposed" title="Group Proposed Selling">{$sku_item.selling_price|number_format:2}</td>
	            <td class="r group_proposed" title="HQ Cost" id="hq_cost-{$sid}">{$sku_item.hq_cost|number_format:$config.global_cost_decimal_points}</td>
	            {if $sku_item.selling_price}
	                {assign var=gp_per value=$sku_item.selling_price-$sku_item.hq_cost}
	                {assign var=gp_per value=$gp_per/$sku_item.selling_price*100}
	            {else}
	                {assign var=gp_per value=0}
	            {/if}
	            {assign var=group_gp_per value=$gp_per}
	            <td class="r group_proposed" title="Group Proposed GP%">{$group_gp_per|number_format:2}</td>

	            <!-- Branch Last -->
	            <td class="r branch_last" title="Branch Last Selling">{$total.$sid.branch_last.selling|number_format:2}</td>
	            <td class="r branch_last" title="Branch Last Cost">{$total.$sid.branch_last.cost|number_format:$config.global_cost_decimal_points}</td>
	            {if $total.$sid.branch_last.selling}
	                {assign var=gp_per value=$total.$sid.branch_last.selling-$total.$sid.branch_last.cost}
	                {assign var=gp_per value=$gp_per/$total.$sid.branch_last.selling*100}
	            {else}
	                {assign var=gp_per value=0}
	            {/if}
	            <td class="r branch_last" title="Branch GP%">{$gp_per|number_format:2}</td>

				{assign var=row_holding_cost value=0}
	            {foreach from=$date_label key=date_key item=dk}
	                {capture assign=tooltips_prefix}All branches \ {$sku_item.sku_item_code} \ {$sku_item.description|escape} \ {$dk.y}-{$months[$dk.m]} \{/capture}
	                {if $date_key>$sku_item.max_report_date_key}
	                    {assign var=repeated_sku value='repeated_sku'}
					{else}
					    {assign var=repeated_sku value=''}
					{/if}
					
	            	{assign var=col_class value="col_`$dk.y`_`$dk.m`"}
	            	{assign var=dk_m value=$dk.m}
	            	
	            	<!-- In (Qty) -->
	            	<td class="r in_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Opening Qty">{$total.$sid.opening.$date_key.qty|qty_nf}</td>
	            	<td class="r in_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} In Qty">{$total.$sid.grn.$date_key.qty|qty_nf}</td>
	            	{assign var=total_in value=$total.$sid.opening.$date_key.qty+$total.$sid.grn.$date_key.qty}
	            	<td class="r in_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Opening Qty">{$total_in|qty_nf}</td>

	            	<!-- Sales (Qty) -->
	            	<td class="r sales_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Sales Qty">{$total.$sid.pos.$date_key.qty|qty_nf}</td>
	            	{if $total_in}
	            	    {assign var=sales_per value=$total.$sid.pos.$date_key.qty/$total_in*100}
	            	{else}
	            	    {assign var=sales_per value=0}
	            	{/if}
	            	<td class="r sales_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Sales %">{$sales_per|number_format:2}</td>

	            	<!-- IBT (Qty) -->
	            	<td class="r ibt_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} DO Qty">{$total.$sid.do.$date_key.qty|qty_nf}</td>
	            	<td class="r ibt_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} IBT Adj Qty">{$total.$sid.ibt_adj.$date_key.qty|qty_nf}</td>
	            	{if $total_in}
	            	    {assign var=ibt_adj_per value=$total.$sid.ibt_adj.$date_key.qty/$total_in*100}
	            	{else}
	            	    {assign var=ibt_adj_per value=0}
	            	{/if}
	            	<td class="r ibt_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} IBT Adj %">{$ibt_adj_per|number_format:2}</td>

	            	<!-- GRA (Qty) -->
	            	<td class="r gra_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} GRA Qty">{$total.$sid.gra.$date_key.qty|qty_nf}</td>
	            	{if $total_in}
	            	    {assign var=gra_per value=$total.$sid.gra.$date_key.qty/$total_in*100}
	            	{else}
	            	    {assign var=gra_per value=0}
	            	{/if}
	            	<td class="r gra_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} GRA %">{$gra_per|num_format:2}</td>

	            	<!-- Total Out (Qty) -->
	            	{assign var=total_out value=$total.$sid.pos.$date_key.qty+$total.$sid.do.$date_key.qty+$total.$sid.gra.$date_key.qty}
	            	<td class="r total_out_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Out Qty">{$total_out|qty_nf}</td>
	            	{if $total_in}
	            	    {assign var=total_out_per value=$total_out/$total_in*100}
	            	{else}
	            	    {assign var=total_out_per value=0}
	            	{/if}
	            	<td class="r total_out_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Out %">{$total_out_per|number_format:2}</td>

	            	<!-- Adj (Qty) -->
	            	{assign var=adj value=$total.$sid.adj.$date_key.qty+$total.$sid.stock_check_adj.$date_key.qty}
	            	<td class="r adj_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Stock Take & Adj Qty">{$adj|qty_nf}</td>
	            	{if $total_in}
	            	    {assign var=adj_per value=$adj/$total_in*100}
	            	{else}
	            	    {assign var=adj_per value=0}
	            	{/if}
	            	<td class="r adj_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Stock Take & Adj %">{$adj_per|number_format:2}</td>

	            	<!-- Balance (Qty) -->
					{assign var=balance_qty value=$total_in-$total_out}
					<td class="r balance_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Closing Stock Qty">{$balance_qty|qty_nf}</td>
					{if $total_in}
	            	    {assign var=balance_per value=$balance_qty/$total_in*100}
	            	{else}
	            	    {assign var=balance_per value=0}
	            	{/if}
	            	<td class="r balance_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Closing Stock %">{$balance_per|number_format:2}</td>

	            	<!-- Total Sales (Amt) -->
	            	<td class="r sales_amt {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Selling Cost">{$total.$sid.pos.$date_key.cost|number_format:2}</td>
	            	<td class="r sales_amt {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Selling Amount">{$total.$sid.pos.$date_key.amt|number_format:2}</td>
	            	{assign var=profit_amt value=$total.$sid.pos.$date_key.amt-$total.$sid.pos.$date_key.cost}
	            	<td class="r sales_amt {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Profit Amount">{$profit_amt|number_format:2}</td>
	            	{if $total.$sid.pos.$date_key.amt}
	            	    {assign var=pos_per value=$profit_amt/$total.$sid.pos.$date_key.amt*100}
	            	{else}
	            	    {assign var=pos_per value=0}
	            	{/if}
	            	<td class="r sales_amt {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Profit %">{$pos_per|number_format:2}</td>

	            	<!-- Holding Cost -->
	            	{assign var=holding_cost value=$balance_qty*$sku_item.hq_cost/100}
	            	{assign var=bank_interest_rate value=$dk.interest_rate/12*$dk.mth_no}
	            	{assign var=holding_cost value=$holding_cost*$bank_interest_rate+1}
	            	{assign var=row_holding_cost value=$row_holding_cost+$holding_cost}
	            	
	            	{array_assign array_name="total_hoding_cost" key1=$dk.m value=$total_hoding_cost.$dk_m+$holding_cost}
	            	<td class="r holding_cost {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Holding Cost">{$holding_cost|number_format:2}</td>
	            	
	            	{if $profit_amt}
                        {assign var=holding_cost_per value=$holding_cost/$profit_amt*100}
	            	{else}
	            	    {assign var=holding_cost_per value=0}
	            	{/if}
	            	<td class="r holding_cost {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Holding Cost %">{$holding_cost_per|number_format:2}</td>

	            	<!-- Actual Profit -->
	            	{assign var=actual_profit value=$profit_amt-$holding_cost}
	            	<td class="r actual_profit {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit Amount">{$actual_profit|number_format:2}</td>
	            	{if $total.$sid.pos.$date_key.amt}
	            	    {assign var=actual_profit_per1 value=$actual_profit/$total.$sid.pos.$date_key.amt*100}
	            	{else}
	            	    {assign var=actual_profit_per1 value=0}
	            	{/if}
	            	<td class="r actual_profit {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit %">{$actual_profit_per1|number_format:2}</td>
					{if $total.$sid.pos.$date_key.amt}
	            	    {assign var=actual_profit_per2 value=$total_out*$sku_item.hq_cost}
	            	    {assign var=actual_profit_per2 value=$total.$sid.pos.$date_key.amt-$actual_profit_per2-$holding_cost}
	            	    {assign var=actual_profit_per2 value=$actual_profit_per2/$total.$sid.pos.$date_key.amt*100}
	            	{else}
	            	    {assign var=actual_profit_per2 value=0}
	            	{/if}
	            	
	            	{assign var=actual_profit_grp_amt value=$total.$sid.pos.$date_key.qty*$sku_item.hq_cost}
	            	{assign var=actual_profit_grp_amt value=$total.$sid.pos.$date_key.amt-$actual_profit_grp_amt-$holding_cost}
	            	
	            	{array_assign array_name="total_actual_profit_grp_amt" key1=$dk.m value=$total_actual_profit_grp_amt.$dk_m+$actual_profit_grp_amt}
	            	<td class="r actual_profit {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit Group Amount">{$actual_profit_grp_amt|number_format:2}</td>
	            	
	            	<td class="r actual_profit {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit Group %">{$actual_profit_per2|number_format:2}</td>

	            	<!-- Average Per Unit (Amt) -->
	            	{if $total.$sid.pos.$date_key.qty}
	            	    {assign var=avg_cost value=$total.$sid.pos.$date_key.cost/$total.$sid.pos.$date_key.qty}
	            	    {assign var=avg_amt value=$total.$sid.pos.$date_key.amt/$total.$sid.pos.$date_key.qty}
	            	{else}
	            	    {assign var=avg_cost value=0}
	            	    {assign var=avg_amt value=0}
	            	{/if}
	            	<td class="r avg {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Average Selling Cost">{$avg_cost|number_format:2}</td>
	            	<td class="r avg {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Average Selling Amount">{$avg_amt|number_format:2}</td>

	            	<!-- Variance (Qty & Amt) -->
	            	<!-- Mark up -->
	            	<td class="r markup {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Up Qty">{$total.$sid.variances.$date_key.markup_qty|num_format:2}</td>
	            	<td class="r markup {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Up Amount">{$total.$sid.variances.$date_key.markup_amt|number_format:2}</td>
	            	<!-- Mark down -->
	            	<td class="r markdown {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Down Qty">{$total.$sid.variances.$date_key.markdown_qty|num_format:2}</td>
	            	<td class="r markdown {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Down Amount">{$total.$sid.variances.$date_key.markdown_amt|number_format:2}</td>
	            	<!-- discount -->
	            	<td class="r discount {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Discount Qty">{$total.$sid.variances.$date_key.disc_qty|num_format:2}</td>
	            	<td class="r discount {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Discount Amount">{$total.$sid.variances.$date_key.disc_amt|number_format:2}</td>
	            	<!-- Variance Amt -->
	            	{assign var=variance_amt value=$total.$sid.variances.$date_key.markup_amt-$total.$sid.variances.$date_key.markdown_amt-$total.$sid.variances.$date_key.disc_amt}
	            	<td class="r variance_amt {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Variances Amount">{$variance_amt|number_format:2}</td>
	            {/foreach}
	            <!-- Total -->
	            {capture assign=tooltips_prefix}All branches \ {$sku_item.sku_item_code} \ {$sku_item.description|escape} \ Total \{/capture}
	            
            	<!-- In (Qty) -->
            	<td class="r in_qty col_total" title="{$tooltips_prefix} Opening Qty">{$total.$sid.opening.total.qty|qty_nf}</td>
            	<td class="r in_qty col_total" title="{$tooltips_prefix} In Qty">{$total.$sid.grn.total.qty|qty_nf}</td>
            	{assign var=total_in value=$total.$sid.opening.total.qty+$total.$sid.grn.total.qty}
            	<td class="r in_qty col_total" title="{$tooltips_prefix} Total In">{$total_in|qty_nf}</td>

            	<!-- Sales (Qty) -->
            	<td class="r sales_qty col_total"  title="{$tooltips_prefix} Sales Qty">{$total.$sid.pos.total.qty|qty_nf}</td>
            	{if $total_in}
            	    {assign var=sales_per value=$total.$sid.pos.total.qty/$total_in*100}
            	{else}
            	    {assign var=sales_per value=0}
            	{/if}
            	<td class="r sales_qty col_total"  title="{$tooltips_prefix} Sales %">{$sales_per|number_format:2}</td>

            	<!-- IBT (Qty) -->
            	<td class="r ibt_qty col_total"  title="{$tooltips_prefix} DO Qty">{$total.$sid.do.total.qty|qty_nf}</td>
            	<td class="r ibt_qty col_total"  title="{$tooltips_prefix} IBT Adj Qty">{$total.$sid.ibt_adj.total.qty|qty_nf}</td>
            	{if $total_in}
            	    {assign var=ibt_adj_per value=$total.$sid.ibt_adj.total.qty/$total_in*100}
            	{else}
            	    {assign var=ibt_adj_per value=0}
            	{/if}
            	<td class="r ibt_qty col_total" title="{$tooltips_prefix} IBT Adj %">{$ibt_adj_per|number_format:2}</td>

            	<!-- GRA (Qty) -->
            	<td class="r gra_qty col_total" title="{$tooltips_prefix} GRA Qty">{$total.$sid.gra.total.qty|qty_nf}</td>
            	{if $total_in}
            	    {assign var=gra_per value=$total.$sid.gra.total.qty/$total_in*100}
            	{else}
            	    {assign var=gra_per value=0}
            	{/if}
            	<td class="r gra_qty col_total" title="{$tooltips_prefix} GRA %">{$gra_per|num_format:2}</td>

            	<!-- Total Out (Qty) -->
            	{assign var=total_out value=$total.$sid.pos.total.qty+$total.$sid.do.total.qty+$total.$sid.gra.total.qty}
            	<td class="r total_out_qty col_total" title="{$tooltips_prefix} Total Out Qty">{$total_out|qty_nf}</td>
            	{if $total_in}
            	    {assign var=total_out_per value=$total_out/$total_in*100}
            	{else}
            	    {assign var=total_out_per value=0}
            	{/if}
            	<td class="r total_out_qty col_total" title="{$tooltips_prefix} Total Out %">{$total_out_per|number_format:2}</td>

            	<!-- Adj (Qty) -->
            	{assign var=adj value=$total.$sid.adj.total.qty+$total.$sid.stock_check_adj.total.qty}
            	<td class="r adj_qty col_total" title="{$tooltips_prefix} Stock Take & Adj Qty">{$adj|qty_nf}</td>
            	{if $total_in}
            	    {assign var=adj_per value=$adj/$total_in*100}
            	{else}
            	    {assign var=adj_per value=0}
            	{/if}
            	<td class="r adj_qty col_total"  title="{$tooltips_prefix} Stock Take & Adj %">{$adj_per|number_format:2}</td>

            	<!-- Balance (Qty) -->
				{assign var=balance_qty value=$total_in-$total_out}
				<td class="r balance_qty col_total" title="{$tooltips_prefix} Closing Stock Qty">{$balance_qty|qty_nf}</td>
				{if $total_in}
            	    {assign var=balance_per value=$balance_qty/$total_in*100}
            	{else}
            	    {assign var=balance_per value=0}
            	{/if}
            	<td class="r balance_qty col_total" title="{$tooltips_prefix} Closing Stock %">{$balance_per|number_format:2}</td>

            	<!-- Total Sales (Amt) -->
            	<td class="r sales_amt col_total" title="{$tooltips_prefix} Total Selling Cost">{$total.$sid.pos.total.cost|number_format:2}</td>
            	<td class="r sales_amt col_total" title="{$tooltips_prefix} Total Selling Amount">{$total.$sid.pos.total.amt|number_format:2}</td>
            	{assign var=profit_amt value=$total.$sid.pos.total.amt-$total.$sid.pos.total.cost}
            	<td class="r sales_amt col_total" title="{$tooltips_prefix} Profit Amount">{$profit_amt|number_format:2}</td>
            	{if $total.$sid.pos.total.amt}
            	    {assign var=pos_per value=$profit_amt/$total.$sid.pos.total.amt*100}
            	{else}
            	    {assign var=pos_per value=0}
            	{/if}
            	<td class="r sales_amt col_total" title="{$tooltips_prefix} Profit %">{$pos_per|number_format:2}</td>

            	<!-- Holding Cost -->
            	{assign var=holding_cost value=$row_holding_cost}
            	<td class="r holding_cost col_total"  title="{$tooltips_prefix} Holding Cost">{$holding_cost|number_format:2}</td>
            	{if $profit_amt}
                    {assign var=holding_cost_per value=$holding_cost/$profit_amt*100}
            	{else}
            	    {assign var=holding_cost_per value=0}
            	{/if}
            	<td class="r holding_cost col_total" title="{$tooltips_prefix} Holding Cost %">{$holding_cost_per|number_format:2}</td>

            	<!-- Actual Profit -->
            	{assign var=actual_profit value=$profit_amt-$holding_cost}
            	<td class="r actual_profit col_total" title="{$tooltips_prefix} Actual Profit Amount">{$actual_profit|number_format:2}</td>
            	{if $total.$sid.pos.total.amt}
            	    {assign var=actual_profit_per1 value=$actual_profit/$total.$sid.pos.total.amt*100}
            	{else}
            	    {assign var=actual_profit_per1 value=0}
            	{/if}
            	<td class="r actual_profit col_total" title="{$tooltips_prefix} Actual Profit %">{$actual_profit_per1|number_format:2}</td>
				{if $total.$sid.pos.total.amt}
            	    {assign var=actual_profit_per2 value=$total_out*$sku_item.hq_cost}
            	    {assign var=actual_profit_per2 value=$total.$sid.pos.total.amt-$actual_profit_per2-$holding_cost}
            	    {assign var=actual_profit_per2 value=$actual_profit_per2/$total.$sid.pos.total.amt*100}
            	{else}
            	    {assign var=actual_profit_per2 value=0}
            	{/if}
            
				{assign var=actual_profit_grp_amt value=$total.$sid.pos.total.qty*$sku_item.hq_cost}
            	{assign var=actual_profit_grp_amt value=$total.$sid.pos.total.amt-$actual_profit_grp_amt-$holding_cost}
            	<td class="r actual_profit col_total" title="{$tooltips_prefix} Actual Profit Group Amount">{$actual_profit_grp_amt|number_format:2}</td>
	            	
            	<td class="r actual_profit col_total"  title="{$tooltips_prefix} Actual Profit Group %">{$actual_profit_per2|number_format:2}</td>

            	<!-- Average Per Unit (Amt) -->
            	{if $total.$sid.pos.total.qty}
            	    {assign var=avg_cost value=$total.$sid.pos.total.cost/$total.$sid.pos.total.qty}
            	    {assign var=avg_amt value=$total.$sid.pos.total.amt/$total.$sid.pos.total.qty}
            	{else}
            	    {assign var=avg_cost value=0}
            	    {assign var=avg_amt value=0}
            	{/if}
            	<td class="r avg col_total" title="{$tooltips_prefix} Average Selling Cost">{$avg_cost|number_format:2}</td>
            	<td class="r avg col_total"  title="{$tooltips_prefix} Average Selling Amount">{$avg_amt|number_format:2}</td>

            	<!-- Variance (Qty & Amt) -->
            	<!-- Mark up -->
            	<td class="r markup col_total"  title="{$tooltips_prefix} Mark Up Qty">{$total.$sid.variances.total.markup_qty|qty_nf}</td>
            	<td class="r markup col_total"  title="{$tooltips_prefix} Mark Up Amount">{$total.$sid.variances.total.markup_amt|number_format:2}</td>
            	<!-- Mark down -->
            	<td class="r markdown col_total"  title="{$tooltips_prefix} Mark Down Qty">{$total.$sid.variances.total.markdown_qty|qty_nf}</td>
            	<td class="r markdown col_total"  title="{$tooltips_prefix} Mark Down Amount">{$total.$sid.variances.total.markdown_amt|number_format:2}</td>
            	<!-- discount -->
            	<td class="r discount col_total"  title="{$tooltips_prefix} Discount Qty">{$total.$sid.variances.total.disc_qty|qty_nf}</td>
            	<td class="r discount col_total"  title="{$tooltips_prefix} Discount Amount">{$total.$sid.variances.total.disc_amt|number_format:2}</td>
            	<!-- Variance Amt -->
            	{assign var=variance_amt value=$total.$sid.variances.total.markup_amt-$total.$sid.variances.total.markdown_amt-$total.$sid.variances.total.disc_amt}
            	<td class="r variance_amt col_total"  title="{$tooltips_prefix} Variances Amount">{$variance_amt|number_format:2}</td>

            	<!-- Branch Summary -->
            	<!-- Opening Balance & In Stock -->
            	{assign var=open_bal value=$total_in*$total.$sid.branch_last.cost}
            	{assign var=total_open_bal value=$total_open_bal+$open_bal}
            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Opening Balance & In Stock">{$open_bal|number_format:2}</td>
            	<!-- Sales amount -->
            	{assign var=sales_amt value=$total.$sid.pos.total.amt}
            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Sales amount">{$sales_amt|number_format:2}</td>
				<!-- Sales Cost -->
            	{assign var=sales_cost value=$total.$sid.pos.total.cost}
            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Sales Cost">{$sales_cost|number_format:2}</td>
            	<!-- Profit Amount -->
            	{assign var=profit_amt value=$sales_amt-$sales_cost}
            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Profit Amount">{$profit_amt|number_format:2}</td>
            	<!-- Profit Percent -->
            	{if $sales_amt}
            		{assign var=profit_per value=$profit_amt/$sales_amt*100}
            	{else}
            	    {assign var=profit_per value=0}
            	{/if}
            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Profit Percent">{$profit_per|number_format:2}</td>
            	<!-- Closing Stock (Amt) -->
            	{assign var=close_bal value=$open_bal-$sales_cost}
            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Closing Stock (Amt)">{$close_bal|number_format:2}</td>
            	<!-- % sold on QTY -->
            	{if $total_in}
            		{assign var=sold_qty_per value=$total.$sid.pos.total.qty/$total_in*100}
            	{else}
            	    {assign var=sold_qty_per value=0}
            	{/if}
            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: % sold on QTY">{$sold_qty_per|number_format:2}</td>
            	<!-- Profit & Sales Weighted average -->
            	{assign var=weight_avg value=$profit_per*$sold_qty_per}
            	<!-- td class="r branch_summary">{$weight_avg|number_format:2}</td -->
            	
            	<!-- Sales - Purchase (Amt) -->
            	{assign var=earn value=$total_in*$total.$sid.branch_last.cost}
            	{assign var=earn value=$total.$sid.pos.total.amt-$earn}
            	
            	{assign var=total_earn value=$total_earn+$earn}
            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Sales - Purchase (Amt)">{$earn|number_format:2}</td>

				<!-- Summary -->
				<!-- Selling -->
				{assign var=summary_selling value=$total.$sid.pos.total.amt}
				<td class="r summary" title="{$tooltips_prefix} Summary: Selling">{$summary_selling|number_format:2}</td>
				
				<!-- Cost Branch -->
				{assign var=summary_cost_branch value=$total.$sid.pos.total.cost}
				<td class="r summary" title="{$tooltips_prefix} Summary: Cost Branch">{$summary_cost_branch|number_format:2}</td>
				
				<!-- Cost HQ -->
				{assign var=summary_cost_hq value=$total.$sid.pos.total.qty*$sku_item.hq_cost}
				{array_assign array_name="total_summary_cost_hq" key1=$dk.m value=$total_summary_cost_hq.$dk_m+$summary_cost_hq}
				<td class="r summary" title="{$tooltips_prefix} Summary: Cost HQ">{$summary_cost_hq|number_format:2}</td>
				
				<!-- Profit ({$config.arms_currency.symbol}) Branch -->
				{assign var=summary_profit_rm_branch value=$summary_selling-$summary_cost_branch}
				<td class="r summary" title="{$tooltips_prefix} Summary: Profit ({$config.arms_currency.symbol}) Branch">{$summary_profit_rm_branch|number_format:2}</td>
				
				<!-- Profit ({$config.arms_currency.symbol}) HQ-->
				{assign var=summary_profit_rm_hq value=$summary_selling-$summary_cost_hq}
				<td class="r summary" title="{$tooltips_prefix} Summary: Profit ({$config.arms_currency.symbol}) HQ">{$summary_profit_rm_hq|number_format:2}</td>
				
				<!-- Profit % Branch-->
				{assign var=summary_profit_per_branch value=0}
				{if $summary_selling}
					{assign var=summary_profit_per_branch value=$summary_cost_branch/$summary_selling*100}
				{/if}
				{assign var=temp_val value=100}
				{assign var=summary_profit_per_branch value=$temp_val-$summary_profit_per_branch}
				<td class="r summary" title="{$tooltips_prefix} Summary: Profit % Branch">{$summary_profit_per_branch|number_format:2}</td>
				
				<!-- Profit % HQ -->
				{assign var=summary_profit_per_hq value=0}
				{if $summary_selling}
					{assign var=summary_profit_per_hq value=$summary_cost_hq/$summary_selling*100}
				{/if}
				{assign var=temp_val value=100}
				{assign var=summary_profit_per_hq value=$temp_val-$summary_profit_per_hq}
				<td class="r summary" title="{$tooltips_prefix} Summary: Profit % HQ">{$summary_profit_per_hq|number_format:2}</td>
				
				<!-- Holding Cost -->
				{assign var=summary_holding_cost value=$holding_cost}
				<td class="r summary" title="{$tooltips_prefix} Summary: Holding Cost">{$summary_holding_cost|number_format:2}</td>
				
				<!-- Total Cost Branch -->
				{assign var=summary_total_cost_branch value=$summary_cost_branch+$summary_holding_cost}
				<td class="r summary" title="{$tooltips_prefix} Summary: Total Cost Branch">{$summary_total_cost_branch|number_format:2}</td>
				
				<!-- Total Cost HQ -->
				{assign var=summary_total_cost_hq value=$summary_cost_hq+$summary_holding_cost}
				<td class="r summary" title="{$tooltips_prefix} Summary: Total Cost HQ">{$summary_total_cost_hq|number_format:2}</td>
				
				<!-- Actual Profit ({$config.arms_currency.symbol}) Branch -->
				{assign var=summary_act_profit_rm_branch value=$summary_selling-$summary_total_cost_branch}
				<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit ({$config.arms_currency.symbol}) Branch">{$summary_act_profit_rm_branch|number_format:2}</td>
				
				<!-- Actual Profit ({$config.arms_currency.symbol}) HQ -->
				{assign var=summary_act_profit_rm_hq value=$summary_selling-$summary_total_cost_hq}
				<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit ({$config.arms_currency.symbol}) HQ">{$summary_act_profit_rm_hq|number_format:2}</td>
				
				<!-- Actual Profit % Branch -->
				{assign var=summary_act_profit_per_branch value=0}
				{if $summary_selling}
					{assign var=summary_act_profit_per_branch value=$summary_total_cost_branch/$summary_selling*100}
				{/if}
				{assign var=temp_val value=100}
				{assign var=summary_act_profit_per_branch value=$temp_val-$summary_act_profit_per_branch}
				<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit % Branch">{$summary_act_profit_per_branch|number_format:2}</td>
				
				<!-- Actual Profit % HQ -->
				{assign var=summary_act_profit_per_hq value=0}
				{if $summary_selling}
					{assign var=summary_act_profit_per_hq value=$summary_total_cost_hq/$summary_selling*100}
				{/if}
				{assign var=temp_val value=100}
				{assign var=summary_act_profit_per_hq value=$temp_val-$summary_act_profit_per_hq}
				<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit % HQ">{$summary_act_profit_per_hq|number_format:2}</td>
				
                <!-- Proposal -->
            	<td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
            	<td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
                <td class="col_proposal">&nbsp;</td>
                
                <!-- SP1 -->
                <td class="col_sp1">&nbsp;</td>
                <td class="col_sp1">&nbsp;</td>
                <td class="col_sp1">&nbsp;</td>
                <td class="col_sp1">&nbsp;</td>
                <td class="col_sp1">&nbsp;</td>
                <td class="col_sp1">&nbsp;</td>
                <td class="col_sp1">&nbsp;</td>
                
                <!-- SP2 -->
                <td class="col_sp2">&nbsp;</td>
                <td class="col_sp2">&nbsp;</td>
                <td class="col_sp2">&nbsp;</td>
                <td class="col_sp2">&nbsp;</td>
                <td class="col_sp2">&nbsp;</td>
                <td class="col_sp2">&nbsp;</td>
                <td class="col_sp2">&nbsp;</td>
	        </tr>

	        <!-- Branches List -->
	        {if $BRANCH_CODE eq 'HQ'}
	            <tbody id="tbody_item_branch_list_{$sid}" style="display:none;">
	            {foreach from=$branch_id_arr item=bid}
	                <tr class="tr_item_row">
	                    <td colspan="5">{if $bid eq 'ibt'}IBT{else}{$branches.$bid.code}{/if}</td>

	                    <!-- Branch Last -->
			            <td class="r branch_last" title="Branch Last Selling" id="branch_last_selling-{$bid}-{$sid}">{$data.$bid.$sid.branch_last.selling|number_format:2}</td>
			            <td class="r branch_last" title="Branch Last Cost">{$data.$bid.$sid.branch_last.cost|number_format:$config.global_cost_decimal_points}</td>
			            {if $data.$bid.$sid.branch_last.selling}
			                {assign var=gp_per value=$data.$bid.$sid.branch_last.selling-$data.$bid.$sid.branch_last.cost}
			                {assign var=gp_per value=$gp_per/$data.$bid.$sid.branch_last.selling*100}
			            {else}
			                {assign var=gp_per value=0}
			            {/if}
			            <td class="r branch_last" title="Branch GP %">{$gp_per|number_format:2}</td>

                        {assign var=row_holding_cost value=0}
			            {foreach from=$date_label key=date_key item=dk}
			                {capture assign=tooltips_prefix}{if $bid eq 'ibt'}IBT{else}{$branches.$bid.code}{/if} \ {$sku_item.sku_item_code} \ {$sku_item.description|escape} \ {$dk.y}-{$months[$dk.m]} \{/capture}
			                
			                {if $date_key>$sku_item.max_report_date_key}
			                    {assign var=repeated_sku value='repeated_sku'}
							{else}
							    {assign var=repeated_sku value=''}
							{/if}
							
			            	{assign var=col_class value="col_`$dk.y`_`$dk.m`"}
			            	<!-- In (Qty) -->
			            	<td class="r in_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Opening Qty">{$data.$bid.$sid.opening.$date_key.qty|qty_nf}</td>
			            	<td class="r in_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} In Qty">{$data.$bid.$sid.grn.$date_key.qty|qty_nf}</td>
			            	{assign var=total_in value=$data.$bid.$sid.opening.$date_key.qty+$data.$bid.$sid.grn.$date_key.qty}
			            	<td class="r in_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total In">{$total_in|qty_nf}</td>

			            	<!-- Sales (Qty) -->
			            	<td class="r sales_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Sales Qty">{$data.$bid.$sid.pos.$date_key.qty|qty_nf}</td>
			            	{if $total_in}
			            	    {assign var=sales_per value=$data.$bid.$sid.pos.$date_key.qty/$total_in*100}
			            	{else}
			            	    {assign var=sales_per value=0}
			            	{/if}
			            	<td class="r sales_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Sales %">{$sales_per|number_format:2}</td>

			            	<!-- IBT (Qty) -->
			            	<td class="r ibt_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} DO Qty">{$data.$bid.$sid.do.$date_key.qty|qty_nf}</td>
			            	<td class="r ibt_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} IBT Adj Qty">{$data.$bid.$sid.ibt_adj.$date_key.qty|qty_nf}</td>
			            	{if $total_in}
			            	    {assign var=ibt_adj_per value=$data.$bid.$sid.ibt_adj.$date_key.qty/$total_in*100}
			            	{else}
			            	    {assign var=ibt_adj_per value=0}
			            	{/if}
			            	<td class="r ibt_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} IBT Adj %">{$ibt_adj_per|number_format:2}</td>

			            	<!-- GRA (Qty) -->
			            	<td class="r gra_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} GRA Qty">{$data.$bid.$sid.gra.$date_key.qty|qty_nf}</td>
			            	{if $total_in}
			            	    {assign var=gra_per value=$data.$bid.$sid.gra.$date_key.qty/$total_in*100}
			            	{else}
			            	    {assign var=gra_per value=0}
			            	{/if}
			            	<td class="r gra_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} GRA %">{$gra_per|num_format:2}</td>

			            	<!-- Total Out (Qty) -->
			            	{assign var=total_out value=$data.$bid.$sid.pos.$date_key.qty+$data.$bid.$sid.do.$date_key.qty+$data.$bid.$sid.gra.$date_key.qty}
			            	<td class="r total_out_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Out Qty">{$total_out|qty_nf}</td>
			            	{if $total_in}
			            	    {assign var=total_out_per value=$total_out/$total_in*100}
			            	{else}
			            	    {assign var=total_out_per value=0}
			            	{/if}
			            	<td class="r total_out_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Out %">{$total_out_per|number_format:2}</td>

			            	<!-- Adj (Qty) -->
			            	{assign var=adj value=$data.$bid.$sid.adj.$date_key.qty+$data.$bid.$sid.stock_check_adj.$date_key.qty}
			            	<td class="r adj_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Stock Take & Adj Qty">{$adj|qty_nf}</td>
			            	{if $total_in}
			            	    {assign var=adj_per value=$adj/$total_in*100}
			            	{else}
			            	    {assign var=adj_per value=0}
			            	{/if}
			            	<td class="r adj_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Stock Take & Adj %">{$adj_per|number_format:2}</td>

			            	<!-- Balance (Qty) -->
							{assign var=balance_qty value=$total_in-$total_out}
							<td class="r balance_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Closing Stock Qty">{$balance_qty|qty_nf}</td>
							{if $total_in}
			            	    {assign var=balance_per value=$balance_qty/$total_in*100}
			            	{else}
			            	    {assign var=balance_per value=0}
			            	{/if}
			            	<td class="r balance_qty {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Closing Stock %">{$balance_per|number_format:2}</td>

			            	<!-- Total Sales (Amt) -->
			            	<td class="r sales_amt {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Selling Cost">{$data.$bid.$sid.pos.$date_key.cost|number_format:2}</td>
			            	<td class="r sales_amt {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Selling Amount">{$data.$bid.$sid.pos.$date_key.amt|number_format:2}</td>
			            	{assign var=profit_amt value=$data.$bid.$sid.pos.$date_key.amt-$data.$bid.$sid.pos.$date_key.cost}
			            	<td class="r sales_amt {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Profit Amount">{$profit_amt|number_format:2}</td>
			            	{if $data.$bid.$sid.pos.$date_key.amt}
			            	    {assign var=pos_per value=$profit_amt/$data.$bid.$sid.pos.$date_key.amt*100}
			            	{else}
			            	    {assign var=pos_per value=0}
			            	{/if}
			            	<td class="r sales_amt {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Profit %">{$pos_per|number_format:2}</td>

			            	<!-- Holding Cost -->
			            	{assign var=holding_cost value=$balance_qty*$sku_item.hq_cost/100}
			            	{assign var=bank_interest_rate value=$dk.interest_rate/12*$dk.mth_no}
			            	{assign var=holding_cost value=$holding_cost*$bank_interest_rate}
			            	{assign var=row_holding_cost value=$row_holding_cost+$holding_cost}
			            	<td class="r holding_cost {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Holding Cost">{$holding_cost|number_format:2}</td>
			            	{if $profit_amt}
		                        {assign var=holding_cost_per value=$holding_cost/$profit_amt*100}
			            	{else}
			            	    {assign var=holding_cost_per value=0}
			            	{/if}
			            	<td class="r holding_cost {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Holding Cost %">{$holding_cost_per|number_format:2}</td>

			            	<!-- Actual Profit -->
			            	{assign var=actual_profit value=$profit_amt-$holding_cost}
			            	<td class="r actual_profit {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit Amount">{$actual_profit|number_format:2}</td>
			            	{if $data.$bid.$sid.pos.$date_key.amt}
			            	    {assign var=actual_profit_per1 value=$actual_profit/$data.$bid.$sid.pos.$date_key.amt*100}
			            	{else}
			            	    {assign var=actual_profit_per1 value=0}
			            	{/if}
			            	<td class="r actual_profit {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit %">{$actual_profit_per1|number_format:2}</td>
							{if $data.$bid.$sid.pos.$date_key.amt}
			            	    {assign var=actual_profit_per2 value=$total_out*$sku_item.hq_cost}
			            	    {assign var=actual_profit_per2 value=$data.$bid.$sid.pos.$date_key.amt-$actual_profit_per2-$holding_cost}
			            	    {assign var=actual_profit_per2 value=$actual_profit_per2/$data.$bid.$sid.pos.$date_key.amt*100}
			            	{else}
			            	    {assign var=actual_profit_per2 value=0}
			            	{/if}
			            	
			            	{assign var=actual_profit_grp_amt value=$data.$bid.$sid.pos.$date_key.qty*$sku_item.hq_cost}
			            	{assign var=actual_profit_grp_amt value=$data.$bid.$sid.pos.$date_key.amt-$actual_profit_grp_amt-$holding_cost}
			            	<td class="r actual_profit {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit Group Amount">{$actual_profit_grp_amt|number_format:2}</td>
	            	
			            	<td class="r actual_profit {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit Group %">{$actual_profit_per2|number_format:2}</td>

			            	<!-- Average Per Unit (Amt) -->
			            	{if $data.$bid.$sid.pos.$date_key.qty}
			            	    {assign var=avg_cost value=$data.$bid.$sid.pos.$date_key.cost/$data.$bid.$sid.pos.$date_key.qty}
			            	    {assign var=avg_amt value=$data.$bid.$sid.pos.$date_key.amt/$data.$bid.$sid.pos.$date_key.qty}
			            	{else}
			            	    {assign var=avg_cost value=0}
			            	    {assign var=avg_amt value=0}
			            	{/if}
			            	<td class="r avg {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Average Selling Cost">{$avg_cost|number_format:2}</td>
			            	<td class="r avg {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Average Selling Amount">{$avg_amt|number_format:2}</td>

			            	<!-- Variance (Qty & Amt) -->
			            	<!-- Mark up -->
			            	<td class="r markup {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Up Qty">{$data.$bid.$sid.variances.$date_key.markup_qty|qty_nf}</td>
			            	<td class="r markup {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Up Amount">{$data.$bid.$sid.variances.$date_key.markup_amt|number_format:2}</td>
			            	<!-- Mark down -->
			            	<td class="r markdown {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Down Qty">{$data.$bid.$sid.variances.$date_key.markdown_qty|qty_nf}</td>
			            	<td class="r markdown {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Down Amount">{$data.$bid.$sid.variances.$date_key.markdown_amt|number_format:2}</td>
			            	<!-- discount -->
			            	<td class="r discount {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Discount Qty">{$data.$bid.$sid.variances.$date_key.disc_qty|qty_nf}</td>
			            	<td class="r discount {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Discount Amount">{$data.$bid.$sid.variances.$date_key.disc_amt|number_format:2}</td>
			            	<!-- Variance Amt -->
			            	{assign var=variance_amt value=$data.$bid.$sid.variances.$date_key.markup_amt-$data.$bid.$sid.variances.$date_key.markdown_amt-$data.$bid.$sid.variances.$date_key.disc_amt}
			            	<td class="r variance_amt {$repeated_sku}" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Variances Amount">{$variance_amt|number_format:2}</td>
						{/foreach}
						<!-- Total -->
						{capture assign=tooltips_prefix}{if $bid eq 'ibt'}IBT{else}{$branches.$bid.code}{/if} \ {$sku_item.sku_item_code} \ {$sku_item.description|escape} \ Total \{/capture}
						
		            	<!-- In (Qty) -->
		            	<td class="r in_qty col_total" title="{$tooltips_prefix} Opening Qty">{$data.$bid.$sid.opening.total.qty|qty_nf}</td>
		            	<td class="r in_qty col_total" title="{$tooltips_prefix} In Qty">{$data.$bid.$sid.grn.total.qty|qty_nf}</td>
		            	{assign var=total_in value=$data.$bid.$sid.opening.total.qty+$data.$bid.$sid.grn.total.qty}
		            	<td class="r in_qty col_total" title="{$tooltips_prefix} Total In" id="total_in-{$bid}-{$sid}">{$total_in|qty_nf}</td>

		            	<!-- Sales (Qty) -->
		            	<td class="r sales_qty col_total" title="{$tooltips_prefix} Sales Qty">{$data.$bid.$sid.pos.total.qty|qty_nf}</td>
		            	{if $total_in}
		            	    {assign var=sales_per value=$data.$bid.$sid.pos.total.qty/$total_in*100}
		            	{else}
		            	    {assign var=sales_per value=0}
		            	{/if}
		            	<td class="r sales_qty col_total" title="{$tooltips_prefix} Sales %">{$sales_per|number_format:2}</td>

		            	<!-- IBT (Qty) -->
		            	<td class="r ibt_qty col_total" title="{$tooltips_prefix} DO Qty">{$data.$bid.$sid.do.total.qty|qty_nf}</td>
		            	<td class="r ibt_qty col_total" title="{$tooltips_prefix} IBT Adj Qty">{$data.$bid.$sid.ibt_adj.total.qty|qty_nf}</td>
		            	{if $total_in}
		            	    {assign var=ibt_adj_per value=$data.$bid.$sid.ibt_adj.total.qty/$total_in*100}
		            	{else}
		            	    {assign var=ibt_adj_per value=0}
		            	{/if}
		            	<td class="r ibt_qty col_total" title="{$tooltips_prefix} IBT Adj %">{$ibt_adj_per|number_format:2}</td>

		            	<!-- GRA (Qty) -->
		            	<td class="r gra_qty col_total" title="{$tooltips_prefix} GRA Qty">{$data.$bid.$sid.gra.total.qty|qty_nf}</td>
		            	{if $total_in}
		            	    {assign var=gra_per value=$data.$bid.$sid.gra.total.qty/$total_in*100}
		            	{else}
		            	    {assign var=gra_per value=0}
		            	{/if}
		            	<td class="r gra_qty col_total" title="{$tooltips_prefix} GRA %">{$gra_per|num_format:2}</td>

		            	<!-- Total Out (Qty) -->
		            	{assign var=total_out value=$data.$bid.$sid.pos.total.qty+$data.$bid.$sid.do.total.qty+$data.$bid.$sid.gra.total.qty}
		            	<td class="r total_out_qty col_total" title="{$tooltips_prefix} Total Out Qty">{$total_out|qty_nf}</td>
		            	{if $total_in}
		            	    {assign var=total_out_per value=$total_out/$total_in*100}
		            	{else}
		            	    {assign var=total_out_per value=0}
		            	{/if}
		            	<td class="r total_out_qty col_total" title="{$tooltips_prefix} Total Out %">{$total_out_per|number_format:2}</td>

		            	<!-- Adj (Qty) -->
				    	{assign var=adj value=$data.$bid.$sid.adj.total.qty+$data.$bid.$sid.stock_check_adj.total.qty}
				    	<td class="r adj_qty col_total" title="{$tooltips_prefix} Stock Take & Adj Qty">{$adj|qty_nf}</td>
				    	{if $total_in}
				    	    {assign var=adj_per value=$adj/$total_in*100}
				    	{else}
				    	    {assign var=adj_per value=0}
				    	{/if}
				    	<td class="r adj_qty col_total" title="{$tooltips_prefix} Stock Take & Adj %">{$adj_per|number_format:2}</td>

				    	<!-- Balance (Qty) -->
						{assign var=balance_qty value=$total_in-$total_out}
						<td class="r balance_qty col_total" title="{$tooltips_prefix} Closing Stock Qty">{$balance_qty|qty_nf}</td>
						{if $total_in}
				    	    {assign var=balance_per value=$balance_qty/$total_in*100}
				    	{else}
				    	    {assign var=balance_per value=0}
				    	{/if}
				    	<td class="r balance_qty col_total" title="{$tooltips_prefix} Closing Stock %">{$balance_per|number_format:2}</td>

				    	<!-- Total Sales (Amt) -->
		            	<td class="r sales_amt col_total" title="{$tooltips_prefix} Total Selling Cost">{$data.$bid.$sid.pos.total.cost|number_format:2}</td>
		            	<td class="r sales_amt col_total" title="{$tooltips_prefix} Total Selling Amount">{$data.$bid.$sid.pos.total.amt|number_format:2}</td>
		            	{assign var=profit_amt value=$data.$bid.$sid.pos.total.amt-$data.$bid.$sid.pos.total.cost}
		            	<td class="r sales_amt col_total" title="{$tooltips_prefix} Profit Amount">{$profit_amt|number_format:2}</td>
		            	{if $data.$bid.$sid.pos.total.amt}
		            	    {assign var=pos_per value=$profit_amt/$data.$bid.$sid.pos.total.amt*100}
		            	{else}
		            	    {assign var=pos_per value=0}
		            	{/if}
		            	<td class="r sales_amt col_total" title="{$tooltips_prefix} Profit %">{$pos_per|number_format:2}</td>

		            	<!-- Holding Cost -->
		            	{assign var=holding_cost value=$row_holding_cost}
		            	<td class="r holding_cost col_total" title="{$tooltips_prefix} Holding Cost">{$holding_cost|number_format:2}</td>
		            	{if $profit_amt}
		                    {assign var=holding_cost_per value=$holding_cost/$profit_amt*100}
		            	{else}
		            	    {assign var=holding_cost_per value=0}
		            	{/if}
		            	<td class="r holding_cost col_total" title="{$tooltips_prefix} Holding Cost %">{$holding_cost_per|number_format:2}</td>

		            	<!-- Actual Profit -->
		            	{assign var=actual_profit value=$profit_amt-$holding_cost}
		            	<td class="r actual_profit col_total" title="{$tooltips_prefix} Actual Profit Amount">{$actual_profit|number_format:2}</td>
		            	{if $data.$bid.$sid.pos.total.amt}
		            	    {assign var=actual_profit_per1 value=$actual_profit/$data.$bid.$sid.pos.total.amt*100}
		            	{else}
		            	    {assign var=actual_profit_per1 value=0}
		            	{/if}
		            	<td class="r actual_profit col_total" title="{$tooltips_prefix} Actual Profit %">{$actual_profit_per1|number_format:2}</td>
						{if $data.$bid.$sid.pos.total.amt}
		            	    {assign var=actual_profit_per2 value=$total_out*$sku_item.hq_cost}
		            	    {assign var=actual_profit_per2 value=$data.$bid.$sid.pos.total.amt-$actual_profit_per2-$holding_cost}
		            	    {assign var=actual_profit_per2 value=$actual_profit_per2/$data.$bid.$sid.pos.total.amt*100}
		            	{else}
		            	    {assign var=actual_profit_per2 value=0}
		            	{/if}
		            	
		            	{assign var=actual_profit_grp_amt value=$data.$bid.$sid.pos.total.qty*$sku_item.hq_cost}
		            	{assign var=actual_profit_grp_amt value=$data.$bid.$sid.pos.total.amt-$actual_profit_grp_amt-$holding_cost}
		            	<td class="r actual_profit col_total" title="{$tooltips_prefix} Actual Profit Group Amount">{$actual_profit_grp_amt|number_format:2}</td>
            	
		            	<td class="r actual_profit col_total" title="{$tooltips_prefix} Actual Profit Group %">{$actual_profit_per2|number_format:2}</td>

		            	<!-- Average Per Unit (Amt) -->
		            	{if $data.$bid.$sid.pos.total.qty}
		            	    {assign var=avg_cost value=$data.$bid.$sid.pos.total.cost/$data.$bid.$sid.pos.total.qty}
		            	    {assign var=avg_amt value=$data.$bid.$sid.pos.total.amt/$data.$bid.$sid.pos.total.qty}
		            	{else}
		            	    {assign var=avg_cost value=0}
		            	    {assign var=avg_amt value=0}
		            	{/if}
		            	<td class="r avg col_total" title="{$tooltips_prefix} Average Selling Cost">{$avg_cost|number_format:2}</td>
		            	<td class="r avg col_total" title="{$tooltips_prefix} Average Selling Amount">{$avg_amt|number_format:2}</td>

		            	<!-- Variance (Qty & Amt) -->
		            	<!-- Mark up -->
		            	<td class="r markup col_total" title="{$tooltips_prefix} Mark Up Qty">{$data.$bid.$sid.variances.total.markup_qty|qty_nf}</td>
		            	<td class="r markup col_total" title="{$tooltips_prefix} Mark Up Amount">{$data.$bid.$sid.variances.total.markup_amt|number_format:2}</td>
		            	<!-- Mark down -->
		            	<td class="r markdown col_total" title="{$tooltips_prefix} Mark Down Qty">{$data.$bid.$sid.variances.total.markdown_qty|qty_nf}</td>
		            	<td class="r markdown col_total" title="{$tooltips_prefix} Mark Down Amount">{$data.$bid.$sid.variances.total.markdown_amt|number_format:2}</td>
		            	<!-- discount -->
		            	<td class="r discount col_total" title="{$tooltips_prefix} Discount Qty">{$data.$bid.$sid.variances.total.disc_qty|qty_nf}</td>
		            	<td class="r discount col_total" title="{$tooltips_prefix} Discount Amount">{$data.$bid.$sid.variances.total.disc_amt|number_format:2}</td>
		            	<!-- Variance Amt -->
		            	{assign var=variance_amt value=$data.$bid.$sid.variances.total.markup_amt-$data.$bid.$sid.variances.total.markdown_amt-$data.$bid.$sid.variances.total.disc_amt}
		            	<td class="r variance_amt col_total" title="{$tooltips_prefix} Variances Amount">{$variance_amt|number_format:2}</td>

		            	<!-- Branch Summary -->
	            		<!-- Opening Balance & In Stock -->
		            	{assign var=open_bal value=$total_in*$data.$bid.$sid.branch_last.cost}
		            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Opening Balance & In Stock">{$open_bal|number_format:2}</td>
		            	<!-- Sales amount -->
		            	{assign var=sales_amt value=$data.$bid.$sid.pos.total.amt}
		            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Sales amount" id="branch_summary_sales_amt-{$bid}-{$sid}">{$sales_amt|number_format:2}</td>
						<!-- Sales Cost -->
		            	{assign var=sales_cost value=$data.$bid.$sid.pos.total.cost}
		            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Sales Cost" id="branch_summary_cost_of_goods_sold-{$bid}-{$sid}">{$sales_cost|number_format:2}</td>
		            	<!-- Profit Amount -->
		            	{assign var=profit_amt value=$sales_amt-$sales_cost}
		            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Profit Amount">{$profit_amt|number_format:2}</td>
		            	<!-- Profit Percent -->
		            	{if $sales_amt}
		            		{assign var=profit_per value=$profit_amt/$sales_amt*100}
		            	{else}
		            	    {assign var=profit_per value=0}
		            	{/if}
		            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Profit Percent">{$profit_per|number_format:2}</td>
		            	<!-- Closing Stock (Amt) -->
		            	{assign var=close_bal value=$open_bal-$sales_cost}
		            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Closing Stock (Amt)">{$close_bal|number_format:2}</td>
		            	<!-- % sold on QTY -->
		            	{if $total_in}
		            		{assign var=sold_qty_per value=$data.$bid.$sid.pos.total.qty/$total_in*100}
		            	{else}
		            	    {assign var=sold_qty_per value=0}
		            	{/if}
		            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: % sold on QTY">{$sold_qty_per|number_format:2}</td>
		            	
						<!-- Profit & Sales Weighted average -->
		            	{assign var=weight_avg value=$profit_per*$sold_qty_per}
		            	<!-- td class="r branch_summary">{$weight_avg|number_format:2}</td -->
		            	
		            	<!-- Sales - Purchase (Amt) -->
		            	{assign var=earn value=$total_in*$data.$bid.$sid.branch_last.cost}
		            	{assign var=earn value=$data.$bid.$sid.pos.total.amt-$earn}
		            	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Sales - Purchase (Amt)">{$earn|number_format:2}</td>

						<!-- Summary -->
						<!-- Selling -->
						{assign var=summary_selling value=$data.$bid.$sid.pos.total.amt}
						<td class="r summary" title="{$tooltips_prefix} Summary: Selling">{$summary_selling|number_format:2}</td>
						
						<!-- Summary: Cost Branch -->
						{assign var=summary_cost_branch value=$data.$bid.$sid.pos.total.cost}
						<td class="r summary" title="{$tooltips_prefix} Summary: Cost Branch">{$summary_cost_branch|number_format:2}</td>
						
						<!-- Summary: Cost HQ -->
						{assign var=summary_cost_hq value=$data.$bid.$sid.pos.total.qty*$sku_item.hq_cost}
						<td class="r summary" title="{$tooltips_prefix} Summary: Cost HQ">{$summary_cost_hq|number_format:2}</td>
						
						<!-- Summary: Profit ({$config.arms_currency.symbol}) Branch -->
						{assign var=summary_profit_rm_branch value=$summary_selling-$summary_cost_branch}
						<td class="r summary" title="{$tooltips_prefix} Summary: Profit ({$config.arms_currency.symbol}) Branch">{$summary_profit_rm_branch|number_format:2}</td>
						
						<!-- Summary: Profit ({$config.arms_currency.symbol}) HQ -->
						{assign var=summary_profit_rm_hq value=$summary_selling-$summary_cost_hq}
						<td class="r summary" title="{$tooltips_prefix} Summary: Profit ({$config.arms_currency.symbol}) HQ">{$summary_profit_rm_hq|number_format:2}</td>
						
						<!-- Summary: Profit % Branch -->
						{assign var=summary_profit_per_branch value=0}
						{if $summary_selling}
							{assign var=summary_profit_per_branch value=$summary_cost_branch/$summary_selling*100}
						{/if}
						{assign var=temp_val value=100}
						{assign var=summary_profit_per_branch value=$temp_val-$summary_profit_per_branch}
						<td class="r summary" title="{$tooltips_prefix} Summary: Profit % Branch">{$summary_profit_per_branch|number_format:2}</td>
						
						<!-- Summary: Profit % HQ -->
						{assign var=summary_profit_per_hq value=0}
						{if $summary_selling}
							{assign var=summary_profit_per_hq value=$summary_cost_hq/$summary_selling*100}
						{/if}
						{assign var=temp_val value=100}
						{assign var=summary_profit_per_hq value=$temp_val-$summary_profit_per_hq}
						<td class="r summary" title="{$tooltips_prefix} Summary: Profit % HQ">{$summary_profit_per_hq|number_format:2}</td>
						
						<!-- Summary: Holding Cost -->
						{assign var=summary_holding_cost value=$holding_cost}
						<td class="r summary" title="{$tooltips_prefix} Summary: Holding Cost">{$summary_holding_cost|number_format:2}</td>
						
						<!-- Summary: Total Cost Branch -->
						{assign var=summary_total_cost_branch value=$summary_cost_branch+$summary_holding_cost}
						<td class="r summary" title="{$tooltips_prefix} Summary: Total Cost Branch">{$summary_total_cost_branch|number_format:2}</td>
						
						<!-- Summary: Total Cost HQ -->
						{assign var=summary_total_cost_hq value=$summary_cost_hq+$summary_holding_cost}
						<td class="r summary" title="{$tooltips_prefix} Summary: Total Cost HQ">{$summary_total_cost_hq|number_format:2}</td>
						
						<!-- Summary: Actual Profit ({$config.arms_currency.symbol}) Branch -->
						{assign var=summary_act_profit_rm_branch value=$summary_selling-$summary_total_cost_branch}
						<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit ({$config.arms_currency.symbol}) Branch">{$summary_act_profit_rm_branch|number_format:2}</td>
						
						<!-- Summary: Actual Profit ({$config.arms_currency.symbol}) Branch -->
						{assign var=summary_act_profit_rm_hq value=$summary_selling-$summary_total_cost_hq}
						<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit ({$config.arms_currency.symbol}) HQ">{$summary_act_profit_rm_hq|number_format:2}</td>
						
						<!-- Summary: Actual Profit % Branch -->
						{assign var=summary_act_profit_per_branch value=0}
						{if $summary_selling}
							{assign var=summary_act_profit_per_branch value=$summary_total_cost_branch/$summary_selling*100}
						{/if}
						{assign var=temp_val value=100}
						{assign var=summary_act_profit_per_branch value=$temp_val-$summary_act_profit_per_branch}
						<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit % Branch">{$summary_act_profit_per_branch|number_format:2}</td>
						
						<!-- Summary: Actual Profit % HQ -->
						{assign var=summary_act_profit_per_hq value=0}
						{if $summary_selling}
							{assign var=summary_act_profit_per_hq value=$summary_total_cost_hq/$summary_selling*100}
						{/if}
						{assign var=temp_val value=100}
						{assign var=summary_act_profit_per_hq value=$temp_val-$summary_act_profit_per_hq}
						<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit % HQ">{$summary_act_profit_per_hq|number_format:2}</td>
				
                        <!-- Proposal -->
                        <!-- Existing Branch profit (%) -->
                        {assign var=existing_branch_profit_per value=$profit_per}
                        {if !$existing_branch_profit_per}
                            {assign var=existing_branch_profit_per value=$gp_per}
                        {/if}
            			<td class="r col_proposal" title="{$tooltips_prefix} Existing Branch profit (%)" id="existing_branch_profit_per-{$bid}-{$sid}">{$existing_branch_profit_per|num_format:2}</td>
            			
            			{assign var=existing_group_profit_per value=$actual_profit_per2}
                        {if !$existing_group_profit_per}
                            {assign var=existing_group_profit_per value=$group_gp_per}
                        {/if}
                        <!-- Existing Group profit (%) -->
            			<td class="r col_proposal" title="{$tooltips_prefix} Existing Group profit (%)" id="existing_group_profit_per-{$bid}-{$sid}">{$existing_group_profit_per|num_format:2}</td>
            	        <td class="r col_proposal" title="{$tooltips_prefix} Existing Sales (%)" id="existing_sales_per-{$bid}-{$sid}">{$sold_qty_per|num_format:2}</td>
            	        {assign var=existing_sales_pcs value=$data.$bid.$sid.pos.total.qty}
            	        <td class="r col_proposal" title="{$tooltips_prefix} Existing Sales (Pcs)" id="existing_sales_pcs-{$bid}-{$sid}">{$existing_sales_pcs|num_format}</td>
            	        
            	        {assign var=bal_qty value=$total_in-$data.$bid.$sid.pos.total.qty}
            	        <td class="r col_proposal" title="{$tooltips_prefix} Balance quantity" id="bal_qty-{$bid}-{$sid}">{$bal_qty|qty_nf}</td>
            	        <td class="r col_proposal" title="{$tooltips_prefix} Branch Last selling price">{$data.$bid.$sid.branch_last.selling|number_format:2}</td>
            	        
            	        <!-- proposal 1 sales qty -->
            	        {assign var=p1_sales_qty value=$proposal_data.$bid.$sid.p1_sales_qty}
            	        <td class="r col_proposal {if $bid ne 'ibt'}editable{/if}" title="{$tooltips_prefix} Proposal 1 - Sales Qty" id="p1_sales_qty-{$bid}-{$sid}">{$p1_sales_qty|qty_nf}</td>

						<!-- proposal 1 sales % -->
            	        {assign var=p1_sales_per value=0}
            	        {if $existing_sales_pcs}
            	            {assign var=p1_sales_per value=$sold_qty_per*$p1_sales_qty/$existing_sales_pcs}
            	        {/if}
            	        <td class="r col_proposal" title="{$tooltips_prefix} Proposal 1 - Sales %" id="p1_sales_per-{$bid}-{$sid}">{$p1_sales_per|number_format:2}</td>
            	        
            	        <!-- CALCULATION -->
            	        <!-- proposal 1 selling price -->
            	        {assign var=p1_selling_price value=$proposal_data.$bid.$sid.p1_selling_price}
            	        
            	        <!-- proposal 2 sales qty -->
            	        {assign var=p2_sales_qty value=$bal_qty-$p1_sales_qty}
            	        
            	        <!-- proposal 2 sales % -->
            	        {assign var=p2_sales_per value=0}
            	        {if $existing_sales_pcs}
            	            {assign var=p2_sales_per value=$sold_qty_per*$p2_sales_qty/$existing_sales_pcs}
            	        {/if}
            	        
            	        <!-- proposal 2 selling price -->
            	        {assign var=p2_selling_price value=$proposal_data.$bid.$sid.p2_selling_price}
            	        
            	        <!-- SP1 -->
            	        <!-- Sales at SP1 -->
            	        {assign var=sp1_sales_at_sp1 value=$data.$bid.$sid.branch_last.selling*$sold_qty_per}
            	        {if $sp1_sales_at_sp1}
            	        	{assign var=sp1_sales_at_sp1 value=$sales_amt*$p1_sales_per*$p1_selling_price/$sp1_sales_at_sp1}	
            	        {/if}
            	        
            	        <!-- Cost of sales at SP1 -->
            	        {assign var=sp1_cost_sales_at_sp1 value=0}
            	        {if $sold_qty_per}
            	        	{assign var=sp1_cost_sales_at_sp1 value=$sales_cost*$p1_sales_per/$sold_qty_per}
            	        {/if}
            	        
            	        <!-- Profit for SP1 -->
            	        {assign var=sp1_profit_for_sp1 value=$sp1_sales_at_sp1-$sp1_cost_sales_at_sp1}
            	        
            	        <!-- Total Sales (plus SP1) -->
            	        {assign var=sp1_total_sales_plus_sp1 value=$sp1_sales_at_sp1+$sales_amt}
            	        
            	        <!-- Total COST (plus SP1) -->
            	        {assign var=sp1_total_cost_plus_sp1 value=$sales_cost+$sp1_cost_sales_at_sp1}
            	        
            	        <!-- Net profit after SP1 -->
            	        {assign var=sp1_net_profit_sp1 value=$sp1_total_sales_plus_sp1-$sp1_total_cost_plus_sp1}
            	        
            	        <!-- Profit (%) after SP1 -->
            	        {assign var=sp1_profit_per_sp1 value=0}
            	        {if $sp1_total_sales_plus_sp1}
            	        	{assign var=sp1_profit_per_sp1 value=$sp1_net_profit_sp1/$sp1_total_sales_plus_sp1}
            	        {/if}
            	        
            	        <!-- proposal 1 profit % -->
            	        {assign var=proposal_1_profit value=$sp1_profit_per_sp1}
            	        
            	        <!-- SP2 -->
            	        <!-- Sales at SP2 -->
            	        {assign var=sp2_sales_at_sp2 value=$data.$bid.$sid.branch_last.selling*$sold_qty_per}
            	        {if $sp2_sales_at_sp2}
            	        	{assign var=sp2_sales_at_sp2 value=$sales_amt*$p2_sales_per*$p2_selling_price/$sp1_sales_at_sp1}	
            	        {/if}
            	        
            	        <!-- Cost of sales at SP2 -->
            	        {assign var=sp2_cost_sales_sp2 value=0}
            	        {if $sold_qty_per}
            	        	{assign var=sp2_cost_sales_sp2 value=$sales_cost*$p2_sales_per/$sold_qty_per}
            	        {/if}
            	        
            	        <!-- Profit for SP2 -->
            	        {assign var=sp2_profit_sp2 value=$sp2_sales_at_sp2-$sp2_cost_sales_sp2}
            	        
            	        <!-- Total Sales (SP1+SP2) -->
            	        {assign var=sp2_total_sales_sp1sp2 value=$sp1_total_sales_plus_sp1+$sp2_sales_at_sp2}
            	        
            	        <!-- Total COST (SP1+SP2) -->
            	        {assign var=sp2_total_cost_sp1sp2 value=$sp1_total_cost_plus_sp1+$sp2_cost_sales_sp2}
            	        
            	        <!-- Net profit (SP1+SP2) -->
            	        {assign var=sp2_net_profit_sp1sp2 value=$sp2_total_sales_sp1sp2+$sp2_total_cost_sp1sp2}
            	        
            	        <!-- Profit (%) (SP1+SP2) -->
            	        {assign var=sp2_profit_per_sp1sp2 value=$sp2_net_profit_sp1sp2+$sp2_total_sales_sp1sp2}
            	        
            	        <!-- proposal 2 profit % -->
            	        {assign var=proposal_2_profit value=$sp2_profit_per_sp1sp2}
            	        
            	        <!-- DISPLAY -- >
            	        <!-- proposal 1 selling price -->
            	        <td class="r col_proposal {if $bid ne 'ibt'}editable{/if}" title="{$tooltips_prefix} Proposal 1 - Selling Price" id="p1_selling_price-{$bid}-{$sid}">{$p1_selling_price|number_format:2}</td>
            	        
            	        <!-- proposal 1 profit % -->
            	        <td class="r col_proposal"  title="{$tooltips_prefix} Proposal 1 - Profit %" id="proposal_1_profit-{$bid}-{$sid}">{$proposal_1_profit|number_format:2}</td>
            	        
            	        <!-- proposal 2 sales qty -->
            	        <td class="r col_proposal" title="{$tooltips_prefix} Proposal 2 - Sales Qty" id="p2_sales_qty-{$bid}-{$sid}">{$p2_sales_qty|qty_nf}</td>
            	        
            	        <!-- proposal 2 sales % -->
            	        <td class="r col_proposal" title="{$tooltips_prefix} Proposal 2 - Sales %" id="p2_sales_per-{$bid}-{$sid}">{$p2_sales_per|number_format:2}</td>
            	        
            	        <!-- proposal 2 selling price -->
            	        <td class="r col_proposal {if $bid ne 'ibt'}editable{/if}" title="{$tooltips_prefix} Proposal 2 - Selling Price" id="p2_selling_price-{$bid}-{$sid}">{$p2_selling_price|number_format:2}</td>
            	        
            	        <!-- proposal 2 profit % -->
            	        <td class="r col_proposal" title="{$tooltips_prefix} Proposal 2 - Profit %" id="proposal_2_profit-{$bid}-{$sid}">{$proposal_2_profit|number_format:2}</td>
            	        
            	        <!-- SP1 -->
            	        <!-- Sales at SP1 -->
            	        <td class="r col_sp1" title="{$tooltips_prefix} SP1: Sales at SP1" id="sp1_sales_at_sp1-{$bid}-{$sid}">{$sp1_sales_at_sp1|number_format:2}</td>
            	        
            	        <!-- Cost of sales at SP1 -->
            	        <td class="r col_sp1" title="{$tooltips_prefix} SP1: Cost of sales at SP1" id="sp1_cost_sales_at_sp1-{$bid}-{$sid}">{$sp1_cost_sales_at_sp1|number_format:2}</td>
            	        
            	        <!-- Profit for SP1 -->
            	        <td class="r col_sp1" title="{$tooltips_prefix} SP1: Profit for SP1" id="sp1_profit_for_sp1-{$bid}-{$sid}">{$sp1_profit_for_sp1|number_format:2}</td>
            	        
            	        <!-- Total Sales (plus SP1) -->
            	        <td class="r col_sp1" title="{$tooltips_prefix} SP1: Total Sales (plus SP1)" id="sp1_total_sales_plus_sp1-{$bid}-{$sid}">{$sp1_total_sales_plus_sp1|number_format:2}</td>
            	        
            	        <!-- Total COST (plus SP1) -->
            	        <td class="r col_sp1" title="{$tooltips_prefix} SP1: Total COST (plus SP1)" id="sp1_total_cost_plus_sp1-{$bid}-{$sid}">{$sp1_total_cost_plus_sp1|number_format:2}</td>
            	        
            	        <!-- Net profit after SP1 -->
            	        <td class="r col_sp1" title="{$tooltips_prefix} SP1: Net profit after SP1" id="sp1_net_profit_sp1-{$bid}-{$sid}">{$sp1_net_profit_sp1|number_format:2}</td>
            	        
            	        <!-- Profit (%) after SP1 -->
            	        <td class="r col_sp1" title="{$tooltips_prefix} SP1: Profit (%) after SP1" id="sp1_profit_per_sp1-{$bid}-{$sid}">{$sp1_profit_per_sp1|number_format:2}</td>
            	        
            	        <!-- SP2 -->
            	        <!-- Sales at SP2 -->
            	        <td class="r col_sp2" title="{$tooltips_prefix} SP2: Sales at SP2" id="sp2_sales_at_sp2-{$bid}-{$sid}">{$sp2_sales_at_sp2|number_format:2}</td>
            	        
            	        <!-- Cost of sales at SP2 -->
            	        <td class="r col_sp2" title="{$tooltips_prefix} SP2: Cost of sales at SP2" id="sp2_cost_sales_sp2-{$bid}-{$sid}">{$sp2_cost_sales_sp2|number_format:2}</td>
            	        
            	        <!-- Profit for SP2 -->
            	        <td class="r col_sp2" title="{$tooltips_prefix} SP2: Profit for SP2" id="sp2_profit_sp2-{$bid}-{$sid}">{$sp2_profit_sp2|number_format:2}</td>
            	        
            	        <!-- Total Sales (SP1+SP2) -->
            	        <td class="r col_sp2" title="{$tooltips_prefix} SP2: Total Sales (SP1+SP2)" id="sp2_total_sales_sp1sp2-{$bid}-{$sid}">{$sp2_total_sales_sp1sp2|number_format:2}</td>
            	        
            	        <!-- Total COST (SP1+SP2) -->
            	        <td class="r col_sp2" title="{$tooltips_prefix} SP2: Total COST (SP1+SP2)" id="sp2_total_cost_sp1sp2-{$bid}-{$sid}">{$sp2_total_cost_sp1sp2|number_format:2}</td>
            	        
            	        <!-- Net profit (SP1+SP2) -->
            	        <td class="r col_sp2" title="{$tooltips_prefix} SP2: Net profit (SP1+SP2)" id="sp2_net_profit_sp1sp2-{$bid}-{$sid}">{$sp2_net_profit_sp1sp2|number_format:2}</td>
            	        
            	        <!-- Profit (%) (SP1+SP2) -->
            	        <td class="r col_sp2" title="{$tooltips_prefix} SP2: Net profit (SP1+SP2)" id="sp2_profit_per_sp1sp2-{$bid}-{$sid}">{$sp2_profit_per_sp1sp2|number_format:2}</td>
	                </tr>
	            {/foreach}
	            </tbody>
	        {/if}
	        
	        {if $smarty.foreach.f_sku.iteration%20 eq 0}
	            {php}
	                ob_flush();
	            {/php}
	        {/if}
	        {if $smarty.foreach.f_sku.iteration%$rows_per_table eq 0}
	        	{if $smarty.foreach.f_sku.last}
	        		{include file='report.sku_monitoring2.total_row.tpl'}
	        		{assign var=no_need_total_row value=1}
	        	{/if}
				</table><br /><br />
			{/if}
	    {/foreach}	    
	    
	    {if !$no_need_total_row}{include file='report.sku_monitoring2.total_row.tpl'}{/if}
	    
	</table>
{/if}
{if !$no_header_footer}
<script type="text/javascript">
{literal}
new Draggable('div_group_batch_items_details',{ handle: 'div_group_batch_items_details_header'});
{/literal}

{if $BRANCH_CODE eq 'HQ'}EDITABLE_FIELD_MODULE.initialize();{/if}
</script>
{/if}

{include file='footer.tpl'}
