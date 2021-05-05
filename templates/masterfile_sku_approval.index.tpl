{*
Revision History
================
9/19/2007 1:35:29 PM -gary
- added only the last approval can appear the multics details for keyin.
- alter the submit method from Ajax.Request to Ajax.Updater to refresh the list.
- the promt msg done in php side.

9/21/2007 3:17:05 PM yinsee
- change dropdown window CSS visibility:hidden to display:none (cursor missing if use visiblity) 

1/7/2010 11:09:15 AM Andy
- make approval list will auto select given sku if id is passed

7/30/2013 1:44 PM Andy
- Fix SKU Application Approval cannot reload the next list.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

3/12/2015 2:37 PM Andy
- Enhanced to able to change input tax, output tax, inclusive tax when under approval screen.

4/10/2015 10:51 AM Andy
- Enhance the sku approval screen to auto recalculate cost using trade discount when changes was made.

7/23/2015 2:41 PM Andy
- Enhanced to auto update sku & sku_items gst inherit info.

5/11/2017 11:04 AM Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long
*}

{include file=header.tpl}

<script type="text/javascript">

var config_consignment_modules = {if $config.consignment_modules}1{else}0{/if};

{if $gst_settings}
var is_gst_active = true;
{else}
var is_gst_active = false;
{/if}

var gst_rate_list = [];
var gst_code_list = [];
{foreach from=$input_tax_list item=r}
    gst_rate_list['{$r.id}'] = '{$r.rate}'
    gst_code_list['{$r.id}'] = '{$r.code}'
{/foreach}

{foreach from=$output_tax_list item=r}
    gst_rate_list['{$r.id}'] = '{$r.rate}'
    gst_code_list['{$r.id}'] = '{$r.code}'
{/foreach}

var cat_gst_settings = [];

var category_autocompleter;
var last_idx;
var initial_select;
var submitted_id = 0;

{literal}

function do_submit()
{
	$('bsubmit').style.display = 'none';
	
	submitted_id = document.f_a['id'].value;
	
	new Ajax.Updater('udiv', 'masterfile_sku_approval.php', {
		parameters: Form.serialize(document.f_a),
		evalScripts: true,
	});
	/*	
	new Ajax.Request(
	'masterfile_sku_approval.php',
	{
		method: 'post',
		parameters: Form.serialize(document.f_a),
		evalScripts: true,
		onSuccess: function(m) {
			alert(m.responseText);
			sel_next_tab();
		},
		onFailure: function(m) {
			alert(m.responseText);
		},
		onComplete: function() {
			$('bsubmit').style.display = '';
		}
	});
	*/	
}


function do_terminate()
{
	document.f_a.reason2.value = '';
	var p = prompt('Enter reason to terminate:');
	if (p.trim()=='' || p==null) return;
	document.f_a.reason2.value = p;
	if (confirm('Press OK to Terminate the SKU Application.'))
	{
		document.f_a.a.value = "terminate_approval";
		do_submit();
	}
}

function do_kiv()
{
	if (confirm('Press OK to KIV the SKU Application.'))
	{
	    document.f_a.a.value = "kiv_approval";
		do_submit();
	}
}

function do_approve_all()
{
	if (document.f_a.elements['approval[general]'].value == 'Reject')
	{
	    alert('You have selected Reject for General Information');
	    document.f_a.elements['approval[general]'].focus();
	    return false;
	}

	for (id=0; id<last_idx; id++)
	{
	    if (document.f_a.elements['approval['+id+']'].value == 'Reject')
		{
		    alert('You have selected Reject for item '+id);
		    document.f_a.elements['approval['+id+']'].focus();
			return false;
		}
	}

	if ($('description[0]'))
	{
		for (id=0; id<last_idx; id++)
		{
			if (empty($('description['+id+']'), 'You must enter Description'))
			{
				return false;
			}
			else if (empty($('receipt_description['+id+']'), 'You must enter Receipt Description'))
			{
				return false;
			}
			else if ($('receipt_description['+id+']') !=  ''){
				if(!check_receipt_desc_length('receipt_description['+id+']'))	return false;
			}
		}
	}
	if (confirm('Press OK to Approve the SKU Application.'))
	{
	    document.f_a.a.value = "all_approval";
		do_submit();
	}
}

function do_approve()
{
	// make sure all description is filled
	if (document.f_a.elements['approval[general]'].value == '')
	{
		alert("Approve or Reject not selected for General Information");
	    document.f_a.elements['approval[general]'].focus();
    	return false;
	}
	if ($('description[0]'))
	{
		var v;
		for (id=0; id<last_idx; id++)
		{

			v = document.f_a.elements['approval['+id+']'].value;
			if (v =='')
			{
				alert("Approve or Reject not selected for item "+id);
				document.f_a.elements['approval['+id+']'].focus();
				return false;
			}
			else if (v == 'Approve' && empty($('description['+id+']'), 'You must enter Description'))
			{
				return false;
			}
			else if (v == 'Approve' && empty($('receipt_description['+id+']'), 'You must enter Receipt Description'))
			{
				return false;
			}else if (v == 'Approve' && $('receipt_description['+id+']') !=  ''){
				if(!check_receipt_desc_length('receipt_description['+id+']'))	return false;
			}
		}
	}
	
	if (confirm('Press OK to submit the Application Status for this SKU Application.'))
	{
		do_submit();
	}

	return false;

}

function sel_next_tab()
{
	// remove current tab
	//var id = document.f_a['id'].value;
	var id = submitted_id;
	Element.remove('tab'+id);
	// select first tab
	select_tab();
	// find and display first invi tab
	var lst = $('tab').getElementsByTagName("LI");

	for (i=0;i<lst.length;i++)
	{
		if (lst[i].style.display == "none")
		{
			lst[i].style.display = '';
			break;
		}
	}
}

function select_tab(obj)
{
	if (obj == undefined)
	{
		var lst = $('tab').getElementsByTagName("LI");
		if (lst.length==0)
		{
			alert('Congratulation! You have completed all approval jobs.\nTake a break ;)');
			document.location = '/home.php';
			return;
		}
		
		if(initial_select){
       var sel_lst = $('tab'+initial_select);
       sel_lst.className = "active";
		  load_approval(sel_lst);
    }else{
      lst[0].className = "active";
		  load_approval(lst[0]);
    }

		
	}
	else
	{
		var lst = $('tab').getElementsByTagName("LI");
		$A(lst).each( function(r,idx) {
			if (r.className == "active")
				r.className = '';
		});
		obj.className = "active";
		load_approval(obj);
	}
}

function load_approval(obj)
{
	id = obj.title;
	$('sel_name').innerHTML = obj.innerHTML;

	$('udiv').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...<br><img src=ui/pixel.gif height=500 width=1>';

	new Ajax.Updater('udiv', 'masterfile_sku_approval.php', {
		parameters: 'a=ajax_load_sku&id='+id+'&'+Form.serialize(document.f_on_behalf),
		evalScripts: true
		});
}

function sel_reason(id)
{
	if ($('approval['+id+']').value == 'Reject')
	{
	    $('reject_box['+id+']').style.display = '';
	}
	else
	{
	    $('reject_box['+id+']').style.display = 'none';
	}
}

function fid(id)
{
	if (id=='' || id==0) return;
	if ($('tab'+id))
	{
  		select_tab($('tab'+id));
	}
	else
	{
		alert("The application ID#"+id+" was not found");
	}
	$('tab'+id).value='';
}

function sel_category(obj,have_child)
{
	var str = new String(obj.value);
	str.replace("<span class=sh>", "");
	str.replace("</span>", "");

	// non softline must select bottom-most category
	if (str.indexOf('SOFTLINE')<0 && have_child!=0)
	{
		oid = document.f_a.category_id.value;
		document.f_a.category_id.value = '';
		$('str_findcat').innerHTML = 'You need to select a sub-category';
		obj.value = str.substr(str.lastIndexOf(">")+2, str.length);
		show_child(oid);
		return;
	}

	$('str_findcat').innerHTML = str;
	obj.value = str.substr(str.lastIndexOf(">")+2, str.length);
}
function show_child(id)
{
	// reactivate the auto-completer with child of the category
	setTimeout('category_autocompleter.options.defaultParams = "child='+id+'";category_autocompleter.activate()',250);
}


function calc_all_gst(){
  $$('#udiv input.inp_item_id_list').each(function(inp) {
    var id=inp.value;
	//console.log("id = "+id);
    if (id!=undefined) {
      var item_type = document.f_a['item_type['+id+']'].value;
      if (item_type=='variety'){
        calc_gst(id);
      }
      else{
        calc_matrix_gst(id);
      }
    }
  });
  
  //mst_gst_info_changed();
}

function calc_gst(id,field_type){
	if(is_gst_active == false) return;

	//master sku
	var mst_output_tax = document.f_a['mst_output_tax'].value;
	var mst_inclusive_tax = document.f_a['mst_inclusive_tax'].value;

	if (mst_output_tax==-1) mst_output_tax = cat_gst_settings['output_tax']['rate'];	// found it is inherit to category
	else mst_output_tax=gst_rate_list[mst_output_tax];
	
	if (mst_inclusive_tax=='inherit') mst_inclusive_tax=cat_gst_settings['inclusive_tax']; // found it is inherit to category

	var output_tax = document.f_a['dtl_output_tax['+id+']'].value;
	var inclusive_tax = document.f_a['dtl_inclusive_tax['+id+']'].value;

	if(output_tax == -1) gst_rate= float(mst_output_tax); // found it is inherit to master sku
	else gst_rate = float(gst_rate_list[output_tax]);

	if (inclusive_tax=='inherit') inclusive_tax=mst_inclusive_tax; // found it is inherit to master sku

	$('span_gst_rate_'+id).update(gst_rate);
	
	$('span_gst_indicator_'+id).update((inclusive_tax=='no')?"After":"Before");

	var selling_price = float(document.f_a['selling_price['+id+']'].value);
	var selling_price_gst = 0;
	var gst = 0;
	
	if (field_type=='gst_price') {
		// impossible to reach, since approval screen cant edit selling price
		/*var selling_price_gst = float(document.f_a['selling_price_gst['+id+']'].value);

		if (inclusive_tax=='no') {
			var selling_price=(selling_price_gst*100)/(100+gst_rate);
			var gst=float(selling_price) * gst_rate / 100;
		}
		else{
			var gst=float(selling_price_gst) * gst_rate / 100;
			var selling_price=float(selling_price_gst+gst);
		}

		document.f_a['selling_price['+id+']'].value=round(selling_price,2);*/
	}
	else{
		if (inclusive_tax=='yes') {
			selling_price_gst = (selling_price*100)/(100+gst_rate);
			gst = float(selling_price_gst) * gst_rate / 100;
		}
		else{
			gst = float(selling_price) * gst_rate / 100;
			selling_price_gst = float(selling_price+gst);
		}

		$('span_selling_price_gst-'+id).update(round2(selling_price_gst));
		document.f_a['gst_selling_price['+id+']'].value = round2(selling_price_gst);
	}
  
	var gp_selling_price = 0;
	if(inclusive_tax == 'yes'){
		gp_selling_price = selling_price_gst;
	}else{
		gp_selling_price = selling_price;
	}
	
	// gross profit amt
	/*var gp_amt = gp_selling_price - float(document.f_a["cost_price["+id+"]"].value);
	$('span_gp_amt-'+id).update(round(gp_amt, 4));

	// gross profit percent
	var grossp = 0
	if(gp_selling_price != 0){
		grossp = float(gp_amt/gp_selling_price)*100;
	}
	$('span_gp_per-'+id).update(round(grossp,4));*/
	
	$('span_gst_amt-'+id).update(round(gst,2));
	
	if (document.f_a.sku_type.value == 'CONSIGN'){
		recalculate_all_cost(id);
	}else {
		atom_update_gross(id);
	}
}


function calc_matrix_gst(id,rid,field_type) {
  if(is_gst_active == false) return;
  
  //master sku
  var mst_output_tax = document.f_a['mst_output_tax'].value;
  var mst_inclusive_tax = document.f_a['mst_inclusive_tax'].value;

  if (mst_output_tax==-1) mst_output_tax = cat_gst_settings['output_tax']['rate']; // found it is inherit to category
  else mst_output_tax=gst_rate_list[mst_output_tax];

  if (mst_inclusive_tax=='inherit') mst_inclusive_tax = cat_gst_settings['inclusive_tax']; // found it is inherit to category

  var output_tax = document.f_a['dtl_output_tax['+id+']'].value;
  var inclusive_tax = document.f_a['dtl_inclusive_tax['+id+']'].value;

  if(output_tax == -1) gst_rate= float(mst_output_tax); // found it is inherit to master sku
  else gst_rate = float(gst_rate_list[output_tax]);

  if (inclusive_tax=='inherit') inclusive_tax=mst_inclusive_tax; // found it is inherit to master sku

  $('span_gst_rate_'+id).update(gst_rate);

  $('span_gst_indicator_'+id).update((inclusive_tax=='no')?"After":"Before");

  if (rid!=undefined && field_type=='gst_price') {
	// impossible to reach, since approval screen cant edit selling price
    /*var gst_selling_price = float(document.f_a['tbprice_gst['+id+']['+rid+']'].value);
	
	if (inclusive_tax=='no') {
		var selling_price=(gst_selling_price*100)/(100+gst_rate);
		var gst_amt=float(selling_price) * gst_rate / 100;
	}
	else{
		var gst_amt=float(gst_selling_price) * gst_rate / 100;
		var selling_price=float(gst_selling_price+gst_amt);
	}

    document.f_a['tbgst['+id+']['+rid+']'].value=round(gst_amt,2);
    document.f_a['tbprice['+id+']['+rid+']'].value=round(selling_price,2);*/
  }
  else{
    if(document.f_a["tbcost["+id+"][1]"]){
      var rid = 1;

      while (document.f_a["tbprice["+id+"]["+rid+"]"] != undefined){
        var selling_price = float(document.f_a['tbprice['+id+']['+rid+']'].value);
		
		if (inclusive_tax=='yes') {
			var gst_selling_price=(selling_price*100)/(100+gst_rate);
			var gst_amt=float(gst_selling_price) * gst_rate / 100;
		}
		else{
			var gst_amt=float(selling_price) * gst_rate / 100;
			var gst_selling_price=float(selling_price+gst_amt);
		}

        $('span_gst_amt-'+id+'-'+rid).update(round(gst_amt,2));
        $('span_gst_selling_price-'+id+'-'+rid).update(round(gst_selling_price,2));
		document.f_a['tbprice_gst['+id+']['+rid+']'].value = round(gst_selling_price,2);
        rid++;
      }
    }
  }
  
	if (document.f_a.sku_type.value == 'CONSIGN'){
		recalculate_all_cost(id);
	}
	
	matrix_update_gross(id);
}

function atom_update_gross(n)
{
	var sp = float(document.f_a["selling_price["+n+"]"].value);
	if(is_gst_active){
		if($('span_gst_indicator_'+n).innerHTML=='Before'){
			sp = float(document.f_a.elements["gst_selling_price["+n+"]"].value);
		}
	}
    
	var gp_amt = round(sp - float(document.f_a.elements["cost_price["+n+"]"].value),4);
	document.f_a["gross["+n+"]"].value = round(gp_amt, 4);
	$('span_gp_amt-'+n).update(round(gp_amt, 4));
	
	// gross profit percent
    var grossp = 0
    if(sp != 0){
		grossp = float(document.f_a["gross["+n+"]"].value/sp)*100;
	}
	
	//document.f_a.elements["grossp["+n+"]"].value = round(grossp,4);
	$('span_gp_per-'+n).update(round(grossp,4));
	//calculate_gst(n);
}

function matrix_update_gross(n){
	if(document.f_a["tbcost["+n+"][1]"]){
		var rid = 1;

		var use_sp_gst = false;
		if(is_gst_active){
			if($('span_gst_indicator_'+n).innerHTML=='Before')	use_sp_gst = true;
		}
		
		  while (document.f_a["tbprice["+n+"]["+rid+"]"] != undefined){
			var inp_sp = use_sp_gst ? document.f_a.elements["tbprice_gst["+n+"]["+rid+"]"] : document.f_a.elements["tbprice["+n+"]["+rid+"]"];
			var sp = float(inp_sp.value);
			var cost = float(document.f_a["tbcost["+n+"]["+rid+"]"].value);
			
			// gp
			var gp = round(sp - cost,4);
			// gross profit percent
			var grossp = 0
			if(sp != 0){
				grossp = float(gp/sp)*100;
			}
	
			$('span_gp-'+n+'-'+rid).update(round(gp,4));
			$('span_gp_per-'+n+'-'+rid).update(round(grossp,4));
			
			rid++;
		  }
    }
}

function recalculate_all_cost(n)
{
	//console.log('recalculate_all_cost: '+n);
	var margin = 0;
	if(document.f_a['trade_discount_rate']) margin = document.f_a['trade_discount_rate'].value;

	// got provide item id, so only do for this item
	if (n != undefined)
	{
	    if (document.f_a.elements["cost_price["+n+"]"])
	    {
	    	if(!config_consignment_modules){
		        document.f_a.elements["cost_price["+n+"]"].readOnly = true;
				document.f_a.elements["cost_price["+n+"]"].value = margin + '%';
			}
			var inp_sp = document.f_a.elements['selling_price['+n+']'];
			if(is_gst_active){
				if($('span_gst_indicator_'+n).innerHTML=='Before'){
					inp_sp = document.f_a.elements['gst_selling_price['+n+']'];
				}
			}
			percent_to_amount(inp_sp,document.f_a.elements["cost_price["+n+"]"], $('span_cost_price-'+n));
			atom_update_gross(n);
		}
		else if (document.f_a.elements["tbcost["+n+"][1]"])
		{
		    r = 1;
			var use_sp_gst = false;
			if(is_gst_active){
				if($('span_gst_indicator_'+n).innerHTML=='Before')	use_sp_gst = true;
			}
		    while (document.f_a.elements["tbcost["+n+"]["+r+"]"] != undefined)
		    {
		    	if(!config_consignment_modules){
			        document.f_a.elements["tbcost["+n+"]["+r+"]"].readOnly = true;
					document.f_a.elements["tbcost["+n+"]["+r+"]"].value = margin + '%';
				}
				var inp_sp = use_sp_gst ? document.f_a.elements["tbprice_gst["+n+"]["+r+"]"] : document.f_a.elements["tbprice["+n+"]["+r+"]"];
				percent_to_amount(inp_sp,document.f_a.elements["tbcost["+n+"]["+r+"]"],$('span_tbcost-'+n+'-'+r));
				r++;
			} 
		}
		//console.log('finish recalculate_all_cost');
		return;
	}
	
	// for all items
	/*for (n=0;n<=total_item;n++)
	{
	    if (document.f_a.elements["cost_price["+n+"]"])
	    {
	    	if(!config_consignment_modules){
		        document.f_a.elements["cost_price["+n+"]"].readOnly = true;
				document.f_a.elements["cost_price["+n+"]"].value = margin + '%';
			}
			percent_to_amount(document.f_a.elements['selling_price['+n+']'],document.f_a.elements["cost_price["+n+"]"]);
			atom_update_gross(n);
		}
		else if (document.f_a.elements["tbcost["+n+"][1]"])
		{
		    r = 1;
		    while (document.f_a.elements["tbcost["+n+"]["+r+"]"] != undefined)
		    {
		    	if(!config_consignment_modules){
			        document.f_a.elements["tbcost["+n+"]["+r+"]"].readOnly = true;
					document.f_a.elements["tbcost["+n+"]["+r+"]"].value = margin + '%';
				}
				percent_to_amount(document.f_a.elements["tbprice["+n+"]["+r+"]"],document.f_a.elements["tbcost["+n+"]["+r+"]"]);
				r++;
			}
		}
	}*/
}

function percent_to_amount(selling, percent, cost_label)
{
	if (percent.value.indexOf("%")>=0)
		percent.value = selling.value - (selling.value * float(percent.value) / 100);
	percent.value=round(percent.value,4);
	if(cost_label)	$(cost_label).update(round(percent.value,4));
}

var SKU_APPROVAL_FORM = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		if(is_gst_active){			
			// show cat gst info
			this.show_cat_gst_info();
			// show all sku items inherit info
			this.reload_all_items_sku_inherit_info();
		}
	},
	// function to show category gst info for inherit element
	show_cat_gst_info: function(){
		// get elements
		var opt_sku_inherit_cat_input_tax = $('opt_sku_inherit_cat_input_tax');
		var opt_sku_inherit_cat_output_tax = $('opt_sku_inherit_cat_output_tax');
		var opt_sku_inherit_cat_inclusive_tax = $('opt_sku_inherit_cat_inclusive_tax');
		
		// get original text
		var input_tax_ori_text = opt_sku_inherit_cat_input_tax.readAttribute('ori_text');
		var output_tax_ori_text = opt_sku_inherit_cat_output_tax.readAttribute('ori_text');
		var inclusive_tax_ori_text = opt_sku_inherit_cat_inclusive_tax.readAttribute('ori_text');
		
		// update text
		if(cat_gst_settings){
			// got gst
			opt_sku_inherit_cat_input_tax.text = input_tax_ori_text + ' ' + cat_gst_settings['input_tax']['code']+' '+cat_gst_settings['input_tax']['rate']+'%';
			opt_sku_inherit_cat_output_tax.text = output_tax_ori_text + ' ' + cat_gst_settings['output_tax']['code']+' '+cat_gst_settings['output_tax']['rate']+'%';
			opt_sku_inherit_cat_inclusive_tax.text = inclusive_tax_ori_text + ' ' +  (cat_gst_settings['inclusive_tax']=='yes'?'Yes':'No');
		}else{
			// no gst
			opt_sku_inherit_cat_input_tax.text = input_tax_ori_text;
			opt_sku_inherit_cat_output_tax.text = output_tax_ori_text;
			opt_sku_inherit_cat_inclusive_tax.text = inclusive_tax_ori_text;
			
		}
	},
	// function to reload all sku items inherit info
	reload_all_items_sku_inherit_info: function(){
		// get all sku item id
		var si_id_list = this.get_all_items_id();
		for(var i=0,len=si_id_list.length; i<len; i++){
			this.update_item_inherit_info(si_id_list[i]);
		}
	},
	// function to get all sku item id list
	get_all_items_id: function(){
		var si_id_list = [];
		$(this.f).getElementsBySelector('input.inp_item_id_list').each(function(inp){
			si_id_list.push(inp.value);
		});
		return si_id_list;
	},
	// function to show item inherit info
	update_item_inherit_info: function(sid){
		var sku_input_tax = this.get_sku_input_tax();
		var sku_output_tax = this.get_sku_output_tax();
		var sku_inclusive_tax = this.get_sku_inclusive_tax();
		
		// get elements
		var opt_si_inherit_cat_input_tax = $('opt_si_inherit_cat_input_tax-'+sid);
		var opt_si_inherit_cat_output_tax = $('opt_si_inherit_cat_output_tax-'+sid);
		var opt_si_inherit_cat_inclusive_tax = $('opt_si_inherit_cat_inclusive_tax-'+sid);
		
		// get original text
		var input_tax_ori_text = opt_si_inherit_cat_input_tax.readAttribute('ori_text');
		var output_tax_ori_text = opt_si_inherit_cat_output_tax.readAttribute('ori_text');
		var inclusive_tax_ori_text = opt_si_inherit_cat_inclusive_tax.readAttribute('ori_text');
		
		// update input tax
		if(sku_input_tax){
			opt_si_inherit_cat_input_tax.text = input_tax_ori_text + ' ' + sku_input_tax['code']+' '+sku_input_tax['rate']+'%';
		}else{
			opt_si_inherit_cat_input_tax.text = input_tax_ori_text;
		}
		
		// update output tax
		if(sku_output_tax){
			opt_si_inherit_cat_output_tax.text = output_tax_ori_text + ' ' + sku_output_tax['code']+' '+sku_output_tax['rate']+'%';
		}else{
			opt_si_inherit_cat_output_tax.text = output_tax_ori_text;
		}
		
		// update selling price inclusive tax
		if(sku_inclusive_tax){
			opt_si_inherit_cat_inclusive_tax.text = inclusive_tax_ori_text + ' ' + (sku_inclusive_tax=='yes'?'Yes':'No');
		}else{
			opt_si_inherit_cat_inclusive_tax.text = inclusive_tax_ori_text;
		}
		
	},
	// function to get sku gst input tax
	get_sku_input_tax: function(){
		var sel = this.f['mst_input_tax'];
		if(sel.value>0){
			// got select
			return {'code': gst_code_list[sel.value], 'rate': gst_rate_list[sel.value]};
		}else{
			// inherit cat
			return cat_gst_settings['input_tax'];
		}
	},
	// function to get sku output tax
	get_sku_output_tax: function(){
		var sel = this.f['mst_output_tax'];
		if(sel.value>0){
			// got select
			return {'code': gst_code_list[sel.value], 'rate': gst_rate_list[sel.value]};
		}else{
			// inherit cat
			return cat_gst_settings['output_tax'];
		}
	},
	// function to get sku inclusive tax
	get_sku_inclusive_tax: function(){
		var sel = this.f['mst_inclusive_tax'];
		if(sel.value == 'inherit'){
			return cat_gst_settings['inclusive_tax'];
		}else{
			return sel.value;
		}
	},
	// function when user change sku input tax
	sku_input_tax_changed: function(){
		// show all sku items inherit info
		this.reload_all_items_sku_inherit_info();
	},
	// function when user change sku input tax
	sku_output_tax_changed: function(){
		// show all sku items inherit info
		this.reload_all_items_sku_inherit_info();
		// recalculate gst
		calc_all_gst();
	},
	// function when user change sku inclusive tax
	sku_inclusive_tax_changed: function(){
		// show all sku items inherit info
		this.reload_all_items_sku_inherit_info();
		// recalculate gst
		calc_all_gst();
	}
}

</script>

<style>
#tab_sel {
	border:1px solid #ccc;
	width:600px;
	padding:4px;
	background:#fff url('/ui/findcat_expand.png') right center no-repeat;
}

#tab_sel ul {
	position:absolute;
	display:none;
	background:#fff;
	border:1px solid #ccc;
	border-top:none;
	list-style:none;
	margin:0;padding:0;
	margin-left:-5px;
	margin-top:5px;
	width:608px;
	height:300px;
	overflow:auto;
}
#tab_sel ul li {
	cursor:pointer;
	display:block;
	margin:0;padding:4px;
}
#tab_sel ul li:hover {
	background:#ff9
}

#tab_sel:hover ul {
	display:block;
}

</style>
{/literal}

<h1>SKU Approval{if $approval_on_behalf} (on behalf of {$approval_on_behalf.on_behalf_of_u}){/if}</h1>

<div style="float:left;padding:4px;"><b>Select SKU to approve</b></div>
<div style="float:left" id=tab_sel><span id=sel_name>-</span>
<ul id=tab>
{section name=i loop=$sku}
{strip}
<li onclick="select_tab(this)" id="tab{$sku[i].id}" title="{$sku[i].id}">
{if $sku.status == 0}<img src=ui/notify_sku_new.png  width=16 height=16 align=absmiddle title="New Application">
{elseif $sku.status == 1}<img src=ui/notify_sku_approve.png width=16 height=16 align=absmiddle title="
	{if $sku.approvals=='' or $sku.approvals=='|'}
		Fully Approved
	{else}
	    In Approval Cycle
	{/if}">
{elseif $sku.status == 2}<img src=ui/notify_sku_reject.png width=16 height=16 align=absmiddle title="Rejected">
{elseif $sku.status == 3}<img src=ui/notify_sku_pending.png width=16 height=16 align=absmiddle title="KIV (Pending)">
{else}<img src=ui/notify_sku_terminate.png width=16 height=16 align=absmiddle title="
	{if $sku.approvals=='' or $sku.approvals=='|'}
		Terminated
	{else}
	    In Terminate Cycle
	{/if}">
{/if}
{$sku[i].id} (Brand: {$sku[i].brand}, Category: {$sku[i].category})</li>
{/strip}
  {if $smarty.request.id}
    {if $sku[i].id eq $smarty.request.id}<script>initial_select = '{$smarty.request.id}';</script>{/if}
  {/if}
{/section}
</ul>
</span>
</div>
<br style="clear:both">
<br>

<form name="f_on_behalf">
	{if $approval_on_behalf}
	<input type="hidden" name="on_behalf_of" value="{$approval_on_behalf.on_behalf_of}" />
	<input type="hidden" name="on_behalf_by" value="{$approval_on_behalf.on_behalf_by}" />
	{/if}
</form>

<div class="stdframe" style="background:#fff;">
<div id=udiv>
<!-- data will be loaded here -->
</div>
</div>

<script>
select_tab();
</script>
{include file=footer.tpl}

