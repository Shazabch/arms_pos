{include file='header.tpl'}

<script>

var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var STVR = {
	show_report : function() {
	
		if (document.selection.date.value == '') {
			$('div_table_frame').hide();
			alert('Please select date');
			return;
		}
		
		$('div_table_frame').show();
		$('div_table').update(_loading_).show();
		
		new Ajax.Updater("div_table",phpself,{
			parameters: document.selection.serialize(),
			onComplete: function(){}
		});
	}
};

{/literal}
</script>

<h1>Stock Take Variance Report</h1>

<div class="stdframe">
<form name="selection">

	<input type=hidden name=a value=load_report />

	<table>
		<tr>
			<td><b>Date:</b></td>
			<td>
					<select name="date" style="width:100%;">
						{foreach from=$date item=d}
							<option value="{$d}">{$d}</option>
						{/foreach}
					</select>
			</td>
			<td>&nbsp;&nbsp;&nbsp;</td>
			<td><b>Sort by:</b></td>
			<td>
					<select name="sort_by" style="width:100%;">
							<option value="category">Category</option>
							<option value="description">SKU Description</option>
					</select>
			</td>
			<td>&nbsp;&nbsp;<input type="button" value="Show Report" onclick="STVR.show_report();" /></td>
		</tr>
	</table>
</form>
</div>

<br />

<div id="div_table_frame" style="display:none;"><div id="div_table"></div></div>

{include file='footer.tpl'}
