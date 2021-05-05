{include file='header.tpl'}

{literal}
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


<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var CC_APPROVAL = {
	initialize: function(){
		// auto select first one
		this.select_tab();
	},
	// function when user select the cycle count
	select_tab: function(obj){
		if (obj == undefined){
			var lst = $('tab').getElementsByTagName("LI");
			if (lst.length==0){
				alert('Congratulation! You have completed all approval jobs.\nTake a break ;)');
				document.location = '/home.php';
				return;
			}

			lst[0].className = "active";
			this.load_approval(lst[0]);
		}
		else{
			var lst = $('tab').getElementsByTagName("LI");
			$A(lst).each( function(r,idx) {
				if (r.className == "active")
					r.className = '';
			});
			obj.className = "active";
			this.load_approval(obj);
		}
	},
	// core function to load cycle count
	load_approval: function (obj){
		var line = obj.title.split(",");
		id =line[0];
		branch_id = line[1];

		$('sel_name').innerHTML = obj.innerHTML;

		$('udiv').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...<br><img src=ui/pixel.gif height=500 width=1>';

		new Ajax.Updater('udiv', phpself, {
			parameters: 'a=ajax_load_cycle_count&id='+id+'&branch_id='+branch_id+'&',
			evalScripts: true
		});
	},
	// function when user click on terminate
	do_cancel: function (){
		document.f_approval.comment.value = '';
		var p = prompt('Enter reason to terminate/Cancel:');
		if (p.trim()=='' || p==null) return;
		document.f_approval.comment.value = p;
		if (confirm('Press OK to Terminate this Sales Order.'))
		{
			document.f_approval['status_type'].value = "cancel";
			document.f_approval.submit();
		}
	},
	// function when user click on approve
	do_approve: function (){
		document.f_approval.comment.value = 'Approve';
		if (confirm('Press OK to Approve.'))
		{
			document.f_approval['status_type'].value = "approve";
			document.f_approval.submit();
		}
	},
	// function when user click on reject
	do_reject: function (){
		document.f_approval.comment.value = '';
		var p = prompt('Enter reason to reject:');
		if (p.trim()=='' || p==null) return;
		document.f_approval.comment.value = p;
		if (confirm('Press OK to Reject.')){
			document.f_approval['status_type'].value = "reject";
			document.f_approval.submit();
		}
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<div style="float:left;padding:4px;"><b>Select Cycle Count to approve</b></div>

<div style="float:left" id="tab_sel">
<span id="sel_name">-</span>
<ul id="tab">
{foreach from=$cc_list item=r}
{strip}
<li onclick="CC_APPROVAL.select_tab(this)" id="tab{$r.id}" title="{$r.id},{$r.branch_id}">
 &nbsp;
 {$r.doc_no} &nbsp;
 (Stock Take Branch: {$r.b2_code}, Date: {$r.st_date}, Created: {$r.user_name})</li>
{/strip}
{/foreach}
</ul>
</span>
</div>

<br style="clear:both">
<br>

<div id="udiv">
<!-- data will be loaded here -->
</div>

<script>
CC_APPROVAL.initialize();
</script>
{include file='footer.tpl'}