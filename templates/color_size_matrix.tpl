{*
8/16/2010 2:31:53 PM Alex
- Add checking to add the "do_matrix" button.

3/24/2014 5:56 PM Justin
- Modified the wording from "Color" to "Colour".

05/15/2020 06:08 PM Sheila
- Updated button color
*}

{literal}
<style>
.input_matrix td input {
	width:60px;
	font-size:10px;
	padding: 2px;
	border:1px solid #000;
}
.input_matrix [alt="none"] {
	border:1px solid black;
	width: 50;
}
.input_matrix td input.ntp { /* price */
	background-color:#ff9;
}
.input_matrix [alt="size"] { /* size */
	background-color:#9f9;
	font-weight:bold;
	border:1px solid black;
}
.input_matrix  [alt="colour"] { /* color */
	text-align:center;
	background-color:#9f9;
	font-weight:bold;
	border:1px solid black;
}

</style>
{/literal}

{if $errm.color_size}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.color_size item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<h2>{$description}</h2>

{if !$hideheader}
<form name=f_t>

<div style="height:400px; border:1px solid #ccc; overflow:auto;" id=div_size_color>
{/if}


{if $color}

{foreach from=$branch key=b_id item=b_code}
	<h3>{$b_code}</h3>
	{if $sku_item_id}
		<table class='input_matrix' cellspacing=2>

			{foreach from=$size key=no_s item=si }
			<tr>
			    {if $no_s == 0}
					<td rowspan=2 bgcolor='#00dddd' alt="none">{$si}</td>
				{elseif $no_s >= 2}
					{if $si == 'empty'}
						<td alt='size'>(no_size)</td>
				    {else}
						<td alt='size'>{$si}</td>
					{/if}
				{/if}

				{foreach from=$color key=no_co item=co }
					{if $no_s==0}
						{if $co =='empty'}
							<td alt='colour' {if $type eq 'po'}colspan=2{/if}>(no_colour)</td>
						{else}
							<td alt='colour' {if $type eq 'po'}colspan=2{/if}>{$co}</td>
		            	{/if}
					{elseif $no_s==1}
						<td alt='colour' class='r'>Qty</td>
	{if $type eq 'po'}	<td alt='colour' class='r'>Foc</td>  {/if}
		         	{else}
					    {if !$sku_item_id.$b_id.$si.$co.id}
							<td alt="no_data">{if $type eq 'po'}</td><td alt="no_data"></td>{/if}
						{else}
							<td><input type=text size=5 value="" class="ntp" name="qty[{$sku_item_id.$b_id.$si.$co.id}][{$b_id}]"></td>
	{if $type eq 'po'}		<td><input type=text size=5 value="" class="ntp" name="foc[{$sku_item_id.$b_id.$si.$co.id}][{$b_id}]"></td> {/if}
						{/if}
					{/if}
				{/foreach}
			</tr>
			{/foreach}
		</table>
		<br>
	{else}
		- no SKU found/SKU Items is blocked -
	{/if}
{/foreach}
{else}
<ul>
	{if !$hideheader}
	<li> - no SKU found for this vendor -
	{else}
	<li> - no varieties -
	{/if}

</ul>
{/if}
{if !$hideheader}
</div>
</form>
<div align=center>
<p>
{if $color}<input type=button value="Add" onclick="do_matrix()">&nbsp;&nbsp;{/if}
<input class="btn btn-warning" type=button value="Close" onclick="cancel_matrix()">
</p>
</div>
{/if}

