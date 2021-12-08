{*
8/17/2012 3:33 PM Andy
- Fix all counter popup does not have scrollbar.

3/2/2017 2:18 PM Andy
- Move function periodicLoadBranch() to pos_live.tpl
- Fixed periodicLoad bug.
*}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var t_date = '{$smarty.request.date}';
var LOADING = '<img src="/ui/clock.gif" />';
var context_info;
var bid;
</script>

{literal}
<script type="text/javascript">
function showDetails(){
	window.open(phpself+'?a=load_counter_table&branch_id='+bid+'&date='+t_date,'','menubar=0,toolbar=0,location=0,scrollbars=1');
}

function show_context_menu(obj, branch_id)
{
	context_info = { element: obj, branch_id: branch_id, date: t_date };
	bid = branch_id;
	
	$('item_context_menu').style.left = ((document.body.scrollLeft)+mx) + 'px';
	$('item_context_menu').style.top = ((document.body.scrollTop)+my) + 'px';

	$('item_context_menu').show();

	$('ul_menu').onmouseout = function() {
		context_info.timer = setTimeout('hide_context_menu()', 100);
	}

	$('ul_menu').onmousemove = function() {
		if (context_info.timer!=undefined) clearTimeout(context_info.timer);
		context_info.timer = undefined;
	}
	return false;
}

function hide_context_menu()
{
	$('ul_menu').onmouseout = undefined;
	$('ul_menu').onmousemove = undefined;
	Element.hide('item_context_menu');
}
</script>

{/literal}

{if !$all_branchs}
	{if isset($smarty.request.submits)}<br /><span class="mx-3 text-danger">No data</span>{/if}
{else}
	<!-- Popup menu -->
	<div id="item_context_menu" style="display:none;position:absolute;">
	<ul id="ul_menu" class="contextmenu">
	<li><a href="javascript:showDetails();"><img src=/ui/icons/money.png align=absmiddle> Show All Counter</a></li>
	</ul>
	</div>

	<h1>Branches Sales</h1>
	<div id="div_branchs_table">
		{include file="pos_live.all_branchs.table.tpl"}
	</div>
{/if}

<script>periodicLoadBranch();</script>