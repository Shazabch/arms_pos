{*
6/3/2010 3:44:48 PM Andy
- Fix some wording mistake.

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

8/1/2013 5:56 PM Andy
- Fix show double footer.
- Change to prompt error if the cn/dn is not allow to approve/reject.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'
*}

{include file=header.tpl}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function do_cancel(){
	document.f_b.comment.value = '';
	var p = prompt('Enter reason to terminate/Cancel:');
	if (p.trim()=='' || p==null) return;
	document.f_b.comment.value = p;
	if (confirm('Press OK to Terminate.'))
	{
		document.f_b.a.value = "cancel";
		document.f_b.submit();
	}
}

function do_approve(){
	new Ajax.Request(phpself,{
		method:'post',
		parameters: 'a=check_printed_report&curr_date='+document.f_b.curr_date.value+'&branch_id='+document.f_b.branch_id.value,
	    evalScripts: true,
		onComplete: function (e) {
			var resp = e.responseText.trim();
			if (resp == '0') {
				document.f_b.comment.value = 'Approve';
				if (confirm('Press OK to Approve.'))
				{
					document.f_b.a.value = "approve";
					document.f_b.submit();
				}
			}
			else{
				//$('err').innerHTML = resp;
				alert(resp);
			} 
    	}
	});
}

function do_reject(){
	new Ajax.Request(phpself,{
		method:'post',
		parameters: 'a=check_printed_report&curr_date='+document.f_b.curr_date.value+'&branch_id='+document.f_b.branch_id.value,
	    evalScripts: true,
		onComplete: function (e) {
			var resp = e.responseText.trim();
			if (resp == '0') {
				document.f_b.comment.value = '';
				var p = prompt('Enter reason to reject:');
				if (p.trim()=='' || p==null) return;
				document.f_b.comment.value = p;
				if (confirm('Press OK to Reject.')){
					document.f_b.a.value = "reject";
					document.f_b.submit();
				}
			}
			else{
				//$('err').innerHTML = resp;
				alert(resp);
			} 
    	}
	});
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

	new Ajax.Updater('udiv', phpself, {
		parameters: 'a=ajax_load_cn&id='+id+'&branch_id='+branch_id+'&'+Form.serialize(document.f_on_behalf),
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

<div style="float:left;padding:4px;"><b>Select {$sheet_name|lower|capitalize} to approve</b></div>

<div style="float:left" id="tab_sel">
<span id="sel_name">-</span>
<ul id="tab">
{section name=i loop=$sheet_list}
{strip}
<li onclick="select_tab(this)" id="tab{$sheet_list[i].id}" title="{$sheet_list[i].id},{$sheet_list[i].branch_id}">
 &nbsp;
 {$sheet_list[i].prefix}{$sheet_list[i].id|string_format:"%05d"}  &nbsp;
 (Branch: {$sheet_list[i].branch_name}, Date: {$sheet_list[i].cn_date}, Created: {$sheet_list[i].user_name})</li>
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
