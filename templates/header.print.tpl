{*
5/28/2013 5:07 PM Andy
- Add base tag to define default base href.

5/21/2019 11:13 AM Andy
- Enhanced printing to fit in pdf size.
- Fixed barcode cannot be show when send by email.
*}

{if !$skip_header}
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	{if dirname($smarty.server.REQUEST_URI) ne '/'}<base href="http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.HTTP_HOST}/">{/if}
	<link rel="stylesheet" type="text/css" href="templates/print.css">
	{if $send_email}
		<link rel="stylesheet" type="text/css" href="{if $smarty.server.DOCUMENT_ROOT}{$smarty.server.DOCUMENT_ROOT}{else}{$DOCUMENT_ROOT}{/if}/ui/3of9/stylesheet.css">
	{/if}
	<style>
		{if $send_email}
			{literal}
				body {
					width: 98%;
					height: 98%;
				}
			{/literal}
		{/if}
	</style>
	{config_load file="site.conf"}
{/if}