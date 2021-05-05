{include file='header.tpl' no_menu_templates=1}

<style>
{literal}
input.btn_close{
	width: 100px;
	height: 100px;
	font-size: 20pt;
	color: red;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var ATTENDANCE_CLOCK_INFO = {
	countdown_sec: 10,
	initialize: function(){
		$('btn_close').focus();
		
		this.start_countdown_timer();
	},
	// function when user click on button close
	close_clicked: function(){
		document.location = phpself;
	},
	start_countdown_timer: function(){
		// Check again server time after 60 second
		setTimeout(function(){
			ATTENDANCE_CLOCK_INFO.reduce_countdown_sec();
		}, 1000);
	},
	reduce_countdown_sec: function(){
		this.countdown_sec--;
		$('span_countdown_sec').update(this.countdown_sec);
		
		if(this.countdown_sec>0){
			this.start_countdown_timer();
		}else{
			document.location = phpself;	// redirect
		}
	}
	
}
{/literal}
</script>

<div style="text-align:center;">
	<div class="stdframe" style="width: 450px; margin:auto;">
		{if $status eq 'start_work'}
			{* Just come to work *}
			<h1>Welcome to Work, <span style="color: blue;">{$user.u}</span></h1>
			<h1 style="color:blue;">[{$user.fullname}]</h1>
			
			<h2>Sign In Time</h2>

			<h1 style="color: blue;">{$record_1.scan_time}</h1>
				
		{elseif $status eq 'leave_work'}		
			<h1>Bye, see you again, <span style="color: blue;">{$user.u}</span></h1>
			<h1 style="color:blue;">[{$user.fullname}]</h1>
			
			<h2>Sign Out Time</h2>

			<h1 style="color: blue;">{$record_1.scan_time}</h1>
			
			{* Last Work Duration *}
			{if $last_work_sec ne $total_work_sec}
				Last Work Duration: {show_duration seconds=$last_work_sec}
				<br />
			{/if}
			
			{* Total Work Duration *}
			Total Work Duration: {show_duration seconds=$total_work_sec}
			
		{elseif $status eq 'end_break'}
			<h1>Welcome Back, <span style="color: blue;">{$user.u}</span></h1>
			<h1 style="color:blue;">[{$user.fullname}]</h1>
			
			<h2>Sign In Time</h2>

			<h1 style="color: blue;">{$record_1.scan_time}</h1>
			
			{* Break *}
			Break Duration: {show_duration seconds=$break_duration}
		{/if}
		
		
		<br /><br />
		<input type="button" id="btn_close" value="OK" class="btn_close" onClick="ATTENDANCE_CLOCK_INFO.close_clicked();" />
		<br />
		Auto redirect in <span id="span_countdown_sec">10</span> seconds.
	</div>
</div>

<script>ATTENDANCE_CLOCK_INFO.initialize();</script>
{include file='footer.tpl'}