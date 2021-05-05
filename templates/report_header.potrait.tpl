{*
7/15/2011 3:01:46 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.
*}
{if !$skip_header}
{include file='header.print.tpl'}

<body onload="window.print()">
{/if}

<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0>
<tr>
	<td><img src="{get_logo_url}" height=50 hspace=5 vspace=5></td>
	<td class="small" nowrap>
		<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
		{$branch.address}<br>
		Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if} &nbsp;&nbsp; Fax: {$branch.phone_3}
	</td>

	<td width=33% align=right nowrap>
  		<h4>{$subtitle_r}</h4>
  		{if $page_total>0}<b>Page {$page_n}/{$page_total}</b>{/if}
	</td>
</tr>
<tr>
	<td colspan=3 align=center nowrap>
	    <h1>{$title}</h1>
	</td>
</tr>
</table>

