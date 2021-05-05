{include file='header.tpl'}
<form name="f_c" method="post">
	<input type="hidden" name="a" value="clear_data" />
	<input type="submit" value="Clear Data" />
</form>

<form name="f_a" method="post" enctype="multipart/form-data">
	<input type="hidden" name="a" value="import_sb">
	Select csv file <input type="file" name="sb">
	<br />
	<input type="submit" value="Submit">
</form>

{if $row_imported} {$row_imported} rows imported.{/if}
{if $duplicated}
	<ul>
	{foreach from=$duplicated key=k item=d}
	    <li>{$k} : {$d}</li>
	{/foreach}
	</ul>
{/if}

{if $available_date}
<form name="f_b">
	<input type="hidden" name="a" value="compare_sb">
	Compare<br>
	Stock Balance 1
	<select name="sb1">
	    {foreach from=$available_date item=r}
	        {capture var=from_date}{$r.from_date},{$r.to_date}{/capture}
	        <option value="{$r.from_date},{$r.to_date}" {if $smarty.request.sb1 eq "`$r.from_date`,`$r.to_date`"}selected {/if}>From {$r.from_date} to {$r.to_date}</option>
	    {/foreach}
	</select><br>
	Stock Balance 2
	<select name="sb2">
	    {foreach from=$available_date item=r}
	        {capture var=from_date}{$r.from_date},{$r.to_date}{/capture}
	        <option value="{$r.from_date},{$r.to_date}" {if $smarty.request.sb2 eq "`$r.from_date`,`$r.to_date`"}selected {/if}>From {$r.from_date} to {$r.to_date}</option>
	    {/foreach}
	</select><br>
	Compare
	<input type="radio" name="compare_type" value="opening" {if $smarty.request.compare_type eq 'opening'}checked {/if} /> Opening &nbsp;&nbsp;
	<input type="radio" name="compare_type" value="closing" {if $smarty.request.compare_type eq 'closing'}checked {/if} /> Closing &nbsp;&nbsp;
	<input type="radio" name="compare_type" value="closing_opening" {if $smarty.request.compare_type eq 'closing_opening'}checked {/if} /> Closing to Opening &nbsp;&nbsp;
	<br>
	<input type="submit" value="Submit">
</form>
{/if}

{if $table}
	<table border="1" width="100%">
	    <tr>
	        <th rowspan="2">No.</th>
	        <th rowspan="2"></th>
	        <th rowspan="2">ARMS Code</th>
	        <th colspan="2">{$smarty.request.sb1}</th>
	        <th colspan="2">{$smarty.request.sb2}</th>
	    </tr>
	    <tr>
	        <th>Closing Qty</th>
	        <th>Closng Val</th>
	        <th>Opening Qty</th>
	        <th>Opening Val</th>
	    </tr>
	    {foreach from=$table item=r name=f}
	        <tr {if $r.diff}style="color:red;"{/if}>
	            <td>{$smarty.foreach.f.iteration}</td>
	            <td>{if $r.diff}diff{/if}
	            <td>{$r.sku_item_code}</td>
	            {if $smarty.request.compare_type eq 'closing_opening'}
		            <td>{$r.sb1.closing_bal}</td>
		            <td>{$r.sb1.closing_bal_val}</td>
		            <td>{$r.sb2.open_bal}</td>
		            <td>{$r.sb2.open_bal_val}</td>
				{elseif $smarty.request.compare_type eq 'closing'}
				    <td>{$r.sb1.closing_bal}</td>
		            <td>{$r.sb1.closing_bal_val}</td>
		            <td>{$r.sb2.closing_bal}</td>
		            <td>{$r.sb2.closing_bal_val}</td>
                {elseif $smarty.request.compare_type eq 'opening'}
                    <td>{$r.sb1.open_bal}</td>
		            <td>{$r.sb1.open_bal_val}</td>
		            <td>{$r.sb2.open_bal}</td>
		            <td>{$r.sb2.open_bal_val}</td>
	            {/if}
	        </tr>
	    {/foreach}
	    <tr>
	        <th colspan="3">Total</th>
	        {if $smarty.request.compare_type eq 'closing_opening'}
		        <th>{$total.sb1.closing_bal}</th>
		        <th>{$total.sb1.closing_bal_val}</th>
		        <th>{$total.sb2.open_bal}</th>
		        <th>{$total.sb2.open_bal_val}</th>
			{elseif $smarty.request.compare_type eq 'closing'}
			    <td>{$total.sb1.closing_bal}</td>
	            <td>{$total.sb1.closing_bal_val}</td>
	            <td>{$total.sb2.closing_bal}</td>
	            <td>{$total.sb2.closing_bal_val}</td>
            {elseif $smarty.request.compare_type eq 'opening'}
                <td>{$total.sb1.open_bal}</td>
	            <td>{$total.sb1.open_bal_val}</td>
	            <td>{$total.sb2.open_bal}</td>
	            <td>{$total.sb2.open_bal_val}</td>
	        {/if}
	    </tr>
	</table>

	Total {$diff|number_format} different.
{/if}

{include file='footer.tpl'}
