{foreach from=$category key=cid item=r name=topf}
<tr class="{$r.tree_str}({$cid})"  onmouseout="this.bgColor='';" onmouseover="this.bgColor='#ffffcc';">
	<td><img src="/ui/pixel.gif" width="{$r.level*30}" height="1">
	{if $smarty.foreach.topf.last}
		<img align="absmiddle" src="ui/tree_e.png"/>
	{else}
		<img align="absmiddle" src="ui/tree_m.png"/>
	{/if}
	{if $r.downline_count>0}
	<a href="javascript:" onClick="toggleSub(this,'{$r.tree_str}({$cid})','{$cid}');" title="Expand">{$r.description}</a>
	{else}
	    {$r.description}
	{/if}
	</td>
	{if $BRANCH_CODE eq 'HQ'}
	    {foreach from=$branch_list key=bid item=b}
            <td class="c" title="{$bid}"><input type="text" name="markup[{$bid}][{$cid}]" class="input_markup" onChange="updateField(this,'{$cid}');" title="{$bid}" value="{$markup.$bid.$cid.markup}" /></td>
	    {/foreach}
	{else}
	    {assign var=my_branch value=$sessioninfo.branch_id}
	    <td class="c" title="{$my_branch}"><input type="text" name="markup[{$my_branch}][{$cid}]" class="input_markup" onChange="updateField(this,'{$cid}');" title="{$my_branch}" value="{$markup.$my_branch.$cid.markup}" /></td>
	{/if}
</tr>
	<!--{*{include file=masterfile_category_markup.cat.tpl}*}-->
{/foreach}
