{include file='header.tpl'}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>
var analysis_selected_date = '{$smarty.request.cutoff_date}';
var can_archive = '{$can_archive}';

{literal}
function submit_form(action){
	document.f_a['show_type'].value = action;
	
	if(!document.f_a['cutoff_date'].value){
		alert("Please select date.");
		return false;
	}
	if(action=='start_archive'&&!analysis_selected_date){
		alert("Selected date cannot be archive.");
		return false;
	}
	if(action=='start_archive'){
	    var remark = prompt("Please enter remark.");
	    if(!remark || remark.trim()=='')    return false;
	    else    document.f_a['remark'].value = escape(remark);
		if(!confirm('Are you sure?'))   return false;
	}
	
	document.f_a.submit();
}

function date_change(){
	if(can_archive){
        if(analysis_selected_date==document.f_a['cutoff_date'].value){
            $('span_start_archive').show();
		}else{
			$('span_start_archive').hide();
		}
	}
}

function show_archive_history(){
	curtain(true);
	center_div($('div_archive_history').show());
}
{/literal}
</script>


<div id="div_archive_history" class="curtain_popup" style="position:absolute;z-index:10000;width:900px;height:600px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding: 0 !important;">
	<div id="div_archive_history_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;"><img src="ui/icons/database.png" align="absmiddle" /> Database Archive History</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_archive_history_content" style="padding:2px;overflow-x:hidden;overflow-y:auto;height:90%;">
	    <table width="100%" class="report_table">
	        <tr class="header">
	            <th width="130">Timestamp</th>
	            <th width="60">User</th>
	            <th width="70">Database Date</th>
	            <th>Remark</th>
	            <th>Error Log</th>
	            <th>Data<br />Archived</th>
	        </tr>
	        {foreach from=$archive_history item=r}
	            <tr align="center" bgcolor="#ffffff">
	                <td>{$r.added}</td>
	                <td>{$r.u}</td>
	                <td>{$r.date}</td>
	                <td align="left">{$r.remark}</td>
	                <td align="left">{$r.error_log|nl2br|default:'-'}</td>
	                <td class="r">{$r.deleted_rows|number_format}</td>
	            </tr>
			{foreachelse}
				<tr bgcolor="#ffffff">
				    <td colspan="5">No Data</td>
				</tr>
	        {/foreach}
	    </table>
	</div>
</div>


<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if $smarty.request.msg}
	<p style="color:blue;">{$smarty.request.msg}</p>
{/if}

<form method="post" name="f_a" id="f_a" class="form" onsubmit="return false;">
	<input type="hidden" name="load_data" value="1" />
 	<input type="hidden" name="show_type" value="analysis" />
	<input type="hidden" name="remark" />
	<div style="float:right;"><a href="javascript:void(show_archive_history());"><img src="/ui/icons/database_key.png" border="0" align="absmiddle" /> View Archive History</a></div>
	 <ul>
	 	<li>Archive unused data to other database can keep system run faster.</li>
	 	<li>You will not able to access the data after it moved.</li>
	 	<li>You can only cutoff at least one year old data.</li>
	 	<li>All items will use the stock balance at cutoff date as stock check.</li>
	 	<li><b style="color:red">WARNING! Once database was archived it cannot be revert.</b></li>
	 </ul>
 	<b>Please enter the cutoff date:</b>
 	<input size="10" type="text" name="cutoff_date" value="{$smarty.request.cutoff_date|default:$min_cutoff_date|date_format:'%Y-%m-%d'|ifzero:''}" id="inp_cutoff_date" readonly onChange="date_change();" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_cutoff_date" style="cursor: pointer;" title="Select Date" />
	<p>
	    <button onClick="submit_form('analysis')"><img src="/ui/icons/database_table.png" border="0" align="absmiddle"  /> Analyze Data</button>
	    <span id="span_start_archive" style="display:none;">
	        <button onClick="submit_form('start_archive')"><img src="/ui/icons/database_go.png" border="0" align="absmiddle"  /> Start Archive</button>
	    </span>
	</p>
	Note: The process may take longer time, please be patient when it is loading.
</form>

{if !$total.total.total}
	{if $smarty.request.load_data}No Data{/if}
{else}
	<h1>
		Cutoff Date: {$smarty.request.cutoff_date} &nbsp;&nbsp;&nbsp;&nbsp;
		Estimate Data: {$total.total.total|number_format}
	</h1>

	<!-- PO -->
	{if $data.po}
	    <h1>Purchase Order ({$total.po.total|number_format} records)</h1>
	    <table width="100%" class="report_table">
	        <tr class="header">
	            <th width="60%">Branch</th>
	            <th>Saved</th>
	            <th>Waiting Approval</th>
	            <th>In-Acitve</th>
	            <th>Approved</th>
	        </tr>
			{foreach from=$data.po key=bid item=r }
				<tr>
				    <td>{$branches.$bid.code} - {$branches.$bid.description}</td>
				    <td class="r">{$r.saved|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.waiting_approval|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.inactive|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.approved|number_format|ifzero:'&nbsp;'}</td>
				</tr>
		    {/foreach}
	    </table>
	{/if}
	
	<!-- DO -->
	{if $data.do}
	    <h1>Delivery Order ({$total.do.total|number_format} records)</h1>
	    <table width="100%" class="report_table">
	        <tr class="header">
	            <th width="60%">Branch</th>
	            <th>Saved</th>
	            <th>Waiting Approval</th>
	            <th>In-Acitve</th>
	            <th>Approved</th>
	            <th>Checkout</th>
	        </tr>
			{foreach from=$data.do key=bid item=r }
				<tr>
				    <td>{$branches.$bid.code} - {$branches.$bid.description}</td>
				    <td class="r">{$r.saved|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.waiting_approval|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.inactive|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.approved|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.checkout|number_format|ifzero:'&nbsp;'}</td>
				</tr>
		    {/foreach}
	    </table>
	{/if}
	
	<!-- ADJ -->
	{if $data.adj}
	    <h1>Purchase Order ({$total.adj.total|number_format} records)</h1>
	    <table width="100%" class="report_table">
	        <tr class="header">
	            <th width="60%">Branch</th>
	            <th>Saved</th>
	            <th>Waiting Approval</th>
	            <th>In-Acitve</th>
	            <th>Approved</th>
	        </tr>
			{foreach from=$data.adj key=bid item=r }
				<tr>
				    <td>{$branches.$bid.code} - {$branches.$bid.description}</td>
				    <td class="r">{$r.saved|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.waiting_approval|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.inactive|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.approved|number_format|ifzero:'&nbsp;'}</td>
				</tr>
		    {/foreach}
	    </table>
	{/if}
	
	<!-- GRR -->
	{if $data.grr}
	    <h1>GRR ({$total.grr.total|number_format} records)</h1>
	    <table width="100%" class="report_table">
	        <tr class="header">
	            <th width="60%">Branch</th>
	            <th>Saved</th>
	            <th>In-Acitve</th>
	            <th>Used</th>
	        </tr>
			{foreach from=$data.grr key=bid item=r}
				<tr>
				    <td>{$branches.$bid.code} - {$branches.$bid.description}</td>
				    <td class="r">{$r.saved|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.inactive|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.used|number_format|ifzero:'&nbsp;'}</td>
				</tr>
		    {/foreach}
	    </table>
	{/if}
	
	<!-- GRA -->
	{if $data.gra}
	    <h1>GRA ({$total.gra.total|number_format} records)</h1>
	    <table width="100%" class="report_table">
	        <tr class="header">
	            <th width="60%">Branch</th>
	            <th>Saved</th>
	            <th>Cancelled</th>
	            <th>Returned</th>
	        </tr>
			{foreach from=$data.gra key=bid item=r}
				<tr>
				    <td>{$branches.$bid.code} - {$branches.$bid.description}</td>
				    <td class="r">{$r.saved|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.cancel|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.returned|number_format|ifzero:'&nbsp;'}</td>
				</tr>
		    {/foreach}
	    </table>
	{/if}
	
	<!-- POS -->
	{if $data.pos}
	    <h1>POS ({$total.pos.total|number_format} records)</h1>
	    <table width="100%" class="report_table">
	        <tr class="header">
	            <th width="60%">Branch</th>
	            <th>Active Sales</th>
	            <th>Cancelled Sales</th>
	        </tr>
			{foreach from=$data.pos key=bid item=r}
				<tr>
				    <td>{$branches.$bid.code} - {$branches.$bid.description}</td>
				    <td class="r">{$r.active|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.cancel|number_format|ifzero:'&nbsp;'}</td>
				</tr>
		    {/foreach}
	    </table>
	{/if}
	
	<!-- CI -->
	{if $data.ci}
	    <h1>Consignment Invoice ({$total.ci.total|number_format} records)</h1>
	    <table width="100%" class="report_table">
	        <tr class="header">
	            <th width="60%">Branch</th>
	            <th>Saved</th>
	            <th>Waiting Approval</th>
	            <th>In-Acitve</th>
	            <th>Approved</th>
	        </tr>
			{foreach from=$data.ci key=bid item=r }
				<tr>
				    <td>{$branches.$bid.code} - {$branches.$bid.description}</td>
				    <td class="r">{$r.saved|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.waiting_approval|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.inactive|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.approved|number_format|ifzero:'&nbsp;'}</td>
				</tr>
		    {/foreach}
	    </table>
	{/if}
	
	<!-- CN -->
	{if $data.cn}
	    <h1>Consignment Credit Note ({$total.cn.total|number_format} records)</h1>
	    <table width="100%" class="report_table">
	        <tr class="header">
	            <th width="60%">Branch</th>
	            <th>Saved</th>
	            <th>Waiting Approval</th>
	            <th>In-Acitve</th>
	            <th>Approved</th>
	        </tr>
			{foreach from=$data.cn key=bid item=r }
				<tr>
				    <td>{$branches.$bid.code} - {$branches.$bid.description}</td>
				    <td class="r">{$r.saved|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.waiting_approval|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.inactive|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.approved|number_format|ifzero:'&nbsp;'}</td>
				</tr>
		    {/foreach}
	    </table>
	{/if}
	
	<!-- DN -->
	{if $data.dn}
	    <h1>Consignment Credit Note ({$total.dn.total|number_format} records)</h1>
	    <table width="100%" class="report_table">
	        <tr class="header">
	            <th width="60%">Branch</th>
	            <th>Saved</th>
	            <th>Waiting Approval</th>
	            <th>In-Acitve</th>
	            <th>Approved</th>
	        </tr>
			{foreach from=$data.dn key=bid item=r }
				<tr>
				    <td>{$branches.$bid.code} - {$branches.$bid.description}</td>
				    <td class="r">{$r.saved|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.waiting_approval|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.inactive|number_format|ifzero:'&nbsp;'}</td>
				    <td class="r">{$r.approved|number_format|ifzero:'&nbsp;'}</td>
				</tr>
		    {/foreach}
	    </table>
	{/if}
	
	<h1>Others Data</h1>
	<ul>
		{foreach from=$other_history_data key=k item=kname}
		    {if $data.$k}<li>{$kname} ({$data.$k|number_format})</li>{/if}
		{/foreach}
	</ul>
{/if}
<script type="text/javascript">
{literal}
    Calendar.setup({
        inputField     :    "inp_cutoff_date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "img_cutoff_date",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
    new Draggable('div_archive_history',{ handle: 'div_archive_history_header'});
{/literal}

{if $can_archive}
    $('span_start_archive').show();
{/if}
</script>

{include file='footer.tpl'}
