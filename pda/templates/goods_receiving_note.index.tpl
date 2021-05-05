{*
8/8/2011 11:05:11 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

10/4/2011 11:05:11 AM Justin
- Modified the form layout to fill under PDA screen.

1/17/2013 2:20 PM Justin
- Enhanced to disable save button once being clicked.

7/11/2017 16:58 Qiu Ying
- Bug fixed on removing config grn_have_tax

11/04/2020 5:21 PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu) and link to module menu page

*}
{include file='header.tpl'}

<script>

{literal}
function submit_form(obj){
	obj.disabled = true;
	document.f_a.submit();
}
{/literal}
</script>
<h1>
Setting - {if $form.id}(GRN#{$form.id}){else}New GRN{/if}
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a> {if $form.find_grn || $form.find_grr} > <a href="goods_receiving_note.php?{if $form.find_grn}a=open&find_grn={$form.find_grn}{else}a=show_grr_list&find_grr={$form.find_grr}{/if}">Back to Search</a> {/if}</span>
<div style="margin-bottom:10px;"></div>

{if $form.id && $form.branch_id}{include file='goods_receiving_note.top_include.tpl'}{/if}
<h3>General Information</h3>

{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}

<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return false;">
<input type="hidden" name="a" value="save_setting" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="vendor_id" value="{$form.vendor_id}" />
<input type="hidden" name="branch_id" value="{$form.branch_id}" />
<input type="hidden" name="grr_id" value="{$form.grr_id}" />
<input type="hidden" name="rcv_date" value="{$grr.rcv_date}" />
<input type="hidden" name="grr_item_id" value="{$form.grr_item_id}" />

<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td><b>GRR No</b></td>
		<td>GRR{$grr.grr_id|string_format:"%05d"}</td>
	</tr>

	<tr>
		<td><b>GRR Amount</b></td>
		<td>{$grr.grr_amount|number_format:2}</td>
	</tr>
	
	<tr>
		<td><b>GRR Date</b></td>
		<td>{$grr.added|date_format:$config.dat_format}</td>
	</tr>

	<tr>
		<td><b>By</b></td>
		<td>{$grr.u}</td>
	</tr>

	<tr>
		<td><b>Received Date</b></td>
		<td>{$grr.rcv_date|date_format:$config.dat_format}</td>
	</tr>

	<tr>
		<td><b>By</b></td>
		<td>{$grr.rcv_u}</td>
	</tr>

	<tr>
		<td><b>Received Qty</b></td>
		<td>Ctn:{$grr.grr_ctn|qty_nf} / Pcs:{$grr.grr_pcs|qty_nf}</td>
	</tr>

	<tr>
		<td valign="top"><b>Lorry No</b></td>
		<td>{$grr.transport}</td>
	</tr>

	<tr>
		<td valign="top"><b>Vendor</b></td>
		<td colspan="3">{$grr.vendor}</td>
	</tr>
	
	<tr>
		<td><b>Department</b></td>
		<td colspan="3">
			<input type="hidden" name="department_id" value="{$form.department_id}">
			{$grr.department}
		</td>
	</tr>

	<tr>
		<td width=100 valign="top"><b>Document Type.</b></td>
		<td width=100 valign="top"><font color=blue>{$grr.type}</font></td>
	</tr>

	<tr>
		<td width=100 valign="top"><b>Document No.</b></td>
		<td width=150 valign="top"><font color=blue><input type="hidden" name="doc_no" value="{$grr.doc_no}">{$grr.doc_no}</font></td>
	</tr>

	{if $grr.type eq 'PO'}
		<tr>
			<td width="100" valign="top"><b>PO Amount</b></td>
			<td width="100" valign="top"><font color=blue>{$grr.po_amount|number_format:2}</font></td>
		</tr>

		<tr>
			<td width="100" valign="top"><b>Partial Delivery</b></td>
			<td width="150" valign="top"><font color=blue>{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}</font></td>
			<input type="hidden" name="ttl_grr_amt" value="{$grr.po_amount|round2}">
		</tr>
	{else}
		<input type="hidden" name="ttl_grr_amt" value="{$grr.grr_amount|round2}">
	{/if}
	
	{if $form.grn_tax}
		<tr>
			<td><b>Tax</b></td>
			<td colspan="3"><input type="text" name="grn_tax" value="{$form.grn_tax}" size="5" maxlength="3" onchange="if(this.value>100) this.value=100;" /> %</td>
		</tr>
	{/if}
</table>
<p align="center">
	<input type="button" value="Save" onClick="submit_form(this);" />
</p>
</form>
</div>
{include file='footer.tpl'}
