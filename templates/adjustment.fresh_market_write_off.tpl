{*
7/29/2011 1:43:11 PM Andy
- Fixed the print function not working properly.
*}

{include file='header.tpl'}

<style>
{literal}
#tbody_adj tr:nth-child(odd){
	background-color: #eeeeee;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var tab_num = 1;
var page_num = 0;

{literal}
var search_str = '';

function list_sel(selected){
	var container_list = $('container_list');
	if(!container_list) return;

	if(selected==1){    // tab 1 = search
		var tmp_search_str = $('inp_item_search').value.trim();

		if(tmp_search_str==''){ // empty search string
			return;
		}else 	search_str = tmp_search_str;
	}
	if(typeof(selected)!='undefined'){
		tab_num = selected;
		page_num = 0;
	}

	var all_tab = $$('.tab .a_tab');
	for(var i=0;i<all_tab.length;i++){
		$(all_tab[i]).removeClassName('active');
	}
	$('lst'+tab_num).addClassName('active');

	$(container_list).update(_loading_);
	new Ajax.Updater(container_list,phpself+'?a=ajax_list_sel&ajax=1&t='+tab_num+'&p='+page_num,{
		parameters:{
			search_str: search_str
		},
		onComplete: function(msg){

		},
		evalScripts: true
	});
}

function search_input_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		list_sel(1);
	}
}

function page_change(ele){
	page_num = ele.value;
	list_sel();
}

function do_print(id,bid){
	if(!confirm('Click OK to print'))   return false;
	document.f_print['id'].value = id;
	document.f_print['branch_id'].value = bid;
    document.f_print.a.value='print';
	//document.f_print.target = 'ifprint';
	document.f_print.target = '_blank';
	document.f_print.submit();
}


{/literal}
</script>


<h1>{$PAGE_TITLE}</h1>

<div style="display:none;">
<form name=f_print method="get" action="adjustment.php">
<input type=hidden name="a">
<input type=hidden name="id">
<input type=hidden name="branch_id">
</form></div>
<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>

{if $smarty.request.err_msg}
    <p><img src="ui/cancel.png" align="absmiddle"> {$smarty.request.err_msg}</p>
{/if}

{if $smarty.request.t eq 'save'}
    <p><img src=/ui/approved.png align=absmiddle> ID#{$smarty.request.save_id} was saved</p>
{elseif $smarty.request.t eq 'delete'}
	<p><img src="ui/terminated.png" align="absmiddle" /> ID#{$smarty.request.save_id} was deleted</p>
{elseif $smarty.request.t eq 'cancel'}
	<p><img src="ui/cancel.png" align="absmiddle" /> ID#{$smarty.request.save_id} was cancelled</p>
{elseif $smarty.request.t eq 'confirm'}
    <p><img src="ui/icons/accept.png" align="absmiddle" /> ID#{$smarty.request.save_id} confirmed. </p>
{elseif $smarty.request.t eq 'reset'}
    <p><img src="ui/notify_sku_reject.png" align="absmiddle"> ID#{$smarty.request.save_id} was reset.</p>
{elseif $smarty.request.t eq 'approve'}
	<p><img src="ui/approved.png" align="absmiddle"> ID#{$smarty.request.save_id} was Fully Approved.</p>
{/if}

<p><img src="ui/new.png" align="absmiddle" /> <a href="?a=open">Create New Write-Off</a></p>

<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:void(list_sel(2))" id=lst2 class="active a_tab">Saved</a>
<a href="javascript:void(list_sel(3))" id=lst3 class="a_tab">Waiting for Approval</a>
<a href="javascript:void(list_sel(4))" id=lst4 class="a_tab">Rejected</a>
<a href="javascript:void(list_sel(5))" id=lst5 class="a_tab">Cancelled/Terminated</a>
<a href="javascript:void(list_sel(6))" id=lst6 class="a_tab">Approved</a>
<a class="a_tab" id="lst1">Find <input id="inp_item_search" onKeyPress="search_input_keypress(event);" /> <input type="button" value="Go" onClick="list_sel(1);" /></a>
<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
</div>
<div id="container_list" style="border:1px solid #000">
</div>
</div>

{include file='footer.tpl'}
<script>

list_sel(2);
</script>
