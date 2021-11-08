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

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if $smarty.request.t eq 'saved'}
	<p><img src="ui/approved.png" align="absmiddle"> Package {$smarty.request.doc_no} Saved</p>
{elseif $smarty.request.t eq 'confirmed'}
    <p><img src="ui/icons/accept.png" align="absmiddle" /> Package {$smarty.request.doc_no} confirmed. </p>
{elseif $smarty.request.t eq 'cancelled'}
	<p><img src="ui/cancel.png" align="absmiddle" /> Package {$smarty.request.doc_no} was cancelled</p>
{/if}


<div class="card mx-3">
	<div class="card-body">
		<ul style="list-style-type: none;">
			<li><img src="ui/new.png" align="absmiddle" /> <a href="?a=open">Create New Package</a></li>
		</ul>
	
	</div>
</div>

<div class="row mx-3 mb-3">
	<div id="div_tab" class="tab" style="white-space:nowrap;">
		<a href="javascript:void(MEMBERSHIP_PACKAGE_SETUP.list_sel(1))" id="lst1" class="a_tab btn btn-outline-primary btn-rounded">Saved</a>
		<a href="javascript:void(MEMBERSHIP_PACKAGE_SETUP.list_sel(2))" id="lst2" class="a_tab btn btn-outline-primary btn-rounded">Confirmed</a>
		<a href="javascript:void(MEMBERSHIP_PACKAGE_SETUP.list_sel(3))" id="lst3" class="a_tab btn btn-outline-primary btn-rounded">Cancelled</a>
		
		<a class="a_tab" id="lst0">
		<div class="form-inline mt-2">
			Search [<span class="link" onclick="MEMBERSHIP_PACKAGE_SETUP.toggle_search_info();">?</span>] 
			&nbsp;<input class="form-control" id="inp_item_search" onKeyPress="MEMBERSHIP_PACKAGE_SETUP.search_input_keypress(event);" /> 
			&nbsp;<input class="btn btn-primary" type="button" value="Go" onClick="MEMBERSHIP_PACKAGE_SETUP.list_sel(0);" />
		</div>
		</a>
		<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div id="package_list" >
		</div>
	</div>
</div>

<script>MEMBERSHIP_PACKAGE_SETUP.initialize();</script>
{include file='footer.tpl'}