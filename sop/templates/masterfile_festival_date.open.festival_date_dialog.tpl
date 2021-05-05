<form name="f_festival_date_dialog" onSubmit="return false;">
    <input type="hidden" name="a" value="save_festival_date" />
	<input type="hidden" name="id" value="{$festival_date.id}" />
	<input type="hidden" name="year" value="{$festival_date.year}" />
	
	<table>
	    <tr>
	        <td><b>Title</b></td>
	        <td><input type="text" name="title" value="{$festival_date.title}" size="50" class="required" title="Title" maxlength=50" />
	    </tr>
	    <tr>
	        <td><b>Date</b></td>
	        <td>
	            <input name="date_from" size="12" value="{$festival_date.date_from}" readonly title="Date From" class="required" />
	            <img align="absbottom" title="Required Field" src="/ui/rq.gif" />
				&nbsp;&nbsp;&nbsp;<b>to</b>&nbsp;&nbsp;&nbsp;
				<input name="date_to" size="12" value="{$festival_date.date_to}" readonly title="Date To" class="required" />
				<img align="absbottom" title="Required Field" src="/ui/rq.gif" />
	            
	        </td>
	    </tr>
	</table>
</form>
