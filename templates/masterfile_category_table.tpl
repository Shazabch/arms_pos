{*
5/7/2012 11:23:43 AM Andy
- Fix when add new category, will update parent child count.
*}

{config_load file=site.conf}
<table  border=0 width=100% cellpadding=0 cellspacing=0>
{section name=i loop=$categories}
<tr id="r{$categories[i].id}" height=24 onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';" class="tr_child_of-{$categories[i].root_id}">
{assign var=category_row value=`$categories[i]`}
{include file=masterfile_category_row.tpl}
</tr>
<tr>
<td colspan=9 id="sc{$categories[i].id}" style="display:none">
</td>
</tr>
{/section}
</table>

