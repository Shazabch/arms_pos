{*
10/2/2019 1:22 PM William
- Add new extra module "Machines Upload Summary".

04/08/2020 03:56 PM Sheila
- Modified layout to compatible with new UI.

*}

{if $sessioninfo.privilege.DO}
	<li>
		<a href="#" class="submenu"><i class="icofont-ui-rate-add icofont header-icon"></i>Extra</a>
			<ul>
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/kbfun/machines_upload_summary.php")}
					<li><a href="custom/kbfun/machines_upload_summary.php">Machines Upload Summary</a></li>
				{/if}
			</ul>
		</a>
	</li>
{/if}