{*
8/19/2010 3:33:21 PM Alex
- Add SKU type filter

10/15/2010 11:03:14 AM Alex
- remove sku_type filter
*}


<b class="form-label">Date</b>
<select class="form-control" name="stock_take_date" >
{if $available_date}
	<option value="">-- Please Select --</option>
	{foreach from=$available_date item=val}
		<option value="{$val.date}">{$val.date}</option>
	{/foreach}
{else}
	<option value="">-- No Data --</option>
{/if}
</select>
&nbsp;&nbsp;&nbsp;

{*
<span id="div_{$im_re}_stock_take_sku">
    <b class="form-label">SKU Type</b>
	<select class="form-control" name='sku_type'>
	    <option value=''>-- No Data --</option>
	</select>
</span>
*}
