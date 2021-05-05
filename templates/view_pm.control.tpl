{*
Revision History
================
4/20/07 2:38:52 PM yinsee
- added branch_id for all PM.php call

9/24/2010 11:12:09 AM Andy
- When user click "Mark as read and close", add checking for parent window opener, if not exists dont try to call the function.

12/2/2010 3:43:58 PM Andy
- Fix notification when click "Mark as read and close", it could close wrong pm.

2/17/2011 12:01:37 PM Andy
- Change view pm if no window.opener, when user click "close" it will redirect itself to main page.

7/20/2015 1:04 PM Joo Chia
- Define prototype.js before form.js to prevent error: Ajax is not defined.
*}
<head>
{config_load file="site.conf"}
<link rel="stylesheet" href="{#SITE_CSS#}" type="text/css">
<script src="/js/prototype.js" language=javascript type="text/javascript"></script>
<script src="/js/forms.js" language=javascript type="text/javascript"></script>
</head>

{literal}
<style>
body {
	background-color:#ccc;
	margin:10px;
}
</style>
<script>
function pm_read(branch_id,id)
{
	new Ajax.Request("pm.php",
		{
		parameters:'a=ajax_mark_read&branch_id='+branch_id+'&id='+id,
		onComplete:function()
		{
			if(parent.window.opener && parent.window.opener.pm_read){
                parent.window.opener.pm_read(branch_id, id);
			}	
			close_pm_window();
		}});
}

function close_pm_window(){
	if(parent.window.opener){   // if this windows is open by other windows
        parent.window.close();  // close this windows
	}else{
		parent.window.location = '/index.php';  // redirect to main page
	}
}
</script>
{/literal}

<p align=center>
<input type=button onclick="pm_read({$branch_id},{$id})" value="Mark as Read and Close">
<input type=button onclick="close_pm_window();" value="Close">
</p>
