{*
7/15/2011 1:56:03 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
*}

{include file='header.print.tpl'}

<body onload="window.print()">
<div style="height:7in;width:10in;border:1px solid black;">
	<table width="100%" height="80%">
	    <tr>
	        <td valign="middle" align="center">
	            <table>
	                <tr>
	                    <td align="left">
							<i>
								<h1>{$to_branch.description} {if $to_branch.company_no}({$to_branch.company_no}){/if}</h1>
								<span style="font-size:12pt;">{$to_branch.address|nl2br}</span>
							</i>
				        </td>
				    </tr>
				</table>
			</td>
		</tr>
	</table>
</div>
</body>
</html>
