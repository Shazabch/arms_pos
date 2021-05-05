{*
8/10/2017 09:51 AM Qiu Ying
- Bug fixed on input field value shown in an abnormal way when containing special characters
*}
<table id="tblPreview" style="border: 1px solid #ddd;border-collapse:collapse;margin:5px;text-align:left">
	<tr>
		{foreach from=$header key=k item=items}
			<th style="border: 1px solid #ddd;padding:5px" nowrap>{$items|escape:'html'}</th>
		{/foreach}
	</tr>
	<tr>
		{foreach from=$master item=items}
			<td style="border: 1px solid #ddd;padding:5px" nowrap>{$items|escape:'html'}</td>
		{/foreach}
	</tr>
	<tr>
		{foreach from=$detail item=items}
			<td style="border: 1px solid #ddd;padding:5px" nowrap>{$items|escape:'html'}</td>
		{/foreach}
	</tr>
</table>
