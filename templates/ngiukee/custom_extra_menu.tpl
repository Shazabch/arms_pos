{*
7/10/2013 11:20 AM Andy
- Add UBS Export module in Extra. change to check privilege "WB".
*}

{if $sessioninfo.privilege.WB}
	<li>
		<a href="#" class="submenu">Extra</a>
			<ul>
				{if $sessioninfo.privilege.WB and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/ngiukee/ubs_export.php")}
					<li><a href="custom/ngiukee/ubs_export.php">UBS Export</a></li>
				{/if}		
			</ul>
		</a>
	</li>
{/if}