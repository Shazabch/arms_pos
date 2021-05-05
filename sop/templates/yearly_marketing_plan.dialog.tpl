<form name="f_marketing_plan" method="post" onSubmit="return false;">
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="a" value="save_marketing_plan" />
    
	<h4>General Informations</h4>
	<div class="stdframe ui-corner-all" style="background:#fff">
		<table>
		    <tr>
		        <td width="100"><b>Title</b></td>
		        <td>
		            <input type="text" name="title" value="{$form.title}" maxlength="100" size="50" title="Marketing Plan Title" class="required" />
		            <img align="absbottom" title="Required Field" src="/ui/rq.gif">
		        </td>
		    </tr>
		    <tr>
		        <td><b>Year</b></td>
		        <td>
					<input type="text" name="year" value="{$form.year}" maxlength="4" size="5" onChange="miz(this);" title="Marketing Plan Year" class="required r" />
			        <img align="absbottom" title="Required Field" src="/ui/rq.gif">
		        </td>
		    </tr>
		    <tr>
		        <td><b>Date</b></td>
		        <td>
		            <input name="date_from" size="12" value="{$form.date_from}" readonly title="Date From" class="required" />
		            <img align="absbottom" title="Required Field" src="/ui/rq.gif">
					&nbsp;&nbsp;&nbsp;<b>to</b>&nbsp;&nbsp;&nbsp;
					<input name="date_to" size="12" value="{$form.date_to}" readonly title="Date To" class="required" />
					<img align="absbottom" title="Required Field" src="/ui/rq.gif">
		        </td>
		    </tr>
		    <tr>
		        <td valign="top"><b>Remark</b></td>
		        <td><textarea name="remark" cols="50" rows="4">{$form.remark}</textarea></td>
		    </tr>
		</table>
	</div>
</form>
