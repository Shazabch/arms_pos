<b>Branch</b>
<ul class="form_branches">
{foreach from=$branches item=branch}
<li><input type="checkbox" name="b[]" value="{$branch.id}" {if in_array($branch.id,$selected)}checked="checked"{/if}/>{$branch.code}</li>
{/foreach}
</ul>
<div style="clear:both;"></div>