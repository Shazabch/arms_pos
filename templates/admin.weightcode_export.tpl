{*
4/26/2011 6:17:10 PM Justin
- Added new drop-down list to allow user to export CSV by different types of report format.

5/9/2011 12:24:15 PM Andy
- Hide branch input box when not HQ.

7/27/2011 9:52:32 AM Justin
- Added notes to guide user for different types of export format.

8/11/2011 11:45:11 AM Justin
- Added new format "Mettle Toledo 2" for PKT.

2/17/2012 9:46:29 AM Andy
- Add new export format "Ishida".

7/2/2012 3:57:12 PM Justin
- Added new filter "Vendor".

5/14/2013 4:14 PM Justin
- Added new format "Ishida 2" as default format.

6/26/2013 10:24 AM Justin
- Added new format "Mettle Toledo (China)".

5/30/2017 17:00 Qiu Ying
- Added new format "Digi SM-500".

3/15/2018 3:05 PM HockLee
- Add new export format "BC11 800" and "BC11 800 v2".

4/12/2018 9:35 AM Andy
- Enhanced Digi SM-500 compatible to Digi SM-5300.

6/10/2019 10:06 AM William
- Added new output format "Rongta".

6/28/2019 4:27 PM William
- Added new output format "Digi SM320 with Scale Type".

6/23/2020 10:30 AM Sheila
- Updated button css

02/16/2021 2:26 PM Rayleen
- Added new output format "TM A Barcode".

*}

{include file=header.tpl}
<div class="container">
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">Export Weighing Scale Items</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
</div>
{if $status}<p>{$status}</p>{/if}

<div class="container">
	<div class="alert alert-primary mx-3 rounded p-2">
		<p><b>Note:</b> <br />
			1. Export items with {$config.sku_weight_code_length|default:5} characters Mcode <br />
			2. To export such as Mettle Toledo, DiGi and TM A Series, system requires SKU item that having Scale Type. <br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;> Please set the Scale Type from SKU item become "Fixed Price" or "Weighted".
			</p>
	</div>
</div>

<div class="container">
	<div class="card mx-3">
		<div class="card-body">
			<form name="f1">
				<input type="hidden" name="a" value="export">
				
				<label>Vendor</label>
				<select class="form-control" name="vendor_id">
					<option value="">-- All --</option>
					{foreach from=$vendors item=r}
						<option value="{$r.id}" {if $smarty.request.vendors eq $r.id}selected {/if}>{$r.description}</option>
					{/foreach}
				</select>
				{if $BRANCH_CODE eq 'HQ'}
					<label class="mt-2">Branch</label>
					<select class="form-control" name="branch_id">
					{foreach from=$branch item=b}
					<option value="{$b.id}">{$b.code}</option>
					{/foreach}
					</select>
					
				{else}
					<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}">
				{/if}
				<label class="mt-2">Output Format</label>
				<select class="form-control" name="report_format">
					<option value="default" selected>Default</option>
					<option value="mettle_toledo">Mettle Toledo</option>
					<option value="mettle_toledo_pkt">Mettle Toledo 2</option>
					<option value="mettle_toledo_cn">Mettle Toledo (China)</option>
					<option value="digi">DiGi</option>
					<option value="tma_series">TM A Series</option>
					<option value="ishida">Ishida</option>
					<option value="ishida_2">Ishida 2</option>
					<option value="digi_sm_500">Digi SM-500 / SM-5300</option>
					<option value="bc11_800">BC11 800</option>
					<option value="bc11_800_v2">BC11 800 v2</option>
					<option value="rongta">Rongta</option>
					<option value="digi_sm320_with_scale_type">Digi SM320 with Scale Type</option>
					<option value="tma_barcode">TM A Barcode</option>
				
				</select>
				<br>
				<input class="btn btn-primary" type="submit" value="Download">
				</form>
				
		</div>
	</div>
</div>

<!--iframe name=_ifr width=1 height=1 style="visibility:hidden"></iframe-->
{include file=footer.tpl}
