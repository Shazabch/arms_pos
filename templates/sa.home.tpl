{literal}
<style>
/* standard style for report table */
.rpt_table {
	border-top:1px solid #000;
	border-right:1px solid #000;
}

.rpt_table td, .rpt_table th{
	border-left:1px solid #000;
	border-bottom:1px solid #000;
	padding:4px;
}

.rpt_table tr.header td, .rpt_table tr.header th{
	background:#fe9;
	padding:6px 4px;
}

.clr_red {
	color:#FF0000;
}

.clr_blue {
	color:#306EFF;
}

.comm_amt {
	color: #306EFF;
	font-size: 48px;
	font-weight: bold;
}
</style>
{/literal}

<script>
var phpself = "{$smarty.server.PHP_SELF}";
{literal}

var SA_HOME = {
	f_vendor: undefined,
	f_a: undefined,
	p: 0,
	initialise: function(){
		// initialise functions
	},

	ajax_refresh_sa_details: function(){
		$('div_sa_comm_dtl').update(_loading_);

		var params = {
			'a': 'ajax_refresh_sa_details',
			year: $('year').value,
			month: $('month').value,
		}
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] && ret['html']){ // success
						// Update html
						$('div_sa_comm_dtl').update(ret['html']);
						return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				// prompt the error
				alert(err_msg);
			}
		});
	}
}
{/literal}
</script>

<h1>Welcome, {if $sa_session.code}({$sa_session.code}) {/if}{$sa_session.name}</h1>

<div>
<b>Year</b>
<select id="year">
	{foreach from=$yr_list key=yr item=yr_desc}
		<option value="{$yr}" {if $smarty.request.year eq $yr}selected{/if}>{$yr_desc}</option>	
	{/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;

<b>Month</b>
<select id="month">
	{foreach from=$mth_list key=mth item=mth_desc}
		<option value="{$mth}" {if $smarty.request.month eq $mth}selected{/if}>{$mth_desc}</option>	
	{/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;

<input type="button" name="refresh_btn" value="Refresh" onclick="SA_HOME.ajax_refresh_sa_details();" />
</div>

<br />

<div id="div_sa_comm_dtl">
	{include file="sa.home.commission_details.tpl"}
</div>

<script>
SA_HOME.initialise();
</script>