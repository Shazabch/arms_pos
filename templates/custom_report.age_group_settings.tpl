<form name="f_r" onSubmit="return false;" method="post">
	<input type="hidden" name="id" value="{$form.id}" />
	<h2>Age Group Settings</h2>
	<br>
	
	<div id="div_age_range_list">
	{foreach from=$form.age_group.range.age key=k item=r}
		<div style="margin-bottom: 3px;">
			Less than or equal (<=) <input name="age_group[range][age][]" onChange="this.value=int(round(this.value, 3))" type="text" value="{$r}" maxlength="3" size="3" />
			Description <input name="age_group[range][desc][]" value="{$form.age_group.range.desc.$k}" type="text" />
			<a href="#" onClick="REPORT_BUILDER.remove_range(this.parentNode)"><img src="ui/icons/cancel.png" /></a>
		</div>
	{/foreach}
	</div>
	<a href="#" onClick="REPORT_BUILDER.add_new_range();">Add age range</a>
	<div>Other Age Description (<a href="javascript:void(alert('Age Description will use word (Other) to replace when this field is empty.'))">?</a>) <input name="age_group[other]" type="text" value="{$form.age_group.other}" /></div>

	<p align="center">
		<input type="button" id="btn_save" value="Save" onClick="REPORT_BUILDER.update_age_group_clicked();" style="font:bold 20px Arial; background-color:#f90; color:#fff;">
		<input type="button" onClick="curtain_clicked();" value="Close" style="font:bold 20px Arial; background-color:#09f; color:#fff;">
	</p>
</form>