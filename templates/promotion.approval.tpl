{*
12/21/2010 6:30:51 PM Andy
- Fix promotion approval word mistake.

7/31/2013 3:36 PM Andy
- Fix show double footer.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

5/26/2014 2:16 PM Fithri
- able to select item(s) to reject & must provide reason for each rejected item
*}

{include file=header.tpl}

<script>
var promotion_approval_allow_reject_by_items = '{$config.promotion_approval_allow_reject_by_items}';
</script>

{literal}
<script>

function do_terminate(last_approver){
	document.f_c.approve_comment.value = '';
	var p = prompt('Enter reason to terminate:');
	if (p.trim()=='' || p==null) return;
	document.f_c.approve_comment.value = p;
	if (confirm('Press OK to Terminate the Promption.')){
	    document.f_c.a.value = "terminate_approval";
		document.f_c.submit();
	}
}

function do_kiv(){
	if (confirm('Press OK to KIV the Promotion.')){
	    document.f_c.a.value = "kiv_approval";
	    document.f_c.submit();
	}
}

function do_approve(last_approver,type){
	
	if (promotion_approval_allow_reject_by_items) {
	
		//make sure each of them has reason
		rejected_item_cb = document.getElementsByClassName('rejected_item_cb');
		for (var index = 0, len = rejected_item_cb.length; index < len; ++index) {
			var item = rejected_item_cb[index];
			if ($(item).checked) {
				if ($(item).next('span').down().value.trim() == '') {
					alert('Please provide reason for each rejected item');
					$(item).next('span').down().focus();
					return;
				}
			}
		}
		
		//make not not all items were rejected
		if (type=='discount') item_count = document.getElementsByClassName('item_no').length;
		if (type=='mix&match') item_count = document.getElementsByClassName('div_promo_group').length;
		document.getElementsByClassName('rejected_item_cb').each(function(item) {
			if ($(item).checked) item_count--;
		});
		if (item_count < 1) {
			alert('Cannot reject all promotion items');
			return;
		}
	}
	//alert('ready to submit');return;
	
    document.f_c.approve_comment.value='Approve';
	if (confirm('Press OK to Approve the Promotion.')){
	    document.f_c.a.value = "save_approval";
	    document.f_c.rejected_item_data.value = Form.serialize(document.f_a);
	    document.f_c.submit();
	}
}

function do_reject(last_approver){
	document.f_c.approve_comment.value = '';
	var p = prompt('Enter reason to reject:');
	if (p.trim()=='' || p==null) return;
	document.f_c.approve_comment.value = p;
	if (confirm('Press OK to Reject the Promotion.')){
	    document.f_c.a.value = "reject_approval";
	    document.f_c.submit();
	}
}


function select_tab(obj){
	if (obj == undefined){
		var lst = $('tab').getElementsByTagName("LI");
		if (lst.length==0){
			alert('Congratulation! You have completed all approval jobs.\nTake a break ;)');
			document.location = '/home.php';
			return;
		}

		lst[0].className = "active";
		load_approval(lst[0]);
	}
	else{
		var lst = $('tab').getElementsByTagName("LI");
		$A(lst).each( function(r,idx) {
			if (r.className == "active")
				r.className = '';
		});
		obj.className = "active";
		load_approval(obj);
	}
}

function load_approval(obj){
    var line = obj.title.split(",");
	id =line[0];
	branch_id = line[1];

	$('sel_name').innerHTML = obj.innerHTML;

	$('loadpromo').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...<br><img src=ui/pixel.gif height=500 width=1>';

	new Ajax.Updater('loadpromo', 'promotion_approval.php', {
		parameters: 'a=ajax_load_promotion&id='+id+'&branch_id='+branch_id+'&'+Form.serialize(document.f_on_behalf),
		evalScripts: true
		});
}

var context_info;

function hide_context_menu(){
	$('ul_menu').onmouseout = undefined;
	$('ul_menu').onmousemove = undefined;	 
	Element.hide('item_context_menu');
}

function show_context_menu(obj, id, item_id, is_foc){
	context_info = { element: obj, id: id, sku_item_id: item_id, is_foc: is_foc};
	$('item_context_menu').style.left = ((document.body.scrollLeft)+mx) + 'px';
	$('item_context_menu').style.top = ((document.body.scrollTop)+my) + 'px';
	Element.show('item_context_menu');
	
	$('ul_menu').onmouseout = function() {
		context_info.timer = setTimeout('hide_context_menu()', 100);
	}
	
	$('ul_menu').onmousemove = function() {
		if (context_info.timer!=undefined) clearTimeout(context_info.timer);
		context_info.timer = undefined;
	}
	return false;
}

function get_item_po_history(id){
	center_div('price_history_popup');
	Element.show('price_history_popup');
	$('price_history_list_popup').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('price_history_list_popup','ajax_sku_popups.php',{
		    parameters: 'a=sku_po_history&id='+id,
		    evalScripts:true
	});
}

function get_item_sales_trend(id){
	center_div('price_history_popup');
	Element.show('price_history_popup');
	$('price_history_list_popup').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('price_history_list_popup','ajax_sku_popups.php',{
		    parameters: 'a=sku_sales_trend&id='+id,
		    evalScripts:true
	});
}

function reject_cb_clicked(e) {
	if (e.checked) $(e).next('span').show();
	else $(e).next('span').hide();
}
</script>

<style>
#tab_sel {
	border:1px solid #ccc;
	width:700px;
	padding:4px;
	background:#fff url('/ui/findcat_expand.png') right center no-repeat;
}

#tab_sel ul {
	position:absolute;
	visibility:hidden;
	background:#fff;
	border:1px solid #ccc;
	border-top:none;
	list-style:none;
	margin:0;padding:0;
	margin-left:-5px;
	margin-top:5px;
	width:708px;
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
	visibility:visible;
}

</style>
{/literal}

<h1>{$PAGE_TITLE}{if $approval_on_behalf} (on behalf of {$approval_on_behalf.on_behalf_of_u}){/if}</h1>

<div style="float:left;padding:4px;"><b>Select Promotion to approve</b></div>

<div style="float:left" id=tab_sel><span id=sel_name>-</span>
<ul id=tab>
{section name=i loop=$promotion}
{strip}
<li onclick="select_tab(this)" id="tab{$promotion[i].id}" title="{$promotion[i].id},{$promotion[i].branch_id}">
 &nbsp;
 Promo#{$promotion[i].id} &nbsp;
 (Branch: {$promotion[i].branch_name}, From: {$promotion[i].date_from} {$promotion[i].time_from} to {$promotion[i].date_to} {$promotion[i].time_to}, Created By: {$promotion[i].user_name})</li>
{/strip}
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

<div id=loadpromo>
</div>

<script>
select_tab();
</script>

{* include file=footer.tpl *}
