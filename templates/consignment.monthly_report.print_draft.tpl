{*
7/15/2011 11:35:42 AM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.
*}

{if !$skip_header}
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
	font-size:9.5px;
	border-left:none !important;
}
table.tbd td ,table.tbd th{
	padding:2px !important;
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
.page_container{
    float:left;
	margin:0 30 20px 30px;
}
{/literal}
</style>
{/if}

{*<div class="page">*}
	{if !$skip_header}
		{include file='header.print.tpl'}

		<body onload="window.print();">
	{/if}

<div class=printarea>
	<table width=100% cellspacing=0 cellpadding=0 border=0>
	<tr>
		<td width=60><img src="{get_logo_url}" height=80 hspace=5 vspace=5></td>
		<td class="small" nowrap>
			<h1>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h1>
			{$branch.address|nl2br}<br>
			Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if} &nbsp;&nbsp; Fax: {$branch.phone_3}
		</td>
		<td width=33% align=right nowrap>
			<h1>Monthly Report</h1>
	  		<h4>{$subtitle_r}</h4>
	  		{if $page_n}<b>Page {$page_n}/{$page_total}{/if}</b>
		</td>
	</tr>
	</table>

	{foreach from=$items key=p item=p_items}
		<div class="page_container">
		    Page {$p} of {$total_page}
		   <table class="tbd" cellpadding="0">
		        <tr>
		            <th width="20">{$page_info.$p.discount_code}</th>
		            {if $config.ci_use_split_artno}
			            <th width="30">Code</th>
			            <th width="30">Size</th>
		            {else}
		                <th width="60">Art No.</th>
		            {/if}
		            <th width="50">Open Qty</th>
					<th width="30">Price</th>
		        </tr>
		        {assign var=n value=1}
		        {foreach from=$p_items item=r}
		        	<tr bgcolor="{cycle values='#cccccc,#ffffff'}">
		                <td align=right>{$n}.</td>
      					{if $config.ci_use_split_artno}
			                <td nowrap><b>{$r.artno_code}</b></td>
			                <td nowrap><b>{$r.artno_size}</b></td>
						{else}
						    <td nowrap><b>{$r.art_no}</b></td>
		                {/if}
						<td class="r"><b>{$r.total_open_qty}</b></td>
						<td class="r"><b>{$r.price|number_format:2}</b></td>
		            </tr>
		  	    	{assign var=n value=$n+1}
		        {/foreach}
		        {section name=sc start=$n-1 loop=$pagesize}
		            <tr bgcolor="{cycle values='#cccccc,#ffffff'}">
		                <td align=right>{$n}.</td>
		                {repeat n=3}
		                	<td>&nbsp;&nbsp;</td>
		                {/repeat}
		                {if $config.ci_use_split_artno}
		                	<td>&nbsp;</td>
		                {/if}
		            </tr>
		            {assign var=n value=$n+1}
		        {/section}
		    </table>
	    </div>
    {/foreach}
</div>

