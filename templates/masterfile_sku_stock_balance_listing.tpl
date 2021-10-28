{*
2/20/2017 1:40 PM Andy
- Change to have sample data in php class.
- Enhance to able to load multiple branch stock.

06/26/2020 Sheila 02:26 PM
- Updated button css.
*}

{include file='header.tpl'}
<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var SKU_STOCK_BALANCE_LISTING = {
    f_a: undefined,
    initialize: function() {
        this.f_a = document.f_a;
	},
    download_report: function() {
		this.f_a.submit();
		
    },
	hide_errmsg: function(){
		$("data").style.display = "none";
	},
	// select/de-select branch
	check_branch_by_group: function(is_select){
		var bgid = $('sel_brn_grp').value;
		
		if(bgid){	// got select branch group
			$$('#div_branch_list input.inp_branch_group-'+bgid).each(function(ele){
				ele.checked = is_select;
			});
		}else{	// all
			$$('#div_branch_list input.inp_branch').each(function(ele){
				ele.checked = is_select;
			});
		}
	}
}
{/literal}
</script>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $err}
	<div class="alert alert-danger mx-3">
		The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
	</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" class="stdframe" method="post">
			<input type="hidden" name="download_report" value="1" />
			
			<div>
				<div class="form-inline">
					<b class="form-label">Select Branch By:</b>
				&nbsp;&nbsp;<select class="form-control" id="sel_brn_grp" >
					<option value="">-- All --</option>
					{foreach from=$branch_group.header key=bgid item=bg}
						<option value="{$bgid}" >{$bg.code} - {$bg.description}</option>
					{/foreach}
				</select>&nbsp;&nbsp;
				<input class="btn btn-success" type="button"  value="Select " onclick="SKU_STOCK_BALANCE_LISTING.check_branch_by_group(true);" />&nbsp;
				<input class="btn btn-danger" type="button"  value="De-select" onclick="SKU_STOCK_BALANCE_LISTING.check_branch_by_group(false);" /><br /><br />
				
				</div>
				<div id="div_branch_list" class="mt-2 mb-2" style="padding: 10px; width:100%;height:200px;border:1px solid #ddd;border-radius:10px;overflow:auto;">
					<table>
					{foreach from=$branches key=bid item=b}
						{assign var=bgid value=$branch_group.have_group.$bid.branch_group_id}
						<tr>
							<td>
								<input class="inp_branch {if $bgid}inp_branch_group-{$bgid}{/if}" type="checkbox" name="branch_id_list[]" value="{$bid}" {if (is_array($smarty.request.branch_id_list) and in_array($bid,$smarty.request.branch_id_list))}checked {/if} id="inp_branch-{$bid}" />&nbsp;
								<label for="inp_branch-{$bid}">{$b.code} - {$b.description}</label>
							</td>
						</tr>
					{/foreach}
					</table>
				</div>
			</div>
			
			
			<p>
				<b class="form-label">Vendor</b>
				<select class="form-control" name="vendor_id" onchange="SKU_STOCK_BALANCE_LISTING.hide_errmsg();">
					<option value="">-- All --</option>
					{foreach from=$vendors item=r}
						<option value="{$r.id}" {if $smarty.request.vendor_id eq $r.id}selected {/if}>{$r.description}</option>
					{/foreach}
				</select>
			</p>
			<p onchange="SKU_STOCK_BALANCE_LISTING.hide_errmsg();">{include file="category_autocomplete.tpl" all=true}</p>
			<p>
				<div class="row">
					<div class="col-md-4">
						<b class="form-label">Brand</b>
				<select class="form-control" name="brand_id" onchange="SKU_STOCK_BALANCE_LISTING.hide_errmsg();">
					<option value="">-- All --</option>
					{foreach from=$brand item=r}
						<option value="{$r.id}" {if $smarty.request.brand_id eq $r.id}selected{/if}>{$r.description}</option>
					{/foreach}
				</select>
					</div>

				<div class="col-md-4">
					<b class="form-label">SKU Type</b>
				<select class="form-control" name="sku_type" onchange="SKU_STOCK_BALANCE_LISTING.hide_errmsg();">
					<option value="">-- All --</option>
					{foreach from=$sku_type item=r}
						<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected{/if}>{$r.description}</option>
					{/foreach}
				</select>
				</div>

				<div class="col-md-4">
					<b class="form-label">Status</b>
				<select class="form-control" name="active" onchange="SKU_STOCK_BALANCE_LISTING.hide_errmsg();">
					<option value="">-- All --</option>
					<option value="1" {if $smarty.request.active eq '1'}selected{else}selected{/if}>Active</option>
					<option value="0" {if $smarty.request.active eq '0'}selected{/if}>Inactive</option>
				</select>
				</div>

				</div>
			</p>
			<br />
			<div class="alert alert-primary rounded">
				<span ><b>NOTE:</b> The document will download in zip file format. Each csv file contains 30000 records.</span>
			</div>

			<br/>
			<input class="btn btn-primary" type="button" onclick="SKU_STOCK_BALANCE_LISTING.download_report();" value="Download All"/>
			
		</div>
	</div>
			<div class="div_tbl">
				<div class="breadcrumb-header justify-content-between">
					<div class="my-auto">
						<div class="d-flex">
							<h4 class="content-title mb-0 my-auto ml-4 text-primary">Sample</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
						</div>
					</div>
				</div>

				<div class="card mx-3">
					<div class="card-body">
						<div class="table-responsive">
							<table id="si_tbl" width="100%">
								<thead class="bg-gray-100">
									<tr >
										{foreach from=$sample_header item=v}
											<th>{$v}</th>
										{/foreach}
									</tr>
								</thead>
								{foreach from=$sample_list item=r}
									<tbody class="fs-08">
										<tr>
											{foreach from=$r item=v}
												<td>{$v}</td>
											{/foreach}
										</tr>
									</tbody>
								{/foreach}
							</table>
						</div>
					</div>
				</div>
			</div>
		</form>
	
<br>
{if $data}
	<p id="data">{$data}</p>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
SKU_STOCK_BALANCE_LISTING.initialize();
{/literal}
</script>