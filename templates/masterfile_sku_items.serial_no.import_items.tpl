{*
2/24/2012 12:55:23 PM Justin
- Fixed the report word label while printing report.

10/16/2013 4:02 PM Justin
- Enhanced to have new feature that can auto generate S/N (need config).

6/19/2015 3:32 PM Justin
- Enhanced to allow user can search by S/N.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character
*}

{include file="header.tpl"}
{literal}
<style>
.sold {
	color:#f00;
}

.dtl_items tr:nth-child(even){
	background-color:#dddddd;
}

.dtl_items tr:nth-child(odd){
	background-color:#eeeeee;
}

/* standard style for report table */
.serial_no_tbl {
	border-top:1px solid #000;
	border-right:1px solid #000;
	white-space:no-wrap;
}

.serial_no_tbl td, .serial_no_tbl th{
	border-left:1px solid #000;
	border-bottom:1px solid #000;
	padding:4px;
}

.serial_no_tbl tr.header td, .serial_no_tbl tr.header th{
	background:#fe9;
	padding:6px 4px;
}

.serial_no_tbl tr.sn_dtl:hover{
	background:#ffffcc !important;
}

.dtl_items tr.dtl_items_row:hover{
	background:#ffffcc !important;
}

.serial_no_tbl textarea {
	background-color:#fff;
}

.div_sel{
	width: 400px;
	overflow-x:hidden;
	overflow-y:auto;
}

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var master_title = '{$master_title}';
var print_sn = '{$print_sn}';
var grn_list = '{$grn_list}';
var branch_id = '{$branch_id|default:$sessioninfo.branch_id}';

{literal}

function reset_row(){

	var e = $('serial_no_items').getElementsByClassName('no');
	var total_rows=e.length;

	for(var i=0;i<total_rows;i++)	{
 		var temp_1 =new RegExp('^no');
	 	if (temp_1.test(e[i].id)){
			td_1=(i+1)+'.';
			e[i].innerHTML=td_1;
			e[i].id='no'+(i+1);
		}
	}

	$('autocomplete_sku').select();
}

function del_row(ele){
	if(!confirm('Are you sure?')) return;
	var parent_row=ele.parentNode.parentNode;

	if(!document.f_a.item_del_list.value) document.f_a.item_del_list.value += ele.id;
	else document.f_a.item_del_list.value += ","+ele.id;

	if($('delete_msg'))	$('delete_msg').remove();
	$(parent_row).remove();
	reset_row();
}

function ajax_add_item(){
	//obj.disabled = true;

	new Ajax.Request(phpself, {
		method:"post",
		parameters: Form.serialize(document.f_a)+"&a=import_dtl_items",
		evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onSuccess: function(m){
			eval("var json = "+m.responseText);
			for(var tr_key in json){
				new Insertion.Bottom($('imported_dtl_items'),json[tr_key]['rowdata']);
			}
		},
		onComplete: function(m){
			//reset_row();
			var grn_items = $$('tr.dtl_items_row .grn_check');
			var grn_total_rows = grn_items.length;
			var row_uncheck = 0;

			if(grn_total_rows > 0){
				$A(grn_items).each(
					function (r,idx){
						var mid=$(r.name).getAttribute('mid');
						if (!r.checked){
							if($('sn_item'+mid) != undefined) $('sn_item'+mid).remove();
							row_uncheck++;
						}
					}
				);
			}

			// check if still got checked item, while show the save button
			if(row_uncheck != grn_total_rows) $('sn_items_btn').style.display = "";
			else $('sn_items_btn').style.display = "none";

			obj.disabled = false;
		}
	});
}

function save_sn_items(){
	var err_msg = '';

	/* var sn_sku_items = $('serial_no_div').getElementsByClassName("grp_sku_items");
	var sn_si_rows = sn_sku_items.length;

	// loop item see whether got errors or not
	if(sn_si_rows > 0){
		$A(sn_sku_items).each(
			function(r,idx){
				var sku_item = r.value.split(","); // [0] is ID and [1] is sku item code
				var serial_no_items = $('serial_no_items'+sku_item[0]).getElementsByTagName('input');
				var sn_total_rows = serial_no_items.length;
				var item_count = 0;
				var curr_sku_item = "";

				if(sn_total_rows > 0){
					$A(serial_no_items).each(
						function(r1,idx){
							if(r1.name.indexOf("serial_no")==0){
								item_count++;
								if(!r1.value){
									if(sku_item[0] != curr_sku_item) err_msg += "* "+sku_item[1]+" is having empty S/N row(s).\n";
									//err_msg += "Row ["+item_count+"] having empty S/N. \n";
									curr_sku_item = sku_item[0];
								}
							}
						}
					);
				}
			}
		);
	}

	new Ajax.Request(phpself, {
		method:'post',
		parameters: Form.serialize(document.f_a)+"&a=save",
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function(m) {
			alert(m.responseText);
		}
	});*/

	/*if(err_msg){
		alert("You have encountered below: \n\n"+err_msg);
		return false;
	}else{
		if(!confirm("Are you sure want to save the following imported S/N item(s)?")) return false;
	}*/

	document.f_a.search.value = document.f_tab.search.value;

	for(i=0;i<=2;i++){
		if($('lst'+i).className=='active'){
			var t=i;
			break;
		}
	}

	document.f_a.search.value = document.f_tab.search.value;
	document.f_a.t.value = t;
	document.f_a.a.value = 'save';
	document.f_a.submit();
}

function clear_dtl(){
	$('serial_no_div').innerHTML = '';
	document.f_a.sku_item_id.value = '';
	document.f_a.sku_item_code.value = '';
}

function search_doc(obj){
	
	if(!document.f_a.search.value) return;

	showdiv("dtl_item_imp");
	curtain(true);

	// if found stil search the same grn/do, no ajax will call and will only show previous grn/do items.
	if(document.f_a.search_branch_id != undefined){
		if(document.f_a.prev_search.value == document.f_a.search.value && document.f_a.prev_search_bid.value == document.f_a.search_branch_id.value && dtl_items.innerHTML != "") return;
	}else{
		if(document.f_a.prev_search.value == document.f_a.search.value && dtl_items.innerHTML != "") return;
	}
		

	obj.disabled = true;

	new Ajax.Updater('dtl_items', phpself, {
		method:'post',
		parameters: Form.serialize(document.f_a)+'&',
		evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onSuccess: function(m){
			document.f_dtl_items.dtl.focus();
		},
		onComplete: function(m){
			document.f_a.prev_search.value = document.f_a.search.value;
			if(document.f_a.search_branch_id != undefined) document.f_a.prev_search_bid.value = document.f_a.search_branch_id.value;
			obj.disabled = false;
		}
	});
}

function check_all_items(validate){
	var dtl_items = $('dtl_items_row').getElementsByTagName('input');
	var dtl_total_rows = dtl_items.length;
	var have_dtl_items = '';

	if(dtl_total_rows > 0){
		$A(dtl_items).each(
			function(r,idx){
				if(r.name.indexOf("item_check[")==0){
					if(validate == undefined){
						if($('check_all_inp').checked == true) r.checked = true;
						else r.checked = false;
					}else{
						if(r.checked == true) have_dtl_items = 1;
					}
				}
			}
		);
	}
	
	if(have_dtl_items) return have_dtl_items;
}

function imp_dtl_items(obj){
	var validation = check_all_items(1);
	
	if(!validation){
		alert("Please select 1 or more "+master_title+" items to import.");
		return;
	}

	obj.disabled = true;
	new Ajax.Updater('imported_dtl_items', phpself, {
		method:'post',
		parameters: Form.serialize(document.f_a)+"&"+Form.serialize(document.f_dtl_items)+"&a=import_dtl_items",
		evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onSuccess: function(m){
			if(obj != undefined) obj.disabled = false;
		},
		onComplete: function(m){
			
			curtain_clicked();
		}
	});
}

// reduce the current as rcv qty
function recalc_qty_used(grn_id, sid){
	var sn = document.f_a.elements['sn['+grn_id+']['+sid+']'].value;
	var qty = document.f_a.elements['qty['+grn_id+']['+sid+']'].value;
	split_sn = sn.split("\n");
	var ttl_qty_used = 0;
	var bal_qty = 0;
	var extra_info = "";

	for(var i=0; i<split_sn.length; i++){
		if(split_sn[i].trim() != "") ttl_qty_used++;
	}

	bal_qty = float(qty) - float(ttl_qty_used);


	if(bal_qty != qty){ // show the remaining of S/N can be key in.
		if(bal_qty >= 0) extra_info = " ("+bal_qty+" Pcs remaining)";
		else extra_info = " <b><font color=\"#ff0000\">(Over "+Math.abs(bal_qty)+" S/N)</font></b>";
	}

	$('bal_qty'+grn_id+'_'+sid).innerHTML = "<b>Rcv Qty (Pcs):</b> "+qty+extra_info;
	document.f_a.elements['ttl_sn['+grn_id+']['+sid+']'].value = ttl_qty_used;
}

function disableEnterKey(e){
     var key;

     if(window.event) key = window.event.keyCode;	//IE
     else key = e.which;	//firefox

     if(key == 13) return false;
     else return true;
}

function list_sel(n,s){
	var i;
	for(i=0;i<=2;i++){
		if (i==n) $('lst'+i).addClassName('selected');
		else $('lst'+i).removeClassName('selected');
	}
	//if (s=='') return;
	$('grn_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	$('imported_dtl_items').innerHTML = '';
	$('sn_items_btn').style.display = 'none';

	var pg = '';
	if (s!=undefined) pg = '&s='+s;
	if (n==2) pg +='&search='+ $('search').value;

	new Ajax.Updater('grn_list', phpself, {
		parameters: encodeURI('a=search&t='+n+pg),
		evalScripts: true
	});
}

function show_print_dialog(grn_id, branch_id){
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
	document.f_prn.grn_id.value = grn_id;
	document.f_prn.branch_id.value = branch_id;
	curtain(true);
}

function print_ok(){
	$('print_dialog').style.display = 'none';
	//document.f_prn.target = "ifprint";
	document.f_prn.target = "_blank";
	document.f_prn.submit();
	curtain(false);
}

function print_cancel(){
	$('print_dialog').style.display = 'none';
	curtain(false);
}

function curtain_clicked(){
	hidediv("print_dialog");
	hidediv("show_sn_dialog");
	curtain(false);
}

function view_sn_list(grn_id, branch_id){
	center_div('show_sn_dialog');
	$('show_sn_dialog').style.display = '';
	$('show_sn_dialog').style.zIndex = 10000;
	curtain(true);

	new Ajax.Updater('sn_list', phpself, {
		method:'post',
		parameters: "a=view_sn&grn_id="+grn_id+"&branch_id="+branch_id,
		evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onSuccess: function(m){
			if(!m.responseText) alert("Error! No result found.");
		},
		onComplete: function(m){
			
		}
	});
}

function auto_generate_sn(sid, grn_id){
	var qty = document.f_a['qty['+grn_id+']['+sid+']'].value;
	var ttl_sn = document.f_a['ttl_sn['+grn_id+']['+sid+']'].value;
	var qty_remains = int(qty - ttl_sn);

	if(qty_remains <= 0){
		alert("The S/N has been fully entered, auto generate is not required.");
		return false;
	}
	
	var prm = $(document.f_a).serialize();
	
	var params = {
		'a': 'ajax_auto_generate_sn',
		curr_sid: sid,
		curr_grn_id: grn_id,
		qty_remains: qty_remains,
		curr_sn: document.f_a['sn['+grn_id+']['+sid+']'].value
	};

	prm += '&'+$H(params).toQueryString();
	new Ajax.Request(phpself, {
		parameters: prm,
		method: 'post',
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';

			ret = JSON.parse(str); // try decode json object
			if(ret['ok']==1 && ret['html']){ // success
				// append html
				document.f_a['sn['+grn_id+']['+sid+']'].value = ret['html'];
				recalc_qty_used(grn_id, sid);
				return;
			}else{  // save failed
				if(ret['err_msg'])	err_msg = ret['err_msg'];
				else err_msg = str;
			}

			// prompt the error
			if(err_msg) alert(err_msg);	
		}
	});
}
{/literal}
</script>

<!--div id="dtl_item_imp" class="curtain_popup" style="position:absolute;z-index:20000;width:750px;height:430px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_master_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Import from {$master_title}</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<br />
	<form name="f_dtl_items" method="post">
		<div id="dtl_items"></div>
	</form>
</div-->

<!-- print dialog -->
<div id="print_dialog" style="background:#fff;border:0px solid #000;width:250px;height:160px;position:absolute; padding:2px; display:none;">
	<div style="border:2px ridge #CE0000">
	<div id="div_print_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Print Options</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<br />
	<form name="f_prn" method="get">
		<img src="ui/print64.png" hspace="10" align="left"> <h3>Menu</h3>
		<input type="hidden" name="a" value="print_sn">
		<input type="hidden" name="load" value="1">
		<input type="hidden" name="grn_id" value="">
		<input type="hidden" name="branch_id" value="">
		<input type="checkbox" name="print_sn_report" checked disabled> S/N List by GRN Report
		<p align="center">
			<input type="button" class="btn btn-info" value="Print" onclick="print_ok()"> 
			<input type="button" value="Cancel" class="btn btn-danger" onclick="print_cancel()">
		</p>
	</form>
	</div>
</div>

<iframe width="1" height="1" style="visibility:hidden" name="ifprint"></iframe>

<div id="show_sn_dialog" style="background:#fff;border:0px solid #000;width:700px;height:350px;position:absolute; padding:2px; display:none;">
	<div style="border:2px ridge #CE0000">
	<div id="div_show_sn_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">List of Serial No</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<br />
	<div id="sn_list" align="center"></div>
	<p align="center">
		<input type="button" value="Close" onclick="default_curtain_clicked();">
	</p>
	</div>
</div>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<form onsubmit="list_sel(2,0);return false;" name="f_tab">
<div >
	<div class="tab row mx-3" style="white-space:nowrap;">
		<a href="javascript:list_sel(0)" class="btn btn-outline-primary btn-rounded" id="lst0" {if !$t}class="active"{/if}>Waiting for Import</a>
	&nbsp;&nbsp;	<a href="javascript:list_sel(1)" class="btn btn-outline-primary btn-rounded" id="lst1" {if $t eq 1}class="active"{/if}>Imported</a>
		<a name="find" id="lst2"  {if $t eq 2}class="active"{/if}>
			<div class="form-inline">
				&nbsp;&nbsp;Find GRN / Doc No / Serial No 
			<input id="search" class="form-control" name="find" value="{$smarty.request.search|default:$search}">
			&nbsp;&nbsp; <input type="submit" class="btn btn-primary" value="Go">
			</div></a>
	</div>
</div>
</form>
<div class="card mx-3">
	<div class="card-body">
		<div class="stdframe" >
			<form name="f_a" method="post">
				<input type="hidden" name="a" value="save">
				<div id="grn_list">
					{include file="masterfile_sku_items.serial_no.import_items.list.tpl"}
				</div>
				<div id="imported_dtl_items">
					{if count($sn_items)>0}
						{include file="masterfile_sku_items.serial_no.import_items.list_row.tpl"}
					{/if}
				</div>
				<div id="sn_items_btn" align="center" {if count($sn_items) eq 0}style="display:none;"{/if}>
					<input type="button" class="btn btn-primary" value="Save" onclick="save_sn_items(this);">
				</div>
			</form>
		</div>
	</div>
</div>

{include file="footer.tpl"}
{literal}
<script>
//new Draggable('dtl_item_imp',{ handle: 'div_master_header'});
new Draggable('print_dialog',{ handle: 'div_print_header'});
new Draggable('show_sn_dialog',{ handle: 'div_show_sn_header'});
if(document.f_a.search) document.f_a.search.focus();
if(grn_list){
	show_print_dialog(grn_list, branch_id);
}
</script>
{/literal}
