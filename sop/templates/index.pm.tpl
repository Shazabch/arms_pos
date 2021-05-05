
{literal}
<style>
.unread_pm{
	font-weight:bold;
}
</style>
{/literal}

<script>
var phpself = 'index.php';

{literal}
var PM_MAIN_MODULE = {
	initialize: function(){
	    // event when user click on the pm row
		$('#tbody_pm_list a.a_pm').live('click', function(){
			var id_obj = PM_MAIN_MODULE.get_id(this);   // get branch id & id
			PM_MAIN_MODULE.mark_read(id_obj['branch_id'], id_obj['id']);    // mark as read
		});
		
		// event when user click delete pm
		$('#tbody_pm_list img.img_delete_pm_row').live('click', function(){
            var id_obj = PM_MAIN_MODULE.get_id(this);   // get branch id & id
			PM_MAIN_MODULE.remove_pm_row(id_obj['branch_id'], id_obj['id'], 1);    // mark as read
		});
		
		// user click on unread message number
		$('#span_all_pm_unread_count').live('click', function(){
            PM_MAIN_MODULE.move_to_first_unread_pm();
		});
	},
	get_id: function(ele){  // function to get branch id & id; return object contain branch_id, id
		if(!ele)  return {};
		
		var parent_ele = $(ele).get(0);
		while(parent_ele){    // loop parebt until it found the row contain branch id & id
		    if($.trim(parent_ele.tagName).toLowerCase()=='tr'){
                if($(parent_ele).attr('id').indexOf('tr_pm_row-')>=0){    // found the row
					break;  // break the loop
				}
			}
            parent_ele = $(parent_ele).parent().get(0);
		}
		if(!parent_ele) return {};
		var id_arr = $(parent_ele).attr('id').split('-');
		return {'branch_id': id_arr[1], 'id': id_arr[2]};
	},
	remove_pm_row: function(bid, id, need_ajax_update){
		if(!bid || !id) return false;
		$('#tr_pm_row-'+bid+'-'+id).fadeOut('slow', function(){   // fade out the element and delete
			$(this).remove();   // remove the row
			PM_MAIN_MODULE.recalculate_pm();    // recalculate row num
		});
		
		// if need call ajax to update data
		if(need_ajax_update){
		    // construct params
		    var params = {
				a: 'ajax_delete_pm',
				bid: bid,
				id: id
			};
			// call ajax
			$.post(phpself, params);
		}
	},
	mark_read: function(bid, id){
	    if(!bid || !id) return false;
	    
	    var tr_row = $('#tr_pm_row-'+bid+'-'+id);   // get <tr> element
	    var a_ele  = $(tr_row).find('a.a_pm:first');    // get <a> element
        var msg_text = $(a_ele).text(); // get msg text
        
        $(tr_row).removeClass('unread_pm'); // mark it as read
        
        $('#tbody_pm_list a.a_pm').each(function(index){
			if($(this).text()==msg_text){
				$(this).parent().parent().removeClass('unread_pm'); // mark all same notifications as read
			}
		});
		
		this.recalculate_pm();
	},
	recalculate_pm: function(){ // function to recalculate pm row num
	    var total_count = 0;
	    var unread_count = 0;
		$('#tbody_pm_list tr[id^="tr_pm_row-"]').each(function(index){    // loop all <tr>
            total_count++;
            if($(this).hasClass('unread_pm'))   unread_count++;
		});
		$('#span_all_pm_count').text(total_count);
		$('#span_all_pm_unread_count').text(unread_count);
	},
	move_to_first_unread_pm: function(){
		// get the unread message position
		var unread_tr = $('#tbody_pm_list tr.unread_pm:first').get(0);
		if(!unread_tr)	return false;
		
		var new_pos_y = $('#div_pm_list').scrollTop()+$(unread_tr).position().top;
		// move to the position
		$('#div_pm_list').animate({'scrollTop': new_pos_y});
	}
};

function pm_read(bid, id)
{
	PM_MAIN_MODULE.remove_pm_row(bid, id);   // call function to delete
}
{/literal}
</script>

<h4>You have <span id="span_all_pm_count">{$all_pm.count|number_format}</span> message(s). (<u><span id="span_all_pm_unread_count" class="clickable">{$all_pm.unread_count|number_format}</span></u> unread.)</h4>

<div style="float:right;text-align:right;">
	clear
</div><br style="clear:both;" />
<div style="height:300px;border:2px inset black;overflow-x:hidden;overflow-y:auto;position:relative;" class="ui-corner-all" id="div_pm_list">
	<table width="100%" cellpadding="4" cellspacing="1" border="0">
	    <tbody class="tbody_container" id="tbody_pm_list">
		    {foreach from=$all_pm.pm_list item=pm}
		        <tr class="{if !$pm.opened}unread_pm{/if} trhover" id="tr_pm_row-{$pm.branch_id}-{$pm.id}">
		            <td>
						<a href="/pm.php?a=view_pm&branch_id={$pm.branch_id}&id={$pm.id}" target="_blank" class="a_pm">
							{$pm.msg}
						</a> at {$pm.added}
					</td>
					<td width="20"><img src="/ui/closewin.png" align="absmiddle" class="clickable img_delete_pm_row" title="Close" /></td>
		        </tr>
		    {/foreach}
	    </tbody>
	</table>
</div>

<script>
{literal}
	$(function(){
        PM_MAIN_MODULE.initialize();    // initial module
	});
{/literal}
</script>
