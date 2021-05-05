{*

29/04/2020 04:29 PM Sheila
- Modified layout to compatible with new UI.

*}

<style>
{literal}
.keyword{
	font-weight: bold;
	color: blue;
}
li.li_pwid:hover{
	background-color: #ffc;
}
li.li_pw_selected{
	background-color: #9f0 !important;
}
{/literal}
</style>

<div id="div_promotion_wizard_container_body">
	<div id="div_promotion_wizard_type_list" class="div_wizard_screen">
	</div>
	
	<div id="div_promotion_wizard_disc_target_screen" style="display:none;" class="div_wizard_screen">
	</div>
</div>

<br style="clear:both;" />

<div style="float:left;" class="btn_padding">
	<button onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.back_page();" id="btn_promotion_wizard_back_screen">
		&lt; Back

	</button>
</div>

<div style="float:right;" class="btn_padding">
	<button onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.next_page();" id="btn_promotion_wizard_next_screen">
		Next
		&gt;
	</button>
</div>