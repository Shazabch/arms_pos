{include file='header.tpl'}

{literal}
<style>
input[readOnly]{
    background-color:#cfcfcf;
}
</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var AUTO_REDEMP_SETUP = {
    initialize: function(){
        var THIS = this;
        
        // event for each "allow to use" checkbox
        $(document.f_a).getElementsBySelector("input.inp_allowed").each(function(inp){
            inp.observe('change', function(){
                THIS.input_allowed_changed(inp);          
            });    
        });
        
        // event for "Save" button
        $('btn_save').observe('click', function(){
           THIS.submit_form(); 
        });
    },
    // function when user toggle "allow to use" checkbox
    input_allowed_changed: function(inp){
        var parent_tr = this.get_parent_ele_by_element(inp);
        var ele_list = this.get_all_element_by_tr(parent_tr);
        
        this.change_tr_element_editable(parent_tr, ele_list['allowed'].checked);
    },
    get_all_element_by_tr: function(tr){
        var ret = {};
        if(!tr) return false;
        
        ret['allowed'] = $(tr).getElementsBySelector("input.inp_allowed")[0];
        ret['points_use'] = $(tr).getElementsBySelector("input.inp_points_use")[0];
        ret['max_qty'] = $(tr).getElementsBySelector("input.inp_max_qty")[0];
        
        return ret;
    },
    // function to return parent <tr>
    get_parent_ele_by_element: function(ele){
        var parent_ele = ele

        while(parent_ele){    // loop parebt until it found the tr
            if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_voucher_list_row')){    // found the div
                    break;  // break the loop
               }
            }
            
            // still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
        }
            
        if(!parent_ele) return 0;
        
        return parent_ele;
    },
    // function to toggle, readonly for input 
    change_tr_element_editable: function(parent_tr, editable){
        var ele_list = this.get_all_element_by_tr(parent_tr);
        
        ele_list['points_use'].readOnly = !editable;
        ele_list['max_qty'].readOnly = !editable;
    },
    check_form: function(){
        var tr_list = $(document.f_a).getElementsBySelector("tr.tr_voucher_list_row");
        
        for(var i=0; i<tr_list.length; i++){
            var tr = tr_list[i];    // each <tr>
            var ele_list = this.get_all_element_by_tr(tr);  // all element under this <tr>
            
            if(ele_list['allowed'].checked){    // allow to use is tick
            	var points_use = int(ele_list['points_use'].value);
            	var max_qty = int(ele_list['max_qty'].value);
            	
                if(points_use<0){   // check points use
                    alert('Please enter a valid point(s). Cannot negative.');
                    ele_list['points_use'].focus();
                    return false;
                }
                
                if(max_qty<0){
                	alert('Please enter a valid limit. Cannot negative.');
                    ele_list['max_qty'].focus();
                    return false;
                }
                
                if(points_use<=0 && max_qty<=0){
                	alert('You cannot put both Points and limit blank.');
                    ele_list['points_use'].focus();
                    return false;
                }
            }
        }
        return true;    
    },
    submit_form: function(){
        if(!this.check_form())  return false;
        
        $('btn_save').disabled = true;
        $('span_saving').show();
        
        var params = $(document.f_a).serialize();
        
        new Ajax.Request(phpself+'?a=ajax_save', {
            parameters: params,
            method: 'post',
            onComplete: function(e){
                var str = e.responseText.trim();
                
                if(str == 'OK'){
                    window.location = phpself;
                }else{
                    alert(str);
                }
                
                $('btn_save').disabled = false;
                $('span_saving').hide();
            }
        });
    }
}
{/literal}

</script>

<h1>{$PAGE_TITLE}</h1>

<div class="stdframe">
    <form name="f_a" onSubmit="return false;">        
        <table class="report_table" style="background-color:white;">
            <tr class="header">
                <th>Allow to use</th>
                <th>Voucher <br />Value</th>
                <th>Points <br />per Voucher [<a href="javascript:void(alert('Not Set = Free Voucher'))">?</a>]</th>
                <th>Limit [<a href="javascript:void(alert('Limit Voucher Qty per member per batch'))">?</a>]</th>
            </tr>
            
            {foreach from=$voucher_value_list key=k item=r}
                <tr class="tr_voucher_list_row">
                    <td align="center">
                        <input type="hidden" name="voucher_list_id[{$k}]" value="{$r.info.branch_id}-{$r.info.id}" />
                        <input type="checkbox" name="allowed[{$k}]" value="1" {if $r.info.allowed}checked {/if} class="inp_allowed" />
                    </td>
                    
                    <!-- Value -->
                    <td class="r">
                        <input type="hidden" name="voucher_value[{$k}]" value="{$r.voucher_value}" />
                        {$r.voucher_value}
                    </td>
                    
                    <!-- Point -->
                    <td align="center">
                        <input type="text" size="5" name="points_use[{$k}]"  {if !$r.info.allowed}readOnly {/if} class="inp_points_use" style="text-align:right" onChange="miz(this);" value="{$r.info.points_use|ifzero:''}" />
                    </td>
                    
                    <!-- Max Qty -->
                    <td align="center">
                        <input type="text" size="5" name="max_qty[{$k}]" {if !$r.info.allowed}readOnly {/if} class="inp_max_qty" style="text-align:right" onChange="miz(this);" value="{$r.info.max_qty|ifzero:''}" />
                    </td>
                </tr>
            {/foreach}
        </table>
        
        <p>
            <input class="btn btn-primary" type="button" value="Save" id="btn_save" />
            <span id="span_saving" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Updating...</span>
        </p>
    </form>
</div>

<script type="text/javascript">
    AUTO_REDEMP_SETUP.initialize();
</script>
{include file='footer.tpl'}