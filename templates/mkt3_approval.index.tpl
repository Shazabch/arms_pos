{include file=header.tpl}
{literal}
<script>

function do_approve()
{
    document.f_approve.reason.value = 'Approve';
	if (confirm('Press OK to Approve the MKT.'))
	{
	    document.f_approve.a.value = "approve";
	    document.f_approve.submit();
	}
}

function do_reject()
{
	document.f_approve.reason.value = '';
	var p = prompt('Enter reason to reject:');
	if (p.trim()=='' || p==null) return;
	document.f_approve.reason.value = p;
	if (confirm('Press OK to Reject the MKT.'))
	{
	    document.f_approve.a.value = "reject";
	    document.f_approve.submit();
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

		lst[0].className = "active";
		load_approval(lst[0]);
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
    var line = obj.title.split(",");
	id =line[0];
	branch_id = line[1];
	dept_id = line[2];

	$('sel_name').innerHTML = obj.innerHTML;

	$('udiv').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...<br><img src=ui/pixel.gif height=500 width=1>';

	new Ajax.Updater('udiv', 'mkt3_approval.php', {
		parameters: 'a=ajax_load_mkt3&id='+id+'&branch_id='+branch_id+'&dept_id='+dept_id ,
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

<h1>{$PAGE_TITLE}</h1>

<div style="float:left;padding:4px;"><b>Select MKT to approve</b></div>

<div style="float:left" id=tab_sel><span id=sel_name>-</span>
<ul id=tab>
{section name=i loop=$mkt3}
{strip}
<li onclick="select_tab(this)" id="tab{$mkt3[i].mkt0_id}" title="{$mkt3[i].mkt0_id},{$mkt3[i].branch_id},{$mkt3[i].dept_id}">
{if $mkt3.status == 0}<img src=ui/notify_sku_new.png  width=16 height=16 align=absmiddle title="New Application">
{elseif $mkt3.status == 1}<img src=ui/notify_sku_approve.png width=16 height=16 align=absmiddle title="
	{if $mkt3.approvals=='' or $mkt3.approvals=='|'}
		Fully Approved
	{else}
	    In Approval Cycle
	{/if}">
{elseif $mkt3.status == 2}<img src=ui/notify_sku_reject.png width=16 height=16 align=absmiddle title="Rejected">
{elseif $mkt3.status == 3}<img src=ui/notify_sku_pending.png width=16 height=16 align=absmiddle title="KIV (Pending)">
{else}<img src=ui/notify_sku_terminate.png width=16 height=16 align=absmiddle title="
	{if $mkt3.approvals=='' or $mkt3.approvals=='|'}
		Terminated
	{else}
	    In Terminate Cycle
	{/if}">
{/if}
 &nbsp;
 MKT{$mkt3[i].mkt0_id|string_format:"%05d"} &nbsp;
 (Title: {$mkt3[i].title}, Branch: {$mkt3[i].branch_name}, Dept: {$mkt3[i].dept_name}, Created: {$mkt3[i].user_name})</li>
{/strip}
{/section}
</ul>
</span>
</div>

<br style="clear:both">
<br>

<div class="stdframe" style="background:#fff;">
<div id=udiv>
<!-- data will be loaded here -->
</div>
</div>

<script>
select_tab();
</script>
{include file=footer.tpl}

