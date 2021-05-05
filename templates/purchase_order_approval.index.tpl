{*
REVISION HISTORY
===============
12/3/2007 4:51:24 PM gary
- modify to new/standard approval layout.
*}

{include file=header.tpl}

{literal}
<script>

function do_terminate()
{
	document.f_a.approve_comment.value = '';
	var p = prompt('Enter reason to terminate:');
	if (p.trim()=='' || p==null) return;
	document.f_a.approve_comment.value = p;
	if (confirm('Press OK to Terminate the PO.'))
	{
	    document.f_a.a.value = "terminate_approval";
		document.f_a.submit();
	}
}

function do_kiv()
{
	if (confirm('Press OK to KIV the PO.'))
	{
	    document.f_a.a.value = "kiv_approval";
	    document.f_a.submit();
	}
}

function do_approve()
{
    document.f_a.approve_comment.value = 'Approve';
	if (confirm('Press OK to Approve the PO.'))
	{
	    document.f_a.a.value = "save_approval";
	    document.f_a.submit();
	}
}

function do_reject()
{
	document.f_a.approve_comment.value = '';
	var p = prompt('Enter reason to reject:');
	if (p.trim()=='' || p==null) return;
	document.f_a.approve_comment.value = p;
	if (confirm('Press OK to Reject the PO.'))
	{
	    document.f_a.a.value = "reject_approval";
	    document.f_a.submit();
	}
}


function select_tab(obj){
	if (obj == undefined){
		var lst = $('tab').getElementsByTagName("LI");
		if (lst.length==0)
		{
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

	new Ajax.Updater('loadpo', 'purchase_order_approval.php', {
		parameters: 'a=ajax_load_po&id='+id+'&branch_id='+branch_id,
		evalScripts: true
		});
}

/*
function load_po(id)
{
	$('loadpo').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	var s = id.split(",");
	new Ajax.Updater(
		"loadpo",
		"purchase_order_approval.php",
		{
		    method: 'get',
			parameters: 'a=ajax_load_po&id='+s[0]+'&branch_id='+s[1],
			evalScripts: true
		});
}
*/
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

<h1>{$PAGE_TITLE}</h1>

<div style="float:left;padding:4px;"><b>Select PO to approve</b></div>

<div style="float:left" id=tab_sel><span id=sel_name>-</span>
<ul id=tab>
{section name=i loop=$po}
{strip}
<li onclick="select_tab(this)" id="tab{$po[i].id}" title="{$po[i].id},{$po[i].branch_id}">
 &nbsp;
 {$po[i].prefix}{$po[i].id|string_format:"%05d"} &nbsp;
 (Branch: {$po[i].branch_name}, Date: {$po[i].po_date}, Created By: {$po[i].user_name})</li>
{/strip}
{/section}
</ul>
</span>
</div>

<br style="clear:both">
<br>

<div id=loadpo>
</div>

<script>
select_tab();
</script>

{*
<h1>Purchase Order Approval</h1>
<p>
Select a PO to approve <select id=id>
{section name=i loop=$po}
<option value="{$po[i].id},{$po[i].branch_id}">{$po[i].id}</option>
{/section}
</select> <input type=button onclick="load_po($('id').value)" value="Load">
</p>

<div id=loadpo>
</div>
*}

{include file=footer.tpl}
