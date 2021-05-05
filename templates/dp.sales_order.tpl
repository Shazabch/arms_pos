{*
5/30/2013 6:05 PM Justin
- Enhanced to have delivered tab.
*}
{include file="header.tpl"}

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';
var tab_num = 1;
var page_num = 0;
var order_no_autocomplete;

{literal}
var search_str = '';

function list_sel(selected){
	var order_list = $('order_list');
	if(!order_list) return;

	if(selected==99){
		var tmp_search_str = $('inp_item_search').value.trim();

		if(tmp_search_str==''){
			//alert('Cannot search empty string');
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

	$('order_list').update(_loading_);
	new Ajax.Updater('order_list',phpself+'?a=ajax_list_sel&ajax=1&t='+tab_num+'&p='+page_num,{
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
		list_sel(99);
	}
}

function page_change(ele){
	page_num = ele.value;
	list_sel();
}

{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if isset($smarty.request.t)}
	{if $smarty.request.t eq 'save'}
		<img src="/ui/approved.png" align="absmiddle"> Sales Order saved as ID#{$smarty.request.id}<br>
	{elseif $smarty.request.t eq 'confirm'}
		<img src="/ui/approved.png" align="absmiddle"> Sales Order ID#{$smarty.request.id} confirmed.
	{elseif $smarty.request.t eq 'reset'}
		<img src="/ui/notify_sku_reject.png" align="absmiddle"> Sales Order ID#{$smarty.request.id} was reset.
	{elseif $smarty.request.t eq 'delete'}
		<img src="/ui/cancel.png" align="absmiddle"> Sales Order ID#{$smarty.request.id} was deleted<br>
	{/if}
{/if}

<ul>
	<li> <img src="ui/new.png" align="absmiddle" /> <a href="?a=open">Create New Order</a></li>
</ul>

<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:void(list_sel(1))" id="lst1" class="active a_tab">Saved Order</a>
<a href="javascript:void(list_sel(2))" id="lst2" class="a_tab">Waiting for Approval</a>
<a href="javascript:void(list_sel(5))" id="lst5" class="a_tab">Rejected</a>
<a href="javascript:void(list_sel(3))" id="lst3" class="a_tab">Cancelled/Terminated</a>
<a href="javascript:void(list_sel(4))" id="lst4" class="a_tab">Approved</a>
<a href="javascript:void(list_sel(6))" id="lst6" class="a_tab">Delivered</a>


<a class="a_tab" id="lst99">Find Batch / Order / PO No <input id="inp_item_search" onKeyPress="search_input_keypress(event);" /> <input type="button" value="Go" onClick="list_sel(99);" /></a>
<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing…</span>
</div>
<div id="order_list" style="border:1px solid #000">
</div>
</div>

<script type="text/javascript">
list_sel();
</script>
{include file="footer.tpl"}
