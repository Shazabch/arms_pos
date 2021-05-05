{*

*}

<style>
{literal}
.c {
	text-align:center;
}
.r{
	text-align:right;
}
div.page {
	clear:both;
	page-break-after: always;

}

table.tbd {
	font-size:11px;
	border-left:none !important;
}
table.tbd td ,table.tbd th{
	padding:2px 2px !important;
}
table.tbd tr{
	border-left:1px solid black;
}
.left_btm{
	border-left:0 !important;
	border-bottom:0 !important;
}
.right_btm{
	border-right:0 !important;
	border-bottom:0 !important;
}
tr.no_left{
	border-left:0 !important;
}
{/literal}
</style>
{foreach from=$table key=page_n item=p}
{include file='report_header.landscape.tpl' title='MONTHLY REPORT'}

   <table class="tbd" width="100%" cellpadding="0">
        <tr style="font-size:9.5px;">
            <th rowspan="2">{$page_data.$page_n}</th>
            <th rowspan="2" width=4%>Code<br>No.</th>
            {if $config.ci_use_split_artno}
	            <th rowspan="2">Code</th>
	            <th rowspan="2">Size</th>
            {else}
                <th rowspan="2">Art No.</th>
            {/if}
            <th rowspan="2">Open Qty</th>
            <th rowspan="2">Qty In</th>
            {*<th rowspan="2">Total Qty</th>*}
            <th colspan="31">Qty Sold</th>
			{*
            <th rowspan="2">Total Qty Sold</th>
            <th rowspan="2">Amount</th>
            *}
            <th colspan="3">Normal</th>
            <th colspan="3">Promotion</th>
            {*<th rowspan="2">Ret Qty</th>*}
            <th colspan="2">Closing Stock</th>
            <th rowspan="2">Remark / Rtn</th>
        </tr>
        <tr style="font-size:9.5px;">
            {section loop=31 name=d}
                <th width="2%">{$smarty.section.d.iteration}</th>
            {/section}
            <th>Qty Sold</th>
            <th>Price</th>
            <th width=5%>Amt</th>
            <th>Qty Sold</th>
            <th>Price</th>
            <th width=5%>Amt</th>
            <th>Qty</th>
            <th width=5%>Amt</th>
        </tr>
        {assign var=n value=1}
        {foreach from=$p item=r}
            {assign var=sid value=$r.id}
        	<tr bgcolor="{cycle values='#cccccc,#ffffff'}">
                <td align=right>{$n}.</td>
				<td></td>
				{if $config.ci_use_split_artno}
	                <td nowrap><b>{$r.artno_code}</b></td>
	                <td nowrap><b>{$r.artno_size}</b></td>
				{else}
				    <td nowrap><b>{$r.artno}</b></td>
                {/if}
				<td class="r"><b>{$r.balance.open_bal}</b></td>
				<td class="r">&nbsp;</td>
				{*<td class="r">&nbsp;</td>*}
				{section loop=31 name=d}
            	<td>&nbsp;&nbsp;</td>
    			{/section}
            	{*
				<td></td><td></td>
				*}
				<td></td><td class="r"><b>{$r.price|number_format:2|ifzero}</b></td>
				</td><td><td></td><td></td><td></td>
            	{*
				<td></td>
				*}																
				<td></td><td></td><td width=10%></td>
            </tr>
  	    {assign var=n value=$n+1}
        {/foreach}
        {if $n < $pagesize}
	        {section name=sc start=$n-1 loop=$pagesize}
	            <tr bgcolor="{cycle values='#cccccc,#ffffff'}">
	                <td align=right>{$n}.</td>
	                {repeat n=44}
	                <td>&nbsp;&nbsp;</td>
	                {/repeat}
	                {if $config.ci_use_split_artno}
	                <td>&nbsp;</td>
	                {/if}
	            </tr>
	            {assign var=n value=$n+1}
	        {/section}
        {/if}
{*
        <tr class="no_left">
            <td colspan="5" class="left_btm"></td>
            <td><b>Total</b></td>
            {section loop=31 name=d}
                <td></td>
            {/section}
            <td></td>
            <td colspan="11" class="right_btm"></td>
        </tr>
        <tr class="no_left" style="border-left:0 !important;">
            <td colspan="6" class="left_btm"></td>
            {section loop=31 name=d}
                <td>&nbsp;</td>
            {/section}
            <td></td>
            <td colspan="11" class="right_btm"></td>
        </tr>
*}
    </table>
    <br />
    <table width="100%">
    	<tr>
        <td>_________________________________<br />Approval by Dept.Store (Chop & Sign)</td>
        <td class="c" width="20%">RM____________________________<br />Total Sales Amount</td>
		<td class="c" width="20%">RM____________________________<br />Stock Amount </td>
        </tr>
    </table>
</div>
{assign var=skip_header value=1}
{/foreach}
