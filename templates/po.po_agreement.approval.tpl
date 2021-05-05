{*
7/31/2013 3:36 PM Andy
- Fix show double footer.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'
*}

{include file="header.tpl"}

<script type="text/javascript">

{literal}
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

	$('load_pa').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...<br><img src=ui/pixel.gif height=500 width=1>';

	new Ajax.Updater('load_pa', 'po.po_agreement.approval.php', {
		parameters: 'a=ajax_load_pa&id='+id+'&branch_id='+branch_id+'&'+Form.serialize(document.f_on_behalf),
		evalScripts: true
		});
}

function do_terminate(last_approver){
	document.f_c.approve_comment.value = '';
	var p = prompt('Enter reason to terminate:');
	if (!p || p.trim()=='') return;
	document.f_c.approve_comment.value = p;
	if (confirm('Press OK to Terminate the Promption.')){
	    document.f_c.a.value = "terminate_approval";
		document.f_c.submit();
	}
}

function do_approve(last_approver){
    document.f_c.approve_comment.value='Approve';
	if (confirm('Press OK to Approve the Promotion.')){
	    document.f_c.a.value = "save_approval";
	    document.f_c.submit();
	}
}

function do_reject(last_approver){
	document.f_c.approve_comment.value = '';
	var p = prompt('Enter reason to reject:');
	if (!p || p.trim()=='') return;
	document.f_c.approve_comment.value = p;
	if (confirm('Press OK to Reject the Promotion.')){
	    document.f_c.a.value = "reject_approval";
	    document.f_c.submit();
	}
}
{/literal}
</script>

{literal}
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

<div style="float:left;padding:4px;"><b>Select Purchase Agreement to approve</b></div>

<div style="float:left" id=tab_sel><span id=sel_name>-</span>
<ul id=tab>
{foreach from=$pa_list item=r}
	{strip}
		<li onclick="select_tab(this)" id="tab{$r.id}" title="{$r.id},{$r.branch_id}">
			 &nbsp;
			 Purchase Agreement #{$r.id} &nbsp;
			 (Branch: {$r.branch_name}, From: {$r.date_from} to {$r.date_to}, Created By: {$promotion[i].user_name})
		 </li>
	{/strip}
{/foreach}
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

<div id="load_pa">
</div>

<script>
select_tab();
</script>

{* include file="footer.tpl" *}