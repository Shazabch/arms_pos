{include file="ARMS_UPDATER.header.tpl"}

<script type="text/javascript">

{literal}

var UPDATE_HISTORY = {
	initialize: function(){
	
	},
	// function when user click show all remaining server
	toggle_remaining_server: function(file_id){
		var span = $('#span_remaining_server-'+file_id);
		if(span.css('display')=='none'){
			span.show();
			$('#a_toggle_remaining_server-'+file_id).html('Show less');
		}else{
			span.hide();
			$('#a_toggle_remaining_server-'+file_id).html('Show all');
		}
	}
};
{/literal}
</script>

<h1>Update History</h1>

<table width="100%" class="report_table">
	<tr class="header">
		<th>File Name</th>
		<th>Last Update</th>
		<th>Remaining Server</th>
	</tr>
	
	{foreach from=$file_list key=file_id item=file_info}
		<tr>
			<td>{$file_info.filename}</td>
			
			{* Last Update *}
			<td>
				{foreach from=$file_info.update_result key=server_name item=update_result name=ffl}
					{if !$smarty.foreach.ffl.first}, {/if}
					
					<span style="white-space:nowrap;">
						{if $update_result.result eq 'done'}
							<img src="/ui/approved.png" align="absmiddle" title="Upload Successfully" />
						{else}
							<img src="/ui/cancel.png" align="absmiddle" title="Upload Failed" />
						{/if}
						<b>{$server_name}</b> <small></small>({$update_result.last_update})</small>
					</span>
				{/foreach}
			</td>
			
			{* Remaining Server *}
			<td>
				{assign var=got_show_all value=0}
				{foreach from=$file_info.remaining_server key=server_name item=v name=frs}
					{if $smarty.foreach.frs.iteration eq 5 and count($file_info.remaining_server)>7}
						{assign var=got_show_all value=1}
						<span style="display:none;" id="span_remaining_server-{$file_info.id}">
					{/if}
					
					{if !$smarty.foreach.frs.first}, {/if}
					{$server_name}
				{foreachelse}-
				{/foreach}
				
				{if $got_show_all}
					</span>
					(<a href="javascript:void(UPDATE_HISTORY.toggle_remaining_server('{$file_info.id}'));" id="a_toggle_remaining_server-{$file_info.id}">Show all</a>)
				{/if}
			</td>
		</tr>
	{/foreach}
</table>
<script type="text/javascript">UPDATE_HISTORY.initialize();</script>

{include file="ARMS_UPDATER.footer.tpl"}