<script type="text/javascript">{literal}
//<![CDATA[

  $(document).ready(function(){


    $('select.country').change(function(){
      var sCode = $(this).val();
      var field = $(this).attr('id');
      $('#img'+field).remove();

      var sImg = sCode!='' ? '<img src="/img/flags/'+sCode+'.gif" alt="'+sCode+'" id="img'+field+'" />' : '' ;

      $('span#'+field+'img').html(sImg);

      if ($("form#currency-convert-form select#to").val()!='' && $("form#currency-convert-form input#amount").val()>0 && $("form#currency-convert-form select#from").val()!='' ) {
        $("form#currency-convert-form").submit();
      }

    });

    $('input#amount').keyup(function(){
      if ( $(this).val()!='' && $(this).val()!='0.00' && $("select#to").val()!='' && $("select#from").val()!='' ) {
        $("form#currency-convert-form").submit();
      }
    });


    $("form#currency-convert-form").submit(function(){

      $.ajax({
        type: "POST",
        url: "/economy/currency/calculate",
        data: ({
          amount: $("form#currency-convert-form input#amount").val(),
          from:   $("form#currency-convert-form select#from").val(),
          to:     $("form#currency-convert-form select#to").val(),
          ajx:    1
        }),

        success: function(data) {
          $('#conversion-result').html(data);
        }

      });

      return false;

    });


  });

//]]>
{/literal}</script>

<div id="currency-converter-panel">

  {*<!--
  <div id="currency-converter-options" class="panel-options">

    no options

  </div>
  -->*}


  <div id="currency-converter-content">

    <form id="currency-convert-form" action="/economy/currency/" method="post">
    <table id="converterwidget" cellpadding="3" cellspacing="1">
      <thead>
        <tr>
          <th colspan="4">Currency Converter</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th><label for="from">From</label></th>
          <td is="selfrom">
                <span id="fromimg">&nbsp;</span>
                <select name="from" id="from" class="country">
                <option value="">- Please Select -</option>
                {html_options options=$aCurrencies selected=$smarty.post.from|escape}
                </select>
          </td>
          <th><label for="amount">Amount</label></th>
          <td class="short">
            <input class="text" name="amount" id="amount" size="4" value="{$smarty.post.amount|string_format:'%.2f'|escape}" />
          </td>
        </tr>

        <tr>
          <th ><label for="to">To</label></th>
          <td id="selto">
              <span id="toimg">&nbsp;</span>
              <select name="to" id="to" class="country">
              <option value="">- Please Select -</option>
              {html_options options=$aCurrencies selected=$smarty.post.to|escape}
            </select>
          </td>
          <th id="resultfield"><label for="resultfield">=</label>
          </th>
          <td id="conversion-result" headers="resultfield" class="short brdr-br">
          {if $nConversionAmount}
            {include file="front-end/myres/panels/currency-converter-result.tpl"}
          {/if}
          </td>
        </tr>
        <!--
        <tr>
          <td colspan="3">&nbsp;</td>
          <td ><input type="submit" id="convert"  value="calculate" /></td>
        </tr>
         -->
      </tbody>
    </table>
    <p><input type="hidden" id="currency-converter-process" name="currency_converter_process" value="1" /></p>

    </form>

  </div>

  <div class="panel-actions">

    &nbsp;

  </div>

</div>