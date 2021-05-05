{*
7/31/2013 3:36 PM Andy
- Fix show double footer.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

9/24/2015 4:40 PM DingRen
- Fix wrong prompt message
*}

{include file=header.tpl}
{literal}
<script>

function do_cancel(){
	document.f_b.comment.value = '';
	var p = prompt('Enter reason to terminate/Cancel:');
	if (p.trim()=='' || p==null) return;
	document.f_b.comment.value = p;
	if (confirm('Press OK to Terminate this Sales Order.'))
	{
		document.f_b.a.value = "cancel";
		document.f_b.submit();
	}
}

function do_approve(){
    document.f_b.comment.value = 'Approve';
	if (confirm('Press OK to Approve the Sales Order.'))
	{
	    document.f_b.a.value = "approve";
	    document.f_b.submit();
	}
}

function do_reject(){
	document.f_b.comment.value = '';
	var p = prompt('Enter reason to reject:');
	if (p.trim()=='' || p==null) return;
	document.f_b.comment.value = p;
	if (confirm('Press OK to Reject the Sales Order.')){
	    document.f_b.a.value = "reject";
	    document.f_b.submit();
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

	$('udiv').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...<br><img src=ui/pixel.gif height=500 width=1>';

	new Ajax.Updater('udiv', 'sales_order_approval.php', {
		parameters: 'a=ajax_load_sales_order&id='+id+'&branch_id='+branch_id+'&'+Form.serialize(document.f_on_behalf),
		evalScripts: true
	});
}

</script>

<style>
#tab_sel {
	border:1px solid #ccc;
	width:500px;
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
	width:508px;
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

<div style="float:left;padding:4px;"><b>Select Sales Order to approve</b></div>

<div style="float:left" id="tab_sel">
<span id="sel_name">-</span>
<ul id="tab">
{section name=i loop=$order_list}
{strip}
<li onclick="select_tab(this)" id="tab{$order_list[i].id}" title="{$order_list[i].id},{$order_list[i].branch_id}">
 &nbsp;
 {$order_list[i].order_no} &nbsp;
 (Branch: {$order_list[i].branch_name}, Date: {$order_list[i].order_date}, Created: {$order_list[i].user_name})</li>
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

<div id=udiv>
<!-- data will be loaded here -->
</div>

<script>
select_tab();
</script>
{* include file=footer.tpl *}
