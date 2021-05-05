{*
11/11/2010 2:42:53 PM Alex
- add print branches

6/22/2011 2:40:36 PM Andy
- Add show red color for those in-active branch in resort branch sequence.
- Branch sequences printing add can skip in-active branch.
- Fix printing branch sequence cannot follow the position changed by user.

06/26/20 04:33 PM Sheila
- Updated button css
*}


<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
close_sequence = function(){
	//if(confirm('Are you sure to close?'))   
	default_curtain_clicked();
}

move_up = function(){
    var sel_b = $('sel_branch');
    var temp_sel_b = $('sel_temp_branch');
    var first_i = '';
    var first_obj = '';
    var is_first = true;
    
    if(sel_b.selectedIndex<0){
		alert('Please select a branch name.');
		return false;
	}
	
    for(var i=0; i<sel_b.length; i++){
	    var obj = sel_b.options[i];
        if(obj.selected){
            try {
				temp_sel_b.add(obj,null); // standards compliant; doesn't work in IE
			}catch(ex) {
				temp_sel_b.add(obj); // IE only
			}
			i--;
			if(is_first){
                first_i = i;
                is_first = false;
			}	
		}
	}
	
	if(!first_i||first_i<0) first_i = 0;
	
	first_obj = sel_b.options[first_i];
	swap_back(first_obj,first_i);
}

move_down = function(){
	var sel_b = $('sel_branch');
	var temp_sel_b = $('sel_temp_branch');
	var last_i = 0;
	
	if(sel_b.selectedIndex<0){
		alert('Please select a branch name.');
		return false;
	}
	
	for(var i=0; i<sel_b.length; i++){
	    var obj = sel_b.options[i];
        if(obj.selected){
            try {
				temp_sel_b.add(obj,null); // standards compliant; doesn't work in IE
			}catch(ex) {
				temp_sel_b.add(obj); // IE only
			}
			i--;
			last_i = i;
		}
	}
	
	last_obj = sel_b.options[last_i+2];
	swap_back(last_obj,last_i+2);
}

swap_back = function(obj_position,position){
    var sel_b = $('sel_branch');
    var temp_sel_b = $('sel_temp_branch');
    
    for(var i=0; i<temp_sel_b.length; i++){
	    var obj = temp_sel_b.options[i];
	    try{
            sel_b.add(obj,obj_position);    // standards compliant; doesn't work in IE
		}catch(ex){
            sel_b.add(obj,position);    // IE only
		}
        
        i--;
        obj.selected = true;
	}
}

save_sequence = function(ele){
    var sel_b = $('sel_branch');
    var bid_array = [];
    if(!confirm('Click OK to save'))    return false;
    
    ele.disabled = true;
    // build branch id array base on the position
    for(var i=0; i<sel_b.length; i++){
		bid_array.push(sel_b.options[i].value);
	}
	
	new Ajax.Request(phpself,{
		method: 'post',
		parameters: {
			a: 'save_branch_sequence',
			bid_list: $A(bid_array).toString()
		},
		onComplete: function(e){
			if(e.responseText=='OK'){
				default_curtain_clicked();
			}else{
				alert(e.responseText);
				ele.disabled = false;
			}
		}
	});
}

{/literal}
</script>

<div style="float:right;padding:3px;"><img src="ui/closewin.png" onClick="close_sequence();" /></div>
<form method=post name="f_sequence" id="f_sequence">
<input type=hidden name="a" value="sort_sequence">
<input type=hidden name="sort_by" value="sequence">
<select id="sel_temp_branch" style="display:none;">
</select>
<table width="100%" border=0 cellspacing=0 cellpadding=2>
<tr><th><h2>Sort Branch Sequence</h2></th></tr>
<tr>
	<td>
		<select name="sel_branch[]" id="sel_branch" multiple size=20 style="width:100%">
		{foreach from=$branches key=bid item=r name=i}
		<option value="{$bid}" class="opt_b {if !$r.active}branch_inactive{/if}" >{$r.code} - {$r.description}</option>
		{/foreach}
		</select>
	</td>
</tr>
<tr>
    <td valign=top>
		<input type=button value="Up" onclick="move_up();">
		<input type=button value="Dn" onclick="move_down();">
		<input type=button value="Sort by Code" onclick="sort_sequence('code');">
		<input type=button value="Sort by Description" onclick="sort_sequence('description');">
		<input type=button value="Print Branches" onclick="print_branch_sequence();">
		<input type="checkbox" name="skip_inactive" value="1" {if $smarty.request.skip_inactive}checked {/if} /> Skip in-active
	</td>
</tr>
<tr>
<td colspan=2 align=center>
<input class="btn btn-success" type=button value="Save" onclick="save_sequence(this);">
&nbsp;&nbsp;&nbsp;
<input class="btn btn-error" type=button value="Close" onclick="close_sequence();">
</td>
</tr>
</table>
</form>
<br />
