{include file="header.tpl"}

<h1>{$PAGE_TITLE}</h1>
<table class="report_table">
<tr class="header">
<th rowspan="2">Months Cycle</th>
<th rowspan="2">Year</th>
<th rowspan="2">Month</th>
<th rowspan="2">Total Point after<br> deduct to-date<br>redemption</th>
<th colspan="5">Expired</th>
<td style="background:#ccc" align="center" rowspan="{$totalrows+2}">Total Point Balance<br>after {$lastflushdate} flush</td>
</tr>
<tr class="header">
<th>Date</th>
<th>Points Due<br>to be flushed</th>
<th>Date flushed</th>
<th>Points flushed</th>
<th>Balance after flush</th>
</tr>
{assign var="mc" value=0}
{assign var="remaining" value=$totalpoints}
{foreach from=$data item=r}
<tr>
{assign var=c value=$mc++%12}
<td align="center">{$c+1}</td>
<td align="center">{$r.y}</td>
<td align="center">{$r.m}</td>
<td align="right">{$r.r|number_format}</td>
<td align="center">{$r.exp}</td>
<td align="right">{$r.r|number_format}</td>
{if $r.p!=0}
<td align="center">{$r.d}</td>
<td align="right">{$r.p|number_format}</td>
{else}
<td></td>
<td></td>
{/if}
{assign var="remaining" value=$remaining-$r.r}
{if $r.p!=0}{assign var="lastbalance" value=$remaining}{/if}
<td align="right">{$remaining|number_format}</td>
</tr>
{/foreach}
<tr>
	<td colspan=3></td>
	<td align="right">{$totalpoints|number_format}</td>
	<td colspan=2></td>
	<td align="right">{$totalflush|number_format}</td>
	<td colspan=2></td>
	<td align="right">{$lastbalance|number_format}</td>
</tr>
</table>

{include file="footer.tpl"}
