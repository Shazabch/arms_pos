{*
5/7/2010 2:05:29 PM Andy
- printing format change. (no more use share landscape report header, use own report header)

5/10/2010 6:13:44 PM Andy
- open qty center align
- image height increase (change to 70px)
- give some space at top left corner (move image right around 100px)

8/30/2010 6:05:54 PM Andy
- Add "Sold Agency" when print blank form.

11/11/2010 11:40:50 AM
- print monthly report add over and lost amount (btm right)

5/9/2011 5:38:25 PM Andy
- Change table font size from 10px to 12px.
- Change table font face from times to arial.

7/15/2011 2:20:48 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

4/25/2014 2:58 PM Justin
- Enhanced to add new signature "MARKDOWN APPROVED BY".
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
	font: 12px Arial, "Sans Serif";
	border-left:none !important;
}
table.tbd td ,table.tbd th{
	padding:2px !important;
}
table.tbd tr{
	border-left:1px solid black;
	line-height:14px;
}

table.no_border{
  	border:none;
	font-size:10px;
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
.col_a{
	width: 2%;
}
.col_day{
	width: 1.5%;
}
{/literal}
</style>
{if $smarty.request.month and $smarty.request.year}
{capture assign=subtitle_r}Date: {$days_of_month} {$month_label} {$smarty.request.year}<br />
Promoter's Name:__________________________________________ {/capture}
{/if}

{foreach from=$table key=page_n item=p}

{if !$skip_header}
{include file='header.print.tpl'}

<body onload="window.print();">
{/if}

<div class=printarea>
<table class="report" width=100% cellspacing=0 cellpadding=0 border=0>
<tr>
	<td width="100">&nbsp;</td>
	<td width=60><img src="{get_logo_url}" height="70" hspace=5 vspace=5></td>
	<td class="small" nowrap width="20%">
		{*<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
		<span class="small">{$branch.address|nl2br}<br>
		Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if} &nbsp;&nbsp; Fax: {$branch.phone_3}</span>*}
	</td>

	<td align=center nowrap>
	    <h1>{$title}</h1>
		<h4>{$subtitle_m}</h4>
	</td>
	{if $smarty.request.a eq 'print_empty'}
	    <td align="center">
	    	<h5><br />Sold Agency:___________________________</h5>
	    </td>
	{/if}
	<td width=33% align=right nowrap>
  		<h5>{$subtitle_r}</h5>
  		{if $page_n}<b>Page {$page_n}/{$page_total}{/if}</b>
	</td>
</tr>
</table>

   <table class="tbd" width="100%" cellpadding="0">
        <tr>
            <th rowspan="2" width="1%">{$page_data.$page_n}</th>
            <th rowspan="2" width="2%">Code No.</th>
            {if $config.ci_use_split_artno}
	            <th rowspan="2">Code</th>
	            <th rowspan="2">Size</th>
            {else}
                <th rowspan="2" width="2%" nowrap>Art No.</th>
            {/if}
            <th rowspan="2" class="col_a">Open<br>Qty</th>
            <th rowspan="2" class="col_a">Qty<br>In</th>
            <th rowspan="2" class="col_a">Total<br>Qty</th>
            <th colspan="31">Qty Sold</th>
            <th rowspan="2" width="3%">Total<br>Qty<br>Sold</th>
            <th colspan="4">Normal</th>
            <th colspan="4">Promotion</th>
            <th rowspan="2" class="col_a">Return<br>Qty</th>
            <th colspan="3">Closing Stock</th>
            <th rowspan="2" class="col_a">Remark</th>
        </tr>
        <tr>
            {section loop=31 name=d}
                <th width="1.8%">{$smarty.section.d.iteration}</th>
            {/section}
            <th class="col_a">Qty Sold</th>
            <th width="1%">Price</th>
            <th width="4%" colspan="2">Amt</th>
            <th class="col_a">Qty Sold</th>
            <th class="col_a">Price</th>
            <th width="4%" colspan="2">Amt</th>
            <th class="col_a">Qty</th>
            <th width="4%" colspan="2">Amt</th>
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
				<td class="c"><b>{$r.balance.open_bal}</b></td>
				<td class="r">&nbsp;&nbsp;</td>
				<td class="r">&nbsp;</td>
				{section loop=31 name=d}
            	<td class="col_day">&nbsp;&nbsp;</td>
    			{/section}
            	<td>&nbsp;&nbsp;</td><td></td><td class="r"><b>{$r.price|number_format:2|ifzero}</b></td>
            	<!-- amt --><td></td><td width="1%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		        <td></td>
				<td></td>
				<!-- amt --><td></td><td width="1%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            	<td></td><td></td>
				<!-- amt --><td></td><td width="1%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td></td>
            </tr>
  	    {assign var=n value=$n+1}           
        {/foreach}
        {if $n <32}
	        {section name=sc start=$n-1 loop=$pagesize}
	            <tr bgcolor="{cycle values='#cccccc,#ffffff'}">
	                <td align=right>{$n}.</td>
	                {repeat n=50}
	                <td>&nbsp;&nbsp;</td>
	                {/repeat}
	                {if $config.ci_use_split_artno}
	                <td>&nbsp;</td>
	                {/if}
	            </tr>
	            {assign var=n value=$n+1}
	        {/section}
        {/if}
        <tr>
            {assign var=cols value=37}
            {if $config.ci_use_split_artno}{assign var=cols value=$cols+1}{/if}
           <th colspan="{$cols}" class="r">Total</th>
           <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
           <td></td><td></td><td></td>
        </tr>
   </table>
	<table class="no_border" width="100%">
		<tr>
		 <td width="90%" class=r>Over Amount:</td>
		 <td width="10%">____________________</td>
		</tr>
		<tr>
		    <td class=r>Lost Amount:</td>
		    <td >____________________</td>
		</tr>
	</table>
    <table width="100%">
		<tr>
	        <td class="c" width="25%"><br><br>____________________________<br /><span style="font-size:9px;">PREPARE BY</span></td>
	        <td class="c" width="25%"><br><br>____________________________<br /><span style="font-size:9px;">VERIFIED BY DEPT. SUPERVISOR</span></td>
	        <td class="c" width="25%"><br><br>____________________________<br /><span style="font-size:9px;">MARKDOWN APPROVED BY</span></td>
	        <td valign="bottom" nowrap  width="25%">I hereby declare that the statement given above is true and accurate</td>
	    </tr>
    </table>
</div>
{assign var=skip_header value=1}
{/foreach}
