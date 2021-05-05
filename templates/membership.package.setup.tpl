{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var MEMBERSHIP_PACKAGE_SETUP = {
	tab_num: 1,
	page_num: 0,
	initialize: function(){
		// Auto select first available tab
		this.select_first_tab();
	},
	// Core function to auto select first tab
	select_first_tab: function(){
		// Auto select first tab
		for(var i=1;i<=9;i++){
			if($('lst'+i)){
				this.tab_num = i;
				break;
			}
		}
		// load the list
		this.list_sel();
	},
	list_sel: function(t){
		if(t == undefined){
			// maintain same tab
			if($('sel_page')){
				this.page_num = $('sel_page').value;
			}
		}else{
			// changed tab
			this.tab_num = t;
			this.page_num = 0;
		}
		
		var package_list = $('package_list');
		if(!package_list) return;
		var search_str = '';
		if(this.tab_num == 0){
			var tmp_search_str = $('inp_item_search').value.trim();

			if(tmp_search_str==''){
				alert('Cannot search empty string');
				return;
			}else 	search_str = tmp_search_str;
		}

		var all_tab = $$('#div_tab .a_tab');
		for(var i=0;i<all_tab.length;i++){
			$(all_tab[i]).removeClassName('active');
		}
		$('lst'+this.tab_num).addClassName('active');

		$('package_list').update(_loading_);
		new Ajax.Updater('package_list', phpself+'?a=ajax_list_sel&t='+this.tab_num+'&p='+this.page_num,{
			parameters:{
				search_str: search_str
			},
			onComplete: function(msg){

			},
			evalScripts: true
		});
	},
	toggle_search_info: function(){
		alert("Search by ID / Document No / Title / Linked SKU");
	},
	search_input_keypress: function (event){
		if (event == undefined) event = window.event;
		if(event.keyCode==13){  // enter
			this.list_sel(0);	// Search
		}
	},
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $smarty.request.t eq 'saved'}
	<p><img src="ui/approved.png" align="absmiddle"> Package {$smarty.request.doc_no} Saved</p>
{elseif $smarty.request.t eq 'confirmed'}
    <p><img src="ui/icons/accept.png" align="absmiddle" /> Package {$smarty.request.doc_no} confirmed. </p>
{elseif $smarty.request.t eq 'cancelled'}
	<p><img src="ui/cancel.png" align="absmiddle" /> Package {$smarty.request.doc_no} was cancelled</p>
{/if}

<ul>
	<li><img src="ui/new.png" align="absmiddle" /> <a href="?a=open">Create New Package</a></li>
</ul>

<div id="div_tab" class="tab" style="height:25px;white-space:nowrap;">
	&nbsp;&nbsp;&nbsp;
	
	<a href="javascript:void(MEMBERSHIP_PACKAGE_SETUP.list_sel(1))" id="lst1" class="active a_tab">Saved</a>
	<a href="javascript:void(MEMBERSHIP_PACKAGE_SETUP.list_sel(2))" id="lst2" class="a_tab">Confirmed</a>
	<a href="javascript:void(MEMBERSHIP_PACKAGE_SETUP.list_sel(3))" id="lst3" class="a_tab">Cancelled</a>
	
	<a class="a_tab" id="lst0">
		Search [<span class="link" onclick="MEMBERSHIP_PACKAGE_SETUP.toggle_search_info();">?</span>] 
		<input id="inp_item_search" onKeyPress="MEMBERSHIP_PACKAGE_SETUP.search_input_keypress(event);" /> 
		<input type="button" value="Go" onClick="MEMBERSHIP_PACKAGE_SETUP.list_sel(0);" />
	</a>
	<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
</div>

<div id="package_list" style="border:1px solid #000;">
</div>

<script>MEMBERSHIP_PACKAGE_SETUP.initialize();</script>
{include file='footer.tpl'}