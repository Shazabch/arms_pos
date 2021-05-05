{*
06/24/2020 04:24 PM Sheila
- Updated button css
*}


{include file='header.tpl'}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var tab_num = 1;
var page_num = 0;
var order_no_autocomplete;

{literal}
var search_str = '';


function list_sel(selected){
	var dn_list = $('dn_list');
	if(!dn_list) return;

	if(selected==3){
		var tmp_search_str = $('inp_dn_search').value.trim();

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

	$('dn_list').update(_loading_);
	new Ajax.Updater('dn_list',phpself+'?a=ajax_list_sel&ajax=1&t='+tab_num+'&p='+page_num,{
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
		list_sel(3);
	}
}

function page_change(ele){
	page_num = ele.value;
	list_sel();
}


{/literal}
</script>


<h1>{$PAGE_TITLE}</h1>
<br /><br /><br />

<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:void(list_sel(1))" id="lst1" class="active a_tab">Active</a>
<a href="javascript:void(list_sel(2))" id="lst2" class="a_tab">Cancelled</a>
<a class="a_tab" id="lst3">Find Inv No / DN No / ID <input id="inp_dn_search" onKeyPress="search_input_keypress(event);" /> <input class="btn-primary" type="button" value="Go" onClick="list_sel(3);" /></a>
<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
</div>
<div id="dn_list" style="border:1px solid #000">
</div>
</div>

{include file='footer.tpl'}
<script>

list_sel();

</script>
