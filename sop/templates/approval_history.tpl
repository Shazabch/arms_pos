{literal}
<style>


</style>
{/literal}

{if $approval_history_data}
	{if !$screen_id}{assign var=screen_id value=1}{/if}
	
	<div class="stdframe ui-corner-all" style="margin:1em 0;">
		<h4 class="ui-state-default ui-corner-all slider_header clickable" id="h_approval_history_data_header-{$screen_id}" style="padding:5px 10px;position:relative;">
		    <span class="ui-icon ui-icon-triangle-1-s span_icon" style="top:50%;margin-top:-8px;position:absolute;"></span>
			<span style="padding:20px;">Approval History</span>
		</h4>
		<div class="slider_container ui-corner-all stdframe" id="div_approval_history_data_container-{$screen_id}" style="background: #fff;max-height:300px;overflow:auto;">
			<ul style="list-style-type:none;" id="aaaaa">
			{foreach from=$approval_history_data item=r}
				<li>
					<span style="margin-left:-20px;">
					{if $r.status==0}
						<img src="/ui/notify_sku_reject.png" width="16" height="16" align="absmiddle" title="Reset/Revoke" />
					{elseif $r.status==1}
						<img src="/ui/approved.png" width="16" height="16" align="absmiddle" title="Approved" />
					{elseif $r.status==2}
						<img src="/ui/rejected.png" width="16" height="16" align="absmiddle" title="Rejected" />
					{elseif $r.status==5}
						<img src="/ui/terminated.png" width="16" height="16" align="absmiddle" title="Terminated" />
					{else}
						<!-- ??? -->
					{/if}
					</span>
					{$r.timestamp} by {$r.u}<br>
					{$r.log}
				</li>
			{/foreach}
			</ul>
		</div>
	</div>
{/if}

<script>
{literal}
	$(function(){
	    var screen_id = {/literal}'{$screen_id}';{literal}  // get screen id
        $('#h_approval_history_data_header-'+screen_id).click(function() {
		  $('#div_approval_history_data_container-'+screen_id).slideToggle('slow', function() {
		    // Animation complete.
		    var span_icon = $('#h_approval_history_data_header-'+screen_id+' span.span_icon');
		    if($(this).css('display')=='none'){
				$(span_icon).removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e');
			}else{
                $(span_icon).removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
			}
		  });
		}).bind('mouseover', function(){    // mouseover event
			$(this).addClass('ui-state-hover')
		}).bind('mouseout', function(){ // mouseout event
			$(this).removeClass('ui-state-hover')
		});
	});
	
{/literal}
</script>
