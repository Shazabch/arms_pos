{*
1/3/2013 5:37 PM Justin
- Enhanced to have cancel message.

1/30/2013 4:52 PM Justin
- Enhanced to have export and import item list.
*}

{include file="header.tpl"}

{if !$no_header_footer}
{literal}
<style>
.negative{
	font-weight: bold;
	color: red;
}

.tr_date_total td{
	background-color: #cfcfcf;
}
</style>
{/literal}

<script type="text/javascript">
var tab = '{$smarty.request.t}';
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var GRN = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		this.fprn = document.f_prn;
		this.list_sel(tab, '{$smarty.request.search}');
	},
	list_sel: function(n,s){
		var i;

		tab = n;
		for(i=0;i<=2;i++){
			if($('lst'+i) == null) continue;
			if (i==n)
				$('lst'+i).className='active';
			else
				$('lst'+i).className='';
		}

		//if (s=='') return;
		$('grn_list').update('<img src=ui/clock.gif align="absmiddle"> Loading...');

		var pg = '';
		if (s!=undefined) pg = 's='+s;
		if (n==0) pg +='&search='+ $('search').value;

		new Ajax.Updater('grn_list', phpself, {
			parameters: 'a=ajax_load_grn_list&t='+n+'&'+pg,
			evalScripts: true
		});
		
	},

	search_tab_clicked: function(obj){
		$('lst'+tab).className = '';
		obj.className = 'active';
	},
	
	do_print: function(id, bid){
		this.fprn['id'].value=id;
		this.fprn['branch_id'].value=bid;
		this.show_print_dialog();
	},
	
	show_print_dialog: function(){
		center_div('print_dialog');
		$('print_dialog').style.display = '';
		$('print_dialog').style.zIndex = 10000;
		curtain(true);
	},
	
	print_ok: function(){
		$('print_dialog').style.display = 'none';
		//document.f_prn.target = "ifprint";
		this.fprn.target = "_blank";
		this.fprn.submit();
		curtain(false);
	},

	print_cancel: function(){
		$('print_dialog').style.display = 'none';
		curtain(false);
	},
	
	export_item_list: function(){
		var new_href = phpself+"?a=export_item_list&sort_by="+$('sort_by').value;
		
		document.location = new_href;
	},
	
	show_import_dialog: function(){
		if($('import_dialog').style.display == "") $('import_dialog').hide();
		else $('import_dialog').show();
	},
	
	check_upload: function(){
		if(trim(document.f_a['csv_file'].value) == ""){
			alert("Please select a CSV file to import");
			return false;
		}
		
		if(!confirm("Are you sure want to import?")) return false;
		
		document.f_a.submit();
	}
};
{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

<!-- print dialog -->
<div id="print_dialog" style="background:#fff;border:3px solid #000;width:300px;height:{if $config.use_grn_future}145	{else}125{/if}px;position:absolute; padding:10px; display:none;">
<form name="f_prn" method="get">
<table border="0" width="100%">
	<tr>
		<td rowspan="2"><img src="ui/print64.png" hspace="10" align="left"></td>
		<td><h3>Print Options</h3></td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="print_grn_report" value="1" checked> GRN Report<br />
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<br />
			<input type="button" value="Print" onclick="GRN.print_ok()"> 
			<input type="button" value="Cancel" onclick="GRN.print_cancel()">
		</td>
	</tr>
</table>
<input type="hidden" name="a" value="do_print">
<input type="hidden" name="load" value="1">
<input type="hidden" name="id" value="">
<input type="hidden" name="branch_id" value="">
</form>
</div>

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>
{if $smarty.request.msg}
<div>
	{if $smarty.request.msg eq 'save'}
		<img src="/ui/approved.png" align="absmiddle"> GRN saved as #{$smarty.request.id|string_format:"%05d"}<br>
	{elseif $smarty.request.msg eq 'confirm'}
		<img src="/ui/approved.png" align="absmiddle"> GRN#{$smarty.request.id|string_format:"%05d"} is now official<br>	
	{elseif $smarty.request.msg eq 'cancel'}
		<img src="/ui/approved.png" align="absmiddle"> GRN#{$smarty.request.id|string_format:"%05d"} has been cancelled<br>
	{/if}
</div>
<br />
{/if}
<table width="100%">
	<tr>
		<td width="20"><img src="ui/new.png" align="absmiddle"></td>
		<td><a href="{$smarty.server.PHP_SELF}?a=open">Create New GRN</a></td>
	</tr>
	<tr>
		<td><img src="ui/icons/disk.png" align="absmiddle"></td>
		<td>
			<a href="#" onclick="GRN.export_item_list();">Download Item List</a> >> Sort by&nbsp;
			<select id="sort_by">
				{foreach from=$sort_type key=val item=desc}
					<option value="{$val}">{$desc}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td><img src="ui/icons/layout_add.png" align="absmiddle"></td>
		<td>
			<a href="#" onclick="GRN.show_import_dialog();">Import Item List</a>&nbsp;
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td id="import_dialog" {if !$error_from_import}style="display:none;"{/if}>
			<fieldset>
				<legend>Import Menu</legend>
					<form action="{$smarty.server.PHP_SELF}" name="f_a" method="post" ENCTYPE="multipart/form-data" onsubmit="return GRN.check_upload();">
						<h4>Please select a CSV file to import</h4>
						<input type="hidden" name="a" value="import_item_list">
						<input name="csv_file" type="file"><br>
						* Must be a valid CSV file
						<br><br><input type="submit" value="Upload">
					</form>
			</fieldset>
		</td>
	</tr>
</table>

{if $err}
	<div><div class="errmsg"><ul>
	{foreach from=$err item=e}
		<li> {$e}</li>
	{/foreach}
	</ul></div></div>
{/if}

<div style="padding:10px 0;">
	<form name="f_s" onsubmit="javascript:GRN.list_sel(0,0); return false;">
	<div class="tab" style="height:25px;white-space:nowrap;">
		&nbsp;&nbsp;
		<a href="javascript:GRN.list_sel(1);" id="lst1" class="active">Saved GRN</a>
		<a href="javascript:GRN.list_sel(2);" id="lst2">Confirmed</a>
		<a name="find" id="lst0" onclick="GRN.search_tab_clicked(this);" style="cursor:pointer;">
			Find GRN / Doc No <input name="search" id="search" name="find" value="{$smarty.request.search}">
			<input type="submit" value="Go">
		</a>
	</div>
	<div id="div_grn" style="border:1px solid #000">
		<div id="grn_list">
			{include file=vp.goods_receiving_note.list.tpl}
		</div>
	</div>
	</form>
</div>

<script>
{if $smarty.request.msg eq 'save'}
	alert('GRN#{$smarty.request.id|string_format:"%05d"} saved');
{elseif $smarty.request.msg eq 'confirm'}
	alert('GRN#{$smarty.request.id|string_format:"%05d"} is now official.');
{elseif $smarty.request.msg eq 'cancel'}
	alert('GRN#{$smarty.request.id|string_format:"%05d"} has been cancelled.');
{/if}
{literal}
GRN.initialize();
{/literal}
</script>
{include file="footer.tpl"}