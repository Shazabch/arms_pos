{if !$do_id_list}
	No DO was Generated.
{else}
	<h4>Generated DO</h4>
	<ul>
		{foreach from=$do_id_list item=do_id}
			<li> <a href="do.php?a=open&branch_id={$sessioninfo.branch_id}&id={$do_id}" target="_blank">DO ID#{$do_id}</a></li>
		{/foreach}
	</ul>	
{/if}

<p align="center">
	<input type="button" value="close" onClick="close_curtain2();" />
</p>