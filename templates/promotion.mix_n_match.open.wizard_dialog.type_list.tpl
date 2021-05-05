<h3>Please select the promotion type</h3>

<!-- Promotion type list -->
{assign var=inp_checked_pwid value=1}
{if $promo_data.pwid}
	{assign var=inp_checked_pwid value=$promo_data.pwid}
{/if}

<div style="height:300px;border:2px inset black;background-color:#fff;overflow:auto;" id="div_promotion_wizard_type_list">
	<ol>
	{foreach from=$promotion_wizard_list key=pwid item=r name=f_pwid}
		<li class="li_pwid {if $inp_checked_pwid eq $pwid}li_pw_selected{/if}" id="li_pwid-{$pwid}">
			<input type="radio" name="promotion_wizard_id" value="{$pwid}" {if $inp_checked_pwid eq $pwid}checked {/if} onChange="MIX_MATCH_MAIN_WIZARD_DIALOG.check_promotion_wizard_type();" />
			{$r.title}
		</li>
	{/foreach}
	</ol>
</div>
	
<br />

<!-- description -->
<div style="height:150px;border:1px solid black;background-color:#fff;overflow:auto;" id="div_promotion_wizard_description_list">
	{foreach from=$promotion_wizard_list key=pwid item=r name=f_pwid}
		<div id="div_promotion_wizard_description-{$pwid}" style="{if $inp_checked_pwid ne $pwid}display:none;{/if}" class="div_promotion_wizard_description">
			{$r.desc|nl2br}
		</div>
	{/foreach}
</div>