{*

*}

{include file=header.tpl}

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
<img src=/ui/approved.png align=absmiddle> Announcement saved as ID#{$smarty.request.id}<br>
{elseif $smarty.request.type eq 'cancel'}
<img src=/ui/cancel.png align=absmiddle> Announcement ID#{$smarty.request.id} was cancelled<br>
{elseif $smarty.request.type eq 'delete'}
<img src=/ui/cancel.png align=absmiddle> Announcement ID#{$smarty.request.id} was deleted<br>
{elseif $smarty.request.type eq 'confirm'}
<img src=/ui/approved.png align=absmiddle> Announcement ID#{$smarty.request.id} confirmed.
{/if}
</div>

<div class="card mx-3">
	<div class="card-body">
		<ul style="list-style-type: none;">
			<li> <img src="ui/new.png" align="absmiddle"> <a href="front_end.announcement.php?a=open&id=0">Create New POS Announcement</a></li>
		</ul>
	</div>
</div>


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
	
	$('announcement_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;
	
	new Ajax.Updater('announcement_list', 'front_end.announcement.php', {
		parameters: 'a=ajax_load_announcement_list&t='+n+'&'+pg+'&'+search_params,
		evalScripts: true
		});
}

function search_tab_clicked(obj) {
	$(obj).siblings().each(function(el){el.removeClassName('selected');});
	obj.addClassName('selected');
	$('announcement_list').update();
	$('search_area').show();
}

function display_day_count(v) {
	if (v == 'starting_in' || v == 'ending_in') $('day_count').show();
	else $('day_count').hide();
}
</script>
{/literal}

<form name=f onsubmit="list_sel(0);return false;">
<div class="tab row mx-3 mb-2" style="white-space:nowrap;">
<a href="javascript:list_sel(1)" id=lst1 class="btn btn-outline-primary btn-rounded">Draft</a>
&nbsp;<a href="javascript:list_sel(2)" id=lst2 class="btn btn-outline-primary btn-rounded">Cancelled/Deleted</a>
&nbsp;<a href="javascript:list_sel(3)" id=lst3 class="btn btn-outline-primary btn-rounded">Confirmed</a>
&nbsp;<a name="find" class="btn btn-outline-primary btn-rounded" id="lst0" onclick="search_tab_clicked(this);" style="cursor:pointer;">Find</a>
</div>

<div >

<div id="search_area" style="display:none;">
	<div class="card mx-3">
		<div class="card-body">
			<table border="0">
				<tr>
					<th align="left" class="form-label mt-2">Find Announcement / Doc No</th>
					<td><input class="form-control" name="search" id="search" value="{$smarty.request.search}" size="15" /></td>
				</tr>
				<tr>
					<th align="left" class="form-label mt-2">Status</th>
					<td>
						<div class="form-inline">
							<select class="form-control" name="search_filter" onchange="display_day_count(this.value);">
								<option value="">&nbsp;</option>
								<option value="starting_in">Starting in .. </option>
								<option value="ending_in">Ending in .. </option>
								<option value="currently_active">Currently active </option>
							</select>
							<span id="day_count" style="display:none;">&nbsp;&nbsp;<input type="text" class="form-control" name="day_count" size="2" onchange="mi(this);" /> day(s)</span>
							&nbsp;&nbsp;<input class="btn btn-primary " type="submit" value="Go">
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
</div>
<div id="announcement_list"></div>


</form>

{include file=footer.tpl}

<script>
list_sel(1);
</script>
