{*
6/9/2017 11:50 AM Justin
- Enhanced to have new status filter "Un-checkout".

7/3/2017 10:05 AM Justin
- Bug fixed on amount from SKU not in ARMS will not show when the GRA does not contain any valid GRA items.

5/8/2018 1:16 PM Justin
- Enhanced to have foreign currency feature.

12/12/2018 11:24 AM Justin
- Enhanced to add remark.

06/24/2020 4:22 PM Sheila
- Updated button css
*}

{include file='header.tpl'}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script type="text/javascript">
    {literal}
    var GRA_SUMMARY_BY_CATEGORY = {
        f: undefined,
        initialize: function(){
            this.f = document.f_a;
            Calendar.setup({
				inputField	:	"date_from",		// id of the input field
				ifFormat	:	"%Y-%m-%d",			// format of the input field
				button		:	"img_date_from",	// trigger for the calendar (button ID)
				align		:	"Bl",				// alignment (defaults to "Bl")
				singleClick	:	true
			});

			Calendar.setup({
				inputField	:	"date_to",			// id of the input field
				ifFormat	:	"%Y-%m-%d",			// format of the input field
				button		:	"img_date_to",		// trigger for the calendar (button ID)
				align		:	"Bl",				// alignment (defaults to "Bl")
				singleClick	:	true
			});
        },
        show_sub: function(root_id) {
            document.f_a.root_id.value = root_id;
            document.f_a.submit();
        },
        show_sku: function(root_id) {
            $('show_sku').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
            new Ajax.Updater('show_sku','goods_return_advice.summary_by_category.php?'+Form.serialize(document.f_a)+"&a=generate_sku_table&root_id="+root_id,{evalScripts:true});
        }
    }
    {/literal}
</script>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}
<h1>{$PAGE_TITLE}</h1>
<form class="stdframe" name="f_a">
<input type=hidden name=a value="show_report">
<input type=hidden name=root_id value="">
<table>
    <tr>
        <td><b>Date From : </b></td>
        <td>
            <input type="text" name="date_from" id="date_from" size="10" value="{$form.date_from}" readonly/>
            <img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date From" />&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td><b>Date To : </b></td>
        <td>
            <input type="text" name="date_to" id="date_to" size="10" value="{$form.date_to}" readonly/>
            <img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date To" />
        </td>
    </tr>
    <tr>
        {if $BRANCH_CODE eq "HQ"}
            <td><b>Branch: </b></td>
            <td>
                <select name="branch_id">
                    <option value="">-- ALL --</option>
                    {foreach from=$branch_list key=k item=i}
                        <option value="{$k}" {if $form.branch_id eq $k}selected{/if}>{$i}</option>
                    {/foreach}
                </select>&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
        {else}
            <input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
        {/if}
        <td><b>Status [<a href="javascript:void(alert('Un-checkout: includes Saved & Waiting Approval and Approved GRA.'));">?</a>]: </b></td>
        <td>
            <select name="status">
                <option value="">-- ALL --</option>
                {foreach from=$status_list key=k item=i}
                    <option value="{$k}" {if $form.status eq $k and $form.status ne ''}selected{/if}>{$i}</option>
                {/foreach}
            </select>&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td><b>SKU Type: </b></td>
        <td>
            <select name="sku_type">
                <option value="">-- ALL --</option>
                {foreach from=$sku_type_list item=i}
                    <option value="{$i}" {if $form.sku_type eq $i}selected{/if}>{$i}</option>
                {/foreach}
            </select>&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
    </tr>
</table>
<br>
<input class="btn btn-primary" type="submit" value="Show Report"></input>

<p>
	{if $config.foreign_currency}
		* {$LANG.BASE_CURRENCY_CONVERT_NOTICE}<br />
	{/if}
	* The Amount Is Rounding-Adjustment Exclusive.
</p>

</form>
<br>
{if $data || $extotal}
    <p>
        &#187; <a href="javascript:void(GRA_SUMMARY_BY_CATEGORY.show_sub(0));">ROOT</a> /
		{if $root_cat_info}
		    {foreach from=$root_cat_info.cat_tree_info item=ct}
		        <a href="javascript:void(GRA_SUMMARY_BY_CATEGORY.show_sub('{$ct.id}'));">{$ct.description}</a> /
		    {/foreach}
		    {$root_cat_info.description} /
		{/if}
    </p>
    {include file='goods_return_advice.summary_by_category.category.tpl'}
{else}
    {if $form.form_submit}
        <ul><li>No data</li></ul>
    {/if}
{/if}
{include file='footer.tpl'}

<script type="text/javascript">
{literal}
GRA_SUMMARY_BY_CATEGORY.initialize();
{/literal}
</script>