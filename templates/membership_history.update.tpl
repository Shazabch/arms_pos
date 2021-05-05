{*
10/29/2010 2:38:30 PM Justin
- Added a new config to show adjustment section only.

4/26/2012 5:00:32 PM Justin
- Fixed bug of showing "1970-01-01" on Renew Member Card when there is no renewal history.

9/3/2012 3:44 PM Justin
- Enhanced to have date for all date fields.

10/24/2012 6:17 PM Justin
- Enhanced to have upgrade card feature base on config "membership_enable_additional_features".

2/8/2013 3:48 PM Justin
- Enhanced to show/hide point adjustment section while found new config + privilege is set.

5/24/2013 9:46 AM Justin
- Enhanced to cover all functions to have boxes to prevent confusion on clicking button.

1/23/2017 3:19 PM Andy
- Enhance Calendar to block user to select date more than 2037-12-31.

06/29/2020 11:23 AM Sheila
- Updated button css.
*}
<div style="padding:10px;">
{if $sessioninfo.privilege.MEMBERSHIP_TOPEDIT}
{if !$config.membershp_allow_adjustment_only_at_backend || $config.membership_allow_add_at_backend}
{if $config.membership_enable_additional_features}

<div class="div_upd">
<h4>Upgrade Card</h4>
<form method="post" action="membership.php?t=history" onsubmit="return confirm('Confirm upgrade the current card with '+this.card_no.value)">
<input type="hidden" name="a" value="upgrade_card">
<input type="hidden" name="nric" value="{$form.nric}">
<p>
Enter new card number <input name="card_no" size={$config.membership_length}  maxlength={$config.membership_length} onchange="uc(this)"> <input class="btn btn-primary" type="submit" value="Change">
</p>
</form>
</div>
<br />
{/if}
<div class="div_upd">
<form method="post" action="membership.php?t=history" onsubmit="return confirm('Confirm replace the current card with '+this.card_no.value)">
<input type="hidden" name="a" value="change_card">
<input type="hidden" name="nric" value="{$form.nric}">
<p>
<h4>Change Card / Replacement</h4>
Enter new card number <input name="card_no" size={$config.membership_length}  maxlength={$config.membership_length} onchange="uc(this)"> <input class="btn btn-primary" type="submit" value="Change">
</p>
</form>
</div>
<br />
{/if}
{/if}
{if $sessioninfo.privilege.MEMBERSHIP_TOPEDIT}
{if $config.membership_allow_add_at_backend}
{literal}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
{/literal}

<div class="div_upd">
<h4>Renew Member Card</h4>
<form name="frenew" method="post" action="membership.php?t=history" onsubmit="return false;">
<input type="hidden" name="a" value="renew_card">
<input type="hidden" name="nric" value="{$form.nric}">
Expiry Date <input name="renew_expiry_date" id="renew_expiry_date" value="{$form.next_expiry_date|default:$smarty.now|date_add:'+1 year'|date_format:'%d/%m/%Y'}"> <img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Next Expiry Date"> (Max {$MAX_MYSQL_DATETIME})
<input type="submit" class="btn btn-primary" value="Renew Card" onclick="if(confirm('Click OK to confirm the renewal.')) form.submit();">
</form>
</div>
<br />

<div class="div_upd">
<h4>New Member Card</h4>
<form name="fnew" method="post" action="membership.php?t=history" onsubmit="return false;">
<input type="hidden" name="a" value="new_card">
<input type="hidden" name="nric" value="{$form.nric}">
<table>
<tr><td>
Enter new card number</td><td><input name="card_no" size="{$config.membership_length}" maxlength="{$config.membership_length}" onchange="validate_newcard(this)"> <span id="card_check" class="small"></span>
</td></tr>
<tr><td>
Issue Date</td><td><input name="issue_date" id="issue_date" size="10" value="{$issue_date|default:$smarty.now|date_format:'%d/%m/%Y'}"> <img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Issue Date"> (Max {$MAX_MYSQL_DATETIME})
</td></tr>
<tr><td>
Expiry Date</td><td><input name="expiry_date" id="expiry_date" size="10" onchange="uc(this)" value="{$smarty.now|date_add:'+1 year'|date_format:'%d/%m/%Y'}"> <img align="absmiddle" src="ui/calendar.gif" id="t_added3" style="cursor: pointer;" title="Select Next Expiry Date"> (Max {$MAX_MYSQL_DATETIME})
</td></tr>
</table>
<input class="btn btn-primary" id="submit_new" type="submit" value="Update New Card" disabled onclick="form.submit();">
</form>
</div>
<br>
{/if}


{if $config.membership_adjustment_with_privilege && !$sessioninfo.privilege.MEMBERSHIP_ADD_ADJUSTMENT}
<!-- show nth because no privilege -->
{elseif $config.membershp_allow_adjustment_only_at_backend || $config.membership_allow_add_at_backend}
<div class="div_upd">
<form name="fpoint" method="post" action="membership.php?t=history" onsubmit="return false;">
<input type="hidden" name="a" value="adjust_point">
<input type="hidden" name="nric" value="{$form.nric}">
<h4>Point Adjustment</h4>
<table cellpadding=2 cellspacing=0 border=0>
<tr>
<td>Point Adjustment</td><td><input name="points"></td>
</tr>
<tr valign="top"><td>Remark</td><td><textarea name="remark" rows="3" cols="40"></textarea></td></tr>
</table>
<input class="btn btn-primary" type="submit" value="Update" onclick="if (validate_point(document.fpoint)) if(confirm('Click OK to confirm.')) fpoint.submit();">
</form>
</div>
{/if}
{/if}
</div>
{if $sessioninfo.privilege.MEMBERSHIP_TOPEDIT && $config.membership_allow_add_at_backend}
{literal}
<script type="text/javascript">
	var max_date = new Date(2037,11,31);
	
    Calendar.setup({
        inputField     :    "renew_expiry_date",     // id of the input field
        ifFormat       :    "%d/%m/%Y",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true,
		dateStatusFunc :    check_exceed_max_timestamp
    });
    Calendar.setup({
        inputField     :    "issue_date",     // id of the input field
        ifFormat       :    "%d/%m/%Y",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true,
		dateStatusFunc :    check_exceed_max_timestamp
    });
    Calendar.setup({
        inputField     :    "expiry_date",     // id of the input field
        ifFormat       :    "%d/%m/%Y",      // format of the input field
        button         :    "t_added3",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true,
		dateStatusFunc :    check_exceed_max_timestamp
    });
</script>
{/literal}
{/if}