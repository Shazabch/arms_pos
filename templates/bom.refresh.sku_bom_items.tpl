<div class="form-inline">
    <select class="form-control" name="bom_id" id=bom_id onchange="load_bom_details();">
        <option value="0" {if $form.bom_id eq '0'}selected{/if}>New Item</option>
        {section name=i loop=$bom}
        <option value={$bom[i].id} {if $form.bom_id eq $bom[i].id}selected{/if}>{$bom[i].id} ( {$bom[i].sku_item_code} : {$bom[i].description} )</option>
        {/section}
        </select>
       
        <input type=button class="btn btn-primary ml-0 ml-md-2 mt-2 mt-md-0" value="Refresh" onclick="load_bom_details();">
        
</div>