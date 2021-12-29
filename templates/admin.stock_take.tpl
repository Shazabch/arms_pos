{*
5/3/2010 1:34:15 PM Andy
- Fix Bugs (calendar cannot show)

5/12/2010 6:11:17 PM Andy
- change import and reset notice popup.
- disable script to auto reload table after import stock take.
- add "Set quantity to zero for items not in stock take" at stock take import
- Split add/edit and import/reset stock take.

5/13/2010 2:56:20 PM Andy
- When print stock take report or checklist, open a new page for user to preview first.
- Add column "stock balance" and "variance".

5/20/2010 3:20:38 PM Andy
- Add sorting for location and shelf range.
- Shelf range change to only show those between selected location.

7/23/2010 4:59:47 PM Andy
- Add single server mode and hq can create stock take for branch.
- Fix stock take item list if open multiple tab will cause bugs.
- Fix duplicate ajax call by sku autocomplete.

8/19/2010 3:33:21 PM Alex
- Add SKU type filter

9/7/2010 6:09:54 PM Alex
- add sku scan at main page and stock count sheet no.

6/22/2011 10:57:11 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

9/27/2011 12:55:45 PM Justin
- Modified the Ctn and Pcs round up to base on config set.
- Added new function to change the qty field to enable/disable decimal points key in base on sku item's doc_allow_decimal.

12/2/2011 12:14:32 PM Justin
- Fixed the bugs where system cannot renew qty and recalculate the variance while qty has been changed from Add New Stock.

2/20/2012 3:04:14 PM Alex
- add cost_price and global_cost_decimal_points checking

2/14/2014 4:43 PM Justin
- Enhanced to calculate and show actual variance at the last item when an item have been insert multiple.

12/04/2015 1:43PM DingRen
-  add a legend  "Fresh Market sku need to go fresh market stock take".

12/19/2018 5:14 PM Andy
- Enhanced to can add new stock take by csv.
*}

{include file='header.tpl'}

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
#div_stock_take_details{
    background-color:#FFFFFF;
	background-image:url(/ui/ndiv.jpg);
	background-repeat:repeat-x;
}
#div_stock_take_details_header{
    border:2px ridge #CE0000;
	color:white;
	background-color:#CE0000;
	padding:2px;
	cursor:default;
}

#div_debtor_details_content{
    padding:2px;
}

.calendar, .calendar table {
	z-index:100000;
}
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
</style>
{/literal}


<script>
var sku_item_id;

var phpself = '{$smarty.server.PHP_SELF}';
var tab_num = 1;
var page_num = 0;

// Config Control Here
var stock_take_count_sheet = '{$config.stock_take_count_sheet}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';

{if $config.sku_type_outright}
	var stock_take_sku_type = 'OUTRIGHT';
{else}
	var stock_take_sku_type ='';
{/if}
{literal}
var sku_autocomplete = undefined;
var sku_autocomplete2 = undefined;
var is_escape = false;
var last_obj;
var search_str = '';

function add(){
	open(0);
}

function open(id)
{
  //	curtain(true);
//	center_div('div_stock_take_details');
	jQuery('#div_stock_take_details').modal('show');
  
	$('div_stock_take_details_content').update(_loading_);
	sku_autocomplete = undefined;
	new Ajax.Updater('div_stock_take_details_content',phpself,{
	    parameters:{
			a: 'open',
			id: id,
			branch_id: document.selection['branch_id'].value,
			date: document.selection['dat'].value,
			loc: document.selection['loc'].value,
			shelf: document.selection['shelf'].value
		},
		evalScripts: true
	})
}

function reload_table()
{
	$('span_refreshing').update(_loading_);
	new Ajax.Updater('div_table',phpself,{
		parameters: $(document.tbl).serialize()+'&a=reload_table'
	});
}

function curtain_clicked(){
	jQuery('#div_stock_take_details').modal('hide');
}

function branch_changed(){
	var bid = document.selection['branch_id'].value;
	$('div_date').update(_loading_);
	new Ajax.Updater('div_date', phpself, {
		parameters:{
			'a': 'ajax_load_date',
			'branch_id': bid
		}
	});
	document.selection['loc'].length = 0;
	document.selection['shelf'].length = 0;

	document.selection['loc2'].length = 0;
	document.selection['loc3'].length = 0;
	document.selection['shelf2'].length = 0;
	document.selection['shelf3'].length = 0;
}

function load_location(date)
{
	var branch_id = document.selection['branch_id'].value;
	
  $('div_location').update(_loading_);
	new Ajax.Updater('div_location',phpself,{
		parameters:{
			a: 'load_location',
			d: date,
			'branch_id': branch_id
		},onComplete: function(msg){
				//add other here
				load_range(date, branch_id);
		}
	});
	document.selection['shelf'].length = 0;

}

function load_range(v, branch_id)
{
    $('range').update(_loading_);
  	new Ajax.Updater('range',phpself,{
  		parameters:{
  			a: 'load_range',
  			d: v,
  			'branch_id': branch_id
  		}
  	});
}

function load_shelf(location)
{
  var branch_id = document.selection['branch_id'].value;
  var dats =  document.selection.dat.value;
  $('div_shelf').update(_loading_);
	new Ajax.Updater('div_shelf',phpself,{
		parameters:{
			a: 'load_shelf',
			dat: dats ,
			loc: location,
			'branch_id': branch_id
		}
	});
}

function show_record()
{
	if(!document.selection['dat'].value.trim()){
		alert('Please Select Date.');
		return false;
	}else if(!document.selection['loc'].value.trim()){
		alert('Please Select Location.');
		return false;
    }else if(!document.selection['shelf'].value.trim()){
		alert('Please Select Shelf.');
		return false;
    }
    document.tbl['date'].value = document.selection.dat.value;
    document.tbl['location'].value = document.selection.loc.value;
    document.tbl['shelf'].value = document.selection.shelf.value;
    document.tbl['sku_type'].value = document.selection.sku_type.value;
    document.tbl['branch_id'].value = document.selection['branch_id'].value;

    $('div_table_frame').show();

    $('div_table').update(_loading_).show();
  	new Ajax.Updater("div_table",phpself,{
		parameters: document.selection.serialize(),
	});
}

function sku_show_record()
{
	if(!document.selection['dat'].value.trim())
	{
		return false;
	}
	else if(!document.selection['loc'].value.trim())
	{
		return false;
	}
	else if(!document.selection['shelf'].value.trim())
	{
		return false;
	}
	document.tbl['date'].value = document.selection.dat.value;
	document.tbl['location'].value = document.selection.loc.value;
	document.tbl['shelf'].value = document.selection.shelf.value;
    document.tbl['sku_type'].value = document.selection.sku_type.value;
	document.tbl['branch_id'].value = document.selection['branch_id'].value;

	$('div_table').update(_loading_).show();
	new Ajax.Updater("div_table",phpself,{
		parameters: document.selection.serialize(),
	});
}

function checkkey(event,q)
{
	if($('handheld').checked){
		if(event == undefined) event = window.event;
		if(event.keyCode==13 && q=='1'){  // enter
//			document.f_a['qty'].value=float(round(document.f_a['qty'].value, global_qty_decimal_points));
			submit_form('save');
			document.f_a['sku'].value='';
			document.f_a['qty'].value='';
			document.f_a['sku'].focus();
			document.f_a['sku_scan'].value = '';
		}else if(event.keyCode==13){
			checkcode();
		}
	}
   /*
   if(event.keyCode==13){  // enter
    checkcode();
   } */
}

function checkkey2(event2,q)
{
	if($('handheld2').checked){
		if (event2 == undefined) event2 = window.event;
		if(event2.keyCode==13 && q=='1'){  // enter
//			document.tbl['qty2'].value=float(round(document.tbl['qty2'].value, global_qty_decimal_points));
			submit_form2('save');
			document.tbl['sku2'].value='';
			document.tbl['qty2'].value='';
			document.tbl['sku2'].focus();
		}else if(event2.keyCode==13){
			checkcode2();
		}
	}
}
/*
function checkcode()
{
    var cod = document.f_a['code'].value;
   
    new Ajax.Request(phpself,{
		parameters:{
			a: 'load_add_armcode',
			c:cod 
		},onComplete: function(msg){
				if(msg.responseText=='invalid'){
				alert("Invalid Code");
				return;
				}else
				{
          $('arms').innerHTML = msg.responseText;
        }
		  }
		
	 });
	
    return;
}
*/

/*
function reset_sku_autocomplete()
{
  
	var param_str = "a=ajax_search_sku";
	if (sku_autocomplete != undefined)
	{	
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else
	{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
    afterUpdateElement: function (obj, li) {
    
		s = li.title.split(",");
		   
			document.f_a['sku_item_id'].value =s[0];
			document.f_a['sku_item_code'].value = s[1];
		  //sku_item_id = s[0];
		  //alert(document.f_a['sku_item_id'].value);
			if (s[0]>0)
			{
			  new Ajax.Request(phpself,{
    		parameters:{
    			a: 'load_desc',
    			c:s[0]
    		},onComplete: function(msg){
    				document.f_a['description'].value =msg.responseText;
    		  }
    		
    	 });
			   
				$('autocomplete_sku').disabled = true;
				$('span_loading_item_info').update(_loading_);
			
				new Ajax.Request(phpself+'?a=ajax_get_item_info&ajax=1&sku_item_id='+s[0],
					{
						onComplete:function(msg)
						{
							eval("var json="+msg.responseText);
							$('autocomplete_sku').disabled = false;
							$('span_loading_item_info').update('');
						}
					});
	
			}
			else{
				clear_autocomplete();
			}
		}});
	
	}
	//alert(param_str);
	clear_autocomplete();
	
}

function clear_autocomplete()
{
	$('autocomplete_sku').value = '';
	//$('autocomplete_sku').focus();
}

*/

function reset_sku_autocomplete()
{
	//var param_str = "a=ajax_search_sku&dept_id={/literal}{$form.department_id}{literal}&type="+getRadioValue(document.f_a.search_type);
	var param_str = "a=ajax_search_sku&sku_type="+stock_take_sku_type+"&type="+getRadioValue(document.f_a.search_type)+"&fresh_market_filter=no";
 //sku_autocomplete.options.defaultParams = param_str;
    if(sku_autocomplete==undefined){
        sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		beforeShow: function(){
		  	$('div_loading').update(_loading_);
      		$('sku_item_id').value = '';
    	},
		afterUpdateElement: function (obj, li) {
		  s = li.title.split(",");
			$('sku_item_id').value = s[0];
			$('sku_item_code').value = s[1];
			sku_item_id = s[0];
			
			var doc_allow_decimal = document.f_a.elements['inp_dad,'+s[0]].value;
			
			document.f_a.qty.onchange=function(){roundup_value('qty',doc_allow_decimal,this);};
			roundup_value('qty',doc_allow_decimal,document.f_a.qty);
			if(doc_allow_decimal == 1){
				//document.f_a.qty.onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
			}else{
				//document.f_a.qty.onchange = function(){ mi(this); };
				//document.f_a.qty.value = int(document.f_a.qty.value);
			}
		}});
	}else{
        sku_autocomplete.options.defaultParams = param_str;
	}
 	
  /*
	if (sku_autocomplete != undefined)
	{
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else
	{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		beforeShow: function(){
		  $('div_loading').update(_loading_);
      $('sku_item_id').value = '';
    },
		afterUpdateElement: function (obj, li) {
		  s = li.title.split(",");
			$('sku_item_id').value = s[0];
			$('sku_item_code').value = s[1];
			sku_item_id = s[0];
		}});
	}*/
	$('div_loading').style.display='none';
	clear_autocomplete();
}

function clear_autocomplete(){
	$('sku_item_id').value = '';
	$('sku_item_code').value = '';
	$('autocomplete_sku').value = '';
	//$('autocomplete_sku').focus();
	document.f_a['loc'].focus();
}

function reset_sku_autocomplete2()
{
	var param_str2 = "a=ajax_search_sku&sku_type="+stock_take_sku_type+"&type="+getRadioValue(document.tbl.search_type2)+"&fresh_market_filter=no";
    if(sku_autocomplete2==undefined){
		sku_autocomplete2 = new Ajax.Autocompleter("autocomplete_sku2", "autocomplete_sku_choices2", "ajax_autocomplete.php", {
			parameters:param_str2,
			paramName: "value",
			beforeShow: function(){
				$('div_loading2').update(_loading_);
				$('sku_item_id2').value = '';
	    	},
			afterUpdateElement: function (obj, li) {
				s = li.title.split(",");
				$('sku_item_id2').value = s[0];
				$('sku_item_code2').value = s[1];
				sku_item_id = s[0];
				var doc_allow_decimal = document.tbl.elements['inp_dad,'+s[0]].value;
				
				document.tbl.qty2.onchange = function(){roundup_value('qty',doc_allow_decimal,this);};
				roundup_value('qty',doc_allow_decimal,document.tbl.qty2);
/*
				if(doc_allow_decimal == 1){
					document.tbl.qty2.onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
				}else{
					document.tbl.qty2.onchange = function(){ mi(this); };
					document.tbl.qty2.value = int(document.tbl.qty2.value);
				}
*/			}
		});
	}else{
        sku_autocomplete2.options.defaultParams = param_str2;
	}
	$('div_loading2').style.display='none';
	clear_autocomplete2();
}

function clear_autocomplete2(){
	$('sku_item_id2').value = '';
	$('sku_item_code2').value = '';
	$('autocomplete_sku2').value = '';
}

function load_desc(val)
{
  	new Ajax.Request(phpself,{
		parameters:{
			a: 'load_desc',
			c:val
		},onComplete: function(msg){
				//document.f_a['description'].value =msg.responseText;
		}
	});
}

function init_autocomplete()
{
	category_autocompleter = new Ajax.Autocompleter("autocomplete_category", "autocomplete_category_choices", "ajax_autocomplete.php?a=ajax_search_category&min_level=1", {
	afterUpdateElement: function (obj,li)
	{
	    this.defaultParams = '';
		var s = li.title.split(',');
		$('category_id').value = s[0];
		sel_category(obj,s[1]);
		get_brand($('category_id').value,'All');
	}});
}

function check_branch(branch,i)
{
    if(branch=="")
    {
        return;
    }
    var bn;
    
    if(i=='1') bn = 'bran_date2';
    else bn = 'bran_date';
    
    new Ajax.Updater(bn,phpself,{
	    parameters:{
			a: 'check_available_branch',
			b: branch,
			c: i
		},onComplete: function(msg){
    		if(msg.responseText == ""){
    		    //document.import_stock_take.branch_date.value = "";
				document.import_stock_take['import'].disabled= true;
				if(i=='1') alert("No Stock Take data for this branch");
				else alert("Invalid data for export");
				return;
			}
			document.import_stock_take['import'].disabled= false;
		}
	})

}

function stock_import()
{
    var branch_id;
	if (document.import_stock_take.branch) branch_id = document.import_stock_take.branch.value;
    var branch_date = document.import_stock_take.branch_date.value;
    var fill_zero = document.import_stock_take['fill_zero'].checked ? 1 : 0;
    if(!confirm('Are you sure?'))   return false;
    
    new Ajax.Request(phpself,{
		parameters:{
			a: 'import_stock_take',
			b_id:branch_id,
			b_date:branch_date,
			fill_zero: fill_zero
		},onComplete: function(msg){
		    //reload_table();
			//alert("Data Successful Imported");
			alert(msg.responseText);
		}
	});
}

function init_calendar(){
	Calendar.setup({
		inputField     :    "added1",     // id of the input field
		ifFormat       :    "%Y-%m-%d",      // format of the input field
		button         :    "t_addeded",  // trigger for the calendar (button ID)
		align          :    "Bl",           // alignment (defaults to "Bl")
		singleClick    :    true
		//,
		//onUpdate       :    load_data
	}); 
}

function code_validation()
{  
	if(sku_item_id =="undefined" || document.f_a['sku'].value =="") return;

	sku_item_id = $('sku_item_id').value;
	//alert($('autocomplete_sku').value);

	new Ajax.Request(phpself,{
		parameters:{
			a: 'code_validation',
			sku_code:sku_item_id
		},onComplete: function(msg){
			if(msg.responseText=="no"){
				alert("Invalid Code Enter");
				return;
			}else{
			  //sku_item_id = "0";
			}	
		}
	});
}

function reset_code()
{
    //alert("why");
    sku_item_id = "0";
}

function load_handheld(val)
{
    var s = val.checked;
    if(val.checked)
    {
        $('autocomplete_sku').hide();
        $('sku_search_chooice').hide();
        $('scan').show();
        $('autocomplete_sku').value = '';
    }
    else
    {
        $('autocomplete_sku').show();
        $('sku_search_chooice').show();
        $('scan').hide();
        document.f_a['sku_scan'].value = '';
    }
}

function load_handheld2(val){
    var s = val.checked;
    if(val.checked)
    {
        $('autocomplete_sku2').hide();
        $('sku_search_chooice2').hide();
        $('scan2').show();
        $('autocomplete_sku2').value = '';
    }
    else
    {
        $('autocomplete_sku2').show();
        $('sku_search_chooice2').show();
        $('scan2').hide();
        $('sku_scan2').value = '';
    }
}

function checkcode()
{
    var cod = document.f_a['sku_scan'].value;
    if(!cod)
    {
        alert("Please Scan Your Code");
        return false;
    }
    new Ajax.Request(phpself,{
		parameters:{
			a: 'validate_code',
			code:cod,
			sku_type:stock_take_sku_type
		},onComplete: function(msg){
			if(msg.responseText=="no"){
				alert("Invalid Code Enter");
				//return;
			}else{
				var test = eval("("+msg.responseText+")");
				$('sku_item_id').value = test.id;
				$('sku_scan').value = test.description;
				var doc_allow_decimal = test.doc_allow_decimal;
				//$('sku_scan').value = msg.responseText;
				document.f_a.qty.value = '';
				document.f_a.qty.focus();
				
				document.f_a.qty.onchange = function(){roundup_value('qty',doc_allow_decimal,this);};
				roundup_value('qty',doc_allow_decimal,document.f_a.qty);
/*
				if(doc_allow_decimal == 1){
					document.f_a.qty.onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
				}else{
					document.f_a.qty.onchange = function(){ mi(this); };
					document.f_a.qty.value = int(document.f_a.qty.value);
				}
*/			}
		}
	});
}

function checkcode2()
{
    var cod = document.tbl['sku_scan2'].value;
    if(!cod)
    {
        alert("Please Scan Your Code");
        return false;
    }
    new Ajax.Request(phpself,{
		parameters:{
			a: 'validate_code',
			code:cod,
			sku_type:stock_take_sku_type
		},onComplete: function(msg){
			if(msg.responseText=="no"){
				alert("Invalid Code Enter");
				//return;
			}else{
				var test = eval("("+msg.responseText+")");

				document.tbl['sku_item_id2'].value = test.id;
				document.tbl['sku_scan2'].value = test.description;
				var doc_allow_decimal = test.doc_allow_decimal;
				//$('sku_scan').value = msg.responseText;
				
				document.tbl.qty2.onchange = function(){ roundup_value('qty',doc_allow_decimal,this);};
				roundup_value('qty',doc_allow_decimal,document.tbl.qty2);
/*
				if(doc_allow_decimal == 1){
					document.tbl.qty2.onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
				}else{
					document.tbl.qty2.onchange = function(){ mi(this); };
					document.tbl.qty2.value = int(document.tbl.qty2.value);
				}
*/
				document.tbl.qty2.value = '';
				document.tbl.qty2.focus();
			}
		}
	});
}

function delete_detail(id,k_id)
{
    var se_time = document.f_a['ses_time'].value;
	var bid = document.tbl['branch_id'].value;
    new Ajax.Request(phpself,{
		parameters:{
			a: 'delete_scan_item',
			item_id:id,
			branch_id: bid,
			key_id:k_id,
			s_time:se_time
		},onComplete: function(msg){ 
			$('details_display').innerHTML = msg.responseText;
			reload_table();
			//alert(msg.responseText);
		}
	});
}

function upd_qty(id,qty,k_id)
{
    var se_time = document.f_a['ses_time'].value;
	var bid = document.tbl['branch_id'].value;
    new Ajax.Request(phpself,{
		parameters:{
			a: 'upd_item_qty',
			item_id:id,
			branch_id:bid,
			key_id:k_id,
			qtys:qty,
			s_time:se_time
		},onComplete: function(msg){ 
			$('details_display').innerHTML = msg.responseText;
			if(document.tbl['qtys['+id+']'] != undefined){
				document.tbl['qtys['+id+']'].value = qty;
				var stk_bal = $('span_stk_bal_'+id).innerHTML;
				recalc_variance(id, qty, stk_bal);
			}
			//reload_table();
		}
	});
}

function recalc_variance(id,qty,sb_qty)
{
	var sign="";
	
	if(document.tbl['mid['+id+']'].value > 0){
		var mst_id = document.tbl['mid['+id+']'].value;
		var inp = document.tbl['mid['+id+']'];
		var sid = $(inp).readAttribute('sku_item_id');
		var mst_sb_qty = $(inp).readAttribute('sb_qty');
		var ttl_qty = 0;
		
		$$('.sku_item_id_'+sid).each(function(tr){
			var tmp_id = $(tr).readAttribute('item_id');
 			ttl_qty += float(document.tbl['qtys['+tmp_id+']'].value);
 		});
		
		var variance = float(round(ttl_qty - mst_sb_qty, global_qty_decimal_points));
		if(variance > 0){
			sign = "+";
			$('var_'+mst_id).className = "r positive";
		}else if(variance < 0){
			$('var_'+mst_id).className = "r negative";
		}else $('var_'+mst_id).className = "r";
		$('var_'+mst_id).update(sign+variance);
	}/*else{
		var variance = float(round(qty - sb_qty, global_qty_decimal_points));
		if(variance > 0){
			sign = "+";
			$('var_'+id).className = "r positive";
		}else if(variance < 0){
			$('var_'+id).className = "r negative";
		}else $('var_'+id).className = "r";
		$('var_'+id).update(sign+variance);
	}*/
}

function swap(m,i,bi)
{
    new Ajax.Request(phpself,{
		parameters: $(document.tbl).serialize()+'&mode='+m+'&id='+i+'&a=swap',
		onComplete: function(msg){
          $('div_table').innerHTML = msg.responseText;
		}
	});
}

function reset_stocktake()
{
    var b = document.import_stock_take.branch_reset.value;
    var bd = document.import_stock_take.branch_date2.value;
    
    if(!confirm('Are you sure?'))   return false;
    
    new Ajax.Request(phpself,{
		parameters:{
			a: 'revert_import',
			branch:b,
			branch_date:bd
		},onComplete: function(msg){
			alert(msg.responseText);
		}
	});
}

function reload_shelf_range(){
	$('td_loading_shelf_range').update(_loading_);
	new Ajax.Updater('tr_shelf_range', phpself,{
	    method: 'post',
		parameters: $(document.selection).serialize()+'&a=ajax_reload_shelf_range',
		onComplete: function(e){
            $('td_loading_shelf_range').update('');
		}
	});
}

function form_submit()
{
    if(!document.tbl.date.value){
		alert("Please Select Date");
		return;
    }else if(!document.tbl.location.value){
		alert("Please Select Location");
		return;
    }else if(!document.tbl.shelf.value){
		alert("Please Select Shelf");
		return;
    }

    document.tbl.submit();
}

function delete_record(item_id)
{
    if(!confirm('Are you sure?'))   return false;
    new Ajax.Updater('div_table',phpself,{
		parameters: $(document.tbl).serialize()+'&d='+item_id+'&a=delete_record'
	});
}

function print_report()
{
    //document.tbl.target = 'ifprint';
    document.tbl.target = '_blank';
    document.tbl.a.value = "print_report";
    document.tbl.submit();
    document.tbl.a.value = "save_edit";
    document.tbl.rpt_type.value = "";
    document.tbl.target = '';
}

function print_sheet(val)
{
    if(val == 'sheet')
    {
	    if (stock_take_count_sheet){
			if (!document.selection.count_sheet.value){
			    alert("Please enter stock count sheet no.");
				return;
			}
		}
		
        document.selection.rpt_type.value = val;
        document.selection.target = '_blank';
        document.selection.a.value = "print_report";
        document.selection.submit();
        document.selection.a.value = "load_table_data";
        document.selection.rpt_type.value = "";
    }
}

function deleteAll()
{
    if(!confirm('Are you sure?'))   return false;
    new Ajax.Updater('div_table',phpself,{
		parameters: $(document.tbl).serialize()+'&a=delete_allRecord'
	});
}

//admin.stock_take.open.tpl

function show_stock_take_direct_add_multiple(){
	var v = document.f_a['sku'].value.trim();
	if(v=='')    return false;
	curtain(true);
	center_div($('div_stock_take_direct_add_multiple').show());
	$('div_stock_take_direct_add_multiple_content').update(_loading_);

	var param_str = "a=ajax_search_sku&type="+getRadioValue(document.f_a.search_type)+'&hide_print=1&show_multiple=1';
	param_str += '&fresh_market_filter=no';    // only get fresh market sku
	param_str += "&alt_submit_multi_add=submit_form2('multi_save','f_a')";
	new Ajax.Updater('div_stock_take_direct_add_multiple_content','ajax_autocomplete.php?',{
		parameters: param_str+'&value='+v,
		evalScripts: true
	});
}

//admin.stock_take.table.tpl
function show_stock_take_direct_add_multiple2(){
	var v = document.tbl['sku2'].value.trim();
	if(v=='')    return false;
	curtain(true);
	center_div($('div_stock_take_direct_add_multiple2').show());
	$('div_stock_take_direct_add_multiple_content2').update(_loading_);

	var param_str = "a=ajax_search_sku&type="+getRadioValue(document.tbl.search_type)+'&hide_print=1&show_multiple=1';
	param_str += '&fresh_market_filter=no';    // only get fresh market sku
	param_str += "&alt_submit_multi_add=submit_form2('multi_save','tbl')";
	new Ajax.Updater('div_stock_take_direct_add_multiple_content2','ajax_autocomplete.php?',{
		parameters: param_str+'&value='+v,
		evalScripts: true
	});
}

submit_form2 = function(action, form){
	if(action=='save'){

		$$('.btn_save').each(function(ele){
			ele.disable().value = 'Saving...';
		});

		var date = document.tbl['date'].value;
		var loc = document.tbl['location'].value;
		var shelf = document.tbl['shelf'].value;
		var a = document.tbl['a'].value;
        document.tbl['a'].value='save2';

		new Ajax.Request(phpself,{
			parameters: document.tbl.serialize(),
			onComplete: function(e){
				var msg = e.responseText.trim();
				if(msg!='OK'){
					document.tbl['qty2'].value='0';
					alert(msg);
				}

				$$('.btn_save').each(function(ele){
					ele.enable().value = 'Add';
				});

				document.tbl['sku_scan2'].value='';
				document.tbl['sku_scan2'].focus();
		        reload_table();
			}
		});
        document.tbl['a'].value = a;
	}else if(action=='multi_save'){
		if (form=='tbl'){
			var bid = document.tbl['branch_id'].value;
			var date = document.tbl['date'].value;
			var loc = document.tbl['location'].value;
			var shelf = document.tbl['shelf'].value;
 			var a = 'multi_save';
		}else if(form=='f_a'){
			var bid = document.f_a['bran'].value;
			var date = document.f_a['dat'].value;
			var loc = document.f_a['loc'].value;
			var shelf = document.f_a['shelf'].value;
			var ses_time = document.f_a['ses_time'].value;
	 		var a = 'multi_save';
		}

		if(!date){  // check date
			alert('Please select date.');
			return false;
		}
		if(!loc){   // check location
			alert('Please enter location.');
			return false;
		}
		if(!shelf){ // check location
			alert('Please enter shelf.');
			return false;
		}

		// get all selected sku
		var sid_list = [];
		$$('#tbl_multi_add input.chx_sid_list').each(function(inp){
			if($(inp).checked)  sid_list.push(inp.value);
		});
		
		if(sid_list.length<=0){ // no sku is selected to add
			alert('Please select at least one item.');
			return false;
		}
		var param_str= $H({
		    'a' : a,
			'branch_id': bid,
			'date': date,
			'location': loc,
			'shelf': shelf,
			'table': form,
			'ses_time': ses_time,
			'sid_list[]' : sid_list
		}).toQueryString();

		new Ajax.Request(phpself,{
			parameters: param_str,
			onComplete: function(e){
				var msg = e.responseText.trim();
				if(form=='f_a'){
					document.f_a['ses_time'].value = msg;
				  	load_scan_item();
				  	hidediv('div_stock_take_direct_add_multiple');
				}else{
                    default_curtain_clicked();
				}
				$$('.btn_save').each(function(ele){
					ele.enable().value = 'Add';
				});
		        reload_table();
			}
		});
	}
}

function load_scan_item()
{
    new Ajax.Request(phpself,{
		parameters:{
			a: 'load_scan_item'
		},onComplete: function(msg){
        	$('details_display').innerHTML = msg.responseText;
		}
	});
}

function curtain_clicked()
{
	hidediv('div_stock_take_details');
	$('div_stock_take_direct_add_multiple_content').update('');
	$('div_stock_take_direct_add_multiple_content2').update('');
}

function show_cost_help(){
	var msg = "Leave blank to use latest cost while import";
	alert(msg);
}

roundup_value = function (type,doc_allow_decimal,ele){
	//alert(ele.value.length);
	if (ele.value.length == 0)	return;
	if (type == 'qty'){
		if (doc_allow_decimal == 1){
			ele.value = float(round(ele.value, global_qty_decimal_points));
		}else{
			mi(ele);
		}
	}else if (type == 'cost'){
		if (doc_allow_decimal == 1){
			ele.value = float(round(ele.value, global_cost_decimal_points));
		}else
			ele.value = float(round(ele.value, 2));
	}
}

{/literal}
</script>
<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">Stock Take</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>	
<h6 class="mx-3">
	Fresh Market sku need to go fresh market stock take, click <a style="color:blue;" href="/admin.fresh_market_stock_take.php">here</a>.
</h6>
<iframe width=1 height=1 style="visibility:hidden" name=ifprint2></iframe>
<div class="card mx-3">
	<div class="card-body">
		<form name="selection" action="admin.stock_take.php">

			<input type=hidden name=a value=load_table_data>
			<input type=hidden name=rpt_type>
			{if !$can_select_branch}<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />{/if}
		
			<div class="table-responsive">
				<table>
					<tr>
						{if $can_select_branch}<td><b class="form-label">Branch</b></td>{/if}
						<td valign=top><b class="form-label">Date</b></td>
						<td><b class="form-label">Location</b></td>
						<td><b class="form-label">Shelf</b></td>
						<td></td>
					</tr>
					<tr>
						{if $can_select_branch}
							<td>
								<select class="form-control" name="branch_id" onchange="branch_changed(this.value)" size="10">
									{foreach from=$branches item=r}
										<option value="{$r.id}" {if !$smarty.request.branch_id and $BRANCH_CODE eq $r.code}selected {else}{if $smarty.request.branch_id eq $r.id}selected {/if}{/if}>{$r.code}</option>
									{/foreach}
								</select>
							</td>
						{/if}
						<td>
							<div id="div_date" style="min-width:120px;">
								<select class="form-control" name="dat" onchange="load_location(this.value)" size=10 style="width:100%;">
									{foreach from=$dat item=val}
										<option value="{$val.date}" {if $smarty.request.date eq $val.date}selected {/if}>{$val.date}</option>
									{/foreach}
								</select>
							</div>
						</td>
						<td>
							<div id="div_location" style="min-width:120px;">
								<select class="form-control" name=loc onchange="load_shelf(this.value)" size=10 style="width:100%;">
									{foreach from=$loc item=val}
										<option value="{$val.location}" {if $smarty.request.location eq $val.location}selected {/if}>{$val.location}</option>
									{/foreach}
								</select>
							</div>
						</td>
						<td>
							<div id="div_shelf" style="min-width:120px;">
								<select class="form-control" name=shelf onchange="show_record()" size=10 style="width:100%;">
									{foreach from=$shelf item=val}
										<option value="{$val.shelf}" {if $smarty.request.shelf eq $val.shelf}selected {/if}>{$val.shelf}</option>
									{/foreach}
								</select>
							</div>
						</td>
						<td valign="top">
							<div id="range" class="ml-5">
								{include file='admin.stock_take.range.tpl'}
							</div>
						</td>
					</tr>
					<tr>
						<td><b class="form-label">Sku Type: </b></td>
						   <td>
							<select class="form-control" name='sku_type' onchange='sku_show_record();'>
								<option value=''>All</option>
								{foreach from=$sku_type item=code}
								<option value='{$code.code}' {if $smarty.request.sku_type eq $code.code} selected {/if}>{$code.code}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
</div>
<div class="modal" id="div_stock_take_details">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content modal-content-demo">
			<div class="modal-header" id="div_stock_take_details_header">
				<h6 class="modal-title text-white " >Stock Take Details</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true" class="text-white mt-2">&times;</span></button>
				<div style="clear:both;"></div>
			</div>
			<div class="modal-body"id="div_stock_take_details_content" >
				
			</div>
		</div>
	</div>
</div>

<!-- multiple add div -->
<div id="div_stock_take_direct_add_multiple" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding: 0 !important;">
	<div id="div_stock_take_direct_add_multiple_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Multiple Add SKU</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="hidediv('div_stock_take_direct_add_multiple');" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_stock_take_direct_add_multiple_content" style="padding:2px;"></div>
</div>

<div id="div_stock_take_direct_add_multiple2" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding: 0 !important;">
	<div id="div_stock_take_direct_add_multiple_header2" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Multiple Add SKU</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_stock_take_direct_add_multiple_content2" style="padding:2px;"></div>
</div>
<!-- end of multiple add div -->


<div class="card mx-3">
	<div class="card-body"><a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd New Stock</a> (Please re-select <b>date</b>, <b>location</b> and <b>shelf</b> to refresh the item list)
		<br /><br />
		<a href="admin.stock_take.upload_csv.php">
			<img src="ui/new.png" title="New" align="absmiddle" border="0" />
			Add New Stock Take by CSV
		</a>
	</div>
</div>
<br>
<form name="tbl" action="admin.stock_take.php" method='post'>
	<input type=hidden name=a value=save_edit>
	<input type=hidden name=date value="{$smarty.request.date}" />
	<input type=hidden name=location value="{$smarty.request.location}" />
	<input type=hidden name=shelf value="{$smarty.request.shelf}" />
	<input type=hidden name=sku_type value="{$smarty.request.sku_type}" />
	<input type=hidden name=rpt_type>
	<input type=hidden name=branch_id value="{$smarty.request.branch_id|default:$sessioninfo.branch_id}">

	<div id="div_table_frame" class="stdframe" style="{if !$smarty.request.date}display:none;{/if}">
	    <div id="div_table">
			{include file='admin.stock_take.table.tpl'}
		</div>
		
		<table border=0 cellpadding=4 cellspacing=1>
			<tr id="sku_search_txt">
				<th align=left>Search SKU</th>
				<td>
					<input id="sku_item_id2" name="sku_item_id2" size=3 type=hidden>
					<input id="sku_item_code2" name="sku_item_code2" size=13 type=hidden>
					<input id="autocomplete_sku2" name="sku2" size=50 onclick="this.select()" style="font-size:14px;width:500px;">
					<div id=scan2 style="display:none"><input id=sku_scan2 name=sku_scan2 size=50 style="font-size:14px;width:500px;" onKeyPress="checkkey2(event)"></div>
					<div id="autocomplete_sku_choices2" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
				</td>
				<td><!--<input type=submit value="Find">--></td>
			</tr>
			<tr>
				<td nowrap><input type=checkbox name=handheld2 id=handheld2 onchange="load_handheld2(this)">By Handheld</td>
				<td id=sku_search_chooice2>
					<input onchange="reset_sku_autocomplete2()" type=radio name="search_type2" value="1" checked> MCode &amp; {$config.link_code_name}
					<input onchange="reset_sku_autocomplete2()" type=radio name="search_type2" value="2" {if $smarty.request.search_type2 eq 2 || (!$smarty.request.search_type2 and $config.consignment_modules)}checked {/if}> Article No
					<input onchange="reset_sku_autocomplete2()" type=radio name="search_type2" value="3"> ARMS Code
					<input onchange="reset_sku_autocomplete2()" type=radio name="search_type2" value="4" > Description
				</td>
			</tr>
			<tr>
				<td valign="top"><b>Quantity</b></td>
				<td><input type="text" size=10 name="qty2" value="{$form.qty2}" onKeyPress="checkkey2(event,'1')">
				<input type=button value="Add" class="btn_save" onclick="submit_form2('save','tbl');" />&nbsp;&nbsp;
				<input type=button value="Multiple Add" id="btn_multiple_save" onclick="show_stock_take_direct_add_multiple2();" />
				<div id=div_loading2></div>&nbsp;&nbsp;</td>
			</tr>
		</table>

		<input type="button" value="Save" onclick="form_submit()">
		<input type="button" value="Delete All" onclick="deleteAll()">
		<input type="button" value="Print Report" onclick="print_report()">
	</div>
</form>
<!--{*
<h2>Import</h2>
<form name=import_stock_take>
<table>
<tr>
{if $branch}
<td><select name=branch onchange="check_branch(this.value)">
<option value="">Please Select</option>
{foreach from=$branch item=val}
<option value="{$val.id}">{$val.code}</option>
{/foreach}
</select></td>{/if}
<td>
<div id=bran_date>
{include file='branch_date.tpl'}
</div>
</td>
<td><input type=button name=import value="Import" onclick="stock_import()">
<input type="checkbox" name="fill_zero" /> <b>Set quantity to zero for items not in stock take</b>
</td>
</tr>
</table>
{if $sessioninfo.level =='9999'}
<h2>Reset</h2>
<table>
<tr>
{if $branch}
<td><select name=branch_reset onchange="check_branch(this.value,'1')">
<option value="">Please Select</option>
{foreach from=$branch item=val}
<option value="{$val.id}">{$val.code}</option>
{/foreach}
</select></td>{/if}
<td>
<div id=bran_date2>
{if $bran_date}
<select name=brn_reset_date>
{foreach from=$bran_date item=val}
<option value="{$val}">{$val}</option>
{/foreach}
</select>
{/if}
</div>
</td>
<td><input type=button name=reset value="Reset" onclick="reset_stocktake()"></td>
</tr>
</table>
{/if}
</form>
*}-->

{if $smarty.request.msg}<script>alert("Data Successful Edited")</script>{/if}
{include file='footer.tpl'}
{literal}
<script>
//document.import_stock_take['import'].disabled= true;
reset_sku_autocomplete2();
new Draggable('div_stock_take_details',{ handle: 'div_stock_take_details_header'});
new Draggable('div_stock_take_direct_add_multiple',{ handle: 'div_stock_take_direct_add_multiple_header'});
new Draggable('div_stock_take_direct_add_multiple2',{ handle: 'div_stock_take_direct_add_multiple_header2'});
</script>
{/literal}
