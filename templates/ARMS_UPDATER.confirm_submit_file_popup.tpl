{*
5/9/2014 11:29 AM Justin
- Enhanced to have "Retry" button whenever the file is under "connecting more than 10 seconds" and "failed".

2/21/2018 5:35 PM Andy
- Enhanced to able to submit svn_type and all the other parameters based on svn_type.
*}

<table width="100%" class="report_table">
	<tr class="header">
		<th>Server</th>
		<th>Information</th>
	</tr>
	
	{foreach from=$selected_server_name key=server_name item=v}
		{assign var=server_url value="http://`$server_name`.dyndns.org:4000"}
		{if $server_list.$server_name.url}
			{assign var=server_url value=$server_list.$server_name.url}
			{if $server_list.$server_name.http_port}
				{assign var=server_url value="`$server_url`:`$server_list.$server_name.http_port`"}
			{/if}
		{/if}
		<tr>
			<td align="center">
				<input type="hidden" id="inp_server_upload_status-{$server_name}" class="inp_server_upload_status" value="preparing" />
				<div>{$server_name}</div><br />
				<div id="div_server_upload_status-{$server_name}" class="div_server_preparing">Preparing</div>
				<div class="div_retry" id="div_retry_{$server_name}">
					<input type="button" value="Retry" name="retry_btn_{$server_name}" onclick="ARMS_UPDATER.start_upload('{$server_name}', 1);">
				</div>
				{*<div class="div_server_connecting">Connecting</div>
				<div class="div_server_connected">Connected</div>
				<div class="div_server_uploading">Uploading</div>
				<div class="div_server_done">Done</div>
				<div class="div_server_failed">Failed</div>*}
			</td>
			<td align="center">
				<form name="f_upload_server_{$server_name}" target="if_upload_server_{$server_name}" action="{$server_url}/ARMS_UPLOADER.php" method="post" id="f_upload_server_{$server_name}">
					<input type="hidden" name="tgz_filename" value="{$tgz_filename}" />
					<input type="hidden" name="server_name" value="{$server_name}" />
					<input type="hidden" name="svn_type" value="{$server_list.$server_name.svn_type}" />
					<input type="hidden" name="total_filesize" value="{if $server_list.$server_name.svn_type eq 'php7'}{$total_filesize_php7}{else}{$total_filesize}{/if}" />
					
					{foreach from=$file_info_list key=filename item=file_info}
						<input type="hidden" name="file_info_list[{$filename}][filesize]" value="{if $server_list.$server_name.svn_type eq 'php7'}{$file_info.filesize_php7}{else}{$file_info.filesize}{/if}" />
						<input type="hidden" name="file_info_list[{$filename}][filemtime]" value="{if $server_list.$server_name.svn_type eq 'php7'}{$file_info.filemtime_php7}{else}{$file_info.filemtime}{/if}" />
					{/foreach}
				</form>
				<iframe class="iframe_server_uploading" name="if_upload_server_{$server_name}" id="if_upload_server_{$server_name}"></iframe>
				<script type="text/javascript">ARMS_UPDATER.start_upload('{$server_name}');</script>
			</td>
		</tr>
		
		
	{/foreach}
</table>
