{if !$field_area}
	{assign var=field_area value="__FIELD_AREA__"}
{/if}
{if !$field_num}
	{assign var=field_num value="__FIELD_NUM__"}
{/if}
{if !$field_type}
	{assign var=field_type value="__FIELD_TYPE__"}
{/if}
{if !isset($field_formula)}
	{assign var=field_formula value="__FIELD_FORMULA__"}
{/if}
{if !$org_field_label}
	{assign var=org_field_label value="__ORG_FIELD_LABEL__"}
{/if}
{if !$field_label}
	{assign var=field_label value="__FIELD_LABEL__"}
{/if}




<li class="li_report_field" style="position:relative;" id="li_report_field-{$field_area}-{$field_num}">
	<input type="hidden" class="inp_field_type" name="report_fields[{$field_area}][{$field_num}][field_type]" value="{$field_type}" />
	<input type="hidden" class="inp_field_formula" name="report_fields[{$field_area}][{$field_num}][field_formula]" value="{$field_formula}" />
	<input type="hidden" class="inp_org_field_label" name="report_fields[{$field_area}][{$field_num}][org_field_label]" value="{$org_field_label}" />
	<input type="hidden" class="inp_field_label" name="report_fields[{$field_area}][{$field_num}][field_label]" value="{$field_label|escape:html}" />
	
	<span class="span_label">{$field_label|htmlentities}</span>
	{if $can_edit}
		<div style="top:20%;right:0;position:absolute;"><img src="ui/icons/cancel.png" class="img_delete_report_field" title="Delete" /></div>
	{/if}
</li>