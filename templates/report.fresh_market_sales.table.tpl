{*
2/11/2011 3:58:24 PM Andy
- Add color for those sales without stock take.
- Add cost, gp and gp % for sales without stock take.
*}

{assign var=rowspan value=2}
{assign var=no_sc_span value=1}
{if $sessioninfo.show_report_gp}
    {assign var=rowspan value=$rowspan+4}
    {assign var=no_sc_span value=$no_sc_span+2}
{/if}
{if $sessioninfo.show_cost}
    {assign var=rowspan value=$rowspan+2}
    {assign var=no_sc_span value=$no_sc_span+1}
{/if}

{foreach from=$category key=cid item=cat}
	{if $smarty.request.include_zero_sales or $cat_data.$cid}
	    {assign var=cat_row value=$cat_data.$cid}
	    
	    <!-- pre-calculate contribute % -->
	    {assign var=contrib_per value=0}
		{if $cat_data.total.pos.total.amt}
		    {assign var=contrib_per value=$cat_row.pos.total.amt/$cat_data.total.total.amt*100}
		{/if}
		<tbody id="tbody_cat-{$cid}">
		<tr>
		    <td rowspan="{$rowspan}">
	            {if !$no_header_footer}
	                <img src="/ui/icons/table.png" align="absmiddle" onclick="show_sku('{$cid}', this);" title="Show SKU" />

	                {if $smarty.request.ajax}
						<img src="/ui/pixel.gif" width="{$smarty.request.indent*16}" height="1">
					{/if}
	            {/if}
	            <b>
					{if $cat.have_subcat}
						{if !$no_header_footer}
							<img onclick="expand_sub('{$cid}','{$smarty.request.indent+1}',this,'{$contrib_per}');" src="/ui/expand.gif" />
							<a href="javascript:void(show_sub('{$cid}'))">
						{/if}
						{$cat.description}
						{if !$no_header_footer} </a>{/if}

					{else}
					    {$cat.description}
					{/if}
				</b>
			</td>
			<td>Amt</td>
			{foreach from=$date_cols key=dt item=d}
			    <td align="right" title="Amount">{$cat_row.pos.$dt.amt|number_format:2|ifzero:'&nbsp;'}</td>
			{/foreach}
			
			<!-- Total -->
			<td align="right" title="Amount">{$cat_row.pos.total.amt|number_format:2|ifzero:'&nbsp;'}</td>
			
			<!-- Contribute % -->
			<td rowspan="{$rowspan-$no_sc_span}" align="right" title="Contribution %">{$contrib_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
		</tr>
		{if $sessioninfo.show_cost}
		    <!-- Cost -->
		    <tr>
			    <td>Cost</td>
				{foreach from=$date_cols key=dt item=d}
				    <td align="right" title="Cost">{$cat_row.pos.$dt.cost|number_format:2|ifzero:'&nbsp;'}</td>
				{/foreach}

				<!-- Total -->
				<td align="right" title="Cost">{$cat_row.pos.total.cost|number_format:2|ifzero:'&nbsp;'}</td>
			</tr>
		{/if}
		{if $sessioninfo.show_report_gp}
		    <!-- GP -->
			<tr>
			    <td>GP</td>
				{foreach from=$date_cols key=dt item=d}
				    {assign var=amt value=$cat_row.pos.$dt.amt|round2}
					{assign var=cost value=$cat_row.pos.$dt.cost|round2}
				    {assign var=gp value=$amt-$cost}
				    <td align="right" title="GP">{$gp|number_format:2|ifzero:'&nbsp;'}</td>
				{/foreach}

				<!-- Total -->
				{assign var=amt value=$cat_row.pos.total.amt|round2}
				{assign var=cost value=$cat_row.pos.total.cost|round2}
			    {assign var=gp value=$amt-$cost}
				<td align="right" title="GP">{$gp|number_format:2|ifzero:'&nbsp;'}</td>
			</tr>
			<!-- GP %-->
			<tr>
			    <td>GP%</td>
				{foreach from=$date_cols key=dt item=d}
				    {assign var=gp_per value=0}
				    {if $cat_row.pos.$dt.amt}
				        {assign var=gp value=$cat_row.pos.$dt.amt-$cat_row.pos.$dt.cost}
				        {assign var=gp_per value=$gp/$cat_row.pos.$dt.amt*100}
				    {/if}
				    <td align="right" title="GP %">{$gp_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
				{/foreach}

				<!-- Total -->
				{assign var=gp_per value=0}
			    {if $cat_row.pos.total.amt}
			        {assign var=gp value=$cat_row.pos.total.amt-$cat_row.pos.total.cost}
			        {assign var=gp_per value=$gp/$cat_row.pos.total.amt*100}
			    {/if}
			    <td align="right" title="GP %">{$gp_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
			</tr>
		{/if}
		<tr class="col_no_sc">
		    <td>amt w/o stk</td>
			{foreach from=$date_cols key=dt item=d}
			    <td align="right" title="Amount without stock check">{$cat_row.no_sc_pos.$dt.amt|number_format:2|ifzero:'&nbsp;'}</td>
			{/foreach}
			
			<!-- Total -->
			<td align="right" title="Amount without stock check">{$cat_row.no_sc_pos.total.amt|number_format:2|ifzero:'&nbsp;'}</td>
			
			<!-- Contribute % -->
			{assign var=contrib_per value=0}
			{if $cat_data.total.no_sc_pos.total.amt}
			    {assign var=contrib_per value=$cat_row.no_sc_pos.total.amt/$cat_data.total.total.amt*100}
			{/if}
			<td align="right" title="Contribution %" rowspan="{$no_sc_span}">{$contrib_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
		</tr>
		{if $sessioninfo.show_cost}
		    <!-- Cost -->
		    <tr class="col_no_sc">
			    <td>Cost</td>
				{foreach from=$date_cols key=dt item=d}
				    <td align="right" title="Cost">{$cat_row.no_sc_pos.$dt.cost|number_format:2|ifzero:'&nbsp;'}</td>
				{/foreach}

				<!-- Total -->
				<td align="right" title="Cost">{$cat_row.no_sc_pos.total.cost|number_format:2|ifzero:'&nbsp;'}</td>
			</tr>
		{/if}
		{if $sessioninfo.show_report_gp}
		    <!-- GP -->
			<tr class="col_no_sc">
			    <td>GP</td>
				{foreach from=$date_cols key=dt item=d}
				    {assign var=amt value=$cat_row.no_sc_pos.$dt.amt|round2}
					{assign var=cost value=$cat_row.no_sc_pos.$dt.cost|round2}
				    {assign var=gp value=$amt-$cost}
				    <td align="right" title="GP">{$gp|number_format:2|ifzero:'&nbsp;'}</td>
				{/foreach}

				<!-- Total -->
				{assign var=amt value=$cat_row.no_sc_pos.total.amt|round2}
				{assign var=cost value=$cat_row.no_sc_pos.total.cost|round2}
				{assign var=gp value=$amt-$cost}
				<td align="right" title="GP">{$gp|number_format:2|ifzero:'&nbsp;'}</td>
			</tr>
			<!-- GP %-->
			<tr class="col_no_sc">
			    <td>GP%</td>
				{foreach from=$date_cols key=dt item=d}
				    {assign var=gp_per value=0}
				    {if $cat_row.no_sc_pos.$dt.amt}
				        {assign var=gp value=$cat_row.no_sc_pos.$dt.amt-$cat_row.no_sc_pos.$dt.cost}
				        {assign var=gp_per value=$gp/$cat_row.no_sc_pos.$dt.amt*100}
				    {/if}
				    <td align="right" title="GP %">{$gp_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
				{/foreach}

				<!-- Total -->
				{assign var=gp_per value=0}
			    {if $cat_row.no_sc_pos.total.amt}
			        {assign var=gp value=$cat_row.no_sc_pos.total.amt-$cat_row.no_sc_pos.total.cost}
			        {assign var=gp_per value=$gp/$cat_row.no_sc_pos.total.amt*100}
			    {/if}
			    <td align="right" title="GP %">{$gp_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
			</tr>
		{/if}
		</tbody>
	{/if}
{/foreach}
