{if !$no_header_footer}
</div>

<p align=center class=noprint style="clear:both">{#COPYRIGHT#}</p>
</body>
</html>

<!--
{if $license}
License Expiry: {$license.FILE_EXPIRY}
Copyright: {$license.Copyright.value}
Licensed To: {$license.Licensed.value}
---
{/if}
SSID: {$ssid}
URL: http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}
TIME: {php}global $mt_start;print getmicrotime()-$mt_start."\n"{/php}
SQL: {php}global $query_count,$query_time;print "$query_count\n$query_time"{/php}
-->
{/if}
