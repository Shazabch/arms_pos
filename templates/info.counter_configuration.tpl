{*
*}
{include file=header.tpl}
{literal}
<style>
a{
	cursor:pointer;
}
</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var COUNTER_CONFIGURATION_INFO_MODULE = {
	form_element: undefined,
	initialize : function(){
	},
	
	validate : function(){
		if (empty(document.f_a.branch_id, 'You must choose a Branch')) return false;
	},
	
	// function to reload sa list
	reload_counter_configuration_list: function(){
		if(COUNTER_CONFIGURATION_INFO_MODULE.validate() == false) return false;
	
		$('inp_reload_cc').disabled = true;
		$('span_loading_cc_list').show();
		
		var params = $(document.f_a).serialize();
	
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1){ // success
						$('div_cc_list').update(ret['html']);
					}else{
						if(ret['error']){
							alert(ret['error']);
						}else{
							alert(str);
						}
					}
				}catch(ex){
					alert(str);
				}
				
				$('inp_reload_cc').disabled = false;
				$('span_loading_cc_list').hide();
			}
		});
	},
	
	ajax_reload_counter_list : function(obj){
		if(obj == undefined) return;
		
		var bid = obj.value;
		
		$('counter_list').update("Loading, please wait...");

		var params = {
		    a: 'ajax_reload_counter_list',
			branch_id: bid
		};

		var THIS = this;
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1){ // success
						$('counter_list').update(ret['html']);
					}else{
						if(ret['error']){
							alert(ret['error']);
						}else{
							alert(str);
						}
					}
				}catch(ex){
					alert(str);
				}
			}
		});
	},
}
</script>
{/literal}

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div>
			<form name="f_a" onSubmit="return false;">
				<input type="hidden" name="a" value="ajax_reload_counter_configuration_list" />
				
				<div class="row">
					<div class="col">
						{if $BRANCH_CODE eq "HQ"}
					<span>
						<b class="form-label">Branch:</b>
						<select class="form-control" name="branch_id" onchange="COUNTER_CONFIGURATION_INFO_MODULE.ajax_reload_counter_list(this);">
							<option value="" selected>-- Please Select --</option>
							{foreach from=$branch_list key=bid item=r}
								<option value="{$bid}">{$r.code}</option>
							{/foreach}
						</select>
						
					</span>
				{else}
					<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
				{/if}
					</div>
				
				<div class="col">
					<span id="counter_list">
						{include file="info.counter_configuration.counters.tpl"}
					</span>
				</div>
				</div>
				
				<input id="inp_reload_cc" class="btn btn-primary" type="button" value="Search" onClick="COUNTER_CONFIGURATION_INFO_MODULE.reload_counter_configuration_list();" />
				
				<p>
					<div class="alert alert-primary rounded mt-2" style="max-width: 300px;">
						<b>Note: Requires Counter Version 203 and above in order to view the info.</b>
					</div>
				</p>
			</form>
			<span id="span_loading_cc_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
		</div>
	</div>
</div>

<div id="div_cc_list">
{include file="info.counter_configuration.list.tpl"}
</div>
<br>

</div>

<div style="display:none"><iframe name="_irs" width="500" height="400" frameborder="1"></iframe></div>

<script>
//init_chg(document.f_b);
COUNTER_CONFIGURATION_INFO_MODULE.initialize();
</script>

{include file=footer.tpl}
