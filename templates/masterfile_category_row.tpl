{*
4/23/2010 2:42:12 PM Andy
- Prompt user to confirm if user try to move LINE or DEPARTMENT.
- Delete old department approval flow.
- Delete old allowed department at user privileges.
- Update all documents from old department to new department.

8/16/2010 1:03:39 PM Andy
- Fix cateogry moving bugs.

5/7/2012 11:22:39 AM Andy
- Change "Photos Required for SKU Application" will not appear if category level is more than 2.
*}

{config_load file=site.conf}
{if $sessioninfo.privilege.MST_CATEGORY}
<td bgcolor={#TB_ROWHEADER#} width=60 nowrap>
&nbsp;<a href="javascript:void(ed({$category_row.id}))"><img src=ui/ed.png title="Edit" border=0></a>
<a href="javascript:void(act({$category_row.id},{if $category_row.active}0))"><img src=ui/deact.png title="Deactivate" border=0>{else}1))"><img src=ui/act.png title="Activate" border=0>{/if}</a>
<a href="javascript:void(move({$category_row.id},'{$category_row.tree_str}', '{$category_row.level}', '{$category_row.root_id}'))"><img src=ui/move.png title="Move" border=0></a>
</td>
{/if}
<td width=50>{$category_row.code|default:"&nbsp;"}</td>
{strip}
<td>
{repeat n=`$category_row.level-1`}<img src=ui/pixel.gif width=24 height=1 align=absmiddle>{/repeat}
<img src=ui/tree_{if $smarty.section.i.last}e{else}m{/if}.png align=absmiddle>
<a href="javascript:void(toggle_sub({$category_row.id}))">{$category_row.description}</a> <span style="color:#999999;" id="span_child_count-{$category_row.id}">({$category_row.child_count})</span>
{if $sessioninfo.privilege.MST_CATEGORY}
<img src="ui/add_child.png" style="cursor:pointer" onclick="add({$category_row.id},'{$category_row.tree_str}','{$category_row.level+1}')" align=absmiddle title="create Sub-category">
{/if}
</td>
{/strip}
<td width=50 align=right>{$category_row.area|number_format:2|ifzero:"&nbsp;"}</td>
{foreach from=$sku_type item=v key=k}
<td width=50 align=center>
	{if $category_row.level<=2}
		{if $category_row.min_sku_photo[$k] == -1}
			&nbsp;
		{elseif $category_row.min_sku_photo[$k] == 0}
			<img src="/ui/deact.png" />
		{elseif $category_row.min_sku_photo[$k]>0}
			<img src="/ui/approved.png" /> {$category_row.min_sku_photo[$k]|default:"&nbsp;"}
		{/if}
	{/if}
</td>
{/foreach}
<td width=50 align=center>{if $category_row.grn_po_qty}<img src=/ui/approved.png>{else}&nbsp;{/if}</td>
<td width=50 align=center>{if $category_row.grn_get_weight}<img src=/ui/approved.png>{else}&nbsp;{/if}</td>
