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
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.SETTING} - {if $form.id}(GRN#{$form.id}){else} {$LNG.NEW} {$LNG.GRN} {/if}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a  href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a>
		</li>
		{if $form.find_grn || $form.find_grr} >
		<li class="breadcrumb-item">
			<a href="goods_receiving_note.php?{if $form.find_grn}a=open&find_grn={$form.find_grn}{else}a=show_grr_list&find_grr={$form.find_grr}{/if}">{$LNG.BACK_TO_SEARCH}</a>
		</li>
		{/if}
	</ol>
</nav>
<!-- /BreadCrumbs -->
<!-- Error Message -->
{if $err}
	{foreach from=$err item=e}
	<div class="alert alert-danger mg-b-0 " role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		{$e}
	</div>
    {/foreach}
{/if}

<!-- /Error Message -->
{if $form.id && $form.branch_id}{include file='goods_receiving_note.top_include.tpl'}{/if}

<div class="card  mt-2">
	<div class="card-body">
		<div class="card-title border-bottom pb-2">{$LNG.GENERAL_INFORMATION}</div>
		<form name="f_a" method="post" onSubmit="return false;">
			<input type="hidden" name="a" value="save_setting" />
			<input type="hidden" name="id" value="{$form.id}" />
			<input type="hidden" name="vendor_id" value="{$form.vendor_id}" />
			<input type="hidden" name="branch_id" value="{$form.branch_id}" />
			<input type="hidden" name="grr_id" value="{$form.grr_id}" />
			<input type="hidden" name="rcv_date" value="{$grr.rcv_date}" />
			<input type="hidden" name="grr_item_id" value="{$form.grr_item_id}" />
			<div class="table-responsive">
				<table class="table table-sm mb-0 text-md-nowrap table-borderless">
					<tr>
						<th>{$LNG.GRR_NO}</th>
						<td class="pl-2">GRR{$grr.grr_id|string_format:"%05d"}</td>
					</tr>
					<tr>
						<th>{$LNG.GRR_AMOUNT}</th>
						<td class="pl-2">{$grr.grr_amount|number_format:2}</td>
					</tr>
					<tr>
						<th>{$LNG.GRR_DATE}</th>
						<td class="pl-2">{$grr.added|date_format:$config.dat_format}</td>
					</tr>
					<tr>
						<th>{$LNG.BY}</th>
						<td class="pl-2">{$grr.u}</td>
					</tr>
					<tr>
						<th>{$LNG.RECEIVED_DATE}</th>
						<td class="pl-2">{$grr.rcv_date|date_format:$config.dat_format}</td>
					</tr>
					<tr>
						<th>{$LNG.BY}</th>
						<td class="pl-2">{$grr.rcv_u}</td>
					</tr>
					<tr>
						<th>{$LNG.RECEIVED_QTY}</th>
						<td class="pl-2">Ctn:{$grr.grr_ctn|qty_nf} / Pcs:{$grr.grr_pcs|qty_nf}</td>
					</tr>
					<tr>
						<th>{$LNG.LORRY_NO}</th>
						<td class="pl-2">{$grr.transport}</td>
					</tr>
					<tr>
						<th>{$LNG.VENDOR}</th>
						<td class="pl-2">{$grr.vendor}</td>
					</tr>
					<tr>
						<th>{$LNG.DEPARTMENT}</th>
						<td class="pl-2">
							<input type="hidden" name="department_id" value="{$form.department_id}">
							{$grr.department}
						</td>
					</tr>
					<tr>
						<th>{$LNG.DOC_TYPE}</th>
						<td class="pl-2">{$grr.type}</td>
					</tr>
					<tr>
						<th>{$DOC_NO}</th>
						<td class="pl-2"><font color=blue><input type="hidden" name="doc_no" value="{$grr.doc_no}">{$grr.doc_no}</font></td>
					</tr>
					{if $grr.type eq 'PO'}
						<tr>
							<th>{$LNG.PO_AMOUNT}</th>
							<td class="pl-2"><font color=blue>{$grr.po_amount|number_format:2}</font></td>
						</tr>
						<tr>
							<th>{$LNG.PARTIAL_DELIVERY}</th>
							<td class="pl-2"><font color=blue>{if $grr.pd_po}{$grr.pd_po} ({$LNG.NOT_ALLOWED}){else} {$LNG.ALLOWED} {/if}</font></td>
							<input type="hidden" name="ttl_grr_amt" value="{$grr.po_amount|round2}">
						</tr>
					{else}
						<input type="hidden" name="ttl_grr_amt" value="{$grr.grr_amount|round2}">
					{/if}
					{if $form.grn_tax}
						<tr>
							<th>{$LNG.TAX}</th>
							<td class="pl-2">
								<input type="text" name="grn_tax" value="{$form.grn_tax}" class="form-control" size="5" maxlength="3" onchange="if(this.value>100) this.value=100;"> %
							</td>
						</tr>
					{/if}
				</table>
			</div>
			<div class="text-right">
				<button class="btn btn-primary btn-block-sm" value="Save" onClick="submit_form(this);">{$LNG.SAVE}</button>
			</div>
		</form>
	</div>
</div>
{include file='footer.tpl'}
