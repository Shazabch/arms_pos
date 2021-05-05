{if $dataGAF || $data}
  {if $dataGAF}
    {$dataGAF|nl2br}
  {else}
    <table border="1" cellpadding="2" width="100%">
      {foreach name="cs" from=$data item=i}
        {if $smarty.foreach.cs.first}
          <tr>
          {foreach from=$i item=d}
            <th nowrap>{$d}</th>
          {/foreach}
          </tr>
        {else}
          <tr>
            {if $i[0] eq 'DETAIL'}
              {foreach name="cs_data" from=$i item=d}
                {assign var="idx" value=$smarty.foreach.cs_data.index}
                {if $smarty.foreach.cs_data.first}
                  <td nowrap align="right">{$d}</td>
                {else}
                  <td nowrap {if $header2[$idx]} align="right" {/if}>{$d}</td>
                {/if}
              {/foreach}
            {else}
              {foreach name="cs_data" from=$i item=d}
                {assign var="idx" value=$smarty.foreach.cs_data.index}
                <td nowrap {if $header[$idx]} align="right" {/if}>{$d}</td>
              {/foreach}
            {/if}
          </tr>
        {/if}
      {/foreach}
    </table>

    {if $dataPT}
    <br/>
    <table border="1" cellpadding="2" width="100%">
      {foreach name="cs" from=$dataPT item=i}
        {if $smarty.foreach.cs.first}
          <tr>
          {foreach from=$i item=d}
            <th nowrap>{$d}</th>
          {/foreach}
          </tr>
        {else}
          <tr>
            {if $i[0] eq 'DETAIL'}
              {foreach name="cs_data" from=$i item=d}
                {assign var="idx" value=$smarty.foreach.cs_data.index}
                {if $smarty.foreach.cs_data.first}
                  <td nowrap align="right">{$d}</td>
                {else}
                  <td nowrap {if $header2[$idx]} align="right" {/if}>{$d}</td>
                {/if}
              {/foreach}
            {else}
              {foreach name="cs_data" from=$i item=d}
                {assign var="idx" value=$smarty.foreach.cs_data.index}
                <td nowrap {if $header[$idx]} align="right" {/if}>{$d}</td>
              {/foreach}
            {/if}
          </tr>
        {/if}
      {/foreach}
    </table>
    {/if}

    {if $count gte 100}<center><h3>Please export to view full item</h3></center>{/if}
  {/if}
{else}
 No Data found.
{/if}
