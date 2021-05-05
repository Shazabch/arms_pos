{include file='header.tpl'}
{literal}
<style>
</style>
{/literal}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var POS_AUDIT_LOG = {
    f_a: undefined,
    initialize: function() {
        this.f_a = document.f_a;
        
        Calendar.setup({
	        inputField     :    "date_from",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "t_added1",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
	    });
	
	    Calendar.setup({
	        inputField     :    "date_to",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "t_added2",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
	    });
	},
    show_report: function() {
		this.f_a['type'].value = 'show';
        this.f_a.submit();
    },
    download_report: function(branch_id, counter_id, date) {
		var date_from = new Date(this.f_a['date_from'].value);
		var date_to = new Date(this.f_a['date_to'].value);
		
		if(new Date(date_to - date_from)/(24*3600*1000) > 30) {
			this.f_a['date_from'].value = new Date(strtotime('-30 day', strtotime(this.f_a['date_to'].value)) * 1000).toISOString().slice(0,10);	
		}
        
        this.f_a['type'].value = 'download';
		
		this.f_a['branch_id'].value = '';
		this.f_a['counter_id'].value = '';
		this.f_a['date'].value = '';
		
		if(branch_id && counter_id) {
			this.f_a['branch_id'].value = branch_id;
			this.f_a['counter_id'].value = counter_id;
		}
		if(date)	this.f_a['date'].value = date;
		this.f_a.submit();
    },
	toggle_info: function(branch_id, counter_id, date) {
		if ($("info_"+branch_id+"_"+counter_id+"_"+date).style.display == 'none') {
			$("button_"+branch_id+"_"+counter_id+"_"+date).src = "/ui/collapse.gif";
			$("button_"+branch_id+"_"+counter_id+"_"+date).title = "Hide Detail";
			$("info_"+branch_id+"_"+counter_id+"_"+date).show();
		}else{
			$("button_"+branch_id+"_"+counter_id+"_"+date).src = "/ui/expand.gif";
			$("button_"+branch_id+"_"+counter_id+"_"+date).title = "Show Detail";
			$("info_"+branch_id+"_"+counter_id+"_"+date).hide();
		}
	},
	toggle_group_info: function(branch_id, counter_id, mode) {
		$$("table.report_table tr.group_info_"+branch_id+"_"+counter_id).each(function(element) {
			if(mode == 'expand') {
				element.show();
				$$("table.report_table img.group_button_"+branch_id+"_"+counter_id).each(function(button) {
					button.src = "/ui/collapse.gif";
					button.title = "Hide Detail";
				});
			}else{
				element.hide();
				$$("table.report_table img.group_button_"+branch_id+"_"+counter_id).each(function(button) {
					button.src = "/ui/expand.gif";
					button.title = "Show Detail";
				});
			}
		});
	}
}
{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>
<form name="f_a" class="stdframe" method="post">
    <input type="hidden" name="a" value="load_data">
	<input type="hidden" name="type" value="">
	<input type="hidden" name="form_submit" value="1">
	<input type="hidden" name="branch_id" value="">
	<input type="hidden" name="counter_id" value="">
	<input type="hidden" name="date" value="">
    <table>
        <tr>
            <th>Counter</th>
            <td>
                <select name="counters">
					{if $BRANCH_CODE == 'HQ'}
						<option value="">-- All --</option>
					{/if}
                    {foreach from=$counters item=r}
                        {capture assign=counter_all}{$r.branch_id}|all{/capture}
                        {capture assign=counter_item}{$r.branch_id}|{$r.id}{/capture}
                        {if $last_bid ne $r.branch_id}
                            <option value="{$counter_all}" {if $smarty.request.counters eq $counter_all}selected {/if}>{$r.code}</option>
                            {assign var=last_bid value=$r.branch_id}
                        {/if}
                        <option value="{$counter_item}" {if $smarty.request.counters eq $counter_item}selected {/if}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$r.network_name}</option>
                    {/foreach}
                </select>&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            <th>From</th>
            <td>
                <input size=10 type=text name=date_from value="{$form.date_from}" id="date_from" readonly>
                <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            <th>To</th>
            <td>
                <input size=10 type=text name=date_to value="{$form.date_to}" id="date_to" readonly>
                <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">  
            </td>
        </tr>
    </table>
    <br>
    <input type="button" onclick="POS_AUDIT_LOG.show_report();" value="Show Report"/>&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="button" onclick="POS_AUDIT_LOG.download_report();" value="Download All"/>
    <br><br>
    <ul>
        <li>Report maximum show 30 days of transaction only.</li>
    </ul>
</form>
<br>
{if $item_list}
	{foreach from=$item_list item=i name=p}
		<h3>Branch: {$i.branch_code}&nbsp;&nbsp;&nbsp;&nbsp; Counter: {$i.counter_name}</h3>
		<p>
			<a href="javascript:void(POS_AUDIT_LOG.toggle_group_info({$i.branch_id}, {$i.counter_id}, 'expand'))"><img src="/ui/expand.gif" title="Expand All"> Expand All</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
			<a href="javascript:void(POS_AUDIT_LOG.toggle_group_info({$i.branch_id}, {$i.counter_id}, 'collapse'))"><img src="/ui/collapse.gif" title="Collapse All" class="clickable"> Collapse All</a>
			<button style="float: right;width: 100px;margin-right: 5px" onClick="POS_AUDIT_LOG.download_report('{$i.branch_id}', '{$i.counter_id}')">Download All</button>
		</p>
		<table class="report_table" style="border-collapse: collapse" width="100%">
		{foreach from=$i.info key=k item=r name=c}
			<tr class="header">
				<td style="border-right: none;">
					{assign var=date value=$k|replace:"-":""}
					{assign var=d value=$k}
					<b>{$k}</b>
					&nbsp;<img {if $smarty.foreach.p.iteration ne 1 or $smarty.foreach.c.iteration ne 1}src="/ui/expand.gif"{else}src="/ui/collapse	.gif"{/if} class="group_button_{$i.branch_id}_{$i.counter_id}" id="button_{$i.branch_id}_{$i.counter_id}_{$date}" onClick="POS_AUDIT_LOG.toggle_info({$i.branch_id}, {$i.counter_id}, {$date})" title="Show Detail">
				</td>
				<td style="border-left: none;">
					<button style="float: right;width: 100px" onClick="POS_AUDIT_LOG.download_report('{$i.branch_id}', '{$i.counter_id}', '{$d}')">Download</button>
				</td>
			</tr>
			<tr class="info group_info_{$i.branch_id}_{$i.counter_id}" id="info_{$i.branch_id}_{$i.counter_id}_{$date}" style="{if $smarty.foreach.p.iteration ne 1 or $smarty.foreach.c.iteration ne 1}display: none;{/if}background-color: whitesmoke;">
				<td colspan="2"><pre>{$r|escape:'html'}</pre></td>
			</tr>
		{/foreach}
		</table>
		<br>
	{/foreach}
{else}
	{if $form.form_submit}
		<ul><li>No data</li></ul>
	{/if}
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
POS_AUDIT_LOG.initialize();
{/literal}
</script>