{*
9/9/2013 11:40 AM Fithri
- in 'view available PO', show HQ PO no if available

1/23/2014 3:53 PM Andy
- Fix cancel_date sometime show as "Array" bug.
- Fix wrong PO no. (PP)

1/27/2014 2:20 PM Justin
- Enhanced to take off PO No (PP).
*}

<h2>Available PO</h2>
{count var=$po} record(s) found :
<br>
<div style="height:220px; border:1px solid #ccc; overflow:auto;">
{if !$po}
- no PO found for this vendor -
{else}
<ul style="list-style-type:none;margin:0;padding:0;">
{section name=i loop=$po}
<li style="display:block;margin:0;padding:2px;" onmouseover="this.style.backgroundColor='#ff9'" onmouseout="this.style.backgroundColor=''" id=li{$po[i].id}>
<b>
{$po[i].po_no} 
/ {$po[i].own_report_prefix}{$po[i].id|string_format:"%05d"}(PP)
</b>
<span class=small>[<a href="./po.php?a=view&id={$po[i].id}&branch_id={$po[i].branch_id}" target="_blank">details</a>]
<br />
<font color=blue>PO Date :</font> {$po[i].po_date|date_format:$config.dat_format}
<font color=blue>Cancel Date :</font> {$po[i].cancel_date2}

</span>

{/section}
</ul>
{/if}
</div>
<div align=center>
<input type=button value="Close" onclick="close_available_dialog()">
</div>
