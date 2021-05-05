{*
10/18/2011 11:24:40 AM Andy
- Change to show full category description.

10/20/2011 5:39:59 PM Andy
- Change row height to 21px.

11/8/2011 11:46:34 AM Andy
- Resize table column width.
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
	font-size:9px;
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
.div_day{
	width:12px;
	text-align:center;
}
{/literal}
</style>
{foreach from=$table key=page_n item=p}
{* include file='report_header.landscape.tpl' title='MONTHLY REPORT' *}

{if !$skip_header}
{include file='header.print.tpl'}

<body onload="window.print();">
{/if}

<div class=printarea>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td width="50" rowspan="2"><img src="{get_logo_url}" height="80" hspace="5" vspace="5" /></td>
		
		<td class="small" nowrap>
			<h1>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h1>
		</td>
	
		<td align="center" nowrap>
		    <h1>MONTHLY REPORT</h1>
		</td>
		
		<td width="33%" align="right" nowrap rowspan="2">
	  		<h4>{$subtitle_r}</h4>
	  		{if $page_n}<b>Page {$page_n}/{$page_total}{/if}</b>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%">
				<tr>
					<td width="50%" nowrap>
						{$branch.address|nl2br}<br>
						Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if} &nbsp;&nbsp; Fax: {$branch.phone_3}
					</td>
					<td colspan="2" align="center">
						<h4>{$subtitle_m}</h4>
					</td>		
				</tr>
			</table>
		</td>		
	</tr>
</table>

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
            <th rowspan="2">Desc</th>
            <th rowspan="2">Open<br>Qty</th>
            <th rowspan="2">Qty<br>In</th>

            <th colspan="31">Qty Sold</th>

            <th colspan="3">Normal</th>
            <th colspan="3">Promotion</th>
            <th colspan="2">Closing Stock</th>
            <th rowspan="2" width="3%">Rtrn</th>
            <th rowspan="2" width="5%">Remark</th>
        </tr>
        <tr style="font-size:9.5px;">
            {section loop=31 name=d}
                <th><div class="div_day" >{$smarty.section.d.iteration}</div></th>
            {/section}
            <th width="2%">Qty<br>Sold</th>
            <th>Price</th>
            <th width="5%">Amt</th>
            <th width="2%">Qty<br>Sold</th>
            <th>Price</th>
            <th width=5%>Amt</th>
            <th width="2%">Qty</th>
            <th width=5%>Amt</th>
        </tr>
        {assign var=n value=1}
        {foreach from=$p item=r}
            {assign var=sid value=$r.id}
        	<tr bgcolor="{cycle values='#cccccc,#ffffff'}" height="21">
                <td align=right>{$n}.</td>
				<td></td>
				{if $config.ci_use_split_artno}
	                <td nowrap><b>{$r.artno_code}</b></td>
	                <td nowrap><b>{$r.artno_size}</b></td>
				{else}
				    <td nowrap><b>{$r.artno}</b></td>
                {/if}
                <td nowrap>{$r.cat_desc}</td>
				<td class="r"><b>{$r.balance.open_bal}</b></td>
				<td class="r">&nbsp;</td>
				
				{section loop=31 name=d}
            	<td>&nbsp;&nbsp;</td>
    			{/section}
            	
				<td></td><td class="r"><b>{$r.price|number_format:2|ifzero}</b></td>
				</td><td><td></td><td></td><td></td>
            																	
				<td></td><td></td><td></td><td></td>
            </tr>
  	    {assign var=n value=$n+1}
        {/foreach}
        {if $n < $pagesize}
	        {section name=sc start=$n-1 loop=$pagesize}
	            <tr bgcolor="{cycle values='#cccccc,#ffffff'}" height="21">
	                <td align=right>{$n}.</td>
	                {repeat n=46}
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
