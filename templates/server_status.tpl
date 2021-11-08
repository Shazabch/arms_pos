{*
3/14/2013 5:28 PM Justin
- Enhanced to show out server status while in multi server mode.
*}

{include file=header.tpl}
{if !$config.single_server_mode}
<h1>Server Status</h1>
<p>Server time is {$smarty.now|date_format:"%e/%m/%y %H:%M:%S"}</p>
<div class="stdframe">
<table  cellspacing=0 cellpadding=4>
<tr>
	<th colspan=2>Server</th>
	<th>IP</th>
	<th>Last Ping</th>
</tr>
{section name=i loop=$stats}
<tr>
<td style="border-bottom:1px solid #999">{$stats[i].code}</td>
<td style="border-bottom:1px solid #999">{$stats[i].description}</td>
<td style="border-bottom:1px solid #999" align=center>{if $stats[i].ip == '0.0.0.0'}&nbsp;{else}{$stats[i].ip}{/if}</td>
<td style="border-bottom:1px solid #999">{if $stats[i].ip == '0.0.0.0'}-{else}{if $stats[i].lastping == 0}&nbsp;{else}{$stats[i].lastping|date_format:"%e/%m/%y %H:%M:%S"}{/if}{/if}</td>
<td>
{if $stats[i].ip == '0.0.0.0'}
<font color=red>this server has never ping to host (check cronjob)</font>
{elseif $stats[i].lastping < $smarty.now-600}
<font color=blue>last ping more than 10 minutes (check connection)</font>
{/if}
</td>
</tr>
{/section}
</table>
</div>
{/if}

{if $sessioninfo.level>=1000}
<br>
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">SQL Replication Status</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		{php}
ini_set('display_errors',1);
include("replica_status.php");
{/php}
{/if}
	</div>
</div>

{include file=footer.tpl}
