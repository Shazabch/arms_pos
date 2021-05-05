{config_load file="site.conf"}
{if $members or $members_bad}
<p><span id="unv"><font color=blue>Total unverified record(s): {$total+$total_bad}</font>
{if $total_bad}<br><font color=red>Incomplete record(s): {$total_bad} [<a href="#incomplete">click here view</a>]</font>{/if}
</span></p>
<table id="tb" >
<tr>
<th bgcolor={#TB_CORNER#} width=20>&nbsp;</th>
<th bgcolor={#TB_COLHEADER#}>NRIC</th>
<th bgcolor={#TB_COLHEADER#}>Name</th>
<th bgcolor={#TB_COLHEADER#}>D.O.B<br>(D-M-Y)</th>
<th bgcolor={#TB_COLHEADER#}>Gender</th>
<th bgcolor={#TB_COLHEADER#}>{$config.membership_cardname} No</th>
<th bgcolor={#TB_COLHEADER#}>Blocked</th>
</tr>
{if $members}
<tr><td colspan=7><h2>Completed record(s)</h2></td></tr>
{/if}
{section name=i loop=$members}
<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';" id="r[{$members[i].nric}]">
<td bgcolor={#TB_ROWHEADER#} nowrap>
<a href="javascript:do_verify('{$members[i].nric}')"><img src=ui/act.png title="Verify" border=0></a>
</td>
<td nowrap>{if strlen($members[i].nric)==12}{$members[i].nric|substr:0:6}-{$members[i].nric|substr:6:2}-{$members[i].nric|substr:8}{else}{$members[i].nric}{/if}</td>
<td nowrap>{$members[i].name}</td>
<td>{$members[i].dob|substr:6}-{$members[i].dob|substr:4:2}-{$members[i].dob|substr:0:4}</td>
<td align=center>{$members[i].gender}</td>
<td>{$members[i].card_no}</td>
<td>{if $members[i].blocked_by > 0}
{$members[i].blocked_reason}
{else}
&nbsp;
{/if}
</td>
</tr>
<!--tr id="r2[{$members[i].nric}]"><td colspan=7 align=center><img src="http://{$ic_branch_ip}/icfiles/{$members[i].nric}.jpg?{$smarty.now}"></td></tr-->
{/section}

{if $members_bad}
<tr><td colspan=7><a name="incomplete"></a><h2>{$total_bad} Incomplete record(s)</h2></td></tr>
{section name=i loop=$members_bad}
<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';" id="r[{$members_bad[i].nric}]" {if $members_bad[i].blocked_by > 0}style="color:#fff;background:#f00;"{/if}>
<td bgcolor={#TB_ROWHEADER#} nowrap>
{if $members_bad[i].blocked_by > 0}<a href="javascript:do_verify('{$members_bad[i].nric}')"><img src=ui/act.png title="Verify" border=0></a>{else}
<a href="?t=update&a=i&nric={$members_bad[i].nric}&from_list=1" target=_blank><img src=ui/ed.png title="Edit" border=0></a>{/if}
</td>
<td nowrap>{if strlen($members_bad[i].nric)==12}{$members_bad[i].nric|substr:0:6}-{$members_bad[i].nric|substr:6:2}-{$members_bad[i].nric|substr:8}{else}{$members_bad[i].nric}{/if}</td>
<td nowrap>{$members_bad[i].name}</td>
<td>{$members_bad[i].dob|substr:6}-{$members_bad[i].dob|substr:4:2}-{$members_bad[i].dob|substr:0:4}</td>
<td align=center>{$members_bad[i].gender}</td>
<td>{$members_bad[i].card_no}</td>
<td>{if $members_bad[i].blocked_by > 0}
{$members_bad[i].blocked_reason}
{else}
&nbsp;
{/if}
</td>
</tr>
<!--tr id="r2[{$members_bad[i].nric}]"><td colspan=7 align=center><img src="http://{$ic_branch_ip}/icfiles/{$members_bad[i].nric}.jpg?{$smarty.now}"></td></tr-->
{/section}
{/if}

</table>
{else}
{$no_record}
{/if}
