{*
7/15/2011 1:54:39 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.
*}

{include file='header.print.tpl'}

<body onload="window.print()">
<div class="printarea">
	<table>
	    <tr>
			<td><b>Code  -  Description</b></td>
		</tr>
		{foreach from=$branches key=bid item=r name=i}
		<tr>
		    <td>{$r.code} - {$r.description}</td>
		</tr>
		{/foreach}
	</table>
</div>
</body>
</html>
