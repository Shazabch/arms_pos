{*
6/27/2011 9:52:11 AM Justin
- Removed all the number format rounding for qty in case customer is using Allow Decimal Point for Qty.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs not to round up fixed by 2 but base on config set.

4/23/2015 3:38 PM Andy
- Enhanced to show Deliver %.

06/24/2020 4:04 PM Sheila
- Updated button css
*}

{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var GRN_DISTRIBUTION_REPORT = {
	grn_doc_no_autocomplete: undefined,
	initialize: function(){
		this.form_element = document.f_a;
		if(!this.form_element){
			alert('Module Failed to load');
			return false;
		}
		
		this.reset_grn_doc_no_autocomplete();
	},
	reset_grn_doc_no_autocomplete: function(){
		var param_str = {
			a: 'ajax_search_grn',
			branch_id: this.form_element['branch_id'].value,
			status: 1,
			approved: 1
		}
		
		param_str = $H(param_str).toQueryString();
		
		if (this.grn_doc_no_autocomplete != undefined){
		     this.grn_doc_no_autocomplete.options.defaultParams = param_str;
		}
		else
		{
			var THIS = this;
			
			this.grn_doc_no_autocomplete = new Ajax.Autocompleter(this.form_element['doc_no'], "div_autocomplete_grn_choices", 'ajax_autocomplete.php', {
			parameters:param_str, 
			paramName: "value",
			indicator: 'span_autocomplete_loading',
			afterUpdateElement: function (obj, li) {
			    s = li.title.split(",");
			    
			    var bid = int(s[0]);
			    var grn_id = int(s[1]);
			    var doc_no = s[2];
			    $('span_grn_desc').update(obj.value);
			    THIS.form_element['grn_desc'].value = obj.value;
			    
			    if (!bid || !grn_id){
			        obj.value='';
			        THIS.form_element['grn_bid_id'].value = '';
			    }else{
					obj.value = doc_no;
					THIS.form_element['grn_bid_id'].value = bid+'_'+grn_id;
				}
			}});
		}
	},
	// user change branch
	change_branch: function(){
		this.reset_grn_doc_no_autocomplete();
	},
	// user submit form
	submit_form: function(){
		if(!this.form_element['grn_bid_id'].value){
			alert('Please search and select a valid GRN');
			return false;
		}
		
		this.form_element.submit();
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
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
			<div class="alert alert-danger rounded mx-3">
				<li> {$e}</li>
			</div>
		{/foreach}
	</ul>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method="post" onSubmit="return false;" class="stdframe">
			<input type="hidden" name="load_report" value="1" />
			
			{if $BRANCH_CODE eq 'HQ'}
			
				<div class="row">
					<div class="col-2">
						<b class="form-label fs-09">GRN Branch</b>
					</div>
					
					<div class="col-3">
						<select class="form-control" name="branch_id" onChange="GRN_DISTRIBUTION_REPORT.change_branch();">
							<option value=''>-- All --</option>
							{foreach from=$branches key=bid item=r}
								{if !$branches_group.have_group.$bid}
									<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
								{/if}
							{/foreach}
							{if $branches_group.header}
								<optgroup label='Branch Group'>
								{foreach from=$branches_group.header key=bgid item=bg}
										{foreach from=$branches_group.items.$bgid item=r}
											<option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
										{/foreach}
									{/foreach}
								</optgroup>
							{/if}
							</select>
					</div>		
				</div>		
				
			
			{else}
				<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
			{/if}
			<div class="row mt-2">
				<div class="col-2">
					<b class="form-label">GRR Document No.</b>
				</div>
				<div class="col-3">
					<input class="form-control" type="text" name="doc_no" style="width:300px;" value="{$smarty.request.doc_no}" onFocus="this.select();" />
						<input type="hidden" name="grn_desc" value="{$smarty.request.grn_desc}" />
						<input type="hidden" name="grn_bid_id" value="{$smarty.request.grn_bid_id}" />
					
				</div>
				<div class="col">
					<span id="span_grn_desc" style="color:green;">{$smarty.request.grn_desc}</span>
				</div>
					
			</div>
			
			<input class="btn btn-primary" type="submit" value="Find" onClick="GRN_DISTRIBUTION_REPORT.submit_form();" />
			<span id="span_autocomplete_loading" style="padding:2px;background:yellow;display:none;margin-left:-200px;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
		</form>
	</div>
</div>

<div id="div_autocomplete_grn_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>

<br />

{if $smarty.request.load_report and !$err}
	{if !$data}
		-- No data --
	{else}
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
		<div class="card mx-3">
			<div class="card-body">
				<div class="table-responsive">
					<table width="100%" class="report_table">
						<thead class="bg-gray-100">
							<tr class="header">
								<th width="20">&nbsp;</th>
								<th>ARMS Code</th>
								<th>Art No.</th>
								<th>Description</th>
								<th>B/F</th>
								<th>GRN Qty</th>
								<th>DO Qty</th>
								<th>Deliver %</th>
								<th>Balance</th>
							</tr>
						</thead>
						{foreach from=$data.grn_items item=r name="fgi"}
						<tbody class="fs-08">
							<tr>
								<td>{$smarty.foreach.fgi.iteration}.</td>
								<td>{$r.sku_item_code}</td>
								<td>{$r.artno}</td>
								<td>{$r.description}</td>
								<td class="r">{$r.opening_qty|qty_nf}</td>
								<td class="r">{$r.qty|qty_nf}</td>
								<td class="r">{$r.do_qty|qty_nf}</td>
								<td class="r">{$r.deliver_per|number_format:2}%</td>
								<td class="r">{$r.balance_qty|qty_nf}</td>
							</tr>
						</tbody>

						{/foreach}
						<tfoot class="bg-gray-100">
							<tr class="header">
								<td class="r" colspan="4"><b>Total</b></td>
								<td class="r">{$data.total.opening_qty|qty_nf}</td>
								<td class="r">{$data.total.qty|qty_nf}</td>
								<td class="r">{$data.total.do_qty|qty_nf}</td>
								<td class="r">{$data.total.deliver_per|number_format:2}%</td>
								<td class="r">{$data.total.balance_qty|qty_nf}</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	{/if}
{/if}

<script>GRN_DISTRIBUTION_REPORT.initialize();</script>

{include file='footer.tpl'}