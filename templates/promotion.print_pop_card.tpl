{*
9/9/2020 2:23 PM William
- Fixed print size to A4 paper size.

12/18/2020 4:32 PM William
- Bug fixed promotion price not using non-member-price when config "membership_module" inactive.
*}
{literal}
<style>
body{
	margin:0;
	font-family: Franklin Gothic Medium, Franklin Gothic, ITC Franklin Gothic, Arial, sans-serif;
}
.printarea{
	font-size: 16pt;
}
.landscape{
	width: 50%;
	float:left;
}
.portrait{
	width: 100%;
}
.landscape_border{
	border-right: 1px dashed black;
}
.div_card_content{
	text-align: center;
	display: grid;
	height: 100%;
}
span{
	display: block;
}
.div_card_items{
    align-items: center;
	display: grid;
}
.price{
	background-color: black!important;
	color:white;
}
.text1{
	font-size: 80pt;
	font-weight: bold;
}
.text2{
	font-size: 50pt;
}
.text3{
	font-size: 35pt;
}
.text4{
	font-size: 23pt;
}
.text5{
	font-size: 19pt;
}
.text_italic{
	font-style: italic;
}
.div_card_background_img {
	height: 100%;
	background-position: top center; 
	background-repeat: no-repeat;
	background-size: cover;
	-webkit-background-size: cover;
	-moz-background-size: cover;
	-o-background-size: cover;
	padding: 0px 30px;
}
@page {
    size: A4;
    padding: 0;
    margin:0mm 0mm 0mm 0mm;
    border: none;
    border-collapse: collapse;
}
</style>
{/literal}

{if $form.printing_format eq 'format1'}
	{foreach from=$promo_items item=r name=pi}
		<div class="printarea {if $form.card_per_page eq 2}landscape {else} portrait{/if}">
			<div class="div_card_background_img {if $form.card_per_page eq 2 && $smarty.foreach.pi.iteration % 2} landscape_border{/if}" style="background-image: url('{$form.promo_pop_photo}');">
				<div class="div_card_content">
					<div class="div_card_items">
						<span class="{if $form.card_per_page eq 2}text3{else}text2{/if}"><b>{$form.title}</b></span>
					</div>
					
					<div class="div_card_items">
						
						<div style="display: block ruby;line-height: 70px;">
							{if $config.membership_module}
								{if $form.member_discount eq '' || $form.member_discount eq 'member'}
								<div style="padding: 0px 15px">
									<span class="{if $form.card_per_page eq 2}text5{else}text4{/if}">For Member {if $r.use_non_member_disc && ($form.member_discount eq '' || $form.member_discount eq 'non_member')}& Non-Member{/if}</span>
									{if $r.member_price}
										<div class="{if $form.card_per_page eq 2}text3{else}text2{/if}">{$config.arms_currency.symbol} {$r.member_price|number_format:2}</div>
									{/if}
									{if !$r.member_price}
										<span class="{if $form.card_per_page eq 2}text2{else}text1{/if}">{$r.member_disc_p}</span>
										<span class="{if $form.card_per_page eq 2}text4{else}text3{/if} text_italic"><b>Discount</b></span>
									{/if}
								</div>
								{/if}
							{/if}
							
							{if ($form.member_discount eq '' || $form.member_discount eq 'non_member') && !$r.use_non_member_disc}
							<div style="padding: 0px 8px">
								{if $r.non_member_price || $r.non_member_disc_p}<span class="{if $form.card_per_page eq 2}text5{else}text4{/if}">For Non-Member</span>{/if}
								{if $r.non_member_price}<div class="{if $form.card_per_page eq 2}text3{else}text2{/if}">{$config.arms_currency.symbol} {$r.non_member_price|number_format:2}</div>{/if}
								{if !$r.non_member_price}
									<span class="{if $form.card_per_page eq 2}text2{else}text1{/if}">{$r.non_member_disc_p}</span>
									<span class="{if $form.card_per_page eq 2}text4{else}text3{/if} text_italic"><b>Discount</b></span>
								{/if}
							</div>
							{/if}
						</div>
						
						{if $form.time_from && $form.time_to}<span class="{if $form.card_per_page eq 2}text3{else}text2{/if}"><b>HAPPY HOUR</b></span>{/if}
						{if (!$r.member_price || !$config.membership_module) && !$r.non_member_price}
						<span class="{if $form.card_per_page eq 2}text2{else}text1{/if} price">{$config.arms_currency.symbol} {$r.selling_price|number_format:2}</span>
						{/if}
					</div>
					
					<div class="div_card_items">
						<div style="line-height:30px;">
							<span class="{if $form.card_per_page eq 2}text5{else}text4{/if}">{$form.date_from} - {$form.date_to}</span>
							{if $form.time_from && $form.time_to}<span class="{if $form.card_per_page eq 2}text5{else}text4{/if}">{$form.time_from} - {$form.time_to}</span>{/if}
						</div>
						<div style="line-height:18px;">
							<span>ARMS Code: {$r.sku_item_code}</span>
							{if $r.mcode}<span>Mcode: {$r.mcode}</span>{/if}
							<span>Description: {$r.description}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	{/foreach}
{elseif $form.printing_format eq 'format2'}
	{foreach from=$promo_items item=r name=pi}
		<div class="printarea {if $form.card_per_page eq 2}landscape {else} portrait{/if}">
			<div class="div_card_background_img {if $form.card_per_page eq 2 && $smarty.foreach.pi.iteration % 2} landscape_border{/if}" style="background-image: url('{$form.promo_pop_photo}');">
				<div class="div_card_content">
				
					<div class="div_card_items">
						<span class="{if $form.card_per_page eq 2}text3{else}text2{/if}" style="text-align:center;">
							{if $form.title}
							<div style="border: 2px solid black;z-index: 100;position: relative;{if !$r.member_price && !$r.non_member_price}width: 70%;margin: 0 15%;background-color: #2fbfce;{/if}">
								<b>{$form.title}</b>
							</div>
							{/if}
							{if (!$r.member_price || !$config.membership_module) && !$r.non_member_price}
							<div class="{if $form.card_per_page eq 2}text2{else}text1{/if}" style="border: 2px solid black;z-index: 90; background-color: #ffff; padding-top: 30px; margin-top: -30px;">{$config.arms_currency.symbol} {$r.selling_price|number_format:2}</div>
							{/if}
						</span>
					</div>
					
					<div class="div_card_items">
						<div style="line-height: 40px;">
							<span class="{if $form.card_per_page eq 2}text4{else}text3{/if}"><b>{$r.description}</b></span>
							<span class="{if $form.card_per_page eq 2}text5{else}text4{/if}">{$r.sku_item_code}{if $r.mcode} / {$r.mcode}{/if}</span>
						</div>
						
						
						<div style="display: block ruby;">
							{if $config.membership_module}
								{if $form.member_discount eq '' || $form.member_discount eq 'member'}
								<div style="padding: 0px 15px">
									{if $r.member_price}
										<span class="{if $form.card_per_page eq 2}text3{else}text2{/if} text_italic" style="text-align: left;">{$config.arms_currency.symbol}</span>
										<div class="{if $form.card_per_page eq 2}text2{else}text1{/if}" style="padding: 0px 15px">{$r.member_price|number_format:2}</div>
									{/if}
									<span class="{if $form.card_per_page eq 2}text5{else}text4{/if}">Member {if $r.use_non_member_disc && ($form.member_discount eq '' || $form.member_discount eq 'non_member')}& Non-Member{/if}</span>
									{if !$r.member_price}
										<span class="{if $form.card_per_page eq 2}text2{else}text1{/if}">{$r.member_disc_p}</span>
										<span class="{if $form.card_per_page eq 2}text4{else}text3{/if}"><b>Discount</b></span>
									{/if}
								</div>
								{/if}
							{/if}
							
							{if ($form.member_discount eq '' || $form.member_discount eq 'non_member') && !$r.use_non_member_disc}
							<div style="padding: 0px 15px">
								{if $r.non_member_price}
									<span class="{if $form.card_per_page eq 2}text3{else}text2{/if} text_italic" style="text-align: left;">{$config.arms_currency.symbol}</span>
									<div class="{if $form.card_per_page eq 2}text2{else}text1{/if}" style="padding: 0px 15px">{$r.non_member_price|number_format:2}</div>
								{/if}
								{if $r.non_member_price || $r.non_member_disc_p}<span class="{if $form.card_per_page eq 2}text5{else}text4{/if}">Non-Member</span>{/if}
								
								{if !$r.non_member_price}
									<span class="{if $form.card_per_page eq 2}text2{else}text1{/if}">{$r.non_member_disc_p}</span>
									<span class="{if $form.card_per_page eq 2}text4{else}text3{/if}"><b>Discount</b></span>
								{/if}
							</div>
							{/if}
						</div>
					</div>
					
					<div class="div_card_items_bottom">
						{if $form.time_from && $form.time_to}<span class="{if $form.card_per_page eq 2}text3{else}text2{/if}"><b>HAPPY HOUR</b></span>{/if}
						<span class="{if $form.card_per_page eq 2}text5{else}text4{/if}">{$form.date_from} - {$form.date_to}</span>
						{if $form.time_from && $form.time_to}<span class="{if $form.card_per_page eq 2}text5{else}text4{/if}">{$form.time_from} - {$form.time_to}</span>{/if}
					</div>
					
				</div>
			</div>
		</div>
	{/foreach}
{/if}