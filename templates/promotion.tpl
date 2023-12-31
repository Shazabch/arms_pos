{*
8/20/2010 6:08:16 PM Andy
- add print promotion at main promotion page

11/4/2010 6:35:31 PM Justin
- Added the checking different type of report printing.
- Added ministry of trade printing format. 

11/11/2010 2:39:51 PM Justin
- Removed the PWP promotion hyperlink.

2/1/2013 3:56 PM Fithri
- mix and match promotion change to no need config, always have for all customer

6/7/2013 11:06 AM Andy
- Add checking privilege "PROMOTION_MIX" to allow user to create/edit/view mix and match.

1/23/2014 11:17 AM Fithri
- add new search filters 'starting in x days', 'ending in x days' & 'currently active'

4/11/2014 10:53 AM Fithri
- add data collector import function at promotion module

5/29/2014 5:56 PM Justin
- Enhanced import from CSV to have few options of choosing import format and delimiter.

3/6/2018 5:44 PM HockLee
- Add new function export_to_csv() to export Promotion to CSV file.

7/6/2018 12:19 PM Andy
- Enhanced import promotion to have column member discount, member price, non-member discount and non-member price.

2/19/2019 5:55 PM Andy
- Enhanced Print Promotion to use shared template.
*}

{include file=header.tpl}
{include file='promotion.print_dialog.tpl'}

{assign var=msg value=$smarty.request.msg}
<p align=center><font color=red>{$msg}</font></p>

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


<div id=show_last>
{if $smarty.request.type eq 'save'}
<img src=/ui/approved.png align=absmiddle> Promotion saved as ID#{$smarty.request.id}<br>
{elseif $smarty.request.type eq 'cancel'}
<img src=/ui/cancel.png align=absmiddle> Promotion ID#{$smarty.request.id} was cancelled<br>
{elseif $smarty.request.type eq 'delete'}
<img src=/ui/cancel.png align=absmiddle> Promotion ID#{$smarty.request.id} was deleted<br>
{elseif $smarty.request.type eq 'confirm'}
<img src=/ui/approved.png align=absmiddle> Promotion ID#{$smarty.request.id} confirmed.
{elseif $smarty.request.type eq 'approved'}
<img src=/ui/approved.png align=absmiddle> Promotion ID#{$smarty.request.id} was Fully Approved.
{/if}
</div>

<div class="card mx-3">
	<div class="card-body">
		<ul>
			<li> <img src="ui/new.png" align="absmiddle"> <a href="promotion.php?a=open&id=0">Create New Discount</a></li>
			<li>
				<img src="ui/new.png" align="absmiddle"> <a href="javascript:void(togglediv('import_data'))">Create New Discount from Data Collector input</a><br />
				<div style="margin: 5px 0px; background: none repeat scroll 0% 0% rgb(255, 255, 255); display:none;" class="stdframe" id="import_data">
				<form enctype="multipart/form-data" method="post" name="f_a">
				<input type="hidden" value="create_from_upload_file" name="a" />
				<table border="0" cellspacing="0" cellpadding="4">
				<tbody>
				<tr>
					<th align="left" class="form-label">Import Format</th>
					<td>
						<input type="radio" name="import_format" value="1" checked /> Default (ARMS CODE / MCODE / {$config.link_code_name|default:'OLD CODE'} / ART NO), Member Discount, Member Price, Non-Member Discount, Non-Member Price<br />
						<input type="radio" name="import_format" value="2" /> GRN Barcode (barcode), Member Discount, Member Price, Non-Member Discount, Non-Member Price
					</td>
				</tr>
				<tr>
					<th align="left" class="form-label">Delimiter</th>
					<td>
						<select class="form-control" name="delimiter">
							<option value="|">| (Pipe)</option>
							<option value="," selected>, (Comma)</option>
							<option value=";">; (Semicolon)</option>
						</select>
					</td>
				</tr>
				<tr>
				<th width="80" valign="top" align="left" class="form-label">Import File<span class="text-danger" title="Required Field"> *</span></th>
				<td align="left">
					<input type="file" size="50" class="files" id="file" name="files" /> 
					<input type="submit" class="btn btn-warning mt-2" value="Upload" />
				</td>
				</tr>
				</tbody>
				</table>
				</form>
				</div>
			</li>
			{if file_exists('promotion.mix_n_match.php')} 
				{if $sessioninfo.privilege.PROMOTION_MIX}
					<li> <img src="ui/new.png" align="absmiddle"> <a href="promotion.mix_n_match.php?a=open">Create New Mix & Match</a></li>
				{else}
					<li> You need privilege to create Mix & Match Promotion</li>	
				{/if}
			{/if}
		</ul>
	</div>
</div>
<br>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function list_sel(n,s)
{
	var i;
	for(i=0;i<=6;i++)
	{
		if ($('lst'+i)!=undefined)
		{
			if (i==n)
				$('lst'+i).addClassName('selected');
			else
				$('lst'+i).removeClassName('selected');
		}
	}
	
	search_params = '';
	if (n == 0) {
		if ((document.f.search_filter.value == 'starting_in' || document.f.search_filter.value == 'ending_in') && document.f.day_count.value == '') {
			alert('Please insert number of day(s)');
			document.f.day_count.focus();
			return false;
		}
		search_params = document.f.serialize();
		//alert(search_params);
	}
	else $('search_area').hide();
	
	$('promotion_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;
	
	new Ajax.Updater('promotion_list', 'promotion.php', {
		parameters: 'a=ajax_load_promotion_list&t='+n+'&'+pg+'&'+search_params,
		evalScripts: true
		});
}

/*function print_promotion(bid, id, mot){
	if(!mot){
		PROMO_PRINT.show(bid, id);
	}else{
		var url = '';
		if(mot != undefined) url="&mot_fmt=1";
		window.open(phpself+'?&a=do_print&branch_id='+bid+'&id='+id+'&load=1'+url);
	}
}*/

function export_to_csv(bid, id){
	window.open(phpself+'?&a=export_to_csv&branch_id='+bid+'&id='+id);
}

function search_tab_clicked(obj) {
	$(obj).siblings().each(function(el){el.removeClassName('selected');});
	obj.addClassName('selected');
	$('promotion_list').update();
	$('search_area').show();
}

function display_day_count(v) {
	if (v == 'starting_in' || v == 'ending_in') $('day_count').show();
	else $('day_count').hide();
}
</script>
{/literal}

<form name=f onsubmit="list_sel(0);return false;">
<div class="tab row mx-3 mb-3" style="white-space:nowrap;">
<a href="javascript:list_sel(1)" id=lst1 class="btn btn-outline-primary btn-rounded">Saved Promotion</a>
&nbsp;<a href="javascript:list_sel(2)" id=lst2 class="btn btn-outline-primary btn-rounded">Waiting for Approval</a>
&nbsp;<a href="javascript:list_sel(5)" id=lst5 class="btn btn-outline-primary btn-rounded">Rejected</a>
&nbsp;<a href="javascript:list_sel(3)" id=lst3 class="btn btn-outline-primary btn-rounded">Cancelled/Terminated</a>
&nbsp;<a href="javascript:list_sel(4)" id=lst4 class="btn btn-outline-primary btn-rounded">Approved</a>
&nbsp;<a name="find" class="btn btn-outline-primary btn-rounded" id="lst0" onclick="search_tab_clicked(this);" style="cursor:pointer;">Find Promotion</a>
</div>

<div >


		<div id="search_area" style="display:none;">
			<div class="card mx-3">
				<div class="card-body">
			<table border="0">
				<tr>
					<th align="left" class="form-label">Find Promotion / Doc No</th>
					<td><input class="form-control" name="search" id="search" value="{$smarty.request.search}" size="15" /></td>
				</tr>
				<tr>
					<th align="left" class="form-label">Status</th>
					<td>
						<select class="form-control" name="search_filter" onchange="display_day_count(this.value);">
							<option value="">&nbsp;</option>
							<option value="starting_in">Starting in .. </option>
							<option value="ending_in">Ending in .. </option>
							<option value="currently_active">Currently active </option>
						</select>
						<span id="day_count" style="display:none;">&nbsp;&nbsp;<input type="text" name="day_count" size="2" onchange="mi(this);" /> day(s)</span>
						&nbsp;&nbsp;<input class="btn btn-primary mt-2" type="submit" value="Go">
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div id="promotion_list"></div>

</div>
	</div>
</div>
</form>

{include file=footer.tpl}

<script>
list_sel(1);
PROMO_PRINT.initialise();
</script>
