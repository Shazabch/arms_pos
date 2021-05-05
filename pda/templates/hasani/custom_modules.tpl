{*
03/11/2020 4:00 PM Rayleen 
- Applied new Menu Design
*}

{if $sessioninfo.privilege.STOCK_TAKE && file_exists("`$smarty.server.DOCUMENT_ROOT`/pda/custom/hasani/stock_take.php")}
	{if $smarty.session.st.date and $smarty.session.st.location and $smarty.session.st.shelf and $smarty.session.st.branch_id eq $sessioninfo.branch_id}
		<div class="home-menu-column">
		  	<a href="/pda/custom/hasani/stock_take.php?a=show_scan">
			    <div><p>Continue Last Stock Take</p></div>
		    </a>
		</div>
	{/if}
	<div class="home-menu-column">
	  	<a href="/pda/custom/hasani/stock_take.php">
		    <div><p>Stock Take</p></div>
	    </a>
	</div>
{/if}

{*
<li>Custom Modules
	<ul>
		{if $sessioninfo.privilege.STOCK_TAKE && file_exists("`$smarty.server.DOCUMENT_ROOT`/pda/custom/hasani/stock_take.php")}
			{if $smarty.session.st.date and $smarty.session.st.location and $smarty.session.st.shelf and $smarty.session.st.branch_id eq $sessioninfo.branch_id}
				<li><a href="/pda/custom/hasani/stock_take.php?a=show_scan">Continue Last Stock Take</a></li>
			{/if}
			<li><a href="/pda/custom/hasani/stock_take.php">Stock Take</a></li>
		{/if}
	</ul>
</li>
*}
