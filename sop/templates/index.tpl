{include file='header.tpl'}

<div style="position:relative;">
<!-- left -->
<div style="left:0; padding-right:10px; border-right: 1px dashed #ddd; width:200px;height:100%;position:absolute;">
	{include file='index.notifications.tpl'}
</div>

<!--  right -->
<div style="right:0px; padding-left:10px; border-left: 1px dashed #ddd; width:200px;height:100%;position:absolute;">
	{include file='index.reminder.tpl'}
</div>

<!-- center -->
<div style="margin-left:220px;margin-right:220px;">
    <h1>Welcome to SOP</h1><hr />
    {include file='index.pm.tpl'}
</div>
</div>
{include file='footer.tpl'}
