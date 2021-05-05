{*
12/7/2010 10:19:13 AM Alex
- fix checking fresh data existed
- hide confirm button if all had confirmed

12/8/2010 12:48:36 PM alex
- add approve icon
- break each line by category root row

12/8/2010 5:56:43 PM Alex
- add searching vendor

12/9/2010 3:18:57 PM Alex
- change searching vendor to popup
- add background color to fresh market weight

12/10/2010 6:11:50 PM Alex
- Confirm user cannot edit data after confirm
- only finalize can edit confirmed data

12/21/2010 3:27:09 PM Alex
- add review privilege to review data
- after review, data cannot be edit

12/27/2010 7:10:40 PM Alex
- add calculate percentage for test mode

12/29/2010 3:48:45 PM Alex
- clear 'Form' word
- Change word "Unreviewed" and "Reviewed" on a button
- After fully review, no unfinalize button

1/6/2011 12:28:36 PM Alex
- add ajax_check_department function to check tick or untick departments
- add star.png and stop.png for finalize and not finalize status

1/10/2011 2:03:37 PM Alex
- fix show and hide buttons under different conditions
- close test button

2/16/2011 6:22:18 PM Alex
- Export Excel with Vendor / Without Vendor
- Search Vendor -> show the column input field allow use to key in necessary data & add filtering SKU Type
- Sales Consignment Input Using % to get the Cost

2/17/2011 6:36:15 PM Alex
- move vendor input form under search vendor form
- fix unable to edit confirmed data bugs

2/18/2011 12:05:50 PM Alex
- add actual sales column while input data at vendor form

2/22/2011 9:28:14 AM Alex
- fix vendor form calculation
- add float while calculation to avoid miscalculation in other computer

2/23/2011 5:41:58 PM Alex
- calculation percentage error. Close uneeded text mode calculation script

2/24/2011 5:01:44 PM Andy
- add addCommas function to add ',' in number

3/7/2011 7:00:11 PM Alex
- save extra value for calculation

3/15/2011 10:40:54 AM Alex
- rearrange the step of updating and closing searching form

6/30/2011 11:16:19 AM Alex
- Turn days use 0 number format

11/23/2011 10:51:15 AM Alex
- directly disable confirm or finalize button if previous month no confirm or finalize

12/1/2011 5:21:26 PM Alex
- remove rebate variable in script calculation
- fix display input form bugs

2/16/2012 11:32:07 AM Alex
- change form method to 'get'

3/7/2012 6:24:59 PM Alex
- fix bugs of adding unidentified variable

4/6/2012 11:03:40 AM Alex
- show item list
- show item div and loading

4/12/2012 2:06:34 PM Alex
- add tmp request into ajax call

4/16/2012 6:51:26 PM Alex
- no button when using tmp 

4/19/2012 3:54:14 PM Alex
- can use button in tmp mode
- no show unfinalize button if less than 2 month of now date 

4/20/2012 3:26:35 PM Alex
- add confirmation pop up when save, confirm, finalize and unfinalize

4/23/2012 10:26:42 AM Alex
- add tmp while save, confirm, finalized and reviewed

4/24/2012 11:57:20 AM Alex
- based on latest finalize report: once finalized, previous month will no longer unfinalize or unreview

3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".
*}

{include file=header.tpl}
{if !$no_header_footer}
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
<script>
{/literal}
var phpself = '{$smarty.server.PHP_SELF}';
var turn_days=float({$days});
{literal}
function show_search_vendor()
{
//	curtain(true);
	center_div('search_vendor_id');
	$('search_vendor_id').style.display = '';

}

function load_vendor(dept_id,sku_type){

	new Ajax.Updater("vendor_id",phpself,{
		parameters:{
			a: 'ajax_load_vendor',
			branch_id: document.f_b['branch_id'].value,
			year:   document.f_b['year'].value,
			month:  document.f_b['month'].value,
			sku_type: sku_type,
			department_id: dept_id,
			tmp: document.f_b['tmp'].value
		},
		evalScripts: true
	});
}

function ajax_load_form_type(sku_type,dept_id,vendor_id){

	$('form_input_cache').update(_loading_);

	new Ajax.Updater("form_input_cache",phpself,{
		parameters:{
			a: 'ajax_load_form_type',
			ajax: 1,
			sku_type: sku_type,
			department_id: dept_id,
			vendor_id: vendor_id,
			tmp: document.f_b['tmp'].value
		},
		onComplete:function(m){
           // $('form_vendor_id').style.display = '';
		},
		evalScripts: true
	});

	center_div($('form_vendor_id'));
	$('form_vendor_id').style.top=int(int($('form_vendor_id').style.top)+int($('search_vendor_id').style.height))+'px';
	$('form_vendor_id').style.left=int($('search_vendor_id').style.left)+'px';
}

function ajax_load_sku_items(sku_type,dept_id,vendor_id){

	$('sku_items_cache').update(_loading_);
    $('sku_items_id').style.display = '';

	new Ajax.Updater("sku_items_cache",phpself,{
		parameters:{
			a: 'ajax_load_item_from_vendor',
			ajax: 1,
			branch_id: document.f_b['branch_id'].value,
			year: document.f_b['year'].value,
			month: document.f_b['month'].value,
			sku_type: sku_type,
			department_id: dept_id,
			vendor_id: vendor_id,
			tmp: document.f_b['tmp'].value,
			sales_gp: $('vacs_c_gp,'+dept_id+","+vendor_id) ? $('vacs_c_gp,'+dept_id+","+vendor_id).value : 0
		},
		onComplete:function(m){
			center_div($('sku_items_id'));
		},
		evalScripts: true
	});
}

function ajax_load_before_stock_check(sku_type,dept_id,vendor_id){

	$('before_stc_cache').update(_loading_);
	$('before_stc_id').style.display = '';
	
	new Ajax.Updater("before_stc_cache",phpself,{
		parameters:{
			a: 'ajax_load_before_stock_check',
			ajax: 1,
			branch_id: document.f_b['branch_id'].value,
			year: document.f_b['year'].value,
			month: document.f_b['month'].value,
			sku_type: sku_type,
			department_id: dept_id,
			vendor_id: vendor_id,
			tmp: document.f_b['tmp'].value

		},
		onComplete:function(m){
			center_div($('before_stc_id'));
		},
		evalScripts: true
	});
}

function display_vendor_row(){
	var dept_id = $("department_id").value;
	var sku_type = $("sku_type_id").value;
	var vendor_id = $("vendor_id").value;
	if (!dept_id){
		alert("Missing department.");
		return;
	}else if (!vendor_id){
		alert("Missing vendor.");
		return;
	}
	if (sku_type == "OUTRIGHT"){
		if ($('outright_'+dept_id)){
			$$('#outright_'+dept_id+' .vendors').each(function(ele,obj){
			    $(ele).hide();
			});

			$$('#outright_'+dept_id+' .dept_'+dept_id+'_ven_'+vendor_id).each(function(ele,obj){
			    $(ele).show();
			});

			$('outright_'+dept_id).show();

			var page_y = Position.page($('outright_'+dept_id))[1];

			if($("exp_col_o_"+dept_id).src.indexOf('expand')<=0){
				$("exp_col_o_"+dept_id).src = '/ui/expand.gif';
			}
		}
	}else if (sku_type == "CONSIGN"){
		if ($('consign_'+dept_id)){
			$$('#consign_'+dept_id+' .vendors').each(function(ele,obj){
			    $(ele).hide();
			});

			$$('#consign_'+dept_id+' .dept_'+dept_id+'_ven_'+vendor_id).each(function(ele,obj){
			    $(ele).show();
			});

			$('consign_'+dept_id).show();

			if($("exp_col_c_"+dept_id).src.indexOf('expand')<=0){
				$("exp_col_c_"+dept_id).src = '/ui/expand.gif';
			}
		}
	}

	$('dept_scroll_'+dept_id).scrollTo();

	center_div('search_vendor_id');
	ajax_load_form_type(sku_type,dept_id,vendor_id);
}

function show_vendor(obj,id){

	if(obj.src.indexOf('expand')>0){
		obj.src = '/ui/collapse.gif';

        $$('#'+id+' .vendors').each(function(ele,obj){
		    $(ele).show();
		});
		
		$(id).show();
	}else{
		obj.src = '/ui/expand.gif';
		$(id).hide();
	}
}

function load_form_data(){
	var sku_type = document.f_input['sku_type'].value;
	var root_id = document.f_input['root_id'].value;
	var dept_id = document.f_input['dept_id'].value;
	var vendor_id = document.f_input['vendor_id'].value;
	var not_input_value=false;

    $$('#form_input_data input').each(function (ele,index){
    
		if (!$(ele.name+','+dept_id+','+vendor_id)){
		    not_input_value=true;
			return false;
		}

		if (ele.name=='vacs_c_cost' || ele.name=='vacs_c_selling')
		    ele.value=$(ele.name+','+dept_id+','+vendor_id).innerHTML;
		else
			ele.value=$(ele.name+','+dept_id+','+vendor_id).value;
	});

	if (not_input_value){
		$('form_vendor_id').style.display = 'none';
	}else{
		$('form_vendor_id').style.display = '';
		if (sku_type == 'CONSIGN')  vendor_form_calculate($('vacs_c_gp_id'));
	}
}

function vendor_form_calculate(ele){
	var vacs_c_gp = float(ele.value.replace('%',''));
	ele.value = round(vacs_c_gp,2)+'%';

    var vacs_c_selling = float($('vacs_c_selling_id').value);

	var vacs_c_cost = float(vacs_c_selling - (vacs_c_gp / 100 * vacs_c_selling));

	$('vacs_c_cost_id').value = ifzero(vacs_c_cost);
}

function replace_data(){
	var got_data=false;
	var overwrite=true;
	var sku_type = document.f_input['sku_type'].value;
	var root_id = document.f_input['root_id'].value;
	var dept_id = document.f_input['dept_id'].value;
	var vendor_id = document.f_input['vendor_id'].value;

	//1st looping check data
    $$('#form_input_data input').each(function (ele,index){
        if($(ele.name+','+dept_id+','+vendor_id).value){
			got_data=true;
			return false;
		}
	});

	if (got_data){
		overwrite = confirm("Current vendor got data. Are you sure want to update them?");
	}

    if (overwrite){
		$$('#form_input_data input').each(function (ele,index){
		    $(ele.name+','+dept_id+','+vendor_id).value = ele.value;
		});
		
		vendor_changes(sku_type,root_id,dept_id,vendor_id);
		$('form_vendor_id').hide();
	}
}

function link_form(type){
	var tmp_file='';
	if (document.f_b['tmp'].value)	tmp_file='&tmp=1' 

	if(type=='save'){
	    if (!confirm_msg(type))	return false;

	    document.f_b.action=phpself+'?save=1'+tmp_file;
	    document.f_b['a'].value='save_form';
	    document.f_b.submit();
	}else if(type=='confirm'){
	    if (!confirm_msg(type))	return false;

		$('confirm_btn').disabled=true;
	
	   	new Ajax.Request(phpself,{
			method:'post',
			parameters: {
			    a: "check_previous",
			    branch_id: document.f_b['branch_id'].value,
				year: document.f_b['year'].value,
				month: document.f_b['month'].value,
				tmp: document.f_b['tmp'].value,
				type: "confirmed"
			},
			onComplete: function(m){

	            if (m.responseText == 'GOT'){
					alert("Previous month report haven't confirm. Please confirm it.");
					$('confirm_btn').disabled=true;
					return;
				}

				$('confirm_btn').disabled=false;
				document.f_b.action=phpself+'?confirm=1'+tmp_file;
			    document.f_b['a'].value='confirm_form';
			    document.f_b.submit();			
			}
		});

	}else if(type=='finalize'){
	    if (!confirm_msg(type))	return false;

		$('finalize_btn').disabled=true;
	
	   	new Ajax.Request(phpself,{
			method:'post',
			parameters: {
			    a: "check_previous",
			    branch_id: document.f_b['branch_id'].value,
				year: document.f_b['year'].value,
				month: document.f_b['month'].value,
				tmp: document.f_b['tmp'].value,
				type: "finalized"
			},
			onComplete: function(m){

	            if (m.responseText == 'GOT'){
					alert("Previous month report haven't finalised. Please finalise it.");
					$('finalize_btn').disabled=true;
					return;
/*					if (!confirm("Previous month report haven't finalized, still continue?")){
						$('finalize_btn').disabled=false;
						return;
					}
*/
				}

				$('finalize_btn').disabled=false;
				document.f_b.action=phpself+'?finalize=1'+tmp_file;
			    document.f_b['a'].value='finalize_form';
			    document.f_b.submit();
			}
		});

	}else if(type=='unfinalize'){
	    if (!confirm_msg(type))	return false;
		document.f_b.action=phpself+'?unfinalize=1'+tmp_file;
	    document.f_b['a'].value='unfinalize_form';
	    document.f_b.submit();
	}else if(type=='review'){
		document.f_b.action=phpself+'?review=1'+tmp_file;
	    document.f_b['a'].value='review_form';
	    document.f_b.submit();
	}else if(type=='hreview'){
		document.f_b.action=phpself+'?unreview_review=1'+tmp_file;
	    document.f_b['a'].value='review_form';
	    document.f_b.submit();
	}else if(type=='unreview'){
		document.f_b.action=phpself+'?unreview=1'+tmp_file;
	    document.f_b['a'].value='review_form';
	    document.f_b.submit();
	}else if(type=='regenerate'){
	    if (confirm('Are you sure?')){
	    	document.f_a.action=phpself+'?regenerate=1';
		    document.f_a['a'].value='regenerate_report';
		    document.f_a.submit();
	    }else{
			return false;
		}
	}else if (type=='edit'){
	    document.f_a.action=phpself+'?edit=1'+tmp_file;
	    document.f_a['a'].value='edit_form';
	    document.f_a.submit();
	}else if(type=='view'){
	    document.f_a.action=phpself+'?view=1'+tmp_file;
	    document.f_a['a'].value='view_form';
	    document.f_a.submit();
	}else if(type=='output'){
	    document.f_a['a'].value='output_excel';
	    document.f_a.submit();
	}else if(type=='output_no_vendor'){
	    document.f_a['a'].value='output_excel_without_vendor';
	    document.f_a.submit();
	}else if(type=='test'){
	    document.f_a['a'].value='test_mode';
	    document.f_a.submit();
	}else{
	    document.f_a.action=phpself+'?show_report=1'+tmp_file;
	    document.f_a['a'].value='show_report';
	    document.f_a.submit();
	}
}

function confirm_msg(type){
	var msg = type;
	if (msg == 'hreview')	msg = "unreview and review";
	return confirm('Are you sure to '+msg+' the report?')
}

function fresh_changes(category_id,root_id){

//fresh market weight
	//actual stock
	var grn_f_selling =  float($("grn_f_selling,"+category_id).value);
	var aos_f_selling =  float($("aos_f_selling,"+category_id).innerHTML);
	var sr_f_selling =  float($("sr_f_selling,"+category_id).innerHTML);
	var adj_f_selling =  float($("adj_f_selling,"+category_id).innerHTML);

	var as_f_selling = aos_f_selling + sr_f_selling + grn_f_selling + adj_f_selling;
	$("as_f_selling,"+category_id).innerHTML = ifzero(as_f_selling);

	//actual sales
	var cs_f_selling =  float($("cs_f_selling,"+category_id).value);
	var acs_f_selling =  float($("acs_f_selling,"+category_id).innerHTML);
	
	var acs_f_gp = float((acs_f_selling - grn_f_selling - aos_f_selling - sr_f_selling + cs_f_selling) / acs_f_selling * 100);
    $("acs_f_gp,"+category_id).innerHTML = ifinfinity(acs_f_gp);
    
 	var acs_f_cost = float(acs_f_selling * (100 - acs_f_gp) / 100);
    $("acs_f_cost,"+category_id).innerHTML = ifzero(acs_f_cost);

//============================================>test mode start
/*
	//promotion amount
	var pa_f_selling = float($("pa_f_selling,"+category_id).innerHTML);
	var pa_f_gp = float(pa_f_selling / acs_f_selling * 100);
	$("pa_f_gp,"+category_id).innerHTML = ifinfinity(pa_f_gp);
	
	//price change amount
	var pca_f_selling = float($("pca_f_selling,"+category_id).innerHTML);
	var pca_f_gp = float(pca_f_selling / acs_f_selling * 100);
	$("pca_f_gp,"+category_id).innerHTML = ifinfinity(pca_f_gp);
*/

//============================================>test mode end

	//rebate
	var r_f_selling =0;
/*	var r_f_selling =  float($("r_f_selling,"+category_id).value);

	var r_f_gp = float((acs_f_selling-acs_f_cost+r_f_selling) / acs_f_selling * 100);
	$("r_f_gp,"+category_id).innerHTML = ifinfinity(r_f_gp);
*/
	//profit margin
	var oi_f_selling = float($("oi_f_selling,"+category_id).value);
	var pm_f_selling = float(acs_f_selling - acs_f_cost + r_f_selling + oi_f_selling);
    $("pm_f_selling,"+category_id).innerHTML = ifzero(pm_f_selling);

	var pm_f_gp = float(pm_f_selling / acs_f_selling * 100);
	$("pm_f_gp,"+category_id).innerHTML = ifinfinity(pm_f_gp);

	//average stock
	var cos_f_selling =  float($("cos_f_selling,"+category_id).innerHTML);
	var av_f_selling =  float((cos_f_selling + cs_f_selling) / 2);

    $("av_f_selling,"+category_id).innerHTML = ifzero(av_f_selling);

    //turn days
	var td_f_selling =  float((av_f_selling / acs_f_cost) * turn_days);
    $("td_f_selling,"+category_id).innerHTML = iftdinfinity(td_f_selling);

//Grand total fresh market
	fresh_grand_total(root_id);

//storewide
	//GRN ADJUSTMENT
		//selling
	var storewide_grn_f_selling=0;
	$$('tbody.t_fresh .t_grn_f_selling').each(function(obj,ele){
		storewide_grn_f_selling = storewide_grn_f_selling + float(obj.innerHTML);
	});

	$("storewide_grn_f_selling").innerHTML = ifzero(storewide_grn_f_selling);

	//actual stock
	var storewide_aos_f_selling =  float($("storewide_aos_f_selling").innerHTML);
	var storewide_sr_f_selling =  float($("storewide_sr_f_selling").innerHTML);
	var storewide_adj_f_selling =  float($("storewide_adj_f_selling").innerHTML);
	var storewide_as_f_selling = storewide_aos_f_selling + storewide_sr_f_selling + storewide_grn_f_selling + storewide_adj_f_selling;

	$("storewide_as_f_selling").innerHTML = ifzero(storewide_as_f_selling);

	//IDT
		//selling
	var storewide_idt_f_selling=0;
	$$('tbody.t_fresh .t_idt_f_selling').each(function(obj,ele){
		storewide_idt_f_selling = storewide_idt_f_selling + float(obj.innerHTML);
	});

	$("storewide_idt_f_selling").innerHTML = ifzero(storewide_idt_f_selling);

	//closing stock
		//selling
	var storewide_cs_f_selling=0;
	$$('tbody.t_fresh .t_cs_f_selling').each(function(obj,ele){
		storewide_cs_f_selling = storewide_cs_f_selling + float(obj.innerHTML);
	});

	$("storewide_cs_f_selling").innerHTML = ifzero(storewide_cs_f_selling);

	//actual sales
	var storewide_acs_f_selling = float($("storewide_acs_f_selling").innerHTML)
    var storewide_acs_f_gp = (storewide_acs_f_selling - storewide_aos_f_selling - storewide_sr_f_selling - storewide_grn_f_selling + storewide_cs_f_selling) / storewide_acs_f_selling * 100;
    $("storewide_acs_f_gp").innerHTML = ifinfinity(storewide_acs_f_gp);

	var storewide_acs_f_cost = float(storewide_acs_f_selling * (100 - storewide_acs_f_gp) / 100);
	$("storewide_acs_f_cost").innerHTML = ifzero(storewide_acs_f_cost);

	//REBATE
		//selling
	var storewide_r_f_selling=0;
/*	$$('tbody.t_fresh .t_r_f_selling').each(function(obj,ele){
		storewide_r_f_selling = storewide_r_f_selling + float(obj.innerHTML);
	});

	$("storewide_r_f_selling").innerHTML = ifzero(storewide_r_f_selling);

		//gp
	var storewide_r_f_gp = (((storewide_acs_f_selling - storewide_acs_f_cost) + storewide_r_f_selling) / storewide_acs_f_selling * 100 );
	$("storewide_r_f_gp").innerHTML = ifinfinity(storewide_r_f_gp);
*/
	//OTHER INCOME
		//selling
	var storewide_oi_f_selling=0;
	$$('tbody.t_fresh .t_oi_f_selling').each(function(obj,ele){
		storewide_oi_f_selling = storewide_oi_f_selling + float(obj.innerHTML);
	});

	$("storewide_oi_f_selling").innerHTML = ifzero(storewide_oi_f_selling);

	//profit margin
	var storewide_pm_f_selling = storewide_acs_f_selling - storewide_acs_f_cost + storewide_r_f_selling + storewide_oi_f_selling;
	var storewide_pm_f_gp = ((storewide_pm_f_selling / storewide_acs_f_selling ) * 100 );

	$("storewide_pm_f_selling").innerHTML = ifzero(storewide_pm_f_selling);
	$("storewide_pm_f_gp").innerHTML = ifinfinity(storewide_pm_f_gp);

	//average stock
    var storewide_cos_f_selling = float($("storewide_cos_f_selling").innerHTML);

	var storewide_av_f_selling = ((storewide_cs_f_selling + storewide_cos_f_selling) / 2);

	$("storewide_av_f_selling").innerHTML = ifzero(storewide_av_f_selling);

	//turn days
	var storewide_td_f_selling = (storewide_av_f_selling / storewide_acs_f_cost * turn_days);
	$("storewide_td_f_selling").innerHTML = iftdinfinity(storewide_td_f_selling);

}

function fresh_grand_total(root_id){

//alert($("acs_f_cost,"+root_id));
	if (!$("t_grn_f_selling,"+root_id)) return;

//Total fresh market
	//grn pending
	var t_grn_f_selling=0;
	$$('tbody.fresh_'+root_id+' .grn_f_selling_'+root_id).each(function(obj,ele){
		t_grn_f_selling = t_grn_f_selling + float(obj.value);
	});
	$("t_grn_f_selling,"+root_id).innerHTML = ifzero(t_grn_f_selling);

	//actual stock
	var t_aos_f_selling =  float($("t_aos_f_selling,"+root_id).innerHTML);
	var t_sr_f_selling =  float($("t_sr_f_selling,"+root_id).innerHTML);
	var t_adj_f_selling =  float($("t_adj_f_selling,"+root_id).innerHTML);
	var t_as_f_selling = t_aos_f_selling + t_sr_f_selling + t_grn_f_selling + t_adj_f_selling;

	$("t_as_f_selling,"+root_id).innerHTML = ifzero(t_as_f_selling);

	//IDT
		//selling
	var t_idt_f_selling=0;
	$$('tbody.fresh_'+root_id+' .idt_f_selling_'+root_id).each(function(obj,ele){
		t_idt_f_selling = t_idt_f_selling + float(obj.value);
	});

	$("t_idt_f_selling,"+root_id).innerHTML = ifzero(t_idt_f_selling);

	//closing stock
		//selling
	var t_cs_f_selling=0;
	$$('tbody.fresh_'+root_id+' .cs_f_selling_'+root_id).each(function(obj,ele){
		t_cs_f_selling = t_cs_f_selling + float(obj.value);
	});

	$("t_cs_f_selling,"+root_id).innerHTML = ifzero(t_cs_f_selling);

	//actual sales
	var t_acs_f_selling = float($("t_acs_f_selling,"+root_id).innerHTML)
    var t_acs_f_gp = (t_acs_f_selling - t_aos_f_selling - t_sr_f_selling - t_grn_f_selling + t_cs_f_selling) / t_acs_f_selling * 100;
    $("t_acs_f_gp,"+root_id).innerHTML = ifinfinity(t_acs_f_gp);

	var t_acs_f_cost = float(t_acs_f_selling * (100 - t_acs_f_gp) / 100);
	$("t_acs_f_cost,"+root_id).innerHTML = ifzero(t_acs_f_cost);

	//REBATE
		//selling
	var t_r_f_selling=0;
/*	$$('tbody.fresh_'+root_id+' .r_f_selling_'+root_id).each(function(obj,ele){
		t_r_f_selling = t_r_f_selling + float(obj.value);
	});

	$("t_r_f_selling,"+root_id).innerHTML = ifzero(t_r_f_selling);

		//gp
	var t_r_f_gp = (((t_acs_f_selling - t_acs_f_cost) + t_r_f_selling) / t_acs_f_selling * 100 );
	$("t_r_f_gp,"+root_id).innerHTML = ifinfinity(t_r_f_gp);
*/
	//OTHER INCOME
		//selling
	var t_oi_f_selling=0;
	$$('tbody.fresh_'+root_id+' .oi_f_selling_'+root_id).each(function(obj,ele){
		t_oi_f_selling = t_oi_f_selling + float(obj.value);
	});

	$("t_oi_f_selling,"+root_id).innerHTML = ifzero(t_oi_f_selling);

	//profit margin
	var t_pm_f_selling = t_acs_f_selling - t_acs_f_cost + t_r_f_selling + t_oi_f_selling;
	var t_pm_f_gp = ((t_pm_f_selling / t_acs_f_selling ) * 100 );

	$("t_pm_f_selling,"+root_id).innerHTML = ifzero(t_pm_f_selling);
	$("t_pm_f_gp,"+root_id).innerHTML = ifinfinity(t_pm_f_gp);

	//average stock
    var t_cos_f_selling = float($("t_cos_f_selling,"+root_id).innerHTML);

	var t_av_f_selling = ((t_cs_f_selling + t_cos_f_selling) / 2);

	$("t_av_f_selling,"+root_id).innerHTML = ifzero(t_av_f_selling);

	//turn days
	var t_td_f_selling = (t_av_f_selling / t_acs_f_cost * turn_days);
	$("t_td_f_selling,"+root_id).innerHTML = iftdinfinity(t_td_f_selling);

//GRAND Total fresh market
	//actual sales
	var dept_acs_t_cost = float($("dept_acs_t_cost,"+root_id).innerHTML);
	var dept_acs_t_selling = float($("dept_acs_t_selling,"+root_id).innerHTML);

    var dept_acs_f_cost = dept_acs_t_cost + t_acs_f_cost;
    var dept_acs_f_selling = dept_acs_t_selling + t_acs_f_selling;
    $("dept_acs_f_cost,"+root_id).innerHTML = ifzero(dept_acs_f_cost);
    $("dept_acs_f_selling,"+root_id).innerHTML = ifzero(dept_acs_f_selling);

	var dept_acs_f_gp = float((dept_acs_f_selling - dept_acs_f_cost) / dept_acs_f_selling * 100);
	$("dept_acs_f_gp,"+root_id).innerHTML = ifinfinity(dept_acs_f_gp);

/*	//rebate
	var dept_r_t_selling = float($("dept_r_t_selling,"+root_id).innerHTML);
	var r_f_selling = float($("t_r_f_selling,"+root_id).innerHTML);

	var dept_r_f_selling = dept_r_t_selling + r_f_selling;
	$("dept_r_f_selling,"+root_id).innerHTML = ifzero(dept_r_f_selling);

	var dept_r_f_gp = ((dept_acs_f_selling - dept_acs_f_cost) + dept_r_f_selling) / dept_acs_f_selling * 100;
	$("dept_r_f_gp,"+root_id).innerHTML = ifinfinity(dept_r_f_gp);
*/
	//other income
	var dept_oi_t_selling = float($("dept_oi_t_selling,"+root_id).innerHTML);
    var oi_f_selling = float($("t_oi_f_selling,"+root_id).innerHTML);

	var dept_oi_f_selling = dept_oi_t_selling + oi_f_selling;
	$("dept_oi_f_selling,"+root_id).innerHTML = ifzero(dept_oi_f_selling);

	//profit margin
	var dept_pm_t_selling = float($("dept_pm_t_selling,"+root_id).innerHTML);
    var pm_f_selling = float($("t_pm_f_selling,"+root_id).innerHTML);

	var dept_pm_f_selling = dept_pm_t_selling + pm_f_selling;
	$("dept_pm_f_selling,"+root_id).innerHTML = ifzero(dept_pm_f_selling);

	var dept_pm_f_gp = dept_pm_f_selling /  dept_acs_f_selling * 100;
	$("dept_pm_f_gp,"+root_id).innerHTML = ifinfinity(dept_pm_f_gp);

}

function vendor_changes(sku_type,root_id,category_id,vendor_id){

//vendor
	   //count by row
	if (sku_type == 'OUTRIGHT'){

//============================================>test mode start
/*
	// system opening stock
		var vos_o_cost = float($("vos_o_cost,"+category_id+","+vendor_id).innerHTML);
		var vos_o_selling = float($("vos_o_selling,"+category_id+","+vendor_id).innerHTML);
		var vos_o_gp = ((vos_o_selling - vos_o_cost) / vos_o_selling * 100);

		$("vos_o_gp,"+category_id+","+vendor_id).innerHTML= ifinfinity(vos_o_gp);


		// opening stock
		var vcos_o_cost = float($("vcos_o_cost,"+category_id+","+vendor_id).innerHTML);
		var vcos_o_selling = float($("vcos_o_selling,"+category_id+","+vendor_id).innerHTML);
		var vcos_o_gp = ((vcos_o_selling - vcos_o_cost) / vcos_o_selling * 100);

		$("vcos_o_gp,"+category_id+","+vendor_id).innerHTML= ifinfinity(vcos_o_gp);

		// stock take
		var vstv_o_cost = float($("vstv_o_cost,"+category_id+","+vendor_id).innerHTML);
		var vstv_o_selling = float($("vstv_o_selling,"+category_id+","+vendor_id).innerHTML);
		var vstv_o_gp = ((vstv_o_selling - vstv_o_cost) / vstv_o_selling * 100);

		$("vstv_o_gp,"+category_id+","+vendor_id).innerHTML= ifinfinity(vstv_o_gp);

		// actual opening stock
		var vaos_o_cost = float($("vaos_o_cost,"+category_id+","+vendor_id).innerHTML);
		var vaos_o_selling = float($("vaos_o_selling,"+category_id+","+vendor_id).innerHTML);
		var vaos_o_gp = ((vaos_o_selling - vaos_o_cost) / vaos_o_selling * 100);

		$("vaos_o_gp,"+category_id+","+vendor_id).innerHTML= ifinfinity(vaos_o_gp);

		//stock receive
		var vsr_o_cost = float($("vsr_o_cost,"+category_id+","+vendor_id).innerHTML);
		var vsr_o_selling =  float($("vsr_o_selling,"+category_id+","+vendor_id).innerHTML);
		var vsr_o_gp = ((vsr_o_selling - vsr_o_cost) / vsr_o_selling * 100);

		$("vsr_o_gp,"+category_id+","+vendor_id).innerHTML= ifinfinity(vsr_o_gp);

		//adjustment
		var vadj_o_cost = float($("vadj_o_cost,"+category_id+","+vendor_id).innerHTML);
		var vadj_o_selling =  float($("vadj_o_selling,"+category_id+","+vendor_id).innerHTML);
		var vadj_o_gp = ((vadj_o_selling - vadj_o_cost) / vadj_o_selling * 100);

		$("vadj_o_gp,"+category_id+","+vendor_id).innerHTML= ifinfinity(vadj_o_gp);

		//return stock
		var vrs_o_cost = float($("vrs_o_cost,"+category_id+","+vendor_id).innerHTML);
		var vrs_o_selling = float($("vrs_o_selling,"+category_id+","+vendor_id).innerHTML);
		var vrs_o_gp = ((vrs_o_selling - vrs_o_cost) / vrs_o_selling * 100);

		$("vrs_o_gp,"+category_id+","+vendor_id).innerHTML= ifinfinity(vrs_o_gp);
*/
//============================================>test mode end

   //grn adjustment
		var vgrn_o_cost = float($("vgrn_o_cost,"+category_id+","+vendor_id).value);
		var vgrn_o_selling =  float($("vgrn_o_selling,"+category_id+","+vendor_id).value);
		var vgrn_o_gp = ((vgrn_o_selling - vgrn_o_cost) / vgrn_o_selling * 100);
		$("vgrn_o_gp,"+category_id+","+vendor_id).innerHTML= ifinfinity(vgrn_o_gp);

		//actual stock
			var vaos_o_cost = float($("r_vaos_o_cost,"+category_id+","+vendor_id).innerHTML);
			var vaos_o_selling =  float($("r_vaos_o_selling,"+category_id+","+vendor_id).innerHTML);

			var vsr_o_cost = float($("r_vsr_o_cost,"+category_id+","+vendor_id).innerHTML);
			var vsr_o_selling =  float($("r_vsr_o_selling,"+category_id+","+vendor_id).innerHTML);

			var vadj_o_cost = float($("r_vadj_o_cost,"+category_id+","+vendor_id).innerHTML);
			var vadj_o_selling =  float($("r_vadj_o_selling,"+category_id+","+vendor_id).innerHTML);

		var vas_o_cost = vaos_o_cost + vsr_o_cost + vgrn_o_cost + vadj_o_cost;
		var vas_o_selling = vaos_o_selling + vsr_o_selling + vgrn_o_selling + vadj_o_selling;
		var vas_o_gp = ((vas_o_selling - vas_o_cost) / vas_o_selling * 100);

		$("vas_o_cost,"+category_id+","+vendor_id).innerHTML = ifzero(vas_o_cost);
		$("vas_o_selling,"+category_id+","+vendor_id).innerHTML = ifzero(vas_o_selling);
		$("vas_o_gp,"+category_id+","+vendor_id).innerHTML = ifinfinity(vas_o_gp);
		$("r_vas_o_cost,"+category_id+","+vendor_id).innerHTML = vas_o_cost;
		$("r_vas_o_selling,"+category_id+","+vendor_id).innerHTML = vas_o_selling;

		//rebate
		var vacs_o_cost = float($("r_vacs_o_cost,"+category_id+","+vendor_id).innerHTML);
		var vacs_o_selling = float($("r_vacs_o_selling,"+category_id+","+vendor_id).innerHTML);

		var vr_o_selling = 0;  
/*		var vr_o_selling =  float($("vr_o_selling,"+category_id+","+vendor_id).value);

		var vr_o_gp = (((vacs_o_selling - vacs_o_cost) + vr_o_selling) / vacs_o_selling * 100 );
		$("vr_o_gp,"+category_id+","+vendor_id).innerHTML = ifinfinity(vr_o_gp);
*/
		//closing stock
			var vrs_o_cost = float($("r_vrs_o_cost,"+category_id+","+vendor_id).innerHTML);
			var vrs_o_selling = float($("r_vrs_o_selling,"+category_id+","+vendor_id).innerHTML);

		var vidt_o_cost = float($("vidt_o_cost,"+category_id+","+vendor_id).value);
		var vidt_o_selling = float($("vidt_o_selling,"+category_id+","+vendor_id).value);
	    var vidt_o_gp = ((vidt_o_selling - vidt_o_cost) / vidt_o_selling * 100);
	    $("vidt_o_gp,"+category_id+","+vendor_id).innerHTML = ifinfinity(vidt_o_gp);

		var vpa_o_selling = float($("r_vpa_o_selling,"+category_id+","+vendor_id).innerHTML);
		var vpca_o_selling = float($("r_vpca_o_selling,"+category_id+","+vendor_id).innerHTML);

//============================================>test mode start
/*
		//promotion
		var vpa_o_gp = (vpa_o_selling / vacs_o_selling * 100);

		$("vpa_o_gp,"+category_id+","+vendor_id).innerHTML= ifinfinity(vpa_o_gp);

		//price change amount
		var vpca_o_gp = (vpca_o_selling / vacs_o_selling * 100);

		$("vpca_o_gp,"+category_id+","+vendor_id).innerHTML= ifinfinity(vpca_o_gp);

		//actual sales
		var vacs_o_gp = ((vacs_o_selling - vacs_o_cost) / vacs_o_selling * 100);

		$("vacs_o_gp,"+category_id+","+vendor_id).innerHTML= ifinfinity(vacs_o_gp);
*/
//============================================>test mode end

		var vcs_o_cost = vas_o_cost - vrs_o_cost + vidt_o_cost - vacs_o_cost;
		var vcs_o_selling = vas_o_selling - vrs_o_selling + vidt_o_selling - vpa_o_selling - vpca_o_selling - vacs_o_selling + vr_o_selling;
		var vcs_o_gp = ((vcs_o_selling - vcs_o_cost) / vcs_o_selling * 100);

		$("vcs_o_cost,"+category_id+","+vendor_id).innerHTML = ifzero(vcs_o_cost);
		$("vcs_o_selling,"+category_id+","+vendor_id).innerHTML = ifzero(vcs_o_selling);
		$("vcs_o_gp,"+category_id+","+vendor_id).innerHTML = ifinfinity(vcs_o_gp);
		$("r_vcs_o_cost,"+category_id+","+vendor_id).value = vcs_o_cost;
		$("r_vcs_o_selling,"+category_id+","+vendor_id).value = vcs_o_selling;

		//profit margin
		var voi_o_selling =  float($("voi_o_selling,"+category_id+","+vendor_id).value);
		var vpm_o_selling = vacs_o_selling - vacs_o_cost + vr_o_selling + voi_o_selling;
		var vpm_o_gp = ((vpm_o_selling / vacs_o_selling ) * 100 );

		$("vpm_o_selling,"+category_id+","+vendor_id).innerHTML = ifzero(vpm_o_selling);
		$("vpm_o_gp,"+category_id+","+vendor_id).innerHTML = ifinfinity(vpm_o_gp);

		//average stock
        var vcos_o_cost = float($("vcos_o_cost,"+category_id+","+vendor_id).innerHTML);

		var vav_o_selling = ((vcs_o_cost + vcos_o_cost) / 2);

		$("vav_o_selling,"+category_id+","+vendor_id).innerHTML = ifzero(vav_o_selling);

		//turn days
		var vtd_o_selling = (vav_o_selling / vacs_o_cost * turn_days);
		$("vtd_o_selling,"+category_id+","+vendor_id).innerHTML = iftdinfinity(vtd_o_selling);


	}else if(sku_type == 'CONSIGN'){
//vendor
    	//actual sales gp%
	    var vacs_c_gp = float($("vacs_c_gp,"+category_id+","+vendor_id).value.replace('%',''));

        $("vacs_c_gp,"+category_id+","+vendor_id).value = round(vacs_c_gp,2)+"%";

	    var vacs_c_selling = float($("r_vacs_c_selling,"+category_id+","+vendor_id).innerHTML);

		var vacs_c_cost = vacs_c_selling-(vacs_c_gp * vacs_c_selling / 100);
		
		$("vacs_c_cost,"+category_id+","+vendor_id).innerHTML = ifzero(vacs_c_cost);

//============================================>test mode start
//        var vpa_c_selling = $("vpa_c_selling,"+category_id+","+vendor_id).innerHTML;
//		var vpa_c_gp = (vpa_c_selling / vacs_c_selling * 100);
        
//		$("vpa_c_gp,"+category_id+","+vendor_id).innerHTML = ifinfinity(vpa_c_gp);
//============================================>test mode end

/*		//rebate
		var vr_c_selling =  float($("vr_c_selling,"+category_id+","+vendor_id).value);
		var vr_c_gp = (((vacs_c_selling - vacs_c_cost) + vr_c_selling) / vacs_c_selling * 100 );
		$("vr_c_gp,"+category_id+","+vendor_id).innerHTML = ifinfinity(vr_c_gp);
*/
		//profit margin
		var voi_c_selling =  float($("voi_c_selling,"+category_id+","+vendor_id).value);
		var vpm_c_selling = vacs_c_selling - vacs_c_cost + voi_c_selling;
		var vpm_c_gp = ((vpm_c_selling / vacs_c_selling ) * 100 );

		$("vpm_c_selling,"+category_id+","+vendor_id).innerHTML = ifzero(vpm_c_selling);
		$("vpm_c_gp,"+category_id+","+vendor_id).innerHTML = ifinfinity(vpm_c_gp);

	}
	//count total
	category_type_total(sku_type,root_id,category_id);
}

function category_type_total(sku_type,root_id,category_id){
    if(sku_type == 'OUTRIGHT'){
//category
	//count category outright
		//GRN ADJUSTMENT
		    //cost
		var total_vgrn_o_cost=0;
		$$('#outright_'+category_id+' input.vgrn_o_cost_'+category_id).each(function(obj,ele){
			total_vgrn_o_cost = total_vgrn_o_cost + float(obj.value);
		});

		$("grn_o_cost,"+category_id).innerHTML = ifzero(total_vgrn_o_cost);
		$("grn_t_cost,"+category_id).innerHTML = ifzero(total_vgrn_o_cost);

			//selling
		var total_vgrn_o_selling=0;
		$$('#outright_'+category_id+' input.vgrn_o_selling_'+category_id).each(function(obj,ele){
			total_vgrn_o_selling = total_vgrn_o_selling + float(obj.value);
		});

		$("grn_o_selling,"+category_id).innerHTML = ifzero(total_vgrn_o_selling);
		$("grn_t_selling,"+category_id).innerHTML = ifzero(total_vgrn_o_selling);

			//gp
		var grn_o_gp= ((total_vgrn_o_selling - total_vgrn_o_cost) / total_vgrn_o_selling * 100);
		$("grn_o_gp,"+category_id).innerHTML = ifinfinity(grn_o_gp);
		$("grn_t_gp,"+category_id).innerHTML = ifinfinity(grn_o_gp);

		//actual stock
		var total_vaos_o_cost = float($("r_aos_o_cost,"+category_id).innerHTML);
		var total_vaos_o_selling =  float($("r_aos_o_selling,"+category_id).innerHTML);

		var total_vsr_o_cost = float($("r_sr_o_cost,"+category_id).innerHTML);
		var total_vsr_o_selling =  float($("r_sr_o_selling,"+category_id).innerHTML);

		var total_vadj_o_cost = float($("r_adj_o_cost,"+category_id).innerHTML);
		var total_vadj_o_selling =  float($("r_adj_o_selling,"+category_id).innerHTML);

		var total_vas_o_cost = total_vaos_o_cost + total_vsr_o_cost + total_vgrn_o_cost + total_vadj_o_cost;
		var total_vas_o_selling = total_vaos_o_selling + total_vsr_o_selling + total_vgrn_o_selling + total_vadj_o_selling;

		var as_o_gp = ((total_vas_o_selling - total_vas_o_cost) / total_vas_o_selling * 100);

		$("as_o_cost,"+category_id).innerHTML = ifzero(total_vas_o_cost);
		$("as_o_selling,"+category_id).innerHTML = ifzero(total_vas_o_selling);
		$("r_as_o_cost,"+category_id).innerHTML = total_vas_o_cost;
		$("r_as_o_selling,"+category_id).innerHTML = total_vas_o_selling;
		$("as_o_gp,"+category_id).innerHTML = ifinfinity(as_o_gp);

		$("as_t_cost,"+category_id).innerHTML = ifzero(total_vas_o_cost);
		$("as_t_selling,"+category_id).innerHTML = ifzero(total_vas_o_selling);
		$("r_as_t_cost,"+category_id).innerHTML = total_vas_o_cost;
		$("r_as_t_selling,"+category_id).innerHTML = total_vas_o_selling;
		$("as_t_gp,"+category_id).innerHTML = ifinfinity(as_o_gp);

		//IDT
			//cost
		var total_vidt_o_cost=0;
		$$('#outright_'+category_id+' input.vidt_o_cost_'+category_id).each(function(obj,ele){
			total_vidt_o_cost = total_vidt_o_cost + float(obj.value);
		});

		$("idt_o_cost,"+category_id).innerHTML = ifzero(total_vidt_o_cost);
		$("idt_t_cost,"+category_id).innerHTML = ifzero(total_vidt_o_cost);

			//selling
		var total_vidt_o_selling=0;
		$$('#outright_'+category_id+' input.vidt_o_selling_'+category_id).each(function(obj,ele){
			total_vidt_o_selling = total_vidt_o_selling + float(obj.value);
		});

		$("idt_o_selling,"+category_id).innerHTML = ifzero(total_vidt_o_selling);
		$("idt_t_selling,"+category_id).innerHTML = ifzero(total_vidt_o_selling);

			//gp
		var idt_o_gp = ((total_vidt_o_selling - total_vidt_o_cost) / total_vidt_o_selling  * 100 );
		$("idt_o_gp,"+category_id).innerHTML = ifinfinity(idt_o_gp);
		$("idt_t_gp,"+category_id).innerHTML = ifinfinity(idt_o_gp);

		//rebate	no use anymore
			//selling

		var total_vr_o_selling=0;
/*		$$('#outright_'+category_id+' input.vr_o_selling_'+category_id).each(function(obj,ele){
			total_vr_o_selling = total_vr_o_selling + float(obj.value);
		});
		$("r_o_selling,"+category_id).innerHTML = ifzero(total_vr_o_selling);
*/
			//gp
		var total_vacs_o_cost = float($("r_acs_o_cost,"+category_id).innerHTML);
		var total_vacs_o_selling = float($("r_acs_o_selling,"+category_id).innerHTML)
/*
		var r_o_gp = (((total_vacs_o_selling - total_vacs_o_cost) + total_vr_o_selling) / total_vacs_o_selling * 100 );
		$("r_o_gp,"+category_id).innerHTML = ifinfinity(r_o_gp);
*/
		//closing stock
		var total_vrs_o_cost = float($("r_rs_o_cost,"+category_id).innerHTML);
		var total_vrs_o_selling = float($("r_rs_o_selling,"+category_id).innerHTML);

		var total_vpa_o_selling = float($("r_pa_o_selling,"+category_id).innerHTML);
		var total_vpca_o_selling = float($("r_pca_o_selling,"+category_id).innerHTML);

		var total_vcs_o_cost = total_vas_o_cost - total_vrs_o_cost + total_vidt_o_cost - total_vacs_o_cost;
		var total_vcs_o_selling = total_vas_o_selling - total_vrs_o_selling + total_vidt_o_selling - total_vpa_o_selling - total_vpca_o_selling - total_vacs_o_selling + total_vr_o_selling;
		var total_vcs_o_gp = ((total_vcs_o_selling - total_vcs_o_cost) / total_vcs_o_selling * 100);

		$("cs_o_cost,"+category_id).innerHTML = ifzero(total_vcs_o_cost);
		$("cs_o_selling,"+category_id).innerHTML = ifzero(total_vcs_o_selling);
		$("r_cs_o_cost,"+category_id).innerHTML = total_vcs_o_cost;
		$("r_cs_o_selling,"+category_id).innerHTML = total_vcs_o_selling;
		$("cs_o_gp,"+category_id).innerHTML = ifinfinity(total_vcs_o_gp);

		//other income
			//selling
		var total_voi_o_selling=0;
		$$('#outright_'+category_id+' input.voi_o_selling_'+category_id).each(function(obj,ele){
			total_voi_o_selling = total_voi_o_selling + float(obj.value);
		});

		$("oi_o_selling,"+category_id).innerHTML = ifzero(total_voi_o_selling);

		//profit margin
		var total_vpm_o_selling = total_vacs_o_selling - total_vacs_o_cost + total_vr_o_selling + total_voi_o_selling;
		var pm_o_gp = ((total_vpm_o_selling / total_vacs_o_selling ) * 100 );

		$("pm_o_selling,"+category_id).innerHTML = ifzero(total_vpm_o_selling);
		$("pm_o_gp,"+category_id).innerHTML = ifinfinity(pm_o_gp);

		//average stock
        var total_vcos_o_cost = float($("cos_o_cost,"+category_id).innerHTML);

		var total_vav_o_selling = ((total_vcs_o_cost + total_vcos_o_cost) / 2);

		$("av_o_selling,"+category_id).innerHTML = ifzero(total_vav_o_selling);

		//turn days
		var total_vtd_o_selling = (total_vav_o_selling / total_vacs_o_cost * turn_days);
		$("td_o_selling,"+category_id).innerHTML = iftdinfinity(total_vtd_o_selling);

	}else if(sku_type == 'CONSIGN'){
//category
	//count category consign
		//actual sales
		    //cost
		var total_vacs_c_cost=0;
		$$('#consign_'+category_id+' td.vacs_c_cost_'+category_id).each(function(obj,ele){
			total_vacs_c_cost = total_vacs_c_cost + float(obj.innerHTML);
		});
			//selling
		$("acs_c_cost,"+category_id).innerHTML = ifzero(total_vacs_c_cost);
		var total_vacs_c_selling = float($("r_acs_c_selling,"+category_id).innerHTML);
			//GP
	    var acs_c_gp = float((total_vacs_c_selling - total_vacs_c_cost) / total_vacs_c_selling * 100);
		$("acs_c_gp,"+category_id).innerHTML = ifinfinity(acs_c_gp);

/*		//rebate
			//selling
		var total_vr_c_selling=0;
		$$('#consign_'+category_id+' input.vr_c_selling_'+category_id).each(function(obj,ele){
			total_vr_c_selling = total_vr_c_selling + float(obj.value);
		});

		$("r_c_selling,"+category_id).innerHTML = ifzero(total_vr_c_selling);
            //GP
		var r_c_gp = (((total_vacs_c_selling - total_vacs_c_cost) + total_vr_c_selling) / total_vacs_c_selling * 100 )
		$("r_c_gp,"+category_id).innerHTML = ifinfinity(r_c_gp);
*/
		//other income
			//selling
		var total_voi_c_selling=0;
		$$('#consign_'+category_id+' input.voi_c_selling_'+category_id).each(function(obj,ele){
			total_voi_c_selling = total_voi_c_selling + float(obj.value);
		});

		$("oi_c_selling,"+category_id).innerHTML = ifzero(total_voi_c_selling);

		//profit margin
		var total_vpm_c_selling = total_vacs_c_selling - total_vacs_c_cost + total_voi_c_selling;
		var pm_c_gp = ((total_vpm_c_selling / total_vacs_c_selling ) * 100 );

		$("pm_c_selling,"+category_id).innerHTML = ifzero(total_vpm_c_selling);
		$("pm_c_gp,"+category_id).innerHTML = ifinfinity(pm_c_gp);
	}
 	cat_total(category_id);
    department_changes(sku_type,root_id)

}

function cat_total(category_id){

//count by category total
	if ($("acs_o_cost,"+category_id) && $("acs_c_cost,"+category_id)){
	    //got outright and consign
		var acs_t_cost= float($("r_acs_o_cost,"+category_id).innerHTML) + float($("acs_c_cost,"+category_id).innerHTML);
		var acs_t_selling= float($("r_acs_o_selling,"+category_id).innerHTML) + float($("r_acs_c_selling,"+category_id).innerHTML);
//		var r_t_selling= float($("r_o_selling,"+category_id).innerHTML) + float($("r_c_selling,"+category_id).innerHTML);
		var oi_t_selling= float($("oi_o_selling,"+category_id).innerHTML) + float($("oi_c_selling,"+category_id).innerHTML);
	}else if (!$("acs_c_cost,"+category_id)){
		//OUTRIGHT only
		var acs_t_cost= float($("r_acs_o_cost,"+category_id).innerHTML);
		var acs_t_selling= float($("r_acs_o_selling,"+category_id).innerHTML);
//		var r_t_selling= float($("r_o_selling,"+category_id).innerHTML);
		var oi_t_selling= float($("oi_o_selling,"+category_id).innerHTML);
	}else if (!$("acs_o_cost,"+category_id)){
	    //CONSIGN only
		var acs_t_cost= float($("acs_c_cost,"+category_id).innerHTML);
		var acs_t_selling= float($("r_acs_c_selling,"+category_id).innerHTML);
//		var r_t_selling= float($("r_c_selling,"+category_id).innerHTML);
		var oi_t_selling= float($("oi_c_selling,"+category_id).innerHTML);
	}

	var r_t_selling=0;

	//actual stock
	var aos_t_cost = float($("r_aos_t_cost,"+category_id).innerHTML);
	var aos_t_selling =  float($("r_aos_t_selling,"+category_id).innerHTML);

	var sr_t_cost = float($("r_sr_t_cost,"+category_id).innerHTML);
	var sr_t_selling =  float($("r_sr_t_selling,"+category_id).innerHTML);

	var grn_t_cost = float($("grn_t_cost,"+category_id).innerHTML);
	var grn_t_selling = float($("grn_t_selling,"+category_id).innerHTML);

	var adj_t_cost = float($("r_adj_t_cost,"+category_id).innerHTML);
	var adj_t_selling =  float($("r_adj_t_selling,"+category_id).innerHTML);

	var idt_t_cost = float($("idt_t_cost,"+category_id).innerHTML);
	var idt_t_selling =  float($("idt_t_selling,"+category_id).innerHTML);

	var as_t_cost = aos_t_cost + sr_t_cost + grn_t_cost + adj_t_cost;
	var as_t_selling = aos_t_selling + sr_t_selling + grn_t_selling + adj_t_selling;
	var as_t_gp = ((as_t_selling - as_t_cost) / as_t_selling * 100);

	$("as_t_cost,"+category_id).innerHTML = ifzero(as_t_cost);
	$("as_t_selling,"+category_id).innerHTML = ifzero(as_t_selling);
	$("r_as_t_cost,"+category_id).innerHTML = as_t_cost;
	$("r_as_t_selling,"+category_id).innerHTML = as_t_selling;
	$("as_t_gp,"+category_id).innerHTML = ifinfinity(as_t_gp);

	//actual sales
	$("acs_t_cost,"+category_id).innerHTML = ifzero(acs_t_cost);
	$("acs_t_selling,"+category_id).innerHTML = ifzero(acs_t_selling);
	$("r_acs_t_cost,"+category_id).innerHTML = acs_t_cost;
	$("r_acs_t_selling,"+category_id).innerHTML = acs_t_selling;

    var acs_t_gp = ((acs_t_selling - acs_t_cost) / acs_t_selling * 100);
	$("acs_t_gp,"+category_id).innerHTML = ifinfinity(acs_t_gp);

/*	//rebate
	$("r_t_selling,"+category_id).innerHTML = ifzero(r_t_selling);

	var r_t_gp = ((acs_t_selling - acs_t_cost + r_t_selling) / acs_t_selling * 100);;
	$("r_t_gp,"+category_id).innerHTML = ifinfinity(r_t_gp);
*/
	//closing stock
	var rs_t_cost = float($("r_rs_t_cost,"+category_id).innerHTML);
	var rs_t_selling = float($("r_rs_t_selling,"+category_id).innerHTML);

	var pa_t_selling = float($("r_pa_t_selling,"+category_id).innerHTML);
	var pca_t_selling = float($("r_pca_t_selling,"+category_id).innerHTML);

	var cs_t_cost = as_t_cost - rs_t_cost + idt_t_cost - acs_t_cost;
	$("cs_t_cost,"+category_id).innerHTML = ifzero(cs_t_cost);
	$("r_cs_t_cost,"+category_id).innerHTML = cs_t_cost;

	var cs_t_selling = as_t_selling - rs_t_selling + idt_t_selling - pa_t_selling - pca_t_selling - acs_t_selling + r_t_selling;
	$("cs_t_selling,"+category_id).innerHTML = ifzero(cs_t_selling);
	$("r_cs_t_selling,"+category_id).innerHTML = cs_t_selling;

	var cs_t_gp = ((cs_t_selling - cs_t_cost) / cs_t_selling * 100);
	$("cs_t_gp,"+category_id).innerHTML = ifinfinity(cs_t_gp);

	//other income
	$("oi_t_selling,"+category_id).innerHTML = ifzero(oi_t_selling);
	
	//profit margin
	var pm_t_selling = acs_t_selling - acs_t_cost + r_t_selling + oi_t_selling;
	$("pm_t_selling,"+category_id).innerHTML = ifzero(pm_t_selling);

	var pm_t_gp = ((pm_t_selling / acs_t_selling ) * 100 );
	$("pm_t_gp,"+category_id).innerHTML = ifinfinity(pm_t_gp);

	//average stock
    var cos_t_cost = float($("cos_t_cost,"+category_id).innerHTML);
	var av_t_selling = ((cs_t_cost + cos_t_cost) / 2);

	$("av_t_selling,"+category_id).innerHTML = ifzero(av_t_selling);

	//turn days
	var td_t_selling= (av_t_selling / acs_t_cost * turn_days);
	$("td_t_selling,"+category_id).innerHTML = iftdinfinity(td_t_selling);
}

function department_changes(sku_type,root_id){
//count each department outright, consign and total
	if (sku_type == 'OUTRIGHT'){
	//OUTRIGHT
		//GRN ADJUSTMENT
		var dept_grn_o_cost=0;
		$$('tbody.dept_outright_'+root_id+' .grn_o_cost_'+root_id).each(function(obj,ele){
			dept_grn_o_cost = dept_grn_o_cost + float(obj.innerHTML);
		});

		$("dept_grn_o_cost,"+root_id).innerHTML = ifzero(dept_grn_o_cost);
		$("dept_grn_t_cost,"+root_id).innerHTML = ifzero(dept_grn_o_cost);

			//selling
		var dept_grn_o_selling=0;
		$$('tbody.dept_outright_'+root_id+' .grn_o_selling_'+root_id).each(function(obj,ele){

			dept_grn_o_selling = dept_grn_o_selling + float(obj.innerHTML);
		});

		$("dept_grn_o_selling,"+root_id).innerHTML = ifzero(dept_grn_o_selling);
		$("dept_grn_t_selling,"+root_id).innerHTML = ifzero(dept_grn_o_selling);

			//gp
		var dept_grn_o_gp= ((dept_grn_o_selling - dept_grn_o_cost) / dept_grn_o_selling * 100);
		$("dept_grn_o_gp,"+root_id).innerHTML = ifinfinity(dept_grn_o_gp);
		$("dept_grn_t_gp,"+root_id).innerHTML = ifinfinity(dept_grn_o_gp);

		//actual stock
		var dept_aos_o_cost = float($("dept_aos_o_cost,"+root_id).innerHTML);
		var dept_aos_o_selling =  float($("dept_aos_o_selling,"+root_id).innerHTML);

		var dept_sr_o_cost = float($("dept_sr_o_cost,"+root_id).innerHTML);
		var dept_sr_o_selling =  float($("dept_sr_o_selling,"+root_id).innerHTML);

		var dept_adj_o_cost = float($("dept_adj_o_cost,"+root_id).innerHTML);
		var dept_adj_o_selling =  float($("dept_adj_o_selling,"+root_id).innerHTML);

		var dept_as_o_cost = dept_aos_o_cost + dept_sr_o_cost + dept_grn_o_cost + dept_adj_o_cost;
		var dept_as_o_selling = dept_aos_o_selling + dept_sr_o_selling + dept_grn_o_selling + dept_adj_o_selling;
		var dept_as_o_gp = ((dept_as_o_selling - dept_as_o_cost) / dept_as_o_selling * 100);

		$("dept_as_o_cost,"+root_id).innerHTML = ifzero(dept_as_o_cost);
		$("dept_as_o_selling,"+root_id).innerHTML = ifzero(dept_as_o_selling);
		$("dept_as_o_gp,"+root_id).innerHTML = ifinfinity(dept_as_o_gp);
		$("dept_as_t_cost,"+root_id).innerHTML = ifzero(dept_as_o_cost);
		$("dept_as_t_selling,"+root_id).innerHTML = ifzero(dept_as_o_selling);
		$("dept_as_t_gp,"+root_id).innerHTML = ifinfinity(dept_as_o_gp);

		//IDT
		var dept_idt_o_cost=0;
		$$('tbody.dept_outright_'+root_id+' .idt_o_cost_'+root_id).each(function(obj,ele){
			dept_idt_o_cost = dept_idt_o_cost + float(obj.innerHTML);
		});

		$("dept_idt_o_cost,"+root_id).innerHTML = ifzero(dept_idt_o_cost);
		$("dept_idt_t_cost,"+root_id).innerHTML = ifzero(dept_idt_o_cost);

			//selling
		var dept_idt_o_selling=0;
		$$('tbody.dept_outright_'+root_id+' .idt_o_selling_'+root_id).each(function(obj,ele){

			dept_idt_o_selling = dept_idt_o_selling + float(obj.innerHTML);
		});

		$("dept_idt_o_selling,"+root_id).innerHTML = ifzero(dept_idt_o_selling);
		$("dept_idt_t_selling,"+root_id).innerHTML = ifzero(dept_idt_o_selling);

			//gp
		var dept_idt_o_gp= ((dept_idt_o_selling - dept_idt_o_cost) / dept_idt_o_selling * 100);
		$("dept_idt_o_gp,"+root_id).innerHTML = ifinfinity(dept_idt_o_gp);
		$("dept_idt_t_gp,"+root_id).innerHTML = ifinfinity(dept_idt_o_gp);

		//REBATE
			//selling
		var dept_r_o_selling=0;
/*		$$('tbody.dept_outright_'+root_id+' .r_o_selling_'+root_id).each(function(obj,ele){
			dept_r_o_selling = dept_r_o_selling + float(obj.innerHTML);
		});

		$("dept_r_o_selling,"+root_id).innerHTML = ifzero(dept_r_o_selling);
*/
			//gp
		var dept_acs_o_cost = float($("dept_acs_o_cost,"+root_id).innerHTML);
		var dept_acs_o_selling = float($("dept_acs_o_selling,"+root_id).innerHTML)
/*
		var dept_r_o_gp = (((dept_acs_o_selling - dept_acs_o_cost) + dept_r_o_selling) / dept_acs_o_selling * 100 );
		$("dept_r_o_gp,"+root_id).innerHTML = ifinfinity(dept_r_o_gp);
*/
		//closing stock
		var dept_rs_o_cost = float($("dept_rs_o_cost,"+root_id).innerHTML);
		var dept_rs_o_selling = float($("dept_rs_o_selling,"+root_id).innerHTML);

		var dept_cs_o_cost = dept_as_o_cost - dept_rs_o_cost + dept_idt_o_cost - dept_acs_o_cost;
		var dept_cs_o_selling = dept_as_o_selling - dept_rs_o_selling + dept_idt_o_selling - dept_acs_o_selling + dept_r_o_selling;
		var dept_cs_o_gp = ((dept_cs_o_selling - dept_cs_o_cost) / dept_cs_o_selling * 100);

		$("dept_cs_o_cost,"+root_id).innerHTML = ifzero(dept_cs_o_cost);
		$("dept_cs_o_selling,"+root_id).innerHTML = ifzero(dept_cs_o_selling);
		$("dept_cs_o_gp,"+root_id).innerHTML = ifinfinity(dept_cs_o_gp);

		//OTHER INCOME
			//selling
		var dept_oi_o_selling=0;
		$$('tbody.dept_outright_'+root_id+' .oi_o_selling_'+root_id).each(function(obj,ele){

			dept_oi_o_selling = dept_oi_o_selling + float(obj.innerHTML);
		});

		$("dept_oi_o_selling,"+root_id).innerHTML = ifzero(dept_oi_o_selling);

		//profit margin
		var dept_pm_o_selling = dept_acs_o_selling - dept_acs_o_cost + dept_r_o_selling + dept_oi_o_selling;
		var dept_pm_o_gp = ((dept_pm_o_selling / dept_acs_o_selling ) * 100 );

		$("dept_pm_o_selling,"+root_id).innerHTML = ifzero(dept_pm_o_selling);
		$("dept_pm_o_gp,"+root_id).innerHTML = ifinfinity(dept_pm_o_gp);

		//average stock
        var dept_cos_o_cost = float($("dept_cos_o_cost,"+root_id).innerHTML);

		var dept_av_o_selling = ((dept_cs_o_cost + dept_cos_o_cost) / 2);

		$("dept_av_o_selling,"+root_id).innerHTML = ifzero(dept_av_o_selling);

		//turn days
		var dept_td_o_selling = (dept_av_o_selling / dept_acs_o_cost * turn_days);
		$("dept_td_o_selling,"+root_id).innerHTML = iftdinfinity(dept_td_o_selling);

	}else if (sku_type == 'CONSIGN'){
	//CONSIGN
		//actual sales
		    //cost
		var dept_acs_c_cost=0;
		$$('tbody.dept_consign_'+root_id+' .acs_c_cost_'+root_id).each(function(obj,ele){
			dept_acs_c_cost = dept_acs_c_cost + float(obj.innerHTML);
		});
			//selling
		$("dept_acs_c_cost,"+root_id).innerHTML = ifzero(dept_acs_c_cost);
		var dept_acs_c_selling = $("dept_acs_c_selling,"+root_id).innerHTML
			//GP
	    var dept_acs_c_gp = ((dept_acs_c_selling - dept_acs_c_cost) / dept_acs_c_selling * 100);
		$("dept_acs_c_gp,"+root_id).innerHTML = ifinfinity(dept_acs_c_gp);

/*		//rebate
			//selling
		var dept_r_c_selling=0;
		$$('tbody.dept_consign_'+root_id+' .r_c_selling_'+root_id).each(function(obj,ele){
			dept_r_c_selling = dept_r_c_selling + float(obj.innerHTML);
		});

		$("dept_r_c_selling,"+root_id).innerHTML = ifzero(dept_r_c_selling);
            //GP
		var dept_r_c_gp = (((dept_acs_c_selling - dept_acs_c_cost) + dept_r_c_selling) / dept_acs_c_selling * 100 )
		$("dept_r_c_gp,"+root_id).innerHTML = ifinfinity(dept_r_c_gp);
*/
		//other income
			//selling
		var dept_oi_c_selling=0;
		$$('tbody.dept_consign_'+root_id+' .oi_c_selling_'+root_id).each(function(obj,ele){
			dept_oi_c_selling = dept_oi_c_selling + float(obj.innerHTML);
		});

		$("dept_oi_c_selling,"+root_id).innerHTML = ifzero(dept_oi_c_selling);

		//profit margin
		var dept_pm_c_selling = dept_acs_c_selling - dept_acs_c_cost + dept_oi_c_selling;
		var dept_pm_c_gp = ((dept_pm_c_selling / dept_acs_c_selling ) * 100 );

		$("dept_pm_c_selling,"+root_id).innerHTML = ifzero(dept_pm_c_selling);
		$("dept_pm_c_gp,"+root_id).innerHTML = ifinfinity(dept_pm_c_gp);


	}
	//total
	dept_total(root_id);
	storewide_changes(sku_type);
}

function dept_total(root_id){
	//TOTAL
	if ($("dept_acs_o_cost,"+root_id) && $("dept_acs_c_cost,"+root_id)){
	    //got outright and consign
		var dept_acs_t_cost= float($("dept_acs_o_cost,"+root_id).innerHTML) + float($("dept_acs_c_cost,"+root_id).innerHTML);
		var dept_acs_t_selling= float($("dept_acs_o_selling,"+root_id).innerHTML) + float($("dept_acs_c_selling,"+root_id).innerHTML);
//		var dept_r_t_selling= float($("dept_r_o_selling,"+root_id).innerHTML) + float($("dept_r_c_selling,"+root_id).innerHTML);
		var dept_oi_t_selling= float($("dept_oi_o_selling,"+root_id).innerHTML) + float($("dept_oi_c_selling,"+root_id).innerHTML);
	}else if (!$("dept_acs_c_cost,"+root_id)){
		//dun have consign
		var dept_acs_t_cost= float($("dept_acs_o_cost,"+root_id).innerHTML);
		var dept_acs_t_selling= float($("dept_acs_o_selling,"+root_id).innerHTML);
//		var dept_r_t_selling= float($("dept_r_o_selling,"+root_id).innerHTML);
		var dept_oi_t_selling= float($("dept_oi_o_selling,"+root_id).innerHTML);
	}else if (!$("dept_acs_o_cost,"+root_id)){
	    //dun have outright
		var dept_acs_t_cost= float($("dept_acs_c_cost,"+root_id).innerHTML);
		var dept_acs_t_selling= float($("dept_acs_c_selling,"+root_id).innerHTML);
//		var dept_r_t_selling= float($("dept_r_c_selling,"+root_id).innerHTML);
		var dept_oi_t_selling= float($("dept_oi_c_selling,"+root_id).innerHTML);
	}

	var dept_r_t_selling=0;

	//actual stock
	var dept_aos_t_cost = float($("dept_aos_t_cost,"+root_id).innerHTML);
	var dept_aos_t_selling =  float($("dept_aos_t_selling,"+root_id).innerHTML);

	var dept_sr_t_cost = float($("dept_sr_t_cost,"+root_id).innerHTML);
	var dept_sr_t_selling =  float($("dept_sr_t_selling,"+root_id).innerHTML);

	var dept_grn_t_cost = float($("dept_grn_t_cost,"+root_id).innerHTML);
	var dept_grn_t_selling = float($("dept_grn_t_selling,"+root_id).innerHTML);

	var dept_adj_t_cost = float($("dept_adj_t_cost,"+root_id).innerHTML);
	var dept_adj_t_selling =  float($("dept_adj_t_selling,"+root_id).innerHTML);

	var dept_idt_t_cost = float($("dept_idt_t_cost,"+root_id).innerHTML);
	var dept_idt_t_selling =  float($("dept_idt_t_selling,"+root_id).innerHTML);

	var dept_as_t_cost = dept_aos_t_cost + dept_sr_t_cost + dept_grn_t_cost + dept_adj_t_cost;
	var dept_as_t_selling = dept_aos_t_selling + dept_sr_t_selling + dept_grn_t_selling + dept_adj_t_selling;
	var dept_as_t_gp = ((dept_as_t_selling - dept_as_t_cost) / dept_as_t_selling * 100);

	$("dept_as_t_cost,"+root_id).innerHTML = ifzero(dept_as_t_cost);
	$("dept_as_t_selling,"+root_id).innerHTML = ifzero(dept_as_t_selling);
	$("dept_as_t_gp,"+root_id).innerHTML = ifinfinity(dept_as_t_gp);

	//actual sales
	$("dept_acs_t_cost,"+root_id).innerHTML = ifzero(dept_acs_t_cost);
	$("dept_acs_t_selling,"+root_id).innerHTML = ifzero(dept_acs_t_selling);

    var dept_acs_t_gp = ((dept_acs_t_selling - dept_acs_t_cost) / dept_acs_t_selling * 100);
	$("dept_acs_t_gp,"+root_id).innerHTML = ifinfinity(dept_acs_t_gp);

	//rebate
//	$("dept_r_t_selling,"+root_id).innerHTML = ifzero(dept_r_t_selling);
/*
	var dept_r_t_gp = ((dept_acs_t_selling - dept_acs_t_cost + dept_r_t_selling) / dept_acs_t_selling * 100);;
	$("dept_r_t_gp,"+root_id).innerHTML = ifinfinity(dept_r_t_gp);
*/
	//closing stock
	var dept_rs_t_cost = float($("dept_rs_t_cost,"+root_id).innerHTML);
	var dept_rs_t_selling = float($("dept_rs_t_selling,"+root_id).innerHTML);

	var dept_pa_t_selling = float($("dept_pa_t_selling,"+root_id).innerHTML);
	var dept_pca_t_selling = float($("dept_pca_t_selling,"+root_id).innerHTML);

	var dept_cs_t_cost = dept_as_t_cost - dept_rs_t_cost + dept_idt_t_cost - dept_acs_t_cost;
	$("dept_cs_t_cost,"+root_id).innerHTML = ifzero(dept_cs_t_cost);

	var dept_cs_t_selling = dept_as_t_selling - dept_rs_t_selling + dept_idt_t_selling - dept_pa_t_selling - dept_pca_t_selling - dept_acs_t_selling + dept_r_t_selling;
	$("dept_cs_t_selling,"+root_id).innerHTML = ifzero(dept_cs_t_selling);

	var dept_cs_t_gp = ((dept_cs_t_selling - dept_cs_t_cost) / dept_cs_t_selling * 100);
	$("dept_cs_t_gp,"+root_id).innerHTML = ifinfinity(dept_cs_t_gp);

	//other income
	$("dept_oi_t_selling,"+root_id).innerHTML = ifzero(dept_oi_t_selling);

	//profit margin
	var dept_pm_t_selling = dept_acs_t_selling - dept_acs_t_cost + dept_r_t_selling + dept_oi_t_selling;
	$("dept_pm_t_selling,"+root_id).innerHTML = ifzero(dept_pm_t_selling);

	var dept_pm_t_gp = ((dept_pm_t_selling / dept_acs_t_selling ) * 100 );
	$("dept_pm_t_gp,"+root_id).innerHTML = ifinfinity(dept_pm_t_gp);

	//average stock
    var dept_cos_t_cost = float($("dept_cos_t_cost,"+root_id).innerHTML);
	var dept_av_t_selling = ((dept_cs_t_cost + dept_cos_t_cost) / 2);

	$("dept_av_t_selling,"+root_id).innerHTML = ifzero(dept_av_t_selling);

	//turn days
	var dept_td_t_selling= (dept_av_t_selling / dept_acs_t_cost * turn_days);
	$("dept_td_t_selling,"+root_id).innerHTML = iftdinfinity(dept_td_t_selling);

//add calc to grand total profit
    fresh_grand_total(root_id);
}

function storewide_changes(sku_type){
//count each department outright, consign and total
	if (sku_type == 'OUTRIGHT'){
	//OUTRIGHT
		//GRN ADJUSTMENT
		var storewide_grn_o_cost=0;
		$$('tbody.storewide_outright .dept_grn_o_cost').each(function(obj,ele){
			storewide_grn_o_cost = storewide_grn_o_cost + float(obj.innerHTML);
		});

		$("storewide_grn_o_cost").innerHTML = ifzero(storewide_grn_o_cost);
		$("storewide_grn_t_cost").innerHTML = ifzero(storewide_grn_o_cost);

			//selling
		var storewide_grn_o_selling=0;
		$$('tbody.storewide_outright .dept_grn_o_selling').each(function(obj,ele){

			storewide_grn_o_selling = storewide_grn_o_selling + float(obj.innerHTML);
		});

		$("storewide_grn_o_selling").innerHTML = ifzero(storewide_grn_o_selling);
		$("storewide_grn_t_selling").innerHTML = ifzero(storewide_grn_o_selling);

			//gp
		var storewide_grn_o_gp= ((storewide_grn_o_selling - storewide_grn_o_cost) / storewide_grn_o_selling * 100);
		$("storewide_grn_o_gp").innerHTML = ifinfinity(storewide_grn_o_gp);
		$("storewide_grn_t_gp").innerHTML = ifinfinity(storewide_grn_o_gp);

		//actual stock
		var storewide_aos_o_cost = float($("storewide_aos_o_cost").innerHTML);
		var storewide_sr_o_cost = float($("storewide_sr_o_cost").innerHTML);
		var storewide_adj_o_cost = float($("storewide_adj_o_cost").innerHTML);
		var storewide_as_o_cost = storewide_aos_o_cost + storewide_sr_o_cost + storewide_grn_o_cost + storewide_adj_o_cost;
		$("storewide_as_o_cost").innerHTML = ifzero(storewide_as_o_cost);
		$("storewide_as_t_cost").innerHTML = ifzero(storewide_as_o_cost);

		var storewide_aos_o_selling =  float($("storewide_aos_o_selling").innerHTML);
		var storewide_sr_o_selling =  float($("storewide_sr_o_selling").innerHTML);
		var storewide_adj_o_selling =  float($("storewide_adj_o_selling").innerHTML);
		var storewide_as_o_selling = storewide_aos_o_selling + storewide_sr_o_selling + storewide_grn_o_selling + storewide_adj_o_selling;
		$("storewide_as_o_selling").innerHTML = ifzero(storewide_as_o_selling);
		$("storewide_as_t_selling").innerHTML = ifzero(storewide_as_o_selling);

		var storewide_as_o_gp = ((storewide_as_o_selling - storewide_as_o_cost) / storewide_as_o_selling * 100);
		$("storewide_as_o_gp").innerHTML = ifinfinity(storewide_as_o_gp);
		$("storewide_as_t_gp").innerHTML = ifinfinity(storewide_as_o_gp);

		//IDT
		var storewide_idt_o_cost=0;
		$$('tbody.storewide_outright .dept_idt_o_cost').each(function(obj,ele){
			storewide_idt_o_cost = storewide_idt_o_cost + float(obj.innerHTML);
		});

		$("storewide_idt_o_cost").innerHTML = ifzero(storewide_idt_o_cost);
		$("storewide_idt_t_cost").innerHTML = ifzero(storewide_idt_o_cost);

			//selling
		var storewide_idt_o_selling=0;
		$$('tbody.storewide_outright .dept_idt_o_selling').each(function(obj,ele){
			storewide_idt_o_selling = storewide_idt_o_selling + float(obj.innerHTML);
		});

		$("storewide_idt_o_selling").innerHTML = ifzero(storewide_idt_o_selling);
		$("storewide_idt_t_selling").innerHTML = ifzero(storewide_idt_o_selling);

			//gp
		var storewide_idt_o_gp= ((storewide_idt_o_selling - storewide_idt_o_cost) / storewide_idt_o_selling * 100);
		$("storewide_idt_o_gp").innerHTML = ifinfinity(storewide_idt_o_gp);
		$("storewide_idt_t_gp").innerHTML = ifinfinity(storewide_idt_o_gp);

		//REBATE
			//selling
		var storewide_r_o_selling=0;
/*		$$('tbody.storewide_outright .dept_r_o_selling').each(function(obj,ele){
			storewide_r_o_selling = storewide_r_o_selling + float(obj.innerHTML);
		});

		$("storewide_r_o_selling").innerHTML = ifzero(storewide_r_o_selling);
*/
			//gp
		var storewide_acs_o_cost = float($("storewide_acs_o_cost").innerHTML);
		var storewide_acs_o_selling = float($("storewide_acs_o_selling").innerHTML)

/*		var storewide_r_o_gp = (((storewide_acs_o_selling - storewide_acs_o_cost) + storewide_r_o_selling) / storewide_acs_o_selling * 100 );
		$("storewide_r_o_gp").innerHTML = ifinfinity(storewide_r_o_gp);
*/
		//closing stock
		var storewide_rs_o_cost = float($("storewide_rs_o_cost").innerHTML);
		var storewide_rs_o_selling = float($("storewide_rs_o_selling").innerHTML);

		var storewide_cs_o_cost = storewide_as_o_cost - storewide_rs_o_cost + storewide_idt_o_cost - storewide_acs_o_cost;
		var storewide_cs_o_selling = storewide_as_o_selling - storewide_rs_o_selling + storewide_idt_o_selling - storewide_acs_o_selling + storewide_r_o_selling;
		var storewide_cs_o_gp = ((storewide_cs_o_selling - storewide_cs_o_cost) / storewide_cs_o_selling * 100);

		$("storewide_cs_o_cost").innerHTML = ifzero(storewide_cs_o_cost);
		$("storewide_cs_o_selling").innerHTML = ifzero(storewide_cs_o_selling);
		$("storewide_cs_o_gp").innerHTML = ifinfinity(storewide_cs_o_gp);

		//OTHER INCOME
			//selling
		var storewide_oi_o_selling=0;
		$$('tbody.storewide_outright .dept_oi_o_selling').each(function(obj,ele){

			storewide_oi_o_selling = storewide_oi_o_selling + float(obj.innerHTML);
		});

		$("storewide_oi_o_selling").innerHTML = ifzero(storewide_oi_o_selling);

		//profit margin
		var storewide_pm_o_selling = storewide_acs_o_selling - storewide_acs_o_cost + storewide_r_o_selling + storewide_oi_o_selling;
		var storewide_pm_o_gp = ((storewide_pm_o_selling / storewide_acs_o_selling ) * 100 );

		$("storewide_pm_o_selling").innerHTML = ifzero(storewide_pm_o_selling);
		$("storewide_pm_o_gp").innerHTML = ifinfinity(storewide_pm_o_gp);

		//average stock
        var storewide_cos_o_cost = float($("storewide_cos_o_cost").innerHTML);

		var storewide_av_o_selling = ((storewide_cs_o_cost + storewide_cos_o_cost) / 2);

		$("storewide_av_o_selling").innerHTML = ifzero(storewide_av_o_selling);

		//turn days
		var storewide_td_o_selling = (storewide_av_o_selling / storewide_acs_o_cost * turn_days);
		$("storewide_td_o_selling").innerHTML = iftdinfinity(storewide_td_o_selling);
	    storewide_total();
	}else if (sku_type == 'CONSIGN'){
	//CONSIGN
		//actual sales
		    //cost
		var storewide_acs_c_cost=0;
		$$('tbody.storewide_consign .dept_acs_c_cost').each(function(obj,ele){
			storewide_acs_c_cost = storewide_acs_c_cost + float(obj.innerHTML);
		});
			//selling
		$("storewide_acs_c_cost").innerHTML = ifzero(storewide_acs_c_cost);
		var storewide_acs_c_selling = $("storewide_acs_c_selling").innerHTML
			//GP
	    var storewide_acs_c_gp = ((storewide_acs_c_selling - storewide_acs_c_cost) / storewide_acs_c_selling * 100);
		$("storewide_acs_c_gp").innerHTML = ifinfinity(storewide_acs_c_gp);

/*		//rebate
			//selling
		var storewide_r_c_selling=0;
		$$('tbody.storewide_consign .dept_r_c_selling').each(function(obj,ele){
			storewide_r_c_selling = storewide_r_c_selling + float(obj.innerHTML);
		});

		$("storewide_r_c_selling").innerHTML = ifzero(storewide_r_c_selling);
            //GP
		var storewide_r_c_gp = (((storewide_acs_c_selling - storewide_acs_c_cost) + storewide_r_c_selling) / storewide_acs_c_selling * 100 )
		$("storewide_r_c_gp").innerHTML = ifinfinity(storewide_r_c_gp);
*/
		//other income
			//selling
		var storewide_oi_c_selling=0;
		$$('tbody.storewide_consign .dept_oi_c_selling').each(function(obj,ele){
			storewide_oi_c_selling = storewide_oi_c_selling + float(obj.innerHTML);
		});

		$("storewide_oi_c_selling").innerHTML = ifzero(storewide_oi_c_selling);

		//profit margin
		var storewide_pm_c_selling = storewide_acs_c_selling - storewide_acs_c_cost + storewide_oi_c_selling;
		$("storewide_pm_c_selling").innerHTML = ifzero(storewide_pm_c_selling);

		var storewide_pm_c_gp = ((storewide_pm_c_selling / storewide_acs_c_selling ) * 100 );
		$("storewide_pm_c_gp").innerHTML = ifinfinity(storewide_pm_c_gp);

	    storewide_total();
	}else if (sku_type == 'FRESH'){
	
	
	
	}

}

function storewide_total(){
	//TOTAL
	if ($("storewide_acs_o_cost") && $("storewide_acs_c_cost")){
	    //got outright and consign
		var storewide_acs_t_cost= float($("storewide_acs_o_cost").innerHTML) + float($("storewide_acs_c_cost").innerHTML);
		var storewide_acs_t_selling= float($("storewide_acs_o_selling").innerHTML) + float($("storewide_acs_c_selling").innerHTML);
//		var storewide_r_t_selling= float($("storewide_r_o_selling").innerHTML) + float($("storewide_r_c_selling").innerHTML);
		var storewide_oi_t_selling= float($("storewide_oi_o_selling").innerHTML) + float($("storewide_oi_c_selling").innerHTML);
	}else if (!$("storewide_acs_c_cost")){
		//dun have consign
		var storewide_acs_t_cost= float($("storewide_acs_o_cost").innerHTML);
		var storewide_acs_t_selling= float($("storewide_acs_o_selling").innerHTML);
//		var storewide_r_t_selling= float($("storewide_r_o_selling").innerHTML);
		var storewide_oi_t_selling= float($("storewide_oi_o_selling").innerHTML);
	}else if (!$("storewide_acs_o_cost")){
	    //dun have outright
		var storewide_acs_t_cost= float($("storewide_acs_c_cost").innerHTML);
		var storewide_acs_t_selling= float($("storewide_acs_c_selling").innerHTML);
//		var storewide_r_t_selling= float($("storewide_r_c_selling").innerHTML);
		var storewide_oi_t_selling= float($("storewide_oi_c_selling").innerHTML);
	}

	var storewide_r_t_selling=0;
	//actual stock
	var storewide_aos_t_cost = float($("storewide_aos_t_cost").innerHTML);
	var storewide_aos_t_selling =  float($("storewide_aos_t_selling").innerHTML);

	var storewide_sr_t_cost = float($("storewide_sr_t_cost").innerHTML);
	var storewide_sr_t_selling =  float($("storewide_sr_t_selling").innerHTML);

	var storewide_grn_t_cost = float($("storewide_grn_t_cost").innerHTML);
	var storewide_grn_t_selling = float($("storewide_grn_t_selling").innerHTML);

	var storewide_adj_t_cost = float($("storewide_adj_t_cost").innerHTML);
	var storewide_adj_t_selling =  float($("storewide_adj_t_selling").innerHTML);

	var storewide_idt_t_cost = float($("storewide_idt_t_cost").innerHTML);
	var storewide_idt_t_selling =  float($("storewide_idt_t_selling").innerHTML);

	var storewide_as_t_cost = storewide_aos_t_cost + storewide_sr_t_cost + storewide_grn_t_cost + storewide_adj_t_cost;
	var storewide_as_t_selling = storewide_aos_t_selling + storewide_sr_t_selling + storewide_grn_t_selling + storewide_adj_t_selling;
	var storewide_as_t_gp = ((storewide_as_t_selling - storewide_as_t_cost) / storewide_as_t_selling * 100);

	$("storewide_as_t_cost").innerHTML = ifzero(storewide_as_t_cost);
	$("storewide_as_t_selling").innerHTML = ifzero(storewide_as_t_selling);
	$("storewide_as_t_gp").innerHTML = ifinfinity(storewide_as_t_gp);

	//actual sales
	$("storewide_acs_t_cost").innerHTML = ifzero(storewide_acs_t_cost);
	$("storewide_acs_t_selling").innerHTML = ifzero(storewide_acs_t_selling);

    var storewide_acs_t_gp = ((storewide_acs_t_selling - storewide_acs_t_cost) / storewide_acs_t_selling * 100);
	$("storewide_acs_t_gp").innerHTML = ifinfinity(storewide_acs_t_gp);

	//rebate
/*	$("storewide_r_t_selling").innerHTML = ifzero(storewide_r_t_selling);

	var storewide_r_t_gp = ((storewide_acs_t_selling - storewide_acs_t_cost + storewide_r_t_selling) / storewide_acs_t_selling * 100);;
	$("storewide_r_t_gp").innerHTML = ifinfinity(storewide_r_t_gp);
*/
	//closing stock
	var storewide_rs_t_cost = float($("storewide_rs_t_cost").innerHTML);
	var storewide_rs_t_selling = float($("storewide_rs_t_selling").innerHTML);

	var storewide_pa_t_selling = float($("storewide_pa_t_selling").innerHTML);
	var storewide_pca_t_selling = float($("storewide_pca_t_selling").innerHTML);

	var storewide_cs_t_cost = storewide_as_t_cost - storewide_rs_t_cost + storewide_idt_t_cost - storewide_acs_t_cost;
	$("storewide_cs_t_cost").innerHTML = ifzero(storewide_cs_t_cost);

	var storewide_cs_t_selling = storewide_as_t_selling - storewide_rs_t_selling + storewide_idt_t_selling - storewide_pa_t_selling - storewide_pca_t_selling - storewide_acs_t_selling + storewide_r_t_selling;
	$("storewide_cs_t_selling").innerHTML = ifzero(storewide_cs_t_selling);

	var storewide_cs_t_gp = ((storewide_cs_t_selling - storewide_cs_t_cost) / storewide_cs_t_selling * 100);
	$("storewide_cs_t_gp").innerHTML = ifinfinity(storewide_cs_t_gp);

	//other income
	$("storewide_oi_t_selling").innerHTML = ifzero(storewide_oi_t_selling);

	//profit margin
	var storewide_pm_t_selling = storewide_acs_t_selling - storewide_acs_t_cost + storewide_r_t_selling + storewide_oi_t_selling;
	$("storewide_pm_t_selling").innerHTML = ifzero(storewide_pm_t_selling);

	var storewide_pm_t_gp = ((storewide_pm_t_selling / storewide_acs_t_selling ) * 100 );
	$("storewide_pm_t_gp").innerHTML = ifinfinity(storewide_pm_t_gp);

	//average stock
    var storewide_cos_t_cost = float($("storewide_cos_t_cost").innerHTML);
	var storewide_av_t_selling = ((storewide_cs_t_cost + storewide_cos_t_cost) / 2);

	$("storewide_av_t_selling").innerHTML = ifzero(storewide_av_t_selling);

	//turn days
	var storewide_td_t_selling= (storewide_av_t_selling / storewide_acs_t_cost * turn_days);
	$("storewide_td_t_selling").innerHTML = iftdinfinity(storewide_td_t_selling);

}

function ifzero(num){
	if (num == 0)
	    return '-';
	else
	    return addCommas(round(num,2));
}

function ifinfinity(num){
	if (num == '-Infinity' || num == 'Infinity' || num == 0)
	    return '-';
	else
	    return addCommas(round(num,2))+"%";
}

function iftdinfinity(num){
	if (num == '-Infinity' || num == 'Infinity' || num == 0)
	    return '-';
	else
	    return addCommas(round(num,0));
}

document.onkeypress = check_perform;
var last_obj;

function check_perform(event){
	if($('selected_popup').style.display==''){
        var kc = event.keyCode;
        var which = event.which;
		var str = String.fromCharCode(which);

		switch(kc)
		{
			case 27:    // escape
	            $('selected_popup').style.display='none';
	     		break;
			case 13:    // enter
			    start_edit();
			    break;
	   		default:    // other
	   		    if((which >=48 && which <= 57) || which ==45){  // 0 - 9, "-"
                    $('edit_text').value = str;
				}
                start_edit2();
	     		break;
		}
		return false;
	}
}

function do_select(obj){
    last_obj = obj;
    var qty = float(obj.innerHTML.replace(/^&nbsp;/,''));
    is_escape = false;
    if(qty==0)  qty='';
    $('edit_text').value = qty;
//    $('selected_popup').update(qty);
//    Position.clone(obj, $('selected_popup'));
//    Element.show('selected_popup');
	do_edit();
}

function start_edit(){
//    Element.hide($('selected_popup'));
    do_edit();
}

function do_edit(){
	var obj = last_obj
	$('edit_text').value = float(obj.innerHTML.replace(/^&nbsp;/,''));
	Position.clone(obj, $('edit_popup'));
	Position.clone(obj, $('edit_text'));
	Element.show('edit_popup');
	$('edit_text').select();
	$('edit_text').focus();
}

function start_edit2(){
    Element.hide($('selected_popup'));
    do_edit2();
}

function do_edit2(){
	obj = last_obj
	//$('edit_text').value = float(obj.innerHTML.replace(/^&nbsp;/,''));
	Position.clone(obj, $('edit_popup'));
	Position.clone(obj, $('edit_text'));
	Element.show('edit_popup');
	//$('edit_text').select();
	$('edit_text').focus();
}

function save(){
	Element.hide('edit_popup');
	last_obj.innerHTML= $('edit_text').value;
	
	var funct=last_obj.parentNode.readAttribute('funct');
	
	var function_arr=funct.split(",");
	
	if (function_arr[0]=='FRESH')
	    fresh_changes(function_arr[1],function_arr[2]);
	else
		vendor_changes(function_arr[0],function_arr[1],function_arr[2],function_arr[3]);
	
//	Element.hide('selected_popup');
//	do_select(last_obj);
}

function check_click(){
    if(last_obj!=undefined){
		Element.hide($('selected_popup'));
	}
}

function checkKey(event){
    if (event == undefined) event = window.event;

	if(event.keyCode==13){  // enter
		save();
	}
	event.stopPropagation();
}

function tick_dept(obj,root_id){

	$$("tbody.dept_total_"+root_id+" .check_"+root_id).each(function(ele){
		if (!ele.disabled)  ele.checked=obj.checked;
	});
}

function ajax_check_departments(ele,title){

	var param_str=Form.serialize(document.f_b)+"&a=ajax_check_departments";

	$('loading_id').update(_loading_);
	$('loading_id').show();
	$(ele).hide();

	new Ajax.Request(phpself,{
		method:'post',
		parameters: param_str,
	    evalScripts: true,
		onComplete: function(m){

			$('loading_id').hide();
			$(ele).show();

			var msg=m.responseText.trim();

			if (msg == "OK"){
               	alert("No review / unreview had been done.");
			}else{
				if (confirm(msg+"\nAre you confirm about this?")){
					link_form(title);
				}else{
					return;
				}
			}
		}
	});
}

</script>
<style>
h3 {
    background:#ccc;
}

th.stock_take {
	background:#ff0 !important;
}
tr.dept_title{
    background:#ccc;
}

tr.outright{
    background:#ff9;
}

tr.consign{
	background:#f99;
}

tr.fresh{
	background:#fdd;
}

tr.total{
	background:#cfc;
}

tr.total_fresh{
	background:#fbb;
}

tr.dept_outright{
    background:#ff3;
}

tr.dept_consign{
	background:#f55;
}

tr.dept_fresh{
	background:#f55;
}

tr.dept_total{
	background:#8f8;
}

tbody.voutright{
    background:#ffc;
}

tbody.vconsign{
    background:#fcc;
}

tr.store_outright{
    background:#ddf;
}

tr.store_consign{
    background:#aaf;
}

tr.store_total{
    background:#88f;
}

a{
	cursor:pointer;
}
input[type=text]{
	width : 80px ;
}

input[readonly]{
	border : 0;
	background: none;
}

input.nv{
	color:#f00;
	text-align:right;
}

input.r{
	color:#000;
	text-align:right;
}

input[disabled]{
	background:none;
	border: 0;
}

.left{
	float: Left;
}

.left_top{
	position:absolute;
	top:0px;
	left:0px;
}
.hide{
	display:none;
}
#finalize_btn:disabled, #confirm_btn:disabled{
	background-color:#aaa;
	border: 0;
}

#confirm_btn{
	background-color:#091;
}

#finalize_btn{
	background-color:#f00;
}
</style>
{/literal}

{if $test}
	{literal}
	<style>
	.keyin{
		background:#ff0;
	}
	</style>

	{/literal}
{/if}

{/if}
<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}
{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li><font color='red'><b>{$e}</b></font></li>
{/foreach}
</ul>
{/if}

<ul class=err>
{foreach from=$scc item=e}
<li><font color='green'><b>{$e}</b></font></li>
{/foreach}
</ul>
<form name="f_a" method="get" class="form" onsubmit='return false;'>
	<input type=hidden name=a>

	{if $BRANCH_CODE eq 'HQ'}
	<b>Branch</b>
	<select name="branch_id">
	    {foreach from=$branches item=b}
	        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
	    {/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}

	<b>Year</b> {dropdown name=year values=$years selected=$smarty.request.year key=year value=year}
	&nbsp;&nbsp;&nbsp;&nbsp;
	<b>Month</b>
	<select name="month">
		{foreach from=$months key=k item=mon}
		    <option value="{$k}" {if $smarty.request.month eq $k}selected{/if}>{$mon}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
		
 	{if $test}
		<input type="checkbox" id="start_opening" name="start_opening" {if $smarty.request.start_opening} checked {/if} >
		<label for="start_opening">Starting Opening Stock</label>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
{*
	<button onclick="link_form('edit');" value="edit_form">Edit Form</button>	&nbsp;&nbsp;&nbsp;&nbsp;

	<button onclick="link_form('view');" value="edit_form">View Form</button>	&nbsp;&nbsp;&nbsp;&nbsp;
*}
	<button onclick="link_form('show');" value="show_report">{#SHOW_REPORT#}</button>&nbsp;&nbsp;&nbsp;&nbsp;
	{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
	<button onclick="link_form('output');" value="output_excel">{#OUTPUT_EXCEL#} with vendor</button>&nbsp;&nbsp;&nbsp;&nbsp;
	<button onclick="link_form('output_no_vendor');" value="output_excel_without_vendor">{#OUTPUT_EXCEL#} without vendor</button>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
{*
	<button onclick="link_form('regenerate')" value="regenerate_report">Regenerate Report</button>
	<button onclick="link_form('test');" value="test_mode">Test Mode</button>&nbsp;&nbsp;&nbsp;&nbsp;
*}
	{if $search_vendor}
		<input type="button" value="Search Vendor" onclick="show_search_vendor();">
	{/if}
</form>

<!-----POP UP----->
	<div id="search_vendor_id" style="position:absolute;width:400px;height:150px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
		<div id="div_search_vendor_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;"><b>Search Vendor</b></span>
	    <span style="float: right;">
	        <a href="javascript:void(hidediv('search_vendor_id'))"><img border="0" align="absmiddle" src="ui/closewin.png"></a>
	    </span>
   		<div style="clear:both;"></div>
	    </div>
		<div style="padding:2px;">
		    <table>
		        <tr>
		            <th>Department</th>
		            <td>
						<select name="department_id" id="department_id" onchange="load_vendor($('department_id').value,$('sku_type_id').value);">
							<option value="">-Please Select-</option>
							{foreach from=$c_dept item=cdesc}
								{foreach from=$cdesc key=cat_id item=desc}
								    <option value="{$cat_id}">{$desc.c_descrip}</option>
								{/foreach}
							{/foreach}
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
				    </td>
				</tr>
				<tr>
				    <th>Sku Type</th>
				    <td>
						<select name="sku_type" id="sku_type_id" onchange="load_vendor($('department_id').value,$('sku_type_id').value);">
							{foreach from=$sku_types item=st}
							    <option value="{$st}">{$st}</option>
							{/foreach}
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
				    </td>
				</tr>
				<tr>
			   	 <th>Vendor</th>
				    <td>
						<select name="vendor_id" id="vendor_id" style="width:300px">
							<option value="">-No Data-</option>
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>
				<tr align="absmiddle" style="height:50px">
				    <td colspan=2>
						<input type="button" value="Search Vendor" onclick="display_vendor_row();">
					</td>
				</tr>
			</table>
		</div>
	</div>
	
<!-----POP UP Form----->
	<div id="form_vendor_id" style="position:absolute;width:400px;height:170px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
		<div id="div_form_vendor_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;"><b>Vendor Form</b></span>
	    <span style="float: right;">
	        <a href="javascript:void(0)" onclick="if (confirm('Are you sure to close this?')) hidediv('form_vendor_id')"><img border="0" align="absmiddle" src="ui/closewin.png"></a>
	    </span>
   		<div style="clear:both;"></div>
	    </div>
		<div style="padding:2px;">
		<!-----POP UP Form----->
			<form name="f_input" method=post onsubmit='return false;'>
			    <span id='form_input_cache'></span>
			</form>
		</div>
	</div>

	<div id="sku_items_id" style="position:absolute;display:none;width:705px;height:577px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
		<div id="div_sku_items_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;"><b>SKU Items List</b></span>
	    <span style="float: right;">
	        <a href="javascript:void(0)" onclick="hidediv('sku_items_id')"><img border="0" align="absmiddle" src="ui/closewin.png"></a>
	    </span>
   		<div style="clear:both;"></div>
	    </div>
		<div id='sku_items_cache' style="padding:2px;width:700px;height:550px;overflow:auto;"></div>
	</div>

	<div id="before_stc_id" style="position:absolute;display:none;width:705px;height:577px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
		<div id="div_before_stc_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;"><b>Before Stock Check</b></span>
	    <span style="float: right;">
	        <a href="javascript:void(0)" onclick="hidediv('before_stc_id')"><img border="0" align="absmiddle" src="ui/closewin.png"></a>
	    </span>
   		<div style="clear:both;"></div>
	    </div>
		<div id='before_stc_cache' style="padding:2px;width:700px;height:550px;overflow:auto;"></div>
	</div>
	{* include file=report.csa.edit_form.tpl *}
{/if}

<form name="f_b" method=post onsubmit='return false;'>
	<input type="hidden" name=branch_id value="{$form.branch_id}">
	<input type="hidden" name=year value="{$form.year}">
	<input type="hidden" name=month value="{$form.month}">
	<input type="hidden" name=a>
	<input type=hidden name=tmp value="{$smarty.request.tmp}">
	<div id=edit_popup style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;">
	<input id=edit_text size=5 onblur="save()" onKeyPress="checkKey(event)" style="text-align:right;">
	</div>
	<div id="selected_popup" style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #0000f0;margin:-2px 0 0 -2px;text-align:right;vertical-align:middle;" onclick="start_edit();">
	</div>

    {if $table}
		<div id="report_id">
			{$report_cache}
			{if !$no_edit}
				{if !$no_header_footer}
					<!-----------------------Special Checking for Save button to confirm and finalize user---------------------->
				    {if ($privilege.REPORTS_CSA_CONFIRM && !$confirmed )|| ($privilege.REPORTS_CSA_FINALIZE && !$finalized)}
 						<button onclick="link_form('save')"  value="save_form" style="font:bold 20px Arial; background-color:#f90; color:#fff;">Save Data</button> &nbsp;&nbsp;&nbsp;&nbsp;
					{/if}
					<!-------End Check-------->

				    {if $privilege.REPORTS_CSA_CONFIRM}
				        {if !$finalized}
							{if !$confirmed}
								<button id="confirm_btn" onclick="link_form('confirm')" value="confirm_form" style="font:bold 20px Arial;  color:#fff;">Confirm</button> &nbsp;&nbsp;&nbsp;&nbsp;
							{/if}
						{/if}
					{/if}

					{if $confirmed}
						{if $privilege.REPORTS_CSA_FINALIZE}
							{if !$finalized}
								<button id="finalize_btn" onclick="link_form('finalize')"  value="finalize_form" style="font:bold 20px Arial; color:#fff;">Finalise</button> &nbsp;&nbsp;&nbsp;&nbsp;
							{else}
	                            {if $ful_review ne 'full'}
	                            	{if $can_unfinalize}
										<button onclick="link_form('unfinalize')"  value="unfinalize_form" style="font:bold 20px Arial; background-color:#f00; color:#fff;" >Unfinalise</button> &nbsp;&nbsp;&nbsp;&nbsp;
									{/if}
								{/if}
							{/if}
						{/if}

						{if $finalized}
							{if $privilege.REPORTS_CSA_REVIEW}
								<span id="loading_id"></span>
								{if (!$can_unfinalize && ($ful_review eq 'none' || $ful_review eq 'half')) || ($can_unfinalize && $ful_review eq 'none')}
									<button onclick="ajax_check_departments(this,'review')" value="review_form" style="font:bold 20px Arial; background-color:#091; color:#fff;">Review</button> &nbsp;&nbsp;&nbsp;&nbsp;							
						        {elseif $can_unfinalize && $ful_review eq 'full'}
								<button onclick="ajax_check_departments(this,'unreview')" value="review_form" style="font:bold 20px Arial; background-color:#091; color:#fff;">Unreview</button> &nbsp;&nbsp;&nbsp;&nbsp;
								{elseif $can_unfinalize && $ful_review eq 'half'}
								<button onclick="ajax_check_departments(this,'hreview')" value="review_form" style="font:bold 20px Arial; background-color:#091; color:#fff;">Review & Unreview</button> &nbsp;&nbsp;&nbsp;&nbsp;
								{/if}
							{/if}
						{/if}
					{/if}
				{/if}
			{/if}
		</div>
	{/if}
</form>

{if !$no_header_footer}
{include file=footer.tpl}

<script>
{literal}
new Draggable('search_vendor_id',{ handle: 'div_search_vendor_header'});
new Draggable('form_vendor_id',{ handle: 'div_form_vendor_header'});
new Draggable('sku_items_id',{ handle: 'div_sku_items_header'});
new Draggable('before_stc_id',{ handle: 'div_before_stc_header'});
{/literal}


{if $test}
	{literal}
	$$('#report_tbl td.keyin').each(function(ele){
	    Event.observe(ele, 'click', function(event) {
		  	do_select(this);
		});
	});
	{/literal}
{/if}

</script>

{/if}
