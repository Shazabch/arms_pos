{*
3/25/2011 6:23:13 PM Andy
- Move privilege group name variable to common.php

12/05/2011 2:44:00 PM Kee Kee
- Change "Others" privilege icon from "expand.gif" to "collapse.gif"

05/08/2019 15:35 Liew
- Add clone privilege from one branch to another branch

5/24/2019 10:25 AM Andy
- Fixed clone privilege only available under HQ.
*}

<table border=0 cellspacing=0 cellpadding=4>
<tbody id=header>
{if $BRANCH_CODE eq 'HQ'}
	<tr>
		<th align=left>Clone Branch Privileges</th>
		<th align=center width=20%>
		Source Branch<br>
		<select name="source_branch" id="sc"> 
		{section name=i loop=$branches}
			<option value={$branches[i].id}>{$branches[i].code}</option>
		{/section}
		</select>
		</th>
		<th align=center width=20%>
		Destination Branch<br>
		<select name="destination_branch" id="dc"> 
		{section name=i loop=$branches}
			<option value={$branches[i].id}>{$branches[i].code}</option>
		{/section}
		</select>
		</th>
		<th>
		<button type="button" onclick="clone_selected_col()">
		Clone
		</button>
		</th>
	</tr>
{/if}
<tr>
	<td colspan=2><h5>User Privileges</h5></td>
	{section name=c loop=$branches}
	<th width=50>
		<a href="javascript:void(checkallcol('user_privilege', '{$branches[c].id}', true))"><img src=ui/checkall.gif border=0 title="Check all"></a><br>
		<a href="javascript:void(checkallcol('user_privilege', '{$branches[c].id}', false))"><img src=ui/uncheckall.gif border=0 title="Uncheck all"></a><br>
		<label title="{$branches[c].description}">{$branches[c].code}</label>
	</th>
	{/section}
	<th align=left width=100%>Description</th>
</tr>
{foreach from=$privileges key=grp item=pg}
<tr>
	<td style="cursor:s-resize;border-top:1px solid #fff;border-bottom:1px solid #999;" colspan="{count var=$branches offset=3}" onclick="togglediv('group[{$grp}]','exp[{$grp}]')">
		<h4 style="margin:0"> 
			<img src="/ui/{if $grp eq 'Others'}collapse{else}expand{/if}.gif" id="exp[{$grp}]" /> 
			{if $grp eq 'Others'}Others{else}{$privilege_groupname.$grp}{/if}
		</h4>
	</td>
</tr>
<tbody id="group[{$grp}]" style="background:#fff;{if $grp ne 'Others'}display:none{/if}">
{foreach from=$pg item=pv}
<tr>
	<td style="border-bottom:1px solid #999"><a href="javascript:void(checkallrow('user_privilege', '{$pv.code}', true))"><img src=ui/checkall.gif border=0 title="Check all"></a><br>
	<a href="javascript:void(checkallrow('user_privilege', '{$pv.code}', false))"><img src=ui/uncheckall.gif border=0 title="Uncheck all"></a></td>
	<th style="border-bottom:1px solid #999" align=left><label title="{$pv.description}">{$pv.code}</label></th>
	{section name=c loop=$branches}
	<td style="border-bottom:1px solid #999" align=center>
	{if $pv.hq_only && $branches[c].code ne 'HQ'}
	-
	{else}
	<input type=checkbox name="user_privilege[{$branches[c].id}][{$pv.code}]" {get2ditem array=$user_privilege r=$branches[c].id c=$pv.code retval="checked"} class="inp_priv-{$branches[c].id}" priv_code="{$pv.code}" />
	{/if}
	</td>
	{/section}
	<td style="border-bottom:1px solid #999" class=small>
	{$pv.description}
	</td>
</tr>
{/foreach}
</tbody>
{/foreach}
</table>
