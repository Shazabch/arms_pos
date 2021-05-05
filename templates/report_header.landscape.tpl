{*
7/10/2009 4:26:35 PM yinsee
- add company-no to header

7/15/2011 3:01:14 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

3:14 PM 4/10/2015 Andy
- Remove the table border.
*}

{if !$skip_header}
{include file='header.print.tpl'}

<body onload="window.print();">
{/if}

<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border="0">
<tr>
	<td width=60><img src="{get_logo_url}" height=80 hspace=5 vspace=5></td>
	<td class="small" nowrap>
		<h1>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h1>
		{$branch.address|nl2br}<br>
		Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if} &nbsp;&nbsp; Fax: {$branch.phone_3}
	</td>

	<td align=center nowrap>
	    <h1>{$title}</h1>
		<h4>{$subtitle_m}</h4>
	</td>
	<td width=33% align=right nowrap>
  		<h4>{$subtitle_r}</h4>
  		{if $page_n}<b>Page {$page_n}/{$page_total}{/if}</b>
	</td>
</tr>
</table>

