{*
9/19/2016 09:05 Qiu Ying
- Enhanced to set selling inclusive or exclusive for bundled price
*}

{assign var=show_search_sku value=0}
{assign var=disc_by_inclusive_tax value=$global_gst_settings.inclusive_tax}

{if $promo_data && !$promo_data.invalid_pwid}
	<div id="div_pw_disc_target_item_list">
		{foreach from=$promo_data.item_list key=row_num item=promo_item name=f_pw_item}
			{include file='promotion.mix_n_match.open.wizard_dialog.discount_target_screen.item_row.tpl'}
		{/foreach}
	</div>
	{if $promo_data.allow_add_discount_target}
		<button onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.add_disc_target_item();" id="btn_pw_add_disc_item_div">
			<img src="/ui/add.png" align="absbottom" />
			Add Discount Item
		</button>
	{/if}
{/if}

