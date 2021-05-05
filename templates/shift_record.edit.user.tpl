{if $smarty.request.ajax=='edit_user'}
<h4>
<font color=black>
Date : </font>{if $month == 1}January / {$year}{/if}
{if $month == 2}February / {$year}{/if}
{if $month == 3}March / {$year}{/if}
{if $month == 4}April / {$year}{/if}
{if $month == 5}May / {$year}{/if}
{if $month == 6}June / {$year}{/if}
{if $month == 7}July / {$year}{/if}
{if $month == 8}August / {$year}{/if}
{if $month == 9}September / {$year}{/if}
{if $month == 10}October / {$year}{/if}
{if $month == 11}November / {$year}{/if}
{if $month == 12}December / {$year}{/if}

&nbsp;
<font color=black>
Branch : </font>
{foreach item="curr_Branch" from=$BranchArray}
{if $curr_Branch.id==$branch}{$curr_Branch.code}{/if}
{/foreach}
</h4>

{assign var=n value=0}
<table cellpadding=1 cellspacing=0 border=0 id=tbl_sr style="table-layout: fixed; border:1px solid #000;padding:0px;">

<tr bgcolor=#ffee99>
<th width=50><b>&nbsp;</th>
<td nowrap width=190 class=sort onclick="do_sort('employee_name')"><b> Employee Name</td>
<td nowrap width=130 class=sort onclick="do_sort('employee_id')"><b>Employee ID</td>
{section name=loopi loop=$i start=1 step=1}
<th nowrap>
<b>
{$smarty.section.loopi.iteration}
{if $smarty.request.a=='edit_day' && $smarty.request.update==$smarty.section.loopi.iteration}
<img src=/ui/application_put.png title="Cancel" onclick="do_refresh();"> {$smarty.section.loopi.iteration} <div class=small>{$showday[loopi]}</div></b>
{else}
{if $shift_record_privilege.SHIFT_RECORD_EDIT.$branch && $smarty.request.ajax!='edit_user'}
<img src=/ui/foc.png title="Edit" onclick="do_edit_day('{$smarty.section.loopi.iteration}')">{/if} &nbsp;<div class=small> {$showday[loopi]}</div></b>
{/if}
</th>
{/section}
{/if}


{strip}
{if $smarty.request.ajax!='3'}{if (($n%2)==1)}<tr id=TR0_{$n} bgcolor=#dddddd>{else}<tr id=TR0_{$n} bgcolor=#ff9999>{/if}{/if}
<th nowrap>
{if $smarty.request.ajax=='edit_user'}
<img src=/ui/chown.png title="On Editing">
{else}
{if $shift_record_privilege.SHIFT_RECORD_EDIT.$branch && $smarty.request.a!='edit_day'}
<img src=/ui/foc.png title="Edit" onclick="call_user('{$curr_list.employee_id|escape:'javascript'}',{$month},{$year},{$branch},'{$department|escape:'javascript'}',{$n})">
{/if}
{/if}
{if $shift_record_privilege.SHIFT_RECORD_EDIT.$branch && $smarty.request.ajax!='edit_user' && $smarty.request.a!='edit_day'}
<img src=/ui/remove16.png title="Delete" onclick="do_delete('{$curr_list.employee_id|escape:'javascript'}',{$month},{$year},{$branch},{$n})">
{/if}
{if $n==0}
&nbsp;
{else}
{$n}
{/if}
</th>

<th align=left  width=190>
{if $smarty.request.ajax=='edit_user'}
<input id=name_edit onchange=uc(this) name=user_name size=30 maxlength=50 value="{$curr_list.employee_name}" class=name_edit>
{else}
{$curr_list.employee_name}
{/if}
</td>

<th align=left  width=130>
{if $smarty.request.ajax=='edit_user'}
<input onkeyup="uc(this);chk_id(this,'{$curr_list.employee_id|escape:'javascript'}');" name=user_id size=15 maxlength=20 value="{$curr_list.employee_id|escape:'javascript'}" class=name_edit>
{else}
{$curr_list.employee_id}
{/if}
</td>

{section name=loopi loop=$i start=1 step=1}

{assign var=ii value=$smarty.section.loopi.iteration}
{assign var=arrayname value=estimate_day_record_$ii}

{if $smarty.request.ajax=='edit_user'}
<td align=center>
<input onchange="uc(this);changered(this);changeblue(form.elements['user_day[{$smarty.section.loopi.iteration}]'],this.value)" {if $curr_list.$arrayname eq 'R'}class='cr_s'{/if} class='normal_s' name=user_Eday[{$smarty.section.loopi.iteration}] size=1 maxlength=1 value="{$curr_list.$arrayname}">
</td>

{elseif $smarty.request.a=='edit_day'}
{if $smarty.request.a=='edit_day' && $smarty.request.update==$smarty.section.loopi.iteration}
<td align=center>
<input onchange="uc(this);changered(this);changeblue(form.elements['day[{$n}][{$smarty.section.loopi.iteration}]'],this.value)" {if $curr_list.$arrayname eq 'R'}class='cr_s'{/if} class='normal_s' name=Eday[{$n}][{$smarty.section.loopi.iteration}] size=1 maxlength=1 value="{$curr_list.$arrayname}">
</td>
{/if}
{else}
<td align=center>
{if $curr_list.$arrayname eq 'R'}<font color=red>{else}<font color=black>{/if}
{$curr_list.$arrayname}&nbsp;
</td>
{/if}
{/section}
{if $smarty.request.a=='edit_day'}<td width=100%>&nbsp;</td>{/if}
{if $smarty.request.ajax!='3'}</tr>{/if}
{/strip}
{strip}

{if $smarty.request.ajax!='3'}{if (($n%2)==1)}<tr id=TR1_{$n} bgcolor=#eeeeee>{else}<tr id=TR1_{$n} bgcolor=#ffcccc>{/if}{/if}

<th>
<input type=checkbox name=print_list["{$curr_list.employee_id|escape:'javascript'}"] value="1" onclick="active_print(this);" class="print_select">
</th>
{if $department eq '%%'}
<td nowrap>
{if $smarty.request.ajax=='edit_user'}
<select id=selected_dept name=selected_dept onchange="check_brand(this,0)">
{if $branch=='1'}
<option {if $curr_list.department eq 'Finance'}selected{/if}>Finance</option>
<option {if $curr_list.department eq 'MIS'}selected{/if}>MIS</option>
<option {if $curr_list.department eq 'HR & Office'}selected{/if}>HR & Office</option>
<option {if $curr_list.department eq 'Internal Audit'}selected{/if}>Internal Audit</option>
<option {if $curr_list.department eq 'Marketing'}selected{/if}>Marketing</option>
<option {if $curr_list.department eq 'Merchandising'}selected{/if}>Merchandising</option>
</select>
{else}
<option {if $curr_list.department eq 'Management'}selected{/if}>Management</option>
<option {if $curr_list.department eq 'Account'}selected{/if}>Account</option>
<option {if $curr_list.department eq 'MIS'}selected{/if}>MIS</option>
<option {if $curr_list.department eq 'HR & Office'}selected{/if}>HR & Office</option>
<option {if $curr_list.department eq 'Security'}selected{/if}>Security</option>
<option {if $curr_list.department eq 'Maintenance'}selected{/if}>Maintenance</option>
<option {if $curr_list.department eq 'A & P'}selected{/if}>A & P</option>
<option {if $curr_list.department eq 'Merchandising'}selected{/if}>Merchandising</option>
<option {if $curr_list.department eq 'Store'}selected{/if}>Store</option>
<option {if $curr_list.department eq 'Sales (Supermarket line)'}selected{/if}>Sales (Supermarket line)</option>
<option {if $curr_list.department eq 'Sales (Soft line)'}selected{/if}>Sales (Soft line)</option>
<option {if $curr_list.department eq 'Sales (Hard line)'}selected{/if}>Sales (Hard line)</option>
<option {if $curr_list.department eq 'Promoter (Supermarket line)'}selected{/if}>Promoter (Supermarket line)</option>
<option {if $curr_list.department eq 'Promoter (Soft line)'}selected{/if}>Promoter (Soft line)</option>
<option {if $curr_list.department eq 'Promoter (Hard line)'}selected{/if}>Promoter (Hard line)</option>
<option {if $curr_list.department eq 'Cashiering'}selected{/if}>Cashiering</option>
<option {if $curr_list.department eq 'Public Relations'}selected{/if}>Public Relations</option>
{/if}
</select>
{else}
{$curr_list.department}
{/if}
</td>

{if $curr_list.department eq 'Promoter (Hard line)' || $curr_list.department eq 'Promoter (Soft line)' || $curr_list.department eq 'Promoter (Supermarket line)'}
<td nowrap>
{if $smarty.request.ajax=='edit_user'}
<input id=brand0 onchange="uc(this);" class=brand type=text size=15 value="{$curr_list.brand}" name="user_brand">&nbsp;
{else}
&nbsp;{$curr_list.brand}
{/if}
</td>
{else}
<td>
{if $smarty.request.ajax=='edit_user'}
<input id=brand0 onchange="uc(this);" class=brand type=hidden size=15 value="{$curr_list.brand}" name="user_brand">&nbsp;
{else}
&nbsp;
{/if}
</td>
{/if}

{else}
{if $department eq 'Promoter (Hard line)' || $department eq 'Promoter (Soft line)' || $department eq 'Promoter (Supermarket line)'}
<td align=center style="font-size:4px;color:#aaa;">Actual Attendance</td>
<td nowrap>
{if $smarty.request.ajax=='edit_user'}
<input id=brand0 class=brand onchange="uc(this);" type=text size=15 value="{$curr_list.brand}" name="user_brand">&nbsp;
{else}
{$curr_list.brand}
{/if}
</td>
{else}
<td colspan=2 align=center style="font-size:4px;color:#aaa;">Actual Attendance</td>
{/if}
{/if}

{section name=loopi loop=$i start=1 step=1}

{assign var=ii value=$smarty.section.loopi.iteration}
{assign var=arrayname value=day_record_$ii} {assign var=arrayEday value=estimate_day_record_$ii}

{if $smarty.request.ajax=='edit_user'}
<td align=center>
<input onchange="uc(this);chk2ndrow(this);changeblue(this,form.elements['user_Eday[{$smarty.section.loopi.iteration}]'].value)" {if $curr_list.$arrayname eq $curr_list.$arrayEday && $curr_list.$arrayname eq 'R'}class=cr_s {elseif $curr_list.$arrayname eq $curr_list.$arrayEday} class=normal_s {else} class='cb_s'{/if}  name=user_day[{$smarty.section.loopi.iteration}] size=1 maxlength=3 value="{$curr_list.$arrayname}">
</td>

{elseif $smarty.request.a=='edit_day'}
{if $smarty.request.a=='edit_day' && $smarty.request.update==$smarty.section.loopi.iteration}
<td align=center>
<input onchange="uc(this);chk2ndrow(this);changeblue(this,form.elements['Eday[{$n}][{$smarty.section.loopi.iteration}]'].value)" {if $curr_list.$arrayname eq $curr_list.$arrayEday && $curr_list.$arrayname eq 'R'}class=cr_s {elseif $curr_list.$arrayname eq $curr_list.$arrayEday} class=normal_s {else} class='cb_s'{/if}  name=day[{$n}][{$smarty.section.loopi.iteration}] size=1 maxlength=3 value="{$curr_list.$arrayname}">
</td>
{/if}
{else}
<td align=center>
{if $curr_list.$arrayname eq $curr_list.$arrayEday && $curr_list.$arrayname eq 'R'}<font color=red> {elseif $curr_list.$arrayname eq $curr_list.$arrayEday} <font color=black> {else}<font color=blue>{/if}
{$curr_list.$arrayname}&nbsp;
</td>
{/if}
{/section}
{if $smarty.request.a=='edit_day'}<td width=100%>&nbsp;</td>{/if}
{if $smarty.request.ajax!='3'}
</tr>{/if}
{if $smarty.request.a=='edit_day'}
<input type=hidden name=id[{$n}] value="{$curr_list.employee_id}">
{/if}
{/strip}

{if $smarty.request.ajax=='edit_user'}
</table>
<input type=hidden name=original_id value="{$original_id}">
<br>
<input type=button value=" Save " onclick="do_edit_user({$no});">&nbsp;&nbsp;<input type=button value=" Cancel " onclick="do_cancel();">
{/if}
