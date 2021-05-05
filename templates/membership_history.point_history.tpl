{*
11/12/2009 12:26:54 PM edward
- check no_of_history>=24 only show height

12/7/2009 11:02:54 AM Andy
- edit date column

12/10/2009 4:17:51 PM Andy
- Add one more parameter pass to function sales_details

8/4/2010 10:33:54 AM Alex
- Change date format to Y-m-d

8/11/2010 12:30:40 PM Justin
- Added new fieild called User.

11/10/2010 4:02:17 PM Justin
- Fixed the bugs where user unable to view sales details when having more than 2 card no in history.

6/20/2011 5:11:59 PM Andy
- Fix printing bugs.

6/22/2012 2:07:00 PM Andy
- Add to show "Auto Redemption" History at membership points history.

11/28/2012 2:54 PM Justin
- Enhance to exclude the type of "ENTRY" for viewing history.

1/17/2013 3:57 PM Andy
- Enhanced sub card point history

2/6/2013 2:46 PM Justin
- Enhanced to show information and link on total row as if found this card is sub card.

8/19/2013 4:26 PM Justin
- Enhanced to take off the sub card separator.
- Enhanced to group principal and supplementary card points history by date.

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".
*}

{if $point_history}
<table width=100%  cellspacing=1 cellpadding=4 border=0>
<thead>
<tr bgcolor=#ffee99>
	<th>{$config.membership_cardname} No</th>
	<th>Branch</th>
	<th>Remark</th>
	<th>Date</th>
	<th>User</th>	
	<th>Type</th>
	<th width="5%">Point</th>
	<th width=16>&nbsp;</th>
</tr>
</thead>
{capture assign=no_of_history}{count var=$point_history}{/capture}

<tbody>
	{assign var=sub_card_row_showed value=0}
	{assign var=total_point value=0}
	{section name=i loop=$point_history}
	{assign var=total_point value=$total_point+$point_history[i].points}
	
	<!--{if $point_history[i].is_subcard and !$sub_card_row_showed}
		{* sub card separator *}
		<tr>
			<td colspan="8" style="background-color:#cccccc">Sub Card</td>
		</tr>
		{assign var=sub_card_row_showed value=0}
	{/if}-->
	
	<tr bgcolor={cycle values=",#eeeeee"}>
	<td>{$point_history[i].card_no|default:"&nbsp;"}{if $point_history[i].is_subcard} (SUB){/if}</td>
	<td>{$point_history[i].code|default:"&nbsp;"}</td>
	<td>{$point_history[i].remark|default:"&nbsp;"}</td>
	<td align=center>
		{if $point_history[i].type eq 'POS' || $point_history[i].type eq 'REDEEM' || $point_history[i].type eq 'AUTO_REDEEM'}
			<a href="javascript:void(0);" onclick="sales_details('{$point_history[i].date|date_format:'%Y-%m-%d'}', '{$point_history[i].card_no}', '{$point_history[i].type}', '{$point_history[i].branch_id}', '{$point_history[i].ord_date}')">
			{$point_history[i].date|default:"&nbsp;"}
			</a>
	 	{else}
	    	{$point_history[i].date|default:"&nbsp;"}
		{/if}
	</td>
	<td align=center>{$point_history[i].user|default:"&nbsp;"}</td>
	<td align=center>
		{if $point_history[i].type eq 'CANCELED'}
			CANCELLED
		{else}
			{$point_history[i].type|default:"&nbsp;"}
		{/if}
	</td>
	<td align=right>{$point_history[i].points|default:"&nbsp;"}</td>
	</tr>
	{/section}

</tbody>
<tr bgcolor="#cccccc">
<td colspan=6 align=right><b>Total Point</b>
{if $form.parent_nric}
<br />
<b>(Points has been transferred to Principal)</b><br />[ <a href="membership.php?t=history&a=i&nric={$form.parent_nric}" target="_blank">click here to refer to Principal Card</a> ]
{/if}
</td>
<td align="right" valign="top">{$total_point}</td>
<th width="16">&nbsp;</th>
</tr>
</table>
{else}
<p align="center">- No Point History -</p>
{/if}
