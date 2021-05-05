{*
12/13/2019 10:30 AM Andy
- Enhanced to hide mobile screen selection if no config.
*}
<div style="background-color: #fff;">
	<div style="background-color: #fff; color: black; font-weight: normal; border: 5px outset red;margin: 20px;">
		<ul>
			<li> Push notifications have length limits imposed by Apple and Google.</li>
			<li> On IOS, the messages in notification center get truncated around 110 characters or 4 lines.</li>
			<li> On Android, The limit is between about 30 and 40 characters, depending on the screen size of the user’s device.</li>
			<li> Your push text should be short and engaging, with clear value to engage the user and prompt them to open your app. Based on the limits above, it’s best to keep your push copy to just a few words.</li>
		</ul>
	</div>
	<form name="f_pn" onSubmit="return false;">
		<input type="hidden" name="pn_guid" />
		
		<b>Push Title: </b><br />
		<input type="text" name="pn_title" style="width:200px;" maxlength="200" /><br />
		<b>Push Text: </b><br />
		<textarea name="pn_msg" style="width:100%;height:50px;" onkeyup="PN_DIALOG.pn_length_calculate();" maxlength="200"></textarea><br />
		<div style="{if !$config.membership_mobile_settings.pn_choose_screen}display:none;{/if}">
			<b>Click on Notification will bring user to Mobile Screen: </b>
			<select name="screen_tag">
				{foreach from=$appCore->memberManager->mobileAppPNScreenList key=screen_tag item=r}
					<option value="{$screen_tag}">{$r.description}</option>
				{/foreach}
			</select>
		</div>
		<p>
			Character Count: <span id="div_pn_length_count">0</span>
		</p>
	</form>
	<div style="height:25px;visibility:hidden;" id="div_pn_progress">
		<table width="100%">
			<tr>
				<td width="80%">
					<progress value="0" max="100" style="width:100%;" id="progress_pn">0 %</progress>
				</td>
				<td>
					<span id="span_pn_progress_label">
					</span>
				</td>
			</tr>
		</table>
	</div>
	<p align="center" id="p_action">
		<input type="button" onclick="PN_DIALOG.send_pn_clicked();" value="Send Push Notification" />
		<input type="button" value="Close" onClick="PN_DIALOG.close();" />
	</p>
</div>