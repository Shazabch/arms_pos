{if $smarty.request.a eq 'export_excel'}
	{if $smarty.request.export_type eq 'sku'}
		<table border="1">
		    <tr>
		        <th rowspan="2">ARMS Code</th>
		        <th rowspan="2">Art. No</th>
		        <th rowspan="2">Description</th>
		        <th colspan="3">Closing Stock</th>
		    </tr>
		    <tr>
		        <th>Unit Cost</th>
		        <th>Qty</th>
		        <th>Balance (RM)</th>
		    </tr>
		    {foreach from=$data item=r}
		        <tr>
		            <td>{$r.sku_item_code}</td>
		            <td>'{$r.artno}</td>
		            <td>{$r.description}</td>
		            <td>{$r.cost|number_format:2}</td>
		            <td>{$r.qty|number_format:2}</td>
		            <td>{$r.bal|number_format:2}</td>
		        </tr>
		    {/foreach}
		</table>
	{else}
        <table border="1">
		    <tr>
		        <th rowspan="2">Branch Code</th>
		        <th rowspan="2">Description</th>
		        <th colspan="2">Closing Stock</th>
		    </tr>
		    <tr>
		        <th>Qty</th>
		        <th>Balance (RM)</th>
		    </tr>
		    {foreach from=$data item=r}
		        <tr>
		            <td>{$r.branch_code}</td>
		            <td>{$r.description}</td>
		            <td>{$r.qty|number_format:2}</td>
		            <td>{$r.bal|number_format:2}</td>
		        </tr>
		    {/foreach}
		</table>
	{/if}
{else}
    <h1>{$PAGE_TITLE}</h1>

	{if $err}
		<ul style="color:red;">
		{foreach from=$err item=e}
		    <li>{$e}</li>
		{/foreach}
		</ul>
	{/if}
	<form name="f_a" method="post" enctype="multipart/form-data">
	<input type="hidden" name="a" value="import_stock" />
	Please select your csv file
	<input type="file" name="stocks" />
	<input type="submit" value="Upload" />
	<br />
	(Branch Code, ARMS Code, Unit Cost, Qty)
	</form>

	{if $data_count>0}
		<p style="color:blue;">{$data_count} rows of data in database.</p>

		<form name="f_export" method="post" style="border:1px solid #fcfcfc;background: #efefef;">
		    <input type="hidden" name="a" value="export_excel" />
		    <b>Export Type</b>
		    <input type="radio" name="export_type" value="sku" checked /> by SKU
		    <input type="radio" name="export_type" value="branch" /> by Branch
		    <br />
		    <b>Sort By</b>
		    <select name="sort_by">
		        <option value="si.artno">Art. No</option>
		        <option value="ies.sku_item_code">ARMS Code</option>
		    </select>
		    <br />
		    <input type="submit" value="Export Excel" />
		</form>
	{/if}
	
{/if}
