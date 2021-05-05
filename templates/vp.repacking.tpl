{include file="header.tpl"}

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var REPACKING_LIST = {
	tab_num: 1,
	page_num: 0,
	search_str: '',
	initialize: function(){
		this.list_sel(1);
	},
	// function when user click change tab
	list_sel: function(t){
		if(t){	// no "t"
			this.tab_num = t;
			this.page_num = 0;
		}
		
		var params = {
			a: 'ajax_list_sel',
			tab_num: this.tab_num,
			page_num: this.page_num
		};

		if(t === 999){ // 999 = search
			var tmp_search_str = $('inp_item_search').value.trim();
	
			if(tmp_search_str==''){
				//alert('Cannot search empty string');
				return;
			}else{
				this.search_str = tmp_search_str;
				params['search_str'] = this.search_str;
			} 	
		}
		
		
		var all_tab = $$('.tab .a_tab');
		for(var i=0;i<all_tab.length;i++){
			$(all_tab[i]).removeClassName('active');
		}
		$('lst-'+this.tab_num).addClassName('active');
		
		
		
		$('repacking_list').update(_loading_);
		
		// call to ajax
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    $('repacking_list').update('');
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						$('repacking_list').update(ret['html']);

		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
				
				// prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user type something in find input
	search_input_keypress: function(event){
		if (event == undefined) event = window.event;
		if(event.keyCode==13){  // enter
			this.list_sel(999);
		}
	},
	// function when user change page
	page_change: function(ele){
		this.page_num = ele.value;
		this.list_sel();
	}
};

{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{* error message *}
{if $smarty.request.err_msg}
    <p><img src="ui/cancel.png" align="absmiddle"> {$smarty.request.err_msg}</p>
{/if}


{* status notification *}
{if $smarty.request.t eq 'delete'}
	<p><img src="ui/terminated.png" align="absmiddle" /> ID#{$smarty.request.save_id} was deleted.</p>
{elseif $smarty.request.t eq 'saved'}
	<p><img src="ui/approved.png" align="absmiddle" /> ID#{$smarty.request.save_id} was saved.</p>
{elseif $smarty.request.t eq 'completed'}
	<p><img src="ui/approved.png" align="absmiddle" /> ID#{$smarty.request.save_id} was confirmed and completed.</p>
{/if}

{* Link *}
<ul>
	<li> <img src="ui/new.png" align="absmiddle" /> <a href="?a=open">Create New Repacking</a></li>
</ul>

{* Repacking list *}
<div class=tab style="height:25px;white-space:nowrap;">
	&nbsp;&nbsp;&nbsp;
	<a href="javascript:void(REPACKING_LIST.list_sel(1))" id="lst-1" class="active a_tab">Draft</a>
	<a href="javascript:void(REPACKING_LIST.list_sel(2))" id="lst-2" class="a_tab">Completed</a>
	<a class="a_tab" id="lst-999">Find <img src="ui/icons/information.png" align="absmiddle" class="clickable" onClick="alert('Can search by ID or Date.');" border="0" /> <input id="inp_item_search" onKeyPress="REPACKING_LIST.search_input_keypress(event);" /> <input type="button" value="Go" onClick="REPACKING_LIST.list_sel(999);" /></a>
	<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing…</span>
</div>
<div id="repacking_list" style="border:1px solid #000; padding: 2px;">
</div>

<script type="text/javascript">REPACKING_LIST.initialize();</script>

{include file="footer.tpl"}
