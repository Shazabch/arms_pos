<li id="li_activity_title-{$promotion_activity.id|default:'sample'}" class="li_activity_title" activity_id="{$promotion_activity.id}">
    <div class="div_title_area">
        <span class="span_tree_line_area"></span>
        <img src="/ui/expand.gif" title="Expand" align="absmiddle" class="clickable img_toggle_sub_activity" />
		<a href="javascript:void(0);" class="a_activity_title">
			<span class="title">{$promotion_activity.title}</span>
		</a>
		<span class="span_row_action">
		    {if $allow_edit and ($marketing_plan.label eq 'approved' or $marketing_plan.label eq 'draft')}
				<img src="/ui/add.png" title="Add sub activity" align="absmiddle" border="0" class="clickable img_add_sub_activity" width="12" height="12" />
			{/if}
			<img src="/ui/icons/arrow_refresh_small.png" title="Refresh sub activity" align="absmiddle" border="0" class="clickable img_refresh_sub_activity" width="12" height="12" />
		</span>
	</div>
	<ul class="ul_promotion_activity_tree" id="ul_promotion_activity_tree-{$promotion_activity.id|default:'sample'}" level="{$promotion_activity.level}">
	</ul>
</li>
