
<div class="padded">


{include file='currency/currency-converter.tpl'}



{foreach from=$aDispRates key=sRateCode item=sRateLabel}

<h2>{$sRateLabel|escape} foreign exchange rates</h2>
{if $sRateCode=='EUR'}
<p class="attrib">Source: The European Central Bank</p>
{/if}

{if $aRates.$sRateCode.$sRateCode.sLastCheck!=''}<p><span class="greydate">Last update: {$aRates.$sRateCode.$sRateCode.sLastCheck|date_format:'%H:%M on %d.%m.%Y'}</span></p>{/if}

<div id="currencylist{$sRateCode|escape}">
<table id="{$sRateCode|escape}rates" cellpadding="6" cellspacing="1" class="dayrates datatable" >
  <thead>
    <tr>
      <th class="curlabel" id="{$sRateCode|escape}currency">Currency</th>
      <th class="rte"   id="{$sRateCode|escape}ytd">Yesterday</th>
      <th class="rte"   id="{$sRateCode|escape}tdy">Today</th>
      <th class="shift" id="{$sRateCode|escape}shift"><span class="hidden">change</span></th>
    </tr>
  </thead>
  <tbody>
{foreach from=$aRates.$sRateCode item=aCurrency}
  {if $aCurrency.code!=$sRateCode}
  <tr>
    <th class="curlabel" headers="{$sRateCode|escape}currency"  id="{$sRateCode|escape}cur{$aCurrency.code|escape}">{$aCurrency.currency_label|escape} ({$aCurrency.code|escape})</th>
    <td class="rte light" headers="{$sRateCode|escape}ytd {$sRateCode|escape}cur{$aCurrency.code|escape}">{$aCurrency.prev_rate|number_format:4:".":","}</td>
    <td class="rte" headers="{$sRateCode|escape}tdy {$sRateCode|escape}cur{$aCurrency.code|escape}">{$aCurrency.rate|number_format:4:".":","}</td>
    <td class="shift light" headers="{$sRateCode|escape}shift {$sRateCode|escape}cur{$aCurrency.code|escape}"><img src="{$aCurrency.oShift->sFilename}" width="{$aCurrency.oShift->nWidth}" height="{$aCurrency.oShift->nHeight}" alt="{$aCurrency.nShift|escape} {$aCurrency.sShiftDirection|escape}" /></td>
  </tr>
  {/if}
{/foreach}
</tbody>
</table>
</div>

{/foreach}


</div>


