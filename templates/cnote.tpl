{*
11/3/2015 1:59 PM DingRen
- add approval link

11/11/2015 6:03 PM DingRen
- remove approval link

06/24/2020 04:46 PM Sheila
- Updated button css
*}
{include file='header.tpl'}

<script>

var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var CNOTE = {
	current_t: 0,
	ajax_obj: undefined,
	initialize: function(){
		// default load saved
		this.reload_list(1);
	},
	// function to check key press and call to reload if found 'enter'
	check_and_search: function(e){
		if (e == undefined) e = window.event;
		if(e.keyCode==13){  // enter
			this.reload_list(0);
		}
	},
	// function to reload the list
	reload_list: function(t){
		if(t == undefined)	t = this.current_t;
		if(this.ajax_obj)	this.ajax_obj.abort();	// cancel the previous ajax

		// remove active tab
		$$('#div_tab a.a_tab').each(function(ele){
			$(ele).removeClassName('active');
		});
		// add class to active tab
		$('lst-'+t).addClassName('active');

		var params = {
			a: 'ajax_reload_list',
			t: t
		}
		if(t==0)	params['search_str'] = $('inp_item_search').value;
		
		// page
		var sel_cn_page = $('sel_cn_page');
		if(sel_cn_page){
			params['p'] = sel_cn_page.value;
		}
		this.current_t = t;

		// show loading
		$('data_list').update(_loading_);
		
		this.ajax_obj = new Ajax.Request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('data_list').update(ret['html']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    //alert(err_msg);
			    $('data_list').update(err_msg);
			}
		});
	},
	// function when user change cn list page
	page_change: function(){
		// call to reload list
		this.reload_list();
	}
}

{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<div id="show_last">
{if $smarty.request.t eq 'save'}
	<img src="/ui/approved.png" align="absmiddle"> Saved as ID#{$smarty.request.cn_id}<br>
{elseif $smarty.request.t eq 'cancel'}
	<img src="/ui/cancel.png" align="absmiddle"> ID#{$smarty.request.cn_id} was cancelled<br>
{elseif $smarty.request.t eq 'delete'}
	<img src="/ui/cancel.png" align="absmiddle"> ID#{$smarty.request.cn_id} was deleted<br>
{elseif $smarty.request.t eq 'confirm'}
	<img src="/ui/approved.png" align="absmiddle"> ID#{$smarty.request.cn_id} confirmed. 
{elseif $smarty.request.t eq 'approve'}
	<img src="/ui/approved.png" align="absmiddle"> ID#{$smarty.request.cn_id} was Fully Approved. 
{elseif $smarty.request.t eq 'reset'}
	<img src="/ui/notify_sku_reject.png" align="absmiddle"> ID#{$smarty.request.cn_id} was reset.
{/if}
</div>

<ul>
	<li> <img src="ui/new.png" align="absmiddle" /> <a href="?a=open">Create New CN</a></li>
</ul>

Hints:
<ul>
	<li> Once CN is approved it will automatically generate Adjustment.</li>
	<li> When CN is reset, all generated adjustments will automatically become cancelled.</li>
</li>

<div id="div_tab" class="tab" style="height:25px;white-space:nowrap;">
	&nbsp;&nbsp;&nbsp;
	<a href="javascript:void(CNOTE.reload_list(1))" id="lst-1" class="active a_tab">Saved</a>
	<a href="javascript:void(CNOTE.reload_list(2))" id="lst-2" class="a_tab">Waiting for Approval</a>
	<a href="javascript:void(CNOTE.reload_list(3))" id="lst-3" class="a_tab">Rejected</a>
	<a href="javascript:void(CNOTE.reload_list(4))" id="lst-4" class="a_tab">Cancelled/Terminated</a>
	<a href="javascript:void(CNOTE.reload_list(5))" id="lst-5" class="a_tab">Approved</a>
	<a class="a_tab" id="lst-0">Find CN No 
		<input id="inp_item_search" onKeyPress="CNOTE.check_and_search(event);" /> 
		<input class="btn-primary" type="button" value="Go" onClick="CNOTE.reload_list(0);" />
	</a>
	<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
</div>

<div id="data_list" style="border:1px solid #000">
	
</div>

<script>
{literal}
CNOTE.initialize();
{/literal}
</script>
{include file='footer.tpl'}
