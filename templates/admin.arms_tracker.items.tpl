<tr style="border:1px solid #999; padding:5px; background-color:#fe9">
<th>&nbsp;</th>
<th>ID</th>
<th width=400>Description</th>
<th>Files</th>
<!--th width=80 nowrap onclick="do_sort('type');">Type</th-->
<th width=80 nowrap>Type</th>
<th width=60>Requested<br>By</th>
<th width=60>Solved<br>By</th>
<th width=60>Verified<br>By</th>
<th width=80>Added</th>
</tr>

{if $all_issues}
{section name=i loop=$all_issues}
{assign var=n value=$smarty.section.i.iteration}

<tr id="tr_{$all_issues[i].priority}" align=left>
<td nowrap>
{if !$all_issues[i].verified_by}
<img src=/ui/add.png align=absmiddle title="Add New Tracker" onclick="do_add_new('{$all_issues[i].priority}','{$total}');">
{/if}

{if !$all_issues[i].solved_by }
<img src=/ui/approved_grey.png align=absmiddle title="Requested by {$all_issues[i].u}" onclick="solve({$all_issues[i].id},this)">
{elseif !$all_issues[i].verified_by}
<img src=/ui/approved.png align=absmiddle title="Solved by {$all_issues[i].solved_by}" onclick="verify({$all_issues[i].id},this)">
{else}
<img src=/ui/lock.png align=absmiddle title="Verified by {$all_issues[i].verified_by}">
{/if}

{if !$all_issues[i].verified_by && $selected_type ne 'ALL' }

{if $n!=1}
<img src=/ui/icons/arrow_up.png align=absmiddle border=0 title="Move Priority" onclick="do_move_up({$all_issues[i].priority},{$all_issues[i].id});">
{else}
<img src=ui/pixel.gif width=17 height=1>
{/if}

{if $n!=$total}
<img src=/ui/icons/arrow_down.png align=absmiddle border=0 title="Move Priority" onclick="do_move_down({$all_issues[i].priority},{$all_issues[i].id});">
{else}
<img src=ui/pixel.gif width=17 height=1>
{/if}
{/if}

{if !$all_issues[i].verified_by}
	{if $all_issues[i].type!='cancelled'}
	<img src=/ui/cancel.png align=absmiddle border=0 title="cancel" onclick="cancel_this('{$all_issues[i].id}');">
	{/if}
<img src=/ui/ed.png align=absmiddle border=0 title="Edit" onclick="edit_this(this,'{$all_issues[i].id}');">
{/if}
</td>

<td>
{$all_issues[i].id}
</td>

<td id="td_{$all_issues[i].id}" {if !$all_issues[i].verified_by}ondblclick="edit_this(this,'{$all_issues[i].id}');"{/if}>
{$all_issues[i].description}
</td>

<td nowrap>
<div id="pics[{$all_issues[i].id}]">
{if $all_issues[i].files}
{foreach from=$all_issues[i].files item=f}
{if (preg_match('/\.xls$/',$f))}
<a href="?a=all_attachment&id={$all_issues[i].id}" target=_blank><img src="/ui/icons/page_excel.png" border=0></a>
{elseif (preg_match('/\.pdf$/',$f))}
<a href="?a=all_attachment&id={$all_issues[i].id}" target=_blank><img src="/ui/icons/page_white_acrobat.png" border=0></a>
{else}
<a href="?a=all_attachment&id={$all_issues[i].id}" target=_blank><img src="/thumb.php?w=25&h=25&img={$f|replace:"`$smarty.server.DOCUMENT_ROOT`/":''}" border=0></a>
{/if}
{/foreach}
{/if}
</div>
<button onclick="addpic(this,{$all_issues[i].id});return false;"><img src=/ui/icons/add.png> Add</button>
</td>

<td align=center>

{if $all_issues[i].verified_by}
{$all_issues[i].type|upper}

{else}
<select id=item_type_{$all_issues[i].id} name=item_type[{$all_issues[i].id}] onchange="do_change_type('{$all_issues[i].id}');">
{section name=j loop=$type}
<option value="{$type[j].type}" {if $all_issues[i].type==$type[j].type}selected{/if}>
{$type[j].type|upper}
</option>
{/section}
</select>
{/if}
</td>

<td align=center>{$all_issues[i].u}</td>
<td align=center>{$all_issues[i].solved_by|default:"--"}</td>
<td align=center>{$all_issues[i].verified_by|default:"--"}</td>
<td align=center>{$all_issues[i].added}</td>
</tr>
{/section}
{else}

<tr id="tr_0">
<td>

<img src=/ui/add.png align=absmiddle title="Add New Trackers" onclick="do_add_new('0','{$total}');">

</td>
<td colspan=7 align=center>--No Record--</td>
</tr>
{/if}
