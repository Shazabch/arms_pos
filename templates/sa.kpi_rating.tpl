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
var SALES_AGENT_KPI_RATING_MODULE = {
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
		if(SALES_AGENT_KPI_RATING_MODULE.validate() == false) return false;
		
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
	
	update_kpi: function(update_type){
		if(update_type == ""){
			alert("Invalid Update Type!");
			return false;
		}
		
		if(update_type == "confirm_sa_kpi" && !confirm("Are you sure want to confirm and close it?")) return false;
		
		document.f_b['a'].value = update_type;
		
		document.f_b.submit();
	}
}
</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>

<div>
	
<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_reload_sa_kpi_list" />
	<span>
		<b>Sales Agent:</b>
		<select name="sa_id">
			<option value="" {if !$smarty.request.sa_id}selected{/if}>-- Please Select --</option>
			{foreach from=$sa_list key=sa_id item=sa}
				<option value="{$sa_id}" {if $smarty.request.sa_id eq $sa_id}selected{/if}>{$sa.code} - {$sa.name}</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	
	<span>
		<b>Year:</b>
		<select name="year">
			{foreach from=$year_list key=dummy item=yr}
				<option value="{$yr}" {if $yr eq $smarty.request.year}selected{/if}>{$yr}</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	
	<input id="inp_reload_sa_kpi" type="button" value="Show" onClick="SALES_AGENT_KPI_RATING_MODULE.reload_sa_kpi_list();" />
</form>
<span id="span_loading_kpi_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	
</div>

<br />

<form name="f_b">
	<div id="div_sa_kpi_list">
		{include file="sa.kpi_rating.list.tpl"}
	</div>
</form>
<br>

<div style="display:none"><iframe name="_irs" width="500" height="400" frameborder="1"></iframe></div>

<script>
//init_chg(document.f_b);
SALES_AGENT_KPI_RATING_MODULE.initialize();
</script>

{include file=footer.tpl}
