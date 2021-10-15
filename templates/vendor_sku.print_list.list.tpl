{*
REVISION HISTORY 
++++++++++++++++

10/29/2007 4:12:22 PM gary
- add brand column.

11/1/2007 12:37:36 PM gary
- add artno column.

11/17/2015 2:30 PM Qiu Ying
- add report title.

5/22/2017 4:44 PM Justin
- Enhanced Load Vendor SKU to check master vendor if got config po_vendor_listing_enable_check_master_vendor.
*}
{if $is_export}
<h3>Vendor: {$vendor_info.description}</h3>
{/if}
{if !$items}
-- No Item --
{else}
<div class="alert alert-primary rounded mx-3">
    Found {count var=$items} item(s).
</div>
<div class="card mx-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="tb table mb-0 text-md-nowrap  table-hover" >
               <thead class="bg-gray-100">
                <tr >
                    <th>ARMS Code</th>
                    <th>MCode</th>
                    {if $config.link_code_name}
                    <th>{$config.link_code_name}</th>
                    {/if}
                    <th>Artno</th>
                    <th>Description</th>
                    <th>Dept</th>
                    <th>Brand</th>
                    {if $smarty.request.show_cost}<th width=50>GRN Cost</th>{/if}
                    <th width=50>Cost</th>
                    <th width=50>Qty</th>
                    <th width=200>Remark</th>
                    </tr>
               </thead>
             <tbody class="fs-08">
                {foreach from=$items name=i key=sid item=item}
                {if $last_dept ne $item.department}
                {assign var=last_dept value=$item.department}
                <tr><th align=left colspan={if $smarty.request.show_cost}11{else}10{/if}>{$item.department}</th></tr>
                {/if}
             </tbody>
              <tbody class="fs-08">
                <tr>
                    <td>{$item.sku_item_code}&nbsp;</td>
                    <td>{$item.mcode|default:"&nbsp;"}&nbsp;</td>
                    {if $config.link_code_name}
                    <td>{$item.link_code|default:"&nbsp;"}&nbsp;</td>
                    {/if}
                    <td>{$item.artno|default:"&nbsp;"}&nbsp;</td>
                    <td class=small>{$item.description|default:"&nbsp;"}&nbsp;</td>
                    <td class=small>{$item.department|default:"&nbsp;"}&nbsp;</td>
                    <td class=small>{$item.brand|default:"&nbsp;"}&nbsp;</td>
                    {if $smarty.request.show_cost}<td align=right>{$item.grn_cost|number_format:2|default:"&nbsp;"}</td>{/if}
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    </tr>
              </tbody>
                {/foreach}
                </table>
        </div>
    </div>
</div>
{/if}
