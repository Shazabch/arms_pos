{*
10/18/2011 12:58:21 PM Justin
- Modified history to show blocked reason while found card has been blocked.

2/27/2012 4:51:43 PM Justin
- Added new feature to check for cancel bill to return error message if user intends to cancel card no with having points.

6/5/2012 11:46:23 AM Justin
- Fixed bugs that when user is not administrator rank, there is a column missing and causing other info placed in wrong column.

12:03 PM 8/29/2012 Justin
- Added new column "Added".

10/22/2012 2:02 PM Justin
- Modified the remark "Change Card" become "Change Card / Replacement".
- Added a new remark type "Upgrade".

1/15/2013 3:04 PM Justin
- Enhanced to load custom remark while presenting history remark.
*}

{if $history}
<table width=100% cellspacing=1 cellpadding=4 border=0>
<tr bgcolor=#ffee99>
	<th></th>
	<th>{$config.membership_cardname} No</th>
	<th>Issue Branch</th>
	<th>Reason</th>	
	<th>Issue Date</th>
	<th>Expiry Date</th>
	<th>Card Type</th>
	<th>By</th>
	<th>Added</th>
</tr>

{section name=i loop=$history}
<tr bgcolor={cycle values=",#eeeeee"}>
<td align=center>
	{if $history[i].remark ne 'CB' && $sessioninfo.level>=9999}
		<img src=/ui/icons/delete.png onclick="cancel_history('{$history[i].nric}','{$history[i].card_no}','{$history[i].branch_id}','{$history[i].id}')"align=absmiddle>
	{else}
		&nbsp;
	{/if}
</td>
<td>{$history[i].card_no|default:"&nbsp;"}</td>
<td>{$history[i].branch_code|default:"&nbsp;"}</td>
<td>
{assign var=type value=$history[i].remark}
{if $config.membership_custom_remark.$type.description}
{$config.membership_custom_remark.$type.description}
{elseif $history[i].remark eq 'N'}
New Card
{elseif $history[i].remark eq 'L'}
Lost Card
{elseif $history[i].remark eq 'R'}
Renewal
{elseif $history[i].remark eq 'LR'}
Lost &amp; Renewal
{elseif $history[i].remark eq 'I'}
Inactive (BLOCKED)
{if $history[i].action_date && $history[i].action_reason}
	<br />
	<span class="small" style="color:red;">Date: {$history[i].action_date} / Reason: {$history[i].action_reason}</span>
{/if}
{elseif $history[i].remark eq 'A'}
Active (UNBLOCKED)
{elseif $history[i].remark eq 'UC'}
Upgrade
{elseif $history[i].remark eq 'C'}
Change Card / Replacement
{elseif $history[i].remark eq 'CB'}
<font color=red>Cancel Bill</font>
{elseif $history[i].remark eq 'T'}
Terminated by System
{elseif $history[i].remark eq 'U'}
Change NRIC or Name
{elseif $history[i].remark eq 'ER'}
Exchange &amp; Renew
{else}
{$history[i].remark}
{/if}
</td>
<td align=center>{$history[i].issue_date|date_format:"%e/%m/%Y"|default:"&nbsp;"}</td>
<td align=center>{$history[i].expiry_date|date_format:"%e/%m/%Y"|default:"&nbsp;"}</td>
<td align=center>{$history[i].card_type|default:"&nbsp;"}</td>
<td align=center>{$history[i].u|default:"&nbsp;"}</td>
<td align=center>{$history[i].added|default:"&nbsp;"|ifzero:"&nbsp;"}</td>
{/section}
</tr>
</table>
{else}
<p align=center>- No Renewal History -</p>
{/if}
{literal}
<script>
function cancel_history(nric,card_no,branch_id,id){
	if (confirm('Cancel this history?')){
		new Ajax.Request("membership.php", {
			method:'post',
			parameters: 't=cancel_history&a=i&nric='+nric+'&card_no='+card_no+'&id='+id+'&branch_id='+branch_id,
			evalScripts: true,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function(m) {
				if(m.responseText != "ok"){
					alert(m.responseText);
				}else{
					var msg = "Renewal History cancelled.";
					window.location = "/membership.php?t=history&a=i&nric="+nric+"&msg="+msg;
				}
			}
		});
	}
}
</script>
{/literal}
