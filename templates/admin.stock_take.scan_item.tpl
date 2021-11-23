{*
6/8/2011 10:51:55 AM Andy
- Add artno column at stock take.

9/27/2011 12:28:11 PM Justin
- Modified the Ctn and Pcs round up to base on config set.
*}

<div class="table-responsive">
  <table  style="border-collapse:collapse;width:570px;">
    <thead class="bg-gray-100">
      <tr >
        <th width="30"></th>
        <th width="80">ARMS Code</th>
        <th>Artno</th>
        <th width="380">Description</th>
        <th>Quantity</th>
      </tr>
    </thead>
      {foreach from=$item_scan key=k item=r}
          {foreach from=$item_scan.$k key=key_id item=r2}
      <tbody class="fs-08">
        <tr>
          <td><a href="#" onclick="delete_detail({$r2.test_id},{$key_id})"><img src=ui/deact.png title="Deactivate" border=0></a></td>
          <td>{$r2.sku_item_code}</td>
          <td>{$r2.artno}</td>
          <td>{$r2.description}</td>
          <td><input type=text size=3 value="{$r2.qty}" style="text-align:right" onchange="{if $r2.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if}; upd_qty({$r2.test_id},this.value,{$key_id});"></td>
          </tr>
      </tbody>
          {/foreach}
      {/foreach}
    </tr>
    </table>
</div>
