{*
2017-03-09 17:33 Qiu Ying
- Enhanced to add "change back to active" for archive

2017-03-14 10:42 AM Qiu Ying
- Change from "change back to active" to "Restore to Active"
*}


<div style="display:none;">
<form id="filter-form" class="form" onsubmit="return false;">
    <input type="hidden" id="schedule_type" name="schedule_type" value="{$smarty.request.schedule_type|default:'active'}"/>
{*<b>Batch No.</b> <input type="text" name="batchno" value="{$smarty.request.batchno}"/>
<input type="button" value="Search" onclick="load_schedule();"/>*}
</form>
</div>
<table width=100% cellpadding=4 cellspacing=1 border=0 style="padding:2px">
    <tr bgcolor=#ffee99>
        <th></th>
        <th>User</th>
        <th>Time</th>
        <th>Batch No.</th>
        <th>Branch</th>
        <th>Date From</th>
        <th>Date To</th>
        <th>Format</th>
        <th>Group By</th>
        <th>Data Type</th>
        <th>Status</th>
        <th>File Size</th>
    </tr>
{if $list}

{foreach from=$list item=l}
<tr id="data-row-{$l.id}" {if $smarty.request.selected_row eq $l.id}class="selected highlight_row"{/if} bgcolor="{cycle values=",#eeeeee"}" >
    <td class="data-action">
        {if $l.status eq "Error"}
            <img src="/ui/messages.gif" alt="Error" title="Error"/>
        {/if}
        {if $l.started}
            {if $l.completed}
                <a href="javascript:void(0);" onclick="do_download({$l.id});"><img src="/ui/application_put.png" alt="Download" title="Download"/></a>
                {if $sessioninfo.id eq $l.user_id || $sessioninfo.level gte 9999}
					{if !$l.archived}
						<a href="javascript:void(0);" onclick="reset({$l.id});"><img src="/ui/refresh.png" alt="Regenerate" title="Regenerate"/></a>
						<a href="javascript:void(0);" onclick="archive({$l.id});"><img src="/ui/icons/compress.png" alt="Archive" title="Archive"/></a>
					{elseif $l.archived}
						<a href="javascript:void(0);" onclick="reactivate({$l.id});"><img src="/ui/icons/arrow_undo.png" alt="Restore to Active" title="Restore to Active"/></a>
                    {/if}
                {/if}
            {else}
                <img src=/ui/clock.gif align=absmiddle>
            {/if}
        {else}
            {if $sessioninfo.id eq $l.user_id || $sessioninfo.level gte 9999}
            <a href="javascript:void(0);" onclick="start_now({$l.id});"><img src="/ui/icons/control_play_blue.png" alt="Start" title="Start"/></a>
            {/if}
        {/if}
        {if !$l.archived && ($sessioninfo.id eq $l.user_id || $sessioninfo.level gte 9999)}
            <a href="javascript:void(0);" onclick="remove_schedule({$l.id});"><img src="/ui/icons/cancel.png" alt="Remove" title="Remove"/></a>
        {/if}
        {if $smarty.request.debug}
            <a href="{$smarty.server.PHP_SELF}?a=show_debug&id={$l.id}" target="_blank">Debug</a>
        {/if}
    </td>
    <td>{$l.u}</td>
    <td>{$l.added}</td>
    <td>{$l.batchno}</td>
    <td>{$l.branch_code}</td>
    <td>{$l.date_from}</td>
    <td>{$l.date_to}</td>
    <td>{$l.export_type}</td>
    <td>{$l.groupby}</td>
    <td>{$global_type[$l.data_type]}</td>
    <td class="data-status">
        {if $l.status eq "Error"}
            <font color="red">Error found. Please contact administrator.</font>
        {else}
            {$l.status}
            {if $l.completed}
            <br/><font color="blue" style="font-size: 10px;">( {$l.end_time} )</font>
            {/if}
        {/if}
    </td>
    <td class="data-file_size" style="text-align: right;">{$l.file_size|formatBytes}</td>
</tr>
{/foreach}

{else}
<tr>
<td colspan="12"><center>&nbsp;&nbsp;&nbsp;No Record found</center></td>
</tr>
{/if}
</table>
