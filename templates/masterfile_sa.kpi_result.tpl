{*
12/19/2019 1:39 PM Justin
- Bug fixed on the report always show "No Data" while first load up the page.
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
var SALES_AGENT_KPI_RESULT_MODULE = {
	curr_kpi_id: undefined,
	curr_user_type: undefined,
	form_element: undefined,
	initialize : function(){
	},
	validate : function(){
		if (empty(document.f_a.sa_id, 'You must choose a Sales Agent')) return false;
	},
	
	// function to reload sa list
	reload_sa_kpi_list: function(){
		// validation
		if(SALES_AGENT_KPI_RESULT_MODULE.validate() == false) return false;
		
		$('inp_reload_sa_kpi').disabled = true;
		$('span_loading_kpi_list').show();
		
		var params = $(document.f_a).serialize();

		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1){ // success
						$('div_sa_kpi_list').update(ret['html']);
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
				
				$('inp_reload_sa_kpi').disabled = false;
				$('span_loading_kpi_list').hide();
			}
		});
	},
	
	scores_validate: function(obj, scores){
		var err_msg = "";
		if(obj.value < 0){
			err_msg = "Negative scores are not allowed";
		}else if(obj.value > scores){
			err_msg = "Scores cannot be higher than ["+scores+"]";
		}
		
		if(err_msg != ""){
			alert(err_msg);
			obj.value = "";
			obj.focus();
			return false;
		}
		
		return true;
	},
	
	update_sa_kpi: function(sa_leader_id){
		if(sa_leader_id == undefined || !sa_leader_id || sa_leader_id == ""){
			alert("Invalid Sales Agent Leader ID!");
			return false;
		}
		if(!confirm("Are you sure want to save it?")) return false;
		
		// ensure the S/A leader ID is empty while do update
		document.f_b['selected_sa_leader_id'].value = sa_leader_id;
		document.f_b.submit();
	},
	
	reset_sa_kpi(sa_leader_id, sa_id, year, obj){
		if(sa_leader_id == undefined || sa_leader_id == "" || sa_leader_id == 0){
			alert("Invalid Sales Agent Leader ID!");
			return false;
		}
		
		if(!confirm("Are you sure want to reset it?")) return false;
		
		obj.disabled = true;
		
		document.f_reset['sa_leader_id'].value = sa_leader_id;
		document.f_reset['sa_id'].value = sa_id;
		document.f_reset['year'].value = year;
		$('span_loading_kpi_list').show();
		
		var params = $(document.f_reset).serialize();
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1){ // success
						THIS.reload_sa_kpi_list();
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
				
				$('span_loading_kpi_list').hide();
			}
		});
		
		document.f_reset.reset();
	}
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

<div>
	
<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" onSubmit="return false;">
			<input type="hidden" name="a" value="ajax_reload_sa_kpi_list" />
			<div class="row">
				<div class="col">
					<span>
						<b class="form-label">Sales Agent:</b>
						<select class="form-control" name="sa_id">
							<option value="" {if !$smarty.request.sa_id}selected{/if}>-- Please Select --</option>
							{foreach from=$sa_list key=sa_id item=sa}
								<option value="{$sa_id}" {if $smarty.request.sa_id eq $sa_id}selected{/if}>{$sa.code} - {$sa.name}</option>
							{/foreach}
						</select>
					</span>
				</div>
				<div class="col">
					
				<span>
					<b class="form-label">Year:</b>
					<select class="form-control" name="year">
						{foreach from=$year_list key=dummy item=yr}
							<option value="{$yr}" {if $yr eq $smarty.request.year}selected{/if}>{$yr}</option>
						{/foreach}
					</select>
				</span>
				</div>
				
				<div class="col">
					<input id="inp_reload_sa_kpi" class="btn btn-primary mt-4" type="button" value="Show" onClick="SALES_AGENT_KPI_RESULT_MODULE.reload_sa_kpi_list();" />
				</div>
			</div>
		</form>
	</div>
</div>
<span id="span_loading_kpi_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	
</div>

<br />

<form name="f_reset">
	<input type="hidden" name="a" value="reset_sa_kpi" />
	<input type="hidden" name="sa_leader_id" value="" />
	<input type="hidden" name="sa_id" value="" />
	<input type="hidden" name="year" value="" />
</form>

<form name="f_b">
	<div id="div_sa_kpi_list"></div>
</form>
<br>

<div style="display:none"><iframe name="_irs" width="500" height="400" frameborder="1"></iframe></div>

<script>
//init_chg(document.f_b);
SALES_AGENT_KPI_RESULT_MODULE.initialize();
</script>

{include file=footer.tpl}
