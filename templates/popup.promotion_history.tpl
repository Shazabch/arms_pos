{*
3/23/2011 5:09:37 PM Andy
- Increase the width of promotion history popup.
*}

<div id="div_promotion_history" style="background:#fff;border:3px solid #000;width:900px;height:480px;position:absolute; padding:10px; display:none;">
<div style="text-align:right"><img src=/ui/closewin.png onclick="curtain_promotion_clicked()"></div>
{if $BRANCH_CODE eq 'HQ'}
<b>Branch:</b> {dropdown id="select_promo_branch" values=$branches selected=$smarty.request.branch_id key=id value=code onchange="show_promotion_cost_history();"}
{else}
<input type=hidden id="select_branch" value="{$sessioninfo.branch_id}">
<input type=hidden id="select_promo_branch" value="{$sessioninfo.branch_id}">
{/if}
<!--input type="button" value="Show" class="small" onClick="show_sku_cost_history();" /-->
<div id="div_promotion_history_list" style="height:450px;overflow:auto;">
</div>
</div>
