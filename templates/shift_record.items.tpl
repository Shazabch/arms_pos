{*
REVISION HISTORY
=================
6/23/2020 03:24 PM Sheila
- Updated button css

*}

{if !$smarty.request.ajax}
<form action=shift_record.php method=post name=f_s_r>
<input type=hidden name=a value="save">
<input type=hidden name=update value="{$smarty.request.update}">
<input type=hidden name=s_field value="{$s_field}">
<input type=hidden name=s_arrow value="{$s_arrow}">
<input type=hidden name=month value="{$month}">
<input type=hidden name=year value="{$year}">
<input type=hidden name=branch value="{$branch}">
<input type=hidden name=department value="{$department}">


<table cellpadding=1 cellspacing=0 border=0 id=tbl_sr style="table-layout: fixed; border:1px solid #000;padding:0px;">

<tr bgcolor=#ffee99>
<th width=50><b>No</th>
<td nowrap width=190 class=sort onclick="do_sort('employee_name')"><b> Employee Name</td>
<td nowrap width=130 class=sort onclick="do_sort('employee_id')"><b>Employee ID</td>

{section name=loopi loop=$i start=1 step=1}

{if $smarty.request.a=='edit_day'}
{if $smarty.request.a=='edit_day' && $smarty.request.update==$smarty.section.loopi.iteration}
<th width=25 nowrap>
<b>
<img src=/ui/application_put.png title="Cancel" onclick="do_refresh();">{$smarty.section.loopi.iteration}<div class=small>{$showday[loopi]}</div></b></th>
{/if}
{else}
<th width=25 nowrap>
<b>
{if $shift_record_privilege.SHIFT_RECORD_EDIT.$branch}
<img src=/ui/foc.png title="Edit" onclick="do_edit_day('{$smarty.section.loopi.iteration}')">
{/if}
{$smarty.section.loopi.iteration}<div class=small> {$showday[loopi]}</div></b>
</th>
{/if}
{/section}
{if $smarty.request.a=='edit_day'}<td width=100%>&nbsp;</td>{/if}
<td width=16><img src=/ui/pixel.gif width=16></td>
</tr>

<tbody id=div_sr style="height:200px;overflow:auto">
{/if}

{if $num_row>0}

{foreach name=j item="curr_list" from=$list}
{assign var=n value=$smarty.foreach.j.iteration}

{include file=shift_record.edit.user.tpl}

{/foreach}
{/if}

{if $smarty.request.a!='edit_day' && $shift_record_privilege.SHIFT_RECORD_EDIT.$branch}
<script>
add_row(undefined,1,'{$department}',{$branch},{$num_row});
</script>
{/if}

{if !$smarty.request.ajax}
</tbody>
</table>
</form>
<p align=center>
<!--<img src=/ui/approved.png title="Save" onclick="do_add('{$department}')">!-->
{if $shift_record_privilege.SHIFT_RECORD_EDIT.$branch}
{if $smarty.request.a=='edit_day'}
<input type=button class="btn btn-warning" value="Cancel Edit" onclick="do_refresh();">
<input id=submits_r name=submits_r class="btn btn-success" type=button onclick="chk_save_day()" value="Save">
{else}
<input id=submits_r name=submits_r class="btn btn-success" type=button onclick="chk_save({$num_row},'{$department}')" value="Save">
{/if}
{else}
<input type=button class="btn btn-error" value="Close" onclick="document.location='/shift_record.php'">
{/if}
</p>
{/if}

{if $smarty.request.a eq 'edit_day'}
{*do nothg*}
{elseif $smarty.request.a eq 'ajax_edit_item'}
<script>
alert('{$n}');
var tr = $('TR0_{$n}');// find the tr element we editing
tr.scrollToPosition;
$('TR0_{$n}').focus();
//$('name_edit').focus();
</script>
{elseif $smarty.request.a eq 'ajax_del_item'}
<script>
$('TR0_{$no-1}').focus();
</script>
{else}
<script>
$('div_sr').scrollTop = $('div_sr').scrollHeight;
//document.f_s_r.elemtens['name[0]'].focus();
</script>
{/if}
