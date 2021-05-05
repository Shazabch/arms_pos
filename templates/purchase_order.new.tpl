{*
++++++++++++++++
REVISION HISTORY
++++++++++++++++
6/12/2007 12:27:04 PM (gary) - added popup cost history for each row
8/2/2007 10:58:39 AM gary - po_date follow today date if eq '0000-00-00'

8/21/2007 4:11:59 PM - yinsee
- remove password requirement for "all category"

2/19/2008 6:40:48 PM gary
- if selling price readonly , fix the selling UOM.

2/20/2008 5:59:46 PM  yinsee
- zero qty not allow confirm

2/22/2008 4:35:18 PM gary
- not allow to add FOC items for the same FOC items.

*}

{include file=header.tpl}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
.sh
{
    background-color:#ff9;
}

.stdframe.active
{
 	background-color:#fea;
	border: 1px solid #f93;
}

td.xc
{
	border-bottom: 1px dashed #aaa;
}

.input_no_border input, .input_no_border select{
	border:1px solid #999;
	background: #fff;
	font-size: 10px;
	padding:2px;
}
</style>
<script>

var sku_autocomplete = [];
var total_sheet = 0;

// delete an SKU from PO
function delete_item(id)
{
 	if (!confirm('Remove this SKU from PO?')) return;

	// insert new row
	new Ajax.Request(
	    "purchase_order.php",
	    {
			method:'post',
			parameters: Form.serialize(document.f_a)+'&a=ajax_delete_po_row&id='+id,
		    evalScripts: true,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function (m) {
                Element.remove('titem'+id);
                refresh_foc_annotations();
                recalc_totals();
        	}
		}
	);
}

// edit foc table
function edit_foc(n, id, sku_item_id)
{
	new Ajax.Updater(
	    "sel_foc_cost",
	    "purchase_order.php",
	    {
			method:'post',
			parameters: '&a=ajax_sel_foc_cost&n='+n+'&id='+id+'&branch_id='+document.f_a.branch_id.value+'&po_id='+document.f_a.id.value+'&sid='+sku_item_id,
		    evalScripts: true,
			onComplete: function()
			{
				div_center_mouse('sel_foc_cost');
				$('sel_foc_cost').style.display = '';
			}
		}
	);
}

// popup FOC cost distribution selection list
function sel_foc_cost(n,id)
{
	var sstr = '['+n+']';

	if (document.f_a.elements["sku_item_id"+sstr].value == '' || document.f_a.elements["sku_item_id"+sstr].value == 0)
	{
	    alert('Please select an SKU to add');
        document.f_a.elements["sku"+sstr].focus();
        return;
	}

	new Ajax.Updater(
	    "sel_foc_cost",
	    "purchase_order.php",
	    {
			method:'post',
			parameters: Form.serialize(document.f_a)+'&a=ajax_sel_foc_cost&n='+n+'&id='+id+'&branch_id='+document.f_a.branch_id.value+'&po_id='+document.f_a.id.value,
		    evalScripts: true,
			onComplete: function()
			{
				div_center_mouse('sel_foc_cost');
				$('sel_foc_cost').style.display = '';
			}
		}
	);
}

// add FOC PO item
function cancel_foc()
{
    $('sel_foc_cost').style.display = 'none';
}

// refresh all foc annotations
function refresh_foc_annotations()
{
    new Ajax.Request(
		"purchase_order.php",
	    {
			method:'post',
			parameters: 'a=ajax_refresh_foc_annotations&po_id='+document.f_a.id.value,
			onComplete: function(m) {
			    var xml = m.responseXML;
			    if (!xml) { alert(m.responseText); return; }
				var records = xml.getElementsByTagName("record");
			 	// update annotations
				$A(records).each(
				    function(r,idx)
				    {
						var i = xml_getData(r,'id').strip();
						var t = xml_getData(r,'tag').strip();
						var f = xml_getData(r,'fid').strip();
				        if ($('foc_id'+i) != undefined)
							$('foc_id'+i).innerHTML = f;
				        if ($('foc_annotation'+i) != undefined)
				        	$('foc_annotation'+i).innerHTML = t;
					}
				)
			}
		});

}
// add FOC PO item
function save_foc_item(n,id)
{
	if (id == 0 || id == undefined)
		add_item(n, true);
	else
	    new Ajax.Request(
			"purchase_order.php",
		    {
				method:'post',
				parameters: Form.serialize(document.f_b)+'&a=ajax_update_foc_row&id='+id+'&branch_id='+document.f_a.branch_id,
			    evalScripts: true,
				onComplete: function(m) {
				    alert(m.responseText);
				 	$('sel_foc_cost').style.display = 'none';
				},
				onSuccess: function(m) {
					refresh_foc_annotations();
				}
			});
}


// add PO item
function add_item(n, is_foc){

	var sstr = '['+n+']';

	if (is_foc == undefined) is_foc = false;

	if (document.f_a.elements["sku_item_id"+sstr].value == '' || document.f_a.elements["sku_item_id"+sstr].value == 0)
	{
	    alert('Please select an SKU to add');
        document.f_a.elements["sku"+sstr].focus();
        return;
	}

	var parms;
	if (!is_foc)
		parms = Form.serialize(document.f_a) + '&' + '&a=ajax_add_po_row&n='+n;
	else
	    parms = Form.serialize(document.f_a) + '&' + Form.serialize(document.f_b)+'&a=ajax_add_foc_row&n='+n;

 	// insert new row
	new Ajax.Request(
	    "purchase_order.php",
	    {
			method:'post',
			parameters: parms,
		    evalScripts: true,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function (m) {
                var tb = $('po_items'+sstr);
                var lbody;
				var xml = m.responseXML;
				if (!xml) { alert(m.responseText); return; }
				var records = xml.getElementsByTagName("record");
		        // Get the text from an XML tag.
				$A(records).each(
				    function(r,idx)
				    {
					    var rowitem = tb.insertRow(-1);
					    rowitem.id = "titem"+xml_getData(r, "id").strip();
					    rowitem.innerHTML = xml_getData(r,'rowdata');
					}
				);
				document.f_a.elements['sku'+sstr].select();
				document.f_a.elements['sku'+sstr].focus();
				refresh_foc_annotations();
			},
			onComplete: function()
			{
				if (is_foc) $('sel_foc_cost').style.display = 'none';
			}

		});
}

// add new sheet
function add_sheet()
{
    $('add_sheet_notify').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
    hidediv('add_sheet');

	new Ajax.Updater(
		 {success:"new_sheets"},
		 "purchase_order.php",
	 	{
			method:'post',
	 	    parameters: Form.serialize(document.f_a)+'&a=ajax_add_sheet&n='+(total_sheet+1),
	 	    evalScripts: true,
	 	    onFailure: function(m) {
				alert(m.responseText);
			 	$('add_sheet_notify').innerHTML = '';
    			showdiv('add_sheet');
			},
	 	    onComplete: sheet_added,
	 	    insertion: Insertion.Bottom
		}
	);
}

// delete sheet
function cancel_sheet(id)
{
    if (!confirm('Are you sure?')) return;
    //$(id).style.display = 'none';
    Effect.DropOut(id, {duration:0.5, afterFinish: function() { Element.remove(id) } });
}

// callback when new sheet added
function sheet_added()
{
	if (!$('sheet['+(total_sheet+1)+']')) return;

	// unhilite current sheet
	if ($('sheet['+(total_sheet)+']')) $('sheet['+(total_sheet)+']').className = "stdframe";
	total_sheet++;
 	$('add_sheet_notify').innerHTML = '';
    showdiv('add_sheet');

	$('submitbtn').style.display='';

	init_sheet(total_sheet);
}

// update autocompleter parameters when vendor_id or department_id changed
function reset_sku_autocomplete(n)
{
	if (n == undefined)
	{
	    var i;
	    for (i=0;i<=total_sheet;i++) reset_sku_autocomplete(i);
	    return;
	}
	var sstr = '['+n+']';
	var param_str;

	if ($('all_dept'+sstr) == undefined || !$('all_dept'+sstr).checked)
		param_str = "a=ajax_search_sku&block_list=1&vendor_id="+document.f_a.vendor_id.value+"&dept_id="+document.f_a.department_id.value+"&type="+getRadioValue(document.f_a.elements["search_type"+sstr]);
	else
		param_str = "a=ajax_search_sku&block_list=1&vendor_id="+document.f_a.vendor_id.value+"&type="+getRadioValue(document.f_a.elements["search_type"+sstr]);
	
    if (sku_autocomplete[n] != undefined)
	{
		sku_autocomplete[n].options.defaultParams = param_str;
	}
	else
	{
		sku_autocomplete[n] = new Ajax.Autocompleter("autocomplete_sku"+sstr, "autocomplete_sku_choices"+sstr, "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		onShow: function(element, update){
		      update.style.position = 'absolute';
		      Position.clone(element, update, {setHeight: false, offsetTop: -parseInt(update.style.height)});
		      update.style.display = '';
    	},
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_a.elements["sku_item_id"+sstr].value = s[0];
			document.f_a.elements["sku_item_code"+sstr].value = s[1];
			// uncheck the passw
			//$('allow_dept'+sstr).checked = false;
			//$('dept_pass'+sstr).value = '';
			//$('pw'+sstr).style.display = 'none';
		    //reset_sku_autocomplete(n);
		}});

	}
	$('autocomplete_sku'+sstr).focus();
}

// temporary allow to search other department if password is correct
function allow_dept_autocomplete(n)
{
    var sstr = '['+n+']';

    sku_autocomplete[n].options.defaultParams = "a=ajax_search_sku&block_list=1&vendor_id="+document.f_a.vendor_id.value+"&type="+getRadioValue(document.f_a.elements["search_type"+sstr]); //+"&pw="+$('dept_pass'+sstr).value;
}

function init_calendar(sstr)
{
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
// initialize sheets for autocompelte and date picker field
function init_sheet(n)
{
	// initialize sku searching autocompleter
	reset_sku_autocomplete(n);

}

function uom_change(value,type,s,id)
{
	var a = value.split(",");
	var old_fraction = document.f_a.elements[type+'_uom_fraction['+s+']['+id+']'].value;

	document.f_a.elements[type+'_uom_id['+s+']['+id+']'].value = a[0];
	document.f_a.elements[type+'_uom_fraction['+s+']['+id+']'].value = a[1];

	// recalculate the selling or purchase price when uom changed
	document.f_a.elements[type+'_price['+s+']['+id+']'].value *= a[1]/old_fraction;
	if (type == 'selling'){
		document.f_a.elements[type+'_price['+s+']['+id+']'].value = round(document.f_a.elements[type+'_price['+s+']['+id+']'].value,2)	
	}
	else{
	    document.f_a.elements[type+'_price['+s+']['+id+']'].value = round(document.f_a.elements[type+'_price['+s+']['+id+']'].value,3)	
	}
	
	if (type == 'order' && document.f_a.elements['resell_price['+s+']['+id+']'] != undefined)
	{
		document.f_a.elements['resell_price['+s+']['+id+']'].value *= a[1]/old_fraction;
        document.f_a.elements['resell_price['+s+']['+id+']'].value = round(document.f_a.elements['resell_price['+s+']['+id+']'].value,3);
	}

	// hide loose  if uom is 1
	//added gary 6/18/2007 1:57:46 PM to avoid selling uom control
	if(type=='order'){
		disabled = (a[1] == 1)
		// if multiple branch, sum the qty and update
		if ($('q'+id) == undefined){
			for (i=0;i<10;i++){
			    if ($('q'+id+'['+i+']') != undefined){
					if (a[1]==1){
						$('q'+id+'['+i+']').value = '--';
						$('f'+id+'['+i+']').value = '--';
					}
					$('q'+id+'['+i+']').disabled = disabled;
					$('f'+id+'['+i+']').disabled = disabled;
				}
			}
		}
		else{
			if (a[1]==1){
	            $('q'+id).value = '--';
	            $('f'+id).value = '--';
			}
			$('q'+id).disabled = disabled;
			$('f'+id).disabled = disabled;
		}
	}
}

// parse formula in the form of 10%+100+x%+y...
// value = orig value
// obj = input field with the formula
// add = true -> add, false -> discount
function parse_formula(value, obj, add)
{
    obj.value = obj.value.regex(/[^0-9\.%+]/g,'');
    obj.value = obj.value.regex(/\+$/,'');
	if (obj.value != '')
	{
		$A(obj.value.split("+")).each( function(r,idx) {
		    if (add)
		    {
				if (r.indexOf("%")>0)
			        value *= (100+float(r))/100;
				else
				    value = float(value) + float(r);
			}
			else
			{
				if (r.indexOf("%")>0)
			        value *= (100-float(r))/100;
				else
				    value = float(value) - float(r);
			}
		}
		);
	}
	return value;
}


function row_recalc(id)
{
	var foc = 0;
	var qty = 0;
	var ctn = 0;
	var total_sell = 0;

	// if multiple branch, sum the qty and update
	if ($('q'+id) == undefined)
	{
		for (i=0;i<10;i++)
		{
		    if ($('q'+id+'['+i+']') != undefined)
		    {
		        if (int($('ouomf'+id).value) > 1 && int($('ql'+id+'['+i+']').value) > int($('ouomf'+id).value))
                {
				    $('q'+id+'['+i+']').value = int($('q'+id+'['+i+']').value) + int($('ql'+id+'['+i+']').value / $('ouomf'+id).value);
                    $('ql'+id+'['+i+']').value = $('ql'+id+'['+i+']').value % $('ouomf'+id).value;
				}
				if (int($('ouomf'+id).value) > 1 && int($('fl'+id+'['+i+']').value) > int($('ouomf'+id).value))
                {
				    $('f'+id+'['+i+']').value = int($('f'+id+'['+i+']').value) + int($('fl'+id+'['+i+']').value / $('ouomf'+id).value);
                    $('fl'+id+'['+i+']').value = $('fl'+id+'['+i+']').value % $('ouomf'+id).value;
				}
				 q1 = int($('q'+id+'['+i+']').value)*float($('ouomf'+id).value) + int($('ql'+id+'['+i+']').value);
				 qty += q1
				 q2 = int($('f'+id+'['+i+']').value)*float($('ouomf'+id).value) + int($('fl'+id+'['+i+']').value);
				 foc += q2;
				 ctn += int($('q'+id+'['+i+']').value)+int($('f'+id+'['+i+']').value);

				 total_sell += $('sp'+id+'['+i+']').value*(q1+q2)/float($('suomf'+id).value);
				 
				 $('br_sp['+i+']['+id+']').innerHTML = $('sp'+id+'['+i+']').value*(q1+q2)/float($('suomf'+id).value);
				 $('br_cp['+i+']['+id+']').innerHTML = $('op'+id).value*q1/float($('ouomf'+id).value);
			}
		}
	}
	else
	// otherwise, just update amount
	{
		if (int($('ouomf'+id).value)>1 && int($('ql'+id).value) > int($('ouomf'+id).value))
	    {
		    $('q'+id).value = int($('q'+id).value) + int($('ql'+id).value / $('ouomf'+id).value);
	        $('ql'+id).value = $('ql'+id).value % $('ouomf'+id).value;
		}
		if (int($('ouomf'+id).value)>1 && int($('fl'+id).value) > int($('ouomf'+id).value))
	    {
		    $('f'+id).value = int($('f'+id).value) + int($('fl'+id).value / $('ouomf'+id).value);
	        $('fl'+id).value = $('fl'+id).value % $('ouomf'+id).value;
		}
		qty = int($('q'+id).value)*$('ouomf'+id).value + int($('ql'+id).value);
		foc = int($('f'+id).value)*$('ouomf'+id).value + int($('fl'+id).value);
		ctn = int($('q'+id).value)+int($('f'+id).value);

		total_sell = $('sp'+id).value*(qty+foc)/float($('suomf'+id).value);
	}

	$('qty'+id).innerHTML = qty;
	$('foc'+id).innerHTML = foc;
	$('ctn'+id).innerHTML = ctn;

	$('total_sell'+id).innerHTML = round2(total_sell);

	amount = $('op'+id).value*qty/float($('ouomf'+id).value);
	if ($('is_foc'+id).value != 1)
		$('gamount'+id).innerHTML = round2(amount);

	if ($('tax'+id).value != '')
	{
		$('tax'+id).value = float($('tax'+id).value);
		amount *= (float($('tax'+id).value)+100)/100;
	}

	camount = amount;
	amount = parse_formula(amount, $('disc'+id), false);
	if ($('disc'+id).value.indexOf("%")>=0)
		$('disc_amount'+id).innerHTML = round2(camount - amount);
	else
		$('disc_amount'+id).innerHTML = '';
		
	if ($('is_foc'+id).value != 1)
		$('amount'+id).innerHTML = round2(amount) + "<br><font color=blue>" + round(amount/(foc+qty),3) + "</font>";

	if ($('is_foc'+id).value !=1)
		$('total_profit'+id).innerHTML = round2(total_sell - amount);
	else
	    $('total_profit'+id).innerHTML = round2(total_sell);

	if (float($('total_profit'+id).innerHTML)<=0)
		$('total_profit'+id).style.color = '#f00';
	else
	    $('total_profit'+id).style.color = '';

	$('total_margin'+id).innerHTML = round2($('total_profit'+id).innerHTML/total_sell*100) + '%';
	if (float($('total_margin'+id).innerHTML)<=0)
		$('total_margin'+id).style.color = '#f00';
	else
	    $('total_margin'+id).style.color = '';
	    
	recalc_totals();
}

// recalculate totals
function recalc_totals()
{
	/// get each SPAN under the table
	var sp = $('po_items[0]').getElementsByTagName("SPAN");
	var aa = 0;
	var ga = 0;
	var ts = 0;
	var tp = 0;
	var qty = 0;
	var foc = 0;
	var ctn = 0;
	var cnt = 1;
	var ttb = new Array();
	$A(sp).each(
		function (r,idx)
		{
		//$('br_sp['+i+']['+id+']').innerHTML = $('sp'+id+'['+i+']').value*(q1+q2)/float($('suomf'+id).value);
		//		 $('br_cp['+i+']['+id+']').innerHTML = $('op'+id).value*(q1+q2)/float($('ouomf'+id).value);
				 
			if (r.id.indexOf("count")==0)
			{
				r.innerHTML = cnt + ".";
				cnt++;
			}
			else if (r.id.indexOf("br_sp")==0)
			{
				vid = r.id.substr(0,r.id.lastIndexOf('['));
				if (isNaN(ttb[vid])) ttb[vid] = 0;
				ttb[vid] += float(r.innerHTML);
			}
			else if (r.id.indexOf("br_cp")==0)
			{
				vid = r.id.substr(0,r.id.lastIndexOf('['));
				if (isNaN(ttb[vid])) ttb[vid] = 0;
				ttb[vid] += float(r.innerHTML);
			}
			else if (r.id.indexOf("amount")==0)
			{
			    if (r.innerHTML != 'FOC')
				    aa += float(r.innerHTML);
			}
			else if (r.id.indexOf("gamount")==0)
			{
			    if (r.innerHTML != 'FOC')
					ga += float(r.innerHTML);
			}
			else if (r.id.indexOf("total_sell")==0)
			{
			    if (r.innerHTML != 'FOC')
					ts += float(r.innerHTML);
			}
			else if (r.id.indexOf("total_profit")==0)
			{
			    if (r.innerHTML != 'FOC')
					tp += float(r.innerHTML);
			}
			else if (r.id.indexOf("qty")==0)
			{
				qty += float(r.innerHTML);
			}
			else if (r.id.indexOf("foc")==0)
			{
				foc += float(r.innerHTML);
			}
			else if (r.id.indexOf('ctn')==0)
			{
				ctn += float(r.innerHTML);
			}
		}
	);

	$('total_ctn[0]').innerHTML = 'Ctn: ' + ctn;
	$('total_pcs[0]').innerHTML = 'Pcs: ' + (qty+foc);
	$('total_check[0]').value = ctn+qty+foc;
	$('total_gross_amount[0]').innerHTML = round2(ga);
	$('total_amount[0]').innerHTML = round2(aa);
	$('total_sell[0]').innerHTML = round2(ts);
	$('total_profit[0]').style.color = (tp<=0) ? '#f00' : '#000';
	$('total_profit[0]').innerHTML = round2(tp);
	$('total_margin[0]').style.color = (tp<=0) ? '#f00' : '#000';
	$('total_margin[0]').innerHTML = round2(tp/ts*100)+'%';
	
	$H(ttb).each(
		function (item)
		{
			$(item.key).innerHTML = round2(item.value);
		}
	);

	// po total	
	var a = float($('total_amount[0]').innerHTML);
	a = parse_formula(a, $('misc_cost[0]'), true);
	a = parse_formula(a, $('sdiscount[0]'), false);
	var b = a; // b is vendor's PO amount, skip calculation from remark#2
	a = parse_formula(a, $('rdiscount[0]'), false);
	a = parse_formula(a, $('ddiscount[0]'), false);
	a += float($('transport_cost[0]').value);
	b += float($('transport_cost[0]').value);
	$('final_amount[0]').innerHTML = round2(a);
	$('final_amount2[0]').innerHTML = round2(b);
	document.f_a.po_amount.value = round2(a);

	var ts = float($('total_sell[0]').innerHTML);
	var pf = ts - a;
	$('final_profit[0]').style.color = (pf<=0) ? '#f00' : '#000';
	$('final_profit[0]').innerHTML = round2(pf);
	$('final_margin[0]').style.color = (pf<=0) ? '#f00' : '#000';
	$('final_margin[0]').innerHTML = round2(pf/ts*100) + '%';
}


function check_a()
{
	if (empty(document.f_a.vendor_id, "You must select a vendor"))
	{
	    return false;
	}
	if (empty(document.f_a.po_date, "You must enter PO Date"))
	{
	    return false;
	}
	if (document.f_a.elements["delivery_date"])
	{
		if (empty(document.f_a.elements["delivery_date"], "You must enter Delivery Date"))
		{
		    return false;
		}
		if (empty(document.f_a.elements["cancel_date"], "You must enter Cancellation Date"))
		{
		    return false;
		}
	}
	else
	{
	    for (i=0;i<10;i++)
	    {
	        if ($("dt_"+i) != undefined && $("dt_"+i).checked)
	        {
	            if (empty(document.f_a.elements["delivery_date["+i+"]"], "You must enter Delivery Date"))
				{
				    return false;
				}
				if (empty(document.f_a.elements["cancel_date["+i+"]"], "You must enter Cancellation Date"))
				{
				    return false;
				}
			}
		}
	}

	return true;
}

function do_save()
{
	document.f_a.a.value='save';
	document.f_a.target = "";
	if(check_a()) document.f_a.submit();
}

function do_delete()
{
	if (confirm('Delete this PO?'))
	{
		document.f_a.a.value='delete';
		document.f_a.target = "";
		document.f_a.submit();
	}
}

function do_cancel()
{
	if (confirm('Cancel this PO?'))
	{
		document.f_a.a.value='cancel';
		document.f_a.target = "";
		document.f_a.submit();
	}
}

function do_print()
{
	if (document.f_a.id.value == '' || document.f_a.id.value == 0)
	{
		alert('You must SAVE the PO before it can be printed.');
		exit;
	}

	curtain(true);
	show_print_dialog();
}

function show_print_dialog()
{
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok()
{
	$('print_dialog').style.display = 'none';
	document.f_a.a.value = "print";
	document.f_a.target = "ifprint";
	document.f_a.submit();
	curtain(false);
}


function print_all()
{
	document.f_a.print_vendor_copy.checked = true;
	document.f_a.print_branch_copy.checked = true;
	print_ok();
}

function print_cancel()
{
	$('print_dialog').style.display = 'none';
	curtain(false);
}

function refresh_tables(){
	document.f_a.a.value = "refresh";
	document.f_a.target = "";
	document.f_a.submit();
}

function do_confirm()
{
	if (confirm('Finalize PO and submit for approval?'))
	{
		document.f_a.a.value = "confirm";
		document.f_a.target = "";
		document.f_a.submit();
	}
}

function hide_sheets()
{
	$('srefresh').style.display='';
	if ($('new_sheets') != undefined)
	{
		$('new_sheets').style.display='none';
		$('submitbtn').style.display='none';
	}
}

function discount_help()
{
	msg = '';
	msg += "Sample input\n";
	msg += "------------\n";
	msg += "10% => discount of 10 percent\n";
	msg += "10  => discount of RM10\n";
	msg += "10%+10 => discount 10%, follow by RM10\n";
	msg += "10+10% => discount RM10, then discount 10%\n";

	alert(msg);
}

function cost_help()
{
	msg = '';
	msg += "Sample input\n";
	msg += "------------\n";
	msg += "10% => add 10 percent\n";
	msg += "10  => add RM10\n";
	msg += "10%+10 => add 10%, follow by RM10\n";
	msg += "10+10% => add RM10, then 10%\n";

	alert(msg);
}

function get_price_history(element,n)
{
	var sstr = '['+n+']';

	if (document.f_a.elements["sku_item_id"+sstr].value == '' || document.f_a.elements["sku_item_id"+sstr].value == 0)
	{
	    alert('Please select an SKU and click the history button again');
        document.f_a.elements["sku"+sstr].focus();
        return;
	}
	
	var id = document.f_a.elements["sku_item_id"+sstr].value;
	Position.clone(element, $('price_history'+sstr), {setHeight: false, setWidth:false, offsetTop: -parseInt($('price_history'+sstr).style.height)});
	Element.show('price_history'+sstr);
	$('price_history_list'+sstr).innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater(
	'price_history_list'+sstr,
	'ajax_autocomplete.php',
		{
		    parameters: 'a=sku_cost_history&id='+id
		}
	);
}

// open sku detail in new window
function show_sku_detail(n)
{
    code = document.f_a.elements['sku_item_id['+n+']'].value;
    if (code=='' || code==0) 
	{
		alert('You have not select any item.');
	    return;
    }
    window.open('masterfile_sku.php?a=view&id='+code+'&from_po=1');
}

// expand the item's varieties
function toggle_vendor_sku(sku_id,id)
{
	if ($('xp'+id).innerHTML == "varieties")
	{
		$('xp'+id).innerHTML = "hide varieties";
		$('cb'+id).disabled = true;
		$('cb'+id).checked = false;
		insert_after = $('li'+id);

		new Ajax.Updater(
		    insert_after,
		    "purchase_order.php",
		    {
				method:'post',
				parameters: '&a=ajax_expand_sku&sku_id='+sku_id,
			    evalScripts: true,
		 	    insertion: Insertion.Bottom
			});
  	}
  	else
  	{
  		$('xp'+id).innerHTML = "varieties";
		$('cb'+id).disabled = false;
  		Element.remove('ul'+sku_id);
	}
}

// show all SKU of selected vendor
function show_vendor_sku(){
	$('sel_vendor_sku').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	showdiv('sel_vendor_sku');
	center_div('sel_vendor_sku');
			
	new Ajax.Updater(
		'sel_vendor_sku',
		'purchase_order.php',
		{
		    method:'post',
		    parameters: Form.serialize(document.f_a)+'&a=ajax_show_vendor_sku',
		    evalScripts: true
		}
	);
}

function show_related_sku(n){
    code = document.f_a.elements['sku_item_id['+n+']'].value;
    if (code=='' || code==0) {
		alert('You have not select any item.');
	    return;
    }
	$('sel_vendor_sku').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	showdiv('sel_vendor_sku');
	center_div('sel_vendor_sku');
			
	new Ajax.Updater(
		'sel_vendor_sku',
		'purchase_order.php',
		{
		    method:'post',
		    parameters: Form.serialize(document.f_a)+'&a=ajax_show_related_sku&sku_item_id='+code,
		    evalScripts: true
		}
	);
}

// cancel Vendor SKU window
function cancel_vendor_sku()
{
	hidediv('sel_vendor_sku');
}

// add all items selected from Vendor SKU window
function do_vendor_sku()
{
	new Ajax.Updater(
	    'sel_vendor_sku',
		'purchase_order.php',
		{
		    method:'post',
		    parameters: Form.serialize(document.f_s)+'&a=ajax_add_vendor_sku&vendor_id='+document.f_a.vendor_id.value+'&branch_id='+document.f_a.branch_id.value+'&id='+document.f_a.id.value,
		    evalScripts: true
		}
	);
}

// show child sku
function sku_show_varieties(sku_id)
{
	showdiv('sel_vendor_sku');
	new Ajax.Updater(
	    'sel_vendor_sku',
		'purchase_order.php',
		{
		    method:'post',
		    parameters: 'a=ajax_expand_sku&showheader=1&sku_id='+sku_id,
		    evalScripts: true
		}
	);
}

//added cost history by gary
function get_price_history_row(obj,id){
	Position.clone(obj, $('price_history_row'), {setHeight: false, setWidth:false});
	Element.show('price_history_row');
	$('price_history_list_row').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater(
	'price_history_list_row',
	'ajax_autocomplete.php',
		{
		    parameters: 'a=sku_cost_history&id='+id
		}
	);
}
</script>
{/literal}

<div id="price_history_row" style="display:none;position:absolute;width:350px;height:275px;background:#fff;border:1px solid #000;padding:5px;">

	<div id="price_history_list_row" style="width:350px;height:250px;overflow:auto;"></div>
	<div align=center style="padding-top:5px">
	<input type=button onclick="Element.hide(this.parentNode.parentNode)" value="Close">
	</div>
</div>

<h1>Purchase Order {if $form.id}(ID#{$form.id}){else}(New){/if}</h1>

{if $approval_history}
<br>
<div class="stdframe" style="background:#fff">
<h4>Approval History</h4>
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
{/if}

<form name="f_a" method=post ENCTYPE="multipart/form-data">
<input type=hidden name=a value="save">
<input type=hidden name=approval_history_id value="{$form.approval_history_id}">
<input type=hidden name=po_amount value="{$form.po_amount}">
<input type=hidden name=id value="{$form.id|default:0}">
<input type=hidden name=user_id value="{$form.user_id|default:$sessioninfo.id}">
<input type=hidden name=branch_id value="{$form.branch_id|default:$sessioninfo.branch_id}">
<input type=hidden name=is_request value="{$form.is_request}">

<!-- print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:250px;height:100px;position:absolute; padding:10px; display:none;">
<img src=ui/print64.png hspace=10 align=left> <h3>Print Options</h3>
<input type=checkbox name="print_vendor_copy" checked> Vendor's Copy<Br>
<input type=checkbox name="print_branch_copy" checked> Branch's Copy (Internal)<br>
<p align=center><input type=button value="Print" onclick="print_ok()"> <!--input type=button value="Print All" onclick="print_all()"--> <input type=button value="Cancel" onclick="print_cancel()"></p>
</div>

<br>

<div class="stdframe" style="background:#fff">
<h4>General Informationds</h4>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<table  border=0 cellspacing=0 cellpadding=4>
{if $form.is_request}
<tr>
	<td><b>PO Request By</b></td>
	<td colspan=3>
    	{$form.request_user}
	</td>
</tr>
{/if}
<tr>
	<td><b>Vendor</b></td>
	<td colspan=3>
    	<input name="vendor_id" size=1 value="{$form.vendor_id}" readonly>
		<input id="autocomplete_vendor" name="vendor" value="{$form.vendor}" size=50>
		<div id="autocomplete_vendor_choices" class="autocomplete" style="width:500px !important"></div>
		<img src=ui/rq.gif align=absbottom title="Required Field">
		<input type=button value="Show SKU of this Vendor" onclick="show_vendor_sku()">
	</td>
</tr>
<tr>
	<td><b>Department</b></td>
	<td>
		<select name="department_id" onchange="reset_sku_autocomplete();{if $sessioninfo.branch_id == 1}refresh_tables();{/if}">
		{section name=i loop=$dept}
		<option value={$dept[i].id} {if $form.department_id eq $dept[i].id}selected{/if}>{$dept[i].description}</option>
		{/section}
		</select>
	</td>
</tr>
<tr>
	<td><b>PO Date</b></td>
	<td>
		<input name="po_date" value="{if $form.po_date>0}{if $form.id>0}{$form.po_date|date_format:"%d/%m/%Y"}{else}{$form.po_date}{/if}{else}{$smarty.now|date_format:"%d/%m/%Y"}{/if}" size=10>
		<!--input name="po_date" value="{if $form.po_date>0}{$form.po_date|date_format:"%d/%m/%Y"}{else}{$smarty.now|date_format:"%d/%m/%Y"}{/if}" size=10--><br>
		dd/mm/yyyy
	</td>
</tr>

{if $form.branch_id == 1}
<tr>
	<td><b>PO Option</b></td>
	<td>
		<!--input onchange="if (total_sheet>=0) hide_sheets()" type=radio name="po_option" value="1" {if $form.po_option eq '1'}checked{/if}> HQ Purchase and sell to Branches <font color=#990000><b>(HQ Payment)</b></font><br>
		<input onchange="if (total_sheet>=0) hide_sheets()" type=radio name="po_option" value="2" {if $form.po_option eq '2'}checked{/if}> HQ purchase on behalf of Branches <font color=#990000><b>(Branch Payment)</b></font-->

		<input type=radio name="po_option" value="2" checked> HQ purchase on behalf of Branches <font color=#990000><b>(Branch Payment)</b></font>
	</td>
</tr>
<tr>
	<td valign=top><b>Delivery Branches</b></td>
	<td>
		You may select multiple branches to deliver <br>
		<table class="small" border=0>
		{section name=i loop=$branch}
		{assign var=bid value=$branch[i].id}
		<tr>
			<td valign=top>
			<input onchange="hide_sheets()" type=checkbox id=dt_{$branch[i].id} name="deliver_to[]" value="{$branch[i].id}" {if is_array($form.deliver_to) and in_array($branch[i].id,$form.deliver_to)}checked{/if}><label for=dt_{$branch[i].id}>&nbsp;{$branch[i].code}</label>
			</td>
			<td>
				<table border=0 {if !is_array($form.deliver_to) or !in_array($branch[i].id,$form.deliver_to)}style="display:none"{/if}>
					<tr>
						<td colspan=6>
						<i>Deliver by</i> <input size=1 name=delivery_vendor[{$branch[i].id}] value="{$form.delivery_vendor[$bid]|default:0}" readonly> <input size=50 id="vendor[{$branch[i].id}]" name=delivery_vendor_name[{$branch[i].id}] value="{$form.delivery_vendor_name[$bid]|default:"-same as above-"}" onclick="this.select()">
						<div id="autocomplete_vendor[{$branch[i].id}]" class="autocomplete"></div>
						<script>
						new Ajax.Autocompleter("vendor[{$branch[i].id}]", "autocomplete_vendor[{$branch[i].id}]", "ajax_autocomplete.php?a=ajax_search_vendor", {literal}{ paramName:"vendor", afterUpdateElement: function (obj, li) { {/literal}document.f_a.elements['delivery_vendor[{$branch[i].id}]'].value = li.title; {literal}}}{/literal});
						</script>
						</td>
					</tr>
					<tr>
						<td><i>Delivery Date</i></td>
						<td>
							<input type="text" name="delivery_date[{$bid}]" id="dt1[{$bid}]" value="{$form.delivery_date[$bid]}" size=12 /> <img align=absbottom src="ui/calendar.gif" id="t_dt1[{$bid}]" style="cursor: pointer;" title="Select Date"/>
						</td>
						<td><i>Cancellation Date</i></td>
						<td>
							<input type="text" name="cancel_date[{$bid}]" id="dt2[{$bid}]" value="{$form.cancel_date[$bid]}" size=12 /> <img align=absbottom src="ui/calendar.gif" id="t_dt2[{$bid}]" style="cursor: pointer;" title="Select Date"/>
						</td>
						<td>
							<input name="partial_delivery[{$bid}]" type="checkbox" {if $form.partial_delivery[$bid]}checked{/if} id="pd{$bid}"> <label for="pd{$bid}">Allow Partial Delivery</label>
						</td>
					</tr>
					
					<tr>
					<td valign=top><i>User Selection</i></td>
					<td colspan=4>
						<div id=user_select style="height:100px;width:200px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;">
						{section name=u loop=`$user_list.$bid.user`}
 						{assign var=u value=`$smarty.section.u.iteration-1`}
 						{assign var=id value=`$user_list.$bid.user_id.$u`}
						<input type=checkbox name=allowed_user[{$bid}][{$id}] {if $form.allowed_user.$bid.$id}checked{/if}>{$user_list.$bid.user.$u}<br>
						{/section}
						</div>
					</td>

					</tr>
				</table>
			</td>
		</tr>
		<script>init_calendar('[{$bid}]');</script>
		{/section}
		</table>
		<div id=srefresh style="display:none; padding-top:10px">
		<input type=button onclick="void(refresh_tables())" style="font-size:1.5em; color:#fff; background:#091" value="click here to continue">
		</div>
	</td>
</tr>


{else}
<tr>
	<td><b>Delivery Branch</b></td>
	<td>{$form.branch|default:$BRANCH_CODE}</td>
</tr>
<tr>
	<td><b>Delivery Date</b></td>
	<td>
		<input type="text" name="delivery_date" id="dt1" value="{$form.delivery_date}" size=12 /> <img align=absbottom src="ui/calendar.gif" id="t_dt1" style="cursor: pointer;" title="Select Date"/>
		<div>dd/mm/yyyy</div>
	</td>

	<td><b>Cancellation Date</b></td>
	<td>
		<input type="text" name="cancel_date" id="dt2" value="{$form.cancel_date}" size=12 /> <img align=absbottom src="ui/calendar.gif" id="t_dt2" style="cursor: pointer;" title="Select Date"/>
		<div>dd/mm/yyyy</div>
	</td>
</tr>

<tr>
	<td><b>Partial Delivery</b></td>
	<td>
		<input name="partial_delivery" type="checkbox" {if $form.partial_delivery}checked{/if} id="pd"> <label for="pd">Allowed</label>
	</td>
</tr>

<script>init_calendar('');</script>
{/if}
</table>
</div>

<br>
{if $form.branch_id != 1 or count($form.deliver_to)>0}
<div id="new_sheets">
{assign var=sheet_n value=0}
{include file=purchase_order.new.sheet.tpl}
<script>init_sheet({$sheet_n});</script>
</div>

<!--div>
<a name="additem"> </a>
<span id="add_sheet">
<img src=ui/new.png align=absmiddle> <a href="javascript:void(add_sheet())">Add Scheduled PO</a>
</span>
<span id="add_sheet_notify"></span>
</div-->
<p id=submitbtn align=center>

<input name=bsubmit type=button value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save()" >
{if !$form.id}
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/purchase_order.php'">
{/if}
{if $form.approval_history_id>0}
<input type=button value="Cancel" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_cancel()">
{else}
<input type=button value="Delete" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_delete()">
{/if}
<input type=button value="Confirm" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_confirm()">

{if $form.id>0}
<input type=button value="Print Draft PO" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_print()">
{/if}
</p>
{else}
<!-- no branch selected for HQ PO -->
* No branch was selected for PO
{/if}
</form>

<div id="sel_foc_cost" style="position:absolute;left:0;top:0;display:none;width:400px;height:250px;padding:10px;border:1px solid #000; background:#fff">
</div>

<div id="sel_vendor_sku" style="position:absolute;left:0;top:0;display:none;width:600px;height:400px;padding:10px;border:1px solid #000; background:#fff">
</div>

<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>
{include file=footer.tpl}

<script>
{literal}
if (total_sheet<0) $('submitbtn').style.display='none';

new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", { afterUpdateElement: function (obj, li) { document.f_a.vendor_id.value = li.title; reset_sku_autocomplete(); }});

new Draggable('sel_foc_cost', {starteffect:undefined,endeffect:undefined});
//new Draggable('sel_vendor_sku', {starteffect:undefined,endeffect:undefined});
//document.f_a.vendor.focus();
{/literal}
</script>
