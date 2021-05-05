{*
5/22/2008 4:06:24 PM yinsee
- add dummy.php calling

11/4/2010 3:18:02 PM Justin
- Simplified confirm and commit function become 1 function.
- Added the calculation for total FOC.

11/11/2010 3:23:34 PM Justin
- Changed the function name calcalate become calculate.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

12/30/2015 11:53 AM Kee Kee
- Added price check before submit and block it

6/23/2017 11:33 AM Qiu Ying
- Enhanced to select multiple department in vendor PO access

11/30/2017 12:07 PM Andy
- Enhanced the save function to use ajax.
- Enhanced to check login status when save, confirm and submit.

06/17/2020 02:05 PM Sheila
- Change button size.

6/17/2020 3:55 PM Andy
- Fixed uom popup doesn't show in the correct position.
- Fixed uom doesn't automatically scroll to the selected uom.
*}
{include file=header.tpl}

{literal}
<style>
#uom_popup ul li {
	cursor:pointer;
	display:block;
	margin:0;padding:4px;
}
#uom_popup ul li:hover {
	background:#ff9;
}

#uom_popup ul li.current {
	background:#9ff;
}

#uom_popup:hover ul {
	visibility:visible;
}
.used{
    color: red;
}

table.uom_table td {
	border-bottom: 0;
    border-right: 0;
}

#div_processing_popup{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}

</style>
{/literal}

<script>
var sku;
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function do_select_uom(sku_id){
	sku=sku_id;
	Element.hide('uom_popup');
	
	Position.clone($('td_uom_'+sku), $('uom_popup'), {setHeight: false, setWidth:false});
	Element.show('uom_popup');
	chklabel = $('label_uom_'+sku).innerHTML;
	$$('#uom_popup li').each(function (obj,idx){
		if (obj.innerHTML == chklabel)
		{
			obj.className = 'current';
			
			//var topPos = obj.offsetTop;
			//$('uom_popup').scrollTop = topPos;
			scroll_to_child_obj($('uom_popup'), obj);
			//obj.scrollToPosition;
			//console.log(obj.scrollToPosition);
		}
		else
			obj.className = '';
	});
}

function do_choose_uom(obj){
	var line =obj.title.split(",");
	var val=line[0];
	var text=line[1];
	var fraction=line[2];
	
	/*
	last_fraction=$('uom_fraction_'+sku).value;	
	var last_price=$('price_'+sku).value;
	old_single_price=round(last_price/last_fraction,2);
	new_price=round(old_single_price*fraction,2);		
	$('price_'+sku).value=new_price;	
	*/
	
	$('uom_fraction_'+sku).value=fraction;	
	$('uom_'+sku).value=val;
	$('label_uom_'+sku).innerHTML=text;
	Element.hide('uom_popup');		
}

function recalculate(id){
	//alert(id);
	//var total_row=($('qty_'+id).value)*($('price_'+id).value)*($('uom_'+id).value);
	//$('row_total_'+id).innerHTML=round(total_row,2);
	calculate_total();
}

var g_qty=0;
function calculate_total(){
	var total=0;
	var qty=0;
	var foc=0;
	var e = $('tbl_items').getElementsByClassName('total');
	for(var i=0;i<e.length;i++)	{
 		if (/^qty_/.test(e[i].id)){
			qty+=float(e[i].value);
		}
 		if (/^foc_/.test(e[i].id)){
			foc+=float(e[i].value);
		}
 		/*if (/^row_total_/.test(e[i].id)){
			total+=float(e[i].innerHTML);
		}*/				
	}
	g_qty=qty;
	$('total_qty').innerHTML=round(qty);
	$('total_foc').innerHTML=round(foc);
	qty=0;
	foc=0;
	//$('total_amt').innerHTML=round(total,2);	
}

function check_qty(){
	if(g_qty>0)
		return true;
	else
		return false;
}

function check_price()
{	
	var e = $('tbl_items').getElementsByClassName('op');
	var e1 = $('tbl_items').getElementsByClassName('total');
	var result = true;
	if(e.length > 0)
	{				
		for(var i=0;i<e.length;i++)	
		{			
			var result1 = true;
			if (/^order_price_/.test(e[i].id)){
				var id = e[i].id;				
				var sku_id = id.gsub(/order_price_/, '');
				var price = e[i].value;				
			}
			
			if (/^qty_/.test(e[i].id)){				
				var qty = e[i].value;							
			}
			
			if(price!="" || qty!="")
			{
				price = float(price);
				qty = float(qty);
								
				if(price <= 0 && qty <=0)
				{					
					$(sku_id).addClassName("highlight_row");
					result1 = false;					
				}
				
				if(price<=0 && qty>0)
				{					
					$(sku_id).addClassName("highlight_row");
					result1 = false;				
				}
							
				if(result1==true)
				{					
					$(sku_id).removeClassName("highlight_row");
				}
				else{
					result = result1;
				}
			}				
		}
	}
	
	return result;			
}

function do_save(){
	//if (check_login()) {
		document.f_a.a.value='save';
		document.f_a.target = '';
		document.f_a.submit();
	//}
}

function do_sc(type){
	if(!check_login(undefined, {'no_pop_login': 1})){
		alert('You have already logout');
		return;
	}
	//if (check_login()) {
		var result1 = true
		var result =check_qty();
		if(type=="submit") 
			result1 = check_price();		
		if(result==true && result1==true){		
				if(!confirm('Are you sure want to '+type+'?')) return false;
				document.f_a.a.value=type;
				document.f_a.target = '';
				document.f_a.submit();
		}
		else{
			if(result==false)
			{
				alert('Please keyin at least 1 item to '+type+'.');
				return;
			}	
			else{
				alert('PO Cost price required.');
			}
		}
	//}
}

function do_print(){
	//if (check_login()) {
		var result=check_qty();
		if(result==true){
			document.f_a.a.value='print';
			document.f_a.target = 'ifprint';
			document.f_a.submit();
		}
		else{
			alert('Please keyin at least 1 item to print.');
			return;
		}
	//}
}

function toggle_view_more(){
	if ($("div_view_more").style.display == "none"){
		$("view_type").innerHTML = "show less";
		$("div_view_more").style.display = "";
	}
	else{
		$("view_type").innerHTML = "show more";
		$("div_view_more").style.display = "none";
	}
}

function ajax_do_save(){

	// show curtain
	curtain(true, 'curtain2');
	center_div($('div_processing_popup').show());
	
	// construct params
	var params = $(document.f_a).serialize();

	new Ajax.Request(phpself, {
		method: 'post',
		parameters: params+'&a=ajax_save_vendor_items',
		onComplete: function(e){
			try{
		        // try to decode json
                eval('var json = '+e.responseText);
                if(json['ok']){
					alert('Save successfully.');
				}else if(json['error']){  // got error
					var err = json['error'];					
					alert(err);
				}
			}catch(ex){
			    // failed to decode json
				alert(e.responseText);
			}
			
			// hide curtain
			$('div_processing_popup').hide();
			curtain(false, 'curtain2');			
		}
	});
}
//refresh the session each 25 minutes to avoid timeout when user take long time (>30 mins) to select sku. (request by SLLEE)
new Ajax.PeriodicalUpdater('', "dummy.php?ac=1", {frequency:1500});
</script>
{/literal}
<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>

<div id="div_processing_popup" style="display:none;width:350px;height:50px;">
	<div id="div_processing_popup_content" style="text-align:center;height:100%;vertical-align:middle;font-size:30px;">
		Processing. . .
	</div>
</div>

<h1>{$PAGE_TITLE}</h1>
<div class=stdframe>
<table class=tl cellspacing=0 cellpadding=4 border=0>
<tr>
<th>Vendor</th><td colspan="3">{$form.vendor}</td>
</tr><tr>
<th valign="top">Department</th>
<td colspan="3">
	{if $form.d_id}
		{assign var=upper_str value=""}
		{assign var=lower_str value=""}
		{foreach name=loop_dept from=$form.d_id item=d_name}
			{assign var=dept_name value=$d_name}
			{if $smarty.foreach.loop_dept.iteration < 5}
				{if $smarty.foreach.loop_dept.iteration neq 4 && $smarty.foreach.loop_dept.iteration neq $form.d_id|@count}
					{assign var=dept_name value="$dept_name, "}
				{/if}
				{assign var=upper_str value=$upper_str$dept_name}
			{else}
				{if $smarty.foreach.loop_dept.iteration neq $form.d_id|@count}
					{assign var=dept_name value="$dept_name, "}
				{/if}
				{assign var=lower_str value=$lower_str$dept_name}
			{/if}
		{/foreach}
		<span>{$upper_str}</span>{if $lower_str neq ""}<span id="div_view_more" style="display:none;">, {$lower_str}</span>
			&nbsp;[<a id="view_type" href="javascript:void(toggle_view_more())">show more</a>]
		{/if}
	{else}
		{$form.dept_name}
	{/if}
</td>
</tr><tr>
	<th>Requested By</th><td>{$form.u_create}</td>
	<td><b>PO Owner</b>&nbsp;&nbsp;{$form.u}</td>
</tr><tr>
<th>IP Logged</th><td colspan="3">{$form.access_ip}</td>
</tr><tr>
<th nowrap>Ticket Created on</th><td colspan="3">{$form.added} (valid for {$config.po_vendor_ticket_expiry} days)</td>
</tr>
</table>
</div>

<div id="uom_popup" style="display:none;position:absolute;z-index:100;background:#fff;border:1px solid #000;margin: 0 0 0 0;width:150px;height:200px;overflow:auto;" class="small">
<ul id="tab">
{foreach item="curr_uom" from=$uom}
<li onclick="do_choose_uom(this);recalculate(sku);" id="selected_uom" title="{$curr_uom.id},{$curr_uom.code},{$curr_uom.fraction}">{$curr_uom.code}</li>
{/foreach}
</ul>
</div>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}

<p>
{if $form.is_confirm}
<h5>Confirmed SKU : <font color=black>{count var=$items} record(s) found</font></h5>
{else}
<h5>Available SKU : <font color=black>{count var=$items} record(s) found</font></h5>
{/if}

<ul>
<li>
- <font color=red>RED</font> color row(s) is the item(s) have been created by you in Vendor PO<br></li>
</ul>

<form name=f_a method=post>
<input type=hidden name=s_field value="{$s_field}">
<input type=hidden name=s_arrow value="{$s_arrow}">
<input type=hidden name=vendor_id value="{$form.vendor_id}">
<input type=hidden name=user_id value="{$form.user_id}">
<input type=hidden name=branch_id value="{$form.branch_id}">
<input type=hidden name=vendor value="{$form.vendor}">
<input type=hidden name=a>
<table id=tbl_items class="sortable tb" border=0 cellspacing=0 cellpadding=4 width="100%">
{include file=vendor_po_request.home.items.tpl}
</table>
</form>

<div style="position:fixed;bottom:0;background:#ddd;width:100%;text-align:center;left:0;padding:3px;opacity:1;height:30px;">
	<input type="button" value="Logout" class="btn btn-primary" onclick="document.location='/vendor_po_request.home.php?a=logout'">
	<input type="button" value="Print" class="btn btn-success" onclick="do_print();">

	{if $form.is_confirm}
		<input id="submits_r" name="submits_r" type="button" onclick="do_sc('submit');" value="Submit" class="btn btn-primary">
		<input type="button" value="Back" class="btn btn-error" onclick="do_save();">
	{else}
		<input id="submits_confirm" name="submits_confirm" type="button" onclick="do_sc('confirm');" value="Confirm" class="btn btn-success">
		<input id="submits_save" name="submits_save" type="button" onclick="ajax_do_save();" value="Save" class="btn btn-warning">
	{/if}
</div>

<script>
calculate_total();
</script>


{include file=footer.tpl}
<div style="height:30px;">

</div>