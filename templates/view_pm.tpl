{*
Revision History
================
4/20/07 2:38:52 PM yinsee
- added branch_id for all PM.php call
*}
<frameset rows="*,50" border=none>
	<frame src="{$url}">
	<frame src="pm.php?a=control&branch_id={$branch_id}&id={$id}">
</frameset>
