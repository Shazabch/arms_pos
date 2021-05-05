<h2>Available DO</h2>
{count var=$do} record(s) found :
<br>
<div style="height:220px; border:1px solid #ccc; overflow:auto;">
{if !$do}
- DO not found -
{else}
<ul style="list-style-type:none;margin:0;padding:0;">
{section name=i loop=$do}
<li style="display:block;margin:0;padding:2px;" onmouseover="this.style.backgroundColor='#ff9'" onmouseout="this.style.backgroundColor=''" id=li{$po[i].id}>
<b>{$do[i].do_no}</b>
<span class=small>[<a href="./do.php?a=view&id={$do[i].id}&branch_id={$do[i].branch_id}" target="_blank">details</a>]
&nbsp;&nbsp;&nbsp;&nbsp;
<font color=blue>DO Date :</font> {$do[i].do_date|date_format:$config.dat_format}
<font color=blue>Amount:</font> {$do[i].total_amount|number_format:2}
</span>

{/section}
</ul>
{/if}
</div>
<div align=center>
<input type=button value="Close" onclick="close_available_dialog()">
</div>
