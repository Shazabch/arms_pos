{if $item}
	<form name="f_e" enctype="multipart/form-data" onsubmit="return false;">
		<input type="hidden" name="a" value="delete_data">
		<input type="hidden" name="search_branch_id" value="{$form.search_branch_id}">
		<input type="hidden" name="search_date" value="{$form.search_date}">
		<input type="hidden" name="search_type" value="{$form.search_type}">
		<input type="hidden" name="search_value" value="{$form.search_value}">
		<p style="color: blue">- Click the table cell to edit data.</p>
		{if $page_num}
			<div>
				Page
				<select name="pg" onchange="STOCK_CHECK_IMPORT.change_page();">
					{section name=p start=0 loop=$page_num}
						<option value="{$smarty.section.p.index}" {if $form.pg eq $smarty.section.p.index}selected{/if}>{$smarty.section.p.index+1}</option>
					{/section}
				</select>
				<span style="margin-left: 500px">
					<input type="button" style="width: 70px" onclick="STOCK_CHECK_IMPORT.button_change_page({$form.pg-1});" {if $form.pg eq 0}disabled{/if} value="Previous">&nbsp;&nbsp;
					<input type="button" style="width: 70px" onclick="STOCK_CHECK_IMPORT.button_change_page({$form.pg+1});" {if $form.pg eq $page_num-1}disabled{/if} value="Next">
				</span>
			</div><br>
		{/if}
		<table id="content_tbl" cellpadding="4" cellspacing="0" border="1">
			<tr>
				<th><input type="checkbox" onClick="STOCK_CHECK_IMPORT.toggle_all(this);"></th>
				<th>Date</th>
				<th>Branch ID</th>
				<th>Arms Code</th>
				<th>Location</th>
				<th>Shelf No</th>
				<th>Item No</th>
				<th>Scanned By</th>
				<th>Qty</th>
				<th>Selling</th>
				<th>Cost</th>
			</tr>
			{foreach from=$item item=r name=pitem}
				{assign var=row_ind value=$smarty.foreach.pitem.index}
				<tr>
					<td><input type="checkbox" class="content_checkbox" value="{$row_ind}"></td>
					{foreach from=$r key=k item=i name=sku}
						{if $smarty.foreach.sku.index < 6}
							<td id="{$k}_{$row_ind}" style="color: #009900">{$i}</td>
						{else}
							<td style="background-color: lightyellow"><div id="{$k}_{$row_ind}" class="{$k}" onclick="STOCK_CHECK_IMPORT.edit_data(this, {$row_ind})">{$i}</div></td>
						{/if}
					{/foreach}
				</tr>
			{/foreach}
		</table><br>
		{if $page_num}
			<div>
				<span style="margin-left: 570px">
					<input type="button" style="width: 70px" onclick="STOCK_CHECK_IMPORT.button_change_page({$form.pg-1});" {if $form.pg eq 0}disabled{/if} value="Previous">&nbsp;&nbsp;
					<input type="button" style="width: 70px" onclick="STOCK_CHECK_IMPORT.button_change_page({$form.pg+1});" {if $form.pg eq $page_num-1}disabled{/if} value="Next">
				</span>
			</div><br>
		{/if}
		<input type="button" onclick="STOCK_CHECK_IMPORT.delete_data()" value="Delete Record">
	</form>
{else $error}
	<p>{$error}</p>
{/if}