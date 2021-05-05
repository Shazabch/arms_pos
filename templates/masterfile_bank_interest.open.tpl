{*
11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field
*}

<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_save_interest" />
	<input type="hidden" name="id" value="{$form.id}" />
	
	<table>
	    <tr>
	        <td><b>Start from year / month</b></td>
	        <td>
	            {assign var=cur_y value=$smarty.now|date_format:'%Y'}
				<input type="text" name="year" value="{$form.year|default:$cur_y}" size="5" maxlength="4" onChange="miz(this);" />
				
				<select name="month">
		            {foreach from=$months key=m item=label}
						<option value="{$m}" {if $m eq $form.month}selected {/if}>{$label}</option>
		            {/foreach}
	            </select>
	        </td>
	    </tr>
     	<tr>
	        <td><b>Interest Rate</b></td>
	        <td>
				<input type="text" name="interest_rate" value="{$form.interest_rate}" size="5" /> % <img src="ui/rq.gif" align="absbottom" title="Required Field">
	        </td>
	    </tr>
	</table>
	<p align="center" id="p_save_f_a">
	    <input type="button" value="Save" onClick="save_interest_rate();" />
	    <input type="button" value="Close" onClick="default_curtain_clicked();" />
	</p>
</form>
