{include file='header.tpl'}

<script>

var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var WORK_ORDER = {
	current_t: 0,
	ajax_obj: undefined,
	initialize: function(){
		// default load transfer out
		this.reload_list(1);
	},
	// function to check key press and call to reload if found 'enter'
	check_and_search: function(e){
		if (e == undefined) e = window.event;
		if(e.keyCode==13){  // enter
			this.reload_list(0);
		}
	},
	// function when user change cn list page
	page_change: function(){
		// call to reload list
		this.reload_list();
	},
	// function to reload the list
	reload_list: function(t){
		if(t == undefined)	t = this.current_t;
		if(this.ajax_obj)	this.ajax_obj.abort();	// cancel the previous ajax

		// remove active tab
		$$('#div_tab a').each(function(ele){
			$(ele).removeClassName('selected');
		});
		// add class to active tab
		$('lst-'+t).addClassName('selected');

		var params = {
			a: 'ajax_reload_list',
			t: t
		}
		if(t==0)	params['search_str'] = $('inp_item_search').value;
		
		// page
		var sel_page = $('sel_page');
		if(sel_page){
			params['p'] = sel_page.value;
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
	}
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
<div id="show_last">
{if $smarty.request.t eq 'save'}
	<img src="/ui/approved.png" align="absmiddle"> Saved as ID#{$smarty.request.wo_id}<br>
{elseif $smarty.request.t eq 'cancel'}
	<img src="/ui/cancel.png" align="absmiddle"> ID#{$smarty.request.wo_id} was cancelled<br>
{elseif $smarty.request.t eq 'delete'}
	<img src="/ui/cancel.png" align="absmiddle"> ID#{$smarty.request.wo_id} was deleted<br>
{elseif $smarty.request.t eq 'confirm'}
	<img src="/ui/approved.png" align="absmiddle"> ID#{$smarty.request.wo_id} confirmed. 
{elseif $smarty.request.t eq 'approve'}
	<img src="/ui/approved.png" align="absmiddle"> ID#{$smarty.request.wo_id} was Fully Completed. 
{elseif $smarty.request.t eq 'reset'}
	<img src="/ui/notify_sku_reject.png" align="absmiddle"> ID#{$smarty.request.wo_id} was reset.
{/if}
</div>

<div class="card mx-3">
	<div class="card-body">
		<ul style="list-style-type: none;">
			{if $sessioninfo.privilege.ADJ_WORK_ORDER_OUT}
				<li> <img src="ui/new.png" align="absmiddle" /> <a class="text-dark bg-gray-100 p-2 rounded" href="?a=open">Create New Work Order (Transfer Out)</a></li>
			{/if}
		</ul>


<div class="alert alert-primary rounded">
	Hints:
<ul>
	<li> Once Work Order is created, it will automatically generate an Adjustment and link the Work Order with it.</li>
	<li> The Work Order will start with Transfer Out, once confirmed, it will send to waiting for "Transfer In", there is no approval flow for Work Order.</li>
	<li> When Work Order is reset, the linked adjustment will automatically reset as well.</li>
</li>
</ul>
</div>
</div>
</div
<br />

<div id="div_tab" class="tab" style="white-space:nowrap;">
<div class="row mx-3">
	<div class="col">
		<a href="javascript:void(WORK_ORDER.reload_list(1))" id="lst-1" class="fs-08 btn btn-outline-primary btn-rounded ">Transfer Out</a>
		<a href="javascript:void(WORK_ORDER.reload_list(2))" id="lst-2" class="fs-08 btn btn-outline-primary btn-rounded ">Transfer In</a>
		<a href="javascript:void(WORK_ORDER.reload_list(3))" id="lst-3" class="fs-08 btn btn-outline-primary btn-rounded ">Cancelled</a>
		<a href="javascript:void(WORK_ORDER.reload_list(4))" id="lst-4" class="fs-08 btn btn-outline-primary btn-rounded ">Completed</a>
	</div>
		<div class="col">
		<div class="form-inline">
			<a class="a_tab" id="lst-0">Find Work Order / Adjustment No 
				<input id="inp_item_search" class="form-control" onKeyPress="WORK_ORDER.check_and_search(event);" /> 
				<input type="button" class="btn btn-primary" value="Go" onClick="WORK_ORDER.reload_list(0);" />
			</a>
		</div>
	</div>
	
	
</div>
	<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
</div>

<div id="data_list" >
	
</div>

<script>
{literal}
WORK_ORDER.initialize();
{/literal}
</script>

{include file='footer.tpl'}