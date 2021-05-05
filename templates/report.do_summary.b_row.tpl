{*
5/27/2015 3:30 PM Justin
- Enhanced to have GST information.

2/26/2016 9:50 AM Qiu Ying
- show empty column when at least one do is under gst
*}

{assign var=all_total value=0}
{assign var=trig value=0}
{foreach from=$branch key=b item=a}
	{foreach from=$do_branch key=d item=e}
		{if $a.$d}
			<tr bgcolor=#ffff99 class={if $smarty.request.count}total{else}b_{$date}{/if}>
				{if $trig eq 0}<td rowspan={$a.row}>{$a.branch_name}</td>{/if}
				<td >{$e.$b.do_branch_name}</td>
				<td class='r'>{$e.$b.qty.transfer|ifzero:"-"}</td>
				{if $is_under_gst}
					<td class='r'>{$e.$b.gross_amt.transfer|number_format:2|ifzero:"-"}</td>
					<td class='r'>{$e.$b.gst_amt.transfer|number_format:2|ifzero:"-"}</td>
				{else}
					{if $at_least_one_under_gst}
						<td class='r'>{$e.$b.gross_amt.transfer|number_format:2|ifzero:"-"}</td>
						<td class='r'>-</td>
					{/if}
				{/if}
			    <td class='r'>{$e.$b.amt.transfer|number_format:2|ifzero:"-"}</td>
				<td class='r'>{$e.$b.qty.open|ifzero:"-"}</td>
				{if $is_under_gst}
					<td class='r'>{$e.$b.gross_amt.open|number_format:2|ifzero:"-"}</td>
					<td class='r'>{$e.$b.gst_amt.open|number_format:2|ifzero:"-"}</td>
				{else}
					{if $at_least_one_under_gst}
						<td class='r'>{$e.$b.gross_amt.open|number_format:2|ifzero:"-"}</td>
						<td class='r'>-</td>
					{/if}
				{/if}
				<td class='r'>{$e.$b.amt.open|number_format:2|ifzero:"-"}</td>
                <td class='r'>{$e.$b.qty.credit_sales|ifzero:"-"}</td>
				{if $is_under_gst}
					<td class='r'>{$e.$b.gross_amt.credit_sales|number_format:2|ifzero:"-"}</td>
					<td class='r'>{$e.$b.gst_amt.credit_sales|number_format:2|ifzero:"-"}</td>
				{else}
					{if $at_least_one_under_gst}
						<td class='r'>{$e.$b.gross_amt.credit_sales|number_format:2|ifzero:"-"}</td>
						<td class='r'>-</td>
					{/if}
				{/if}
				<td class='r'>{$e.$b.amt.credit_sales|number_format:2|ifzero:"-"}</td>

				{assign var=row_qty value=$e.$b.qty.transfer+$e.$b.qty.open+$e.$b.qty.credit_sales}
				{assign var=row_total value=$e.$b.amt.transfer+$e.$b.amt.open+$e.$b.amt.credit_sales}
				{assign var=row_gross_total value=$e.$b.gross_amt.transfer+$e.$b.gross_amt.open+$e.$b.gross_amt.credit_sales}
				{assign var=row_gst_total value=$e.$b.gst_amt.transfer+$e.$b.gst_amt.open+$e.$b.gst_amt.credit_sales}
				<td class='r'>{$row_qty|ifzero:"-"}</td>
				{if $is_under_gst}
					<td class='r'>{$row_gross_total|number_format:2|ifzero:"-"}</td>
					<td class='r'>{$row_gst_total|number_format:2|ifzero:"-"}</td>
				{else}
					{if $at_least_one_under_gst}
						<td class='r'>{$row_gross_total|number_format:2|ifzero:"-"}</td>
						<td class='r'>-</td>
					{/if}
				{/if}
				<td class='r'>{$row_total|number_format:2|ifzero:"-"}</td>
			</tr>
			{assign var=trig value=$trig+1}
			{if $trig eq $a.row}{assign var=trig value=0}{/if}
		{/if}
		
	{/foreach}

{/foreach}
