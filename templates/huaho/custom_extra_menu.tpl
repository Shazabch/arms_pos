{*
7/23/2018 10:16 AM Andy
- Hua Ho Gusta Accounting AP Format.

04/08/2020 04:01 PM Sheila
- Modified layout to compatible with new UI.

*}

{if $sessioninfo.privilege.WB}
	<li>
		<a href="#" class="submenu"><i class="icofont-ui-rate-add icofont header-icon"></i>Extra</a>
			<ul>
				{if $sessioninfo.privilege.WB and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/huaho/gusta_ap.php")}
					<li><a href="custom/huaho/gusta_ap.php">GUSTA AP</a></li>
				{/if}		
			</ul>
		</a>
	</li>
{/if}