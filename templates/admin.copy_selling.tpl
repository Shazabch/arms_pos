{*
8/6/2010 12:17:28 PM yinsee
- add resync reminder

12/9/2010 3:42:24 PM Andy
- Add department filter. (base on user department privilege)

3/22/2013 12:27 PM Justin
- Enhanced to allow user add additional selling price.
*}
{include file=header.tpl}
<div class="container">
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
            </div>
        </div>
    </div>
   <div class="alert alert-primary mx-3" role="alert">
    <b>Note:</b> You need to copy selling price before setup counter. If counter is already setup, please resync master file.
   </div>
   
   <div class="card mx-3">
       <div class="card-body">
        <form name=f1 method=post onsubmit="return confirm('Are you sure?')">
            <input type=hidden name="a" value="copy_selling">
            <label>From</label> {dropdown values=$branches key=id value=code name=from_branch class="form-control select2"}
            <label class="mt-1">To</label> {dropdown values=$branches key=id value=code name=to_branch}
            
            <label class="mt-1">Department</label> {dropdown values=$depts key=id value=description name=dept_id all='-- All --' class="form-control select2"}

            <br>
            <div class="row mt-1">
                <div class="col-md-6">
                    <input type="checkbox" name="clear" {if $smarty.request.clear}checked {/if} /> 
                    <label>Clear existing selling price</label>
                </div>
                <div class="col-md-6">
                    {if $config.masterfile_branch_enable_additional_sp}
                    <input type="checkbox" name="clear" {if $smarty.request.clear}checked {/if} /> 
                    <label>Add additional selling price</label>
                    {/if}
                </div>
            </div>
          
            <button class="btn btn-primary mt-1" value="Copy">&nbsp;Copy&nbsp;</button>
            
       </div>
   </div>
    {if $msg}
    <div class="card mx-3">
        <div class="card-body">
            <p>{$msg}</p>
        </div>
    </div>
    {/if}
</div>
{include file=footer.tpl}
