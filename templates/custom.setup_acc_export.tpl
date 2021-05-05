{*
2017-09-08 11:17 AM Qiu Ying
- Bug fixed on the saved format title name not shown after search
*}

{include file="header.tpl"}

<script type="text/javascript">

{literal}
var phpself = "{$smarty.server.PHP_SELF}";
var SETUP_CUSTOM_ACC_EXPORT = {
	initialize: function(){
		this.f_a = document.f_a;
		var THIS = this;
	},
	search: function(){
		this.f_a['a'].value = 'search';
        this.f_a.submit();
	},
	activate:function(id,bid,active_value){
		this.f_a['id'].value = id;
		this.f_a['branch_id'].value = bid;
		this.f_a['active_value'].value = active_value;
		this.f_a['a'].value = 'activate';
        this.f_a.submit();
	}
};
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if isset($smarty.request.t)}
	{if $smarty.request.t eq 'save'}
		<img src="/ui/approved.png" align="absmiddle"> Format Title ({$smarty.request.title_name}) saved.<br>
	{/if}
	<br />
{/if}

<ul>
	<li>
		<img src="ui/new.png" align="absmiddle"> 
		<a href="?a=open"> Create New Accounting Export Format</a>
	</li>
</ul>
<br />

<table class="stdframe" style="background:#fff" cellspacing="5" cellpadding="4" border="0">
	<form name="f_a"  method="post">
		<input type="hidden" name="a" value="search">
		<input type="hidden" name="branch_id" value="">
		<input type="hidden" name="id" value="">
		<input type="hidden" name="active_value" value="">
		<tr>
			<td><b>Title</b></td>
			<td><input type="text" name="title" size="40" value="{$smarty.request.title}"/></td>
		</tr>
		<tr>
			<td><b>Status</b></td>
			<td>
				<select name="status">
					<option value="all" {if $smarty.request.status eq 'all'}selected{/if}>-- All --</option>
					<option value="1" {if $smarty.request.status eq 1}selected{/if}>Active</option>
					<option value="0" {if isset($smarty.request.status) && !$smarty.request.status && $smarty.request.status neq 'all'}selected{/if}>Inactive</option>
				</select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type="button" name="search" value="Search" onclick="SETUP_CUSTOM_ACC_EXPORT.search();"/></td>
		</tr>
	</form>
</table>

<br /><br />
<table width="100%" class="report_table">
	<tr class="header">
		<th width="50">&nbsp;</th>
		<th>Branch</th>
		<th>Title</th>
		<th>Data Type</th>
		<th>File Format</th>
		<th>Added</th>
		<th>Last Update</th>
		<th>Status</th>
	</tr>
	
	{foreach from=$format_list item=r}
		<tr>
			<td>
				{if $r.branch_id eq $sessioninfo.branch_id}
					<a href="?a=open&id={$r.id}&branch_id={$r.branch_id}">
						<img src="ui/ed.png" border="0" title="Edit" />
					</a>
					<a href="javascript:void(0);" onclick="SETUP_CUSTOM_ACC_EXPORT.activate({$r.id}, {$r.branch_id}, {if $r.active}0{else}1{/if});">
						{if $r.active}
							<img src="ui/deact.png" border="0" title="Deactivate" />
						{else}
							<img src="ui/act.png" border="0" title="Activate" />
						{/if}	
					</a>
				{else}
					<a href="?a=view&id={$r.id}&branch_id={$r.branch_id}">
						<img src="ui/view.png" border="0" title="View" />
					</a>
				{/if}
			</td>
			<td>{$r.code}</td>
			<td>{$r.title}</td>
			<td>{$data_type_option[$r.data_type]}</td>
			<td align="center">{$file_format_list[$r.file_format]}</td>
			<td align="center">{$r.added}</td>
			<td align="center">{$r.last_update}</td>
			<td align="center">{if $r.active eq 1}Active{else}Inactive{/if}</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="8" align="center">No Record Found</td>
		</tr>
	{/foreach}
</table>


<script type="text/javascript">
	SETUP_CUSTOM_ACC_EXPORT.initialize();
</script>

{include file="footer.tpl"}
