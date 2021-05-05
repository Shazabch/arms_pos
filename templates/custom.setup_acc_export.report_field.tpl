{*
8/10/2017 09:51 AM Qiu Ying
- Bug fixed on input field value shown in an abnormal way when containing special characters
*}

{if !$field_area}
	{assign var=field_area value="__FIELD_AREA__"}
{/if}
{if !$field_num}
	{assign var=field_num value="__FIELD_NUM__"}
{/if}
{if !$field_type}
	{assign var=field_type value="__FIELD_TYPE__"}
{/if}
{if !$org_field_label}
	{assign var=org_field_label value="__ORG_FIELD_LABEL__"}
{/if}
{if !$field_label}
	{assign var=field_label value="__FIELD_LABEL__"}
{/if}
{if !$field_label_type}
	{assign var=field_label_type value="__FIELD_LABEL_TYPE__"}
{else}
	{assign var=field_label_type value=$field_label_type}
{/if}
{if !$field_desc}
	{assign var=field_desc value="__FIELD_DESC__"}
{/if}
{if !$field_active}
	{assign var=field_active value="__FIELD_ACTIVE__"}
{/if}
{if !$field_cancel}
	{assign var=field_cancel value="__FIELD_CANCEL__"}
{/if}
{if !$field_value}
	{assign var=field_value value="__FIELD_VALUE__"}
{/if}
<li class="li_report_field" style="position:relative;" id="li_report_field-{$field_area}-{$field_num}">
	<input type="hidden" class="inp_field_label_type" name="report_fields[{$field_area}][{$field_num}][field_label_type]" value="{$field_label_type|escape:html}" />
	<input type="hidden" class="inp_field_type" name="report_fields[{$field_area}][{$field_num}][field_type]" value="{$field_type|escape:html}" />
	<input type="hidden" class="inp_org_field_label" name="report_fields[{$field_area}][{$field_num}][org_field_label]" value="{$org_field_label|escape:html}" />
	<input type="hidden" class="inp_field_label" name="report_fields[{$field_area}][{$field_num}][field_label]" value="{$field_label|escape:html}" />
	<input type="hidden" class="inp_field_desc" name="report_fields[{$field_area}][{$field_num}][field_desc]" value="{$field_desc|escape:html}" />
	<input type="hidden" class="inp_field_active" name="report_fields[{$field_area}][{$field_num}][field_active]" value="{$field_active|escape:html}" />
	<input type="hidden" class="inp_field_cancel" name="report_fields[{$field_area}][{$field_num}][field_cancel]" value="{$field_cancel|escape:html}" />
	<input type="hidden" class="inp_field_value" name="report_fields[{$field_area}][{$field_num}][field_value]" value="{$field_value|escape:html}" />
	<span class="span_label">{$field_label|escape:html}</span>
	{if !$view_only}
		<div style="top:20%;right:0;position:absolute;"><img src="ui/icons/cancel.png" class="img_delete_report_field" title="Delete" /></div>
	{/if}
</li>