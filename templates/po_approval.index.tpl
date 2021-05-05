{*
4/19/2012 2:16:12 PM Andy
- Fix some minor javascript error.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'
*}

{include file=header.tpl}
{literal}
<script>

function do_terminate(last_approver){
	if(last_approver){
		document.f_c.remark2.value=document.f_a.remark2.value;
	}
	document.f_c.approve_comment.value = '';
	var p = prompt('Enter reason to terminate:');
	if (!p || p.trim()=='') return;
	document.f_c.approve_comment.value = p;
	if (confirm('Press OK to Terminate the PO.')){
	    document.f_c.a.value = "terminate_approval";
		document.f_c.submit();
	}
}

function do_kiv(){
	if (confirm('Press OK to KIV the PO.')){
	    document.f_c.a.value = "kiv_approval";
	    document.f_c.submit();
	}
}

function do_approve(last_approver){
	if(last_approver){
		document.f_c.remark2.value=document.f_a.remark2.value;
	}
    document.f_c.approve_comment.value='Approve';
	if (confirm('Press OK to Approve the PO.')){
	    document.f_c.a.value = "save_approval";
	    document.f_c.submit();
	}
}

function do_reject(last_approver){
	if(last_approver){
		document.f_c.remark2.value=document.f_a.remark2.value;
	}
	document.f_c.approve_comment.value = '';
	var p = prompt('Enter reason to reject:');
	if (!p || p.trim()=='') return;
	document.f_c.approve_comment.value = p;
	if (confirm('Press OK to Reject the PO.')){
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

	$('loadpo').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...<br><img src=ui/pixel.gif height=500 width=1>';

	new Ajax.Updater('loadpo', 'po_approval.php', {
		parameters: 'a=ajax_load_po&id='+id+'&branch_id='+branch_id+'&'+Form.serialize(document.f_on_behalf),
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

<div style="float:left;padding:4px;"><b>Select PO to approve</b></div>

<div style="float:left" id=tab_sel><span id=sel_name>-</span>
<ul id=tab>
{section name=i loop=$po}
{strip}
<li onclick="select_tab(this)" id="tab{$po[i].id}" title="{$po[i].id},{$po[i].branch_id}">
 &nbsp;
 {$po[i].prefix}{$po[i].id|string_format:"%05d"} &nbsp;
 (Branch: {$po[i].branch_name}, Vendor: {$po[i].vendor}, Date: {$po[i].po_date}, Created By: {$po[i].user_name})</li>
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

<div id=loadpo>
</div>

<script>
select_tab();
</script>

{include file=footer.tpl}
