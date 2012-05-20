<?php
/**
 *
 * Simple page to show a list of currencies and their rate against the Euro
 * Also available is a template for a Currency Converter widget
 *
 * User: tim
 * Date: 16/05/2012
 * Time: 20:45
 *
 */



require('currency-convertor.php');

$oPage = new CurrencyExchangePage();
$oPage->initCurrencies();
$oPage->initRates();

$aRates = $oPage->aRates;
$aCurrencies = $oPage->aCurrencies;

$nAmount = floatval($_POST['amount']);
$sFromCurrency = filter_var($_POST['from'],FILTER_SANITIZE_STRING);
$sToCurrency = filter_var($_POST['to'],FILTER_SANITIZE_STRING);
$bProcess = $_POST['currency_converter_process'] === '1' ? true : false;

// Initialise the converter widget panel
list($nConversionAmount,$nConversionAmountGBPBase) = $oPage->initPanel($nAmount,$sFromCurrency,$sToCurrency,$bProcess);

if($_POST['ajx']==1) {
 // calculate the conversion rate requested.
  $oPage->calculate($nAmount, $sFromCurrency, $sToCurrency);
  exit;
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Currency Converter</title>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
</head>
<body>

<script type="text/javascript">
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
      url: "index.php",
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
</script>

<div id="currency-converter-panel">

  {*<!--
  <div id="currency-converter-options" class="panel-options">

    no options

  </div>
  -->*}


  <div id="currency-converter-content">

    <form id="currency-convert-form" action="index.php" method="post">
      <table id="converterwidget" cellpadding="3" cellspacing="1">
        <thead>
        <tr>
          <th colspan="4">Currency Converter</th>
        </tr>
        </thead>
        <tbody>
        <tr>
          <th><label for="from">From</label></th>
          <td id="selfrom">
            <span id="fromimg">&nbsp;</span>
            <select name="from" id="from" class="country">
              <option value="">- Please Select -</option>
              <?php
              if ( !empty($aCurrencies) && is_array($aCurrencies) ) {
                foreach ($aCurrencies as $sCur => $aCur) {
                  echo '<option label="' . $sCur . '" value="' . $sCur . '" '. ( $sCur == $_POST['from'] ? ' selected="selected" ' : null ) . '>' . $sCur . '</option>';
                }
              }
              ?>

            </select>
          </td>
          <th><label for="amount">Amount</label></th>
          <td class="short">
            <input class="text" name="amount" id="amount" size="4" value="<?php echo htmlentities( sprintf('%.2f',$_POST['amount']) ) ; ?>" />
          </td>
        </tr>

        <tr>
          <th ><label for="to">To</label></th>
          <td id="selto">
            <span id="toimg">&nbsp;</span>
            <select name="to" id="to" class="country">
              <option value="">- Please Select -</option>
              <?php
              if ( !empty($aCurrencies) && is_array($aCurrencies) ) {
                foreach ($aCurrencies as $sCur => $aCur) {
                  echo '<option label="' . $sCur . '" value="' . $sCur . '" '. ( $sCur == $_POST['to'] ? ' selected="selected" ' : null ) . '>' . $sCur . '</option>';
                }
              }
              ?>
            </select>
          </td>
          <th id="resultfield"><label for="resultfield">=</label>
          </th>
          <td id="conversion-result" headers="resultfield" class="short brdr-br">
            <?php
                echo $nConversionAmountGBPBase;
             ?>
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




  <hr />


<?php

  // loop through the list of currencies, show the rates and market shift.
  $aDispRates = array('EUR'=>'Euro','GBP'=>'Sterling');
  foreach ($aDispRates as $sRateCode => $sRateLabel) {

    echo '<h2>' . $sRateLabel .' foreign exchange rates</h2>';

    if ($sRateCode=='EUR') {
      echo '<p class="attrib">Source: The European Central Bank</p>';
    }

    if ($sLastCheck != '') {
      echo '<p><span class="greydate">Last update: ' . date( '%H:%M on %d.%m.%Y', $sLastCheck ) . '</span></p>';
    }
    ?>

  <div id="currencylist<?php echo $sRateCode; ?>">
    <table id="<?php echo $sRateCode; ?>rates" cellpadding="6" cellspacing="1" class="dayrates datatable" >
      <thead>
      <tr>
        <th class="curlabel" id="<?php echo $sRateCode; ?>currency">Currency</th>
        <th class="rte"   id="<?php echo $sRateCode; ?>ytd">Yesterday</th>
        <th class="rte"   id="<?php echo $sRateCode; ?>tdy">Today</th>
        <th class="shift" id="<?php echo $sRateCode; ?>shift"><span class="hidden">change</span></th>
      </tr>
      </thead>
      <tbody>
      <?php
      if (is_array($aRates[$sRateCode] )) {
      foreach ($aRates[$sRateCode] as $sCode => $aCurrency) {
        if ($sCode != $sRateCode) {
      ?>
      <tr>
        <th class="curlabel" headers="<?php echo $sRateCode ;?>currency"  id="<?php echo $sRateCode ;?>cur<?php echo $sCode ;?>"><?php echo $sCode; ?></th>
        <td class="rte light" headers="<?php echo $sRateCode ;?>ytd <?php echo $sRateCode ;?>cur<?php echo $sCode ;?>"><?php echo sprintf('%0.4f',$aCurrency['prev_rate']); ?></td>
        <td class="rte" headers="<?php echo $sRateCode ;?>tdy <?php echo $sRateCode ;?>cur<?php echo $sCode ;?>"><?php echo sprintf('%0.4f',$aCurrency['rate']); ?> </td>
        <td class="shift light" headers="<?php echo $sRateCode ;?>shift <?php echo $sRateCode ;?>cur<?php echo  $sCode ;?>">
          <?php
            switch (true) {
              case $aCurrency['sShiftDirection']== '-';
                break;
                echo '-';
              case $aCurrency['sShiftDirection']== '-ve';
                break;
                echo '-';
              case $aCurrency['sShiftDirection']== '+ve';
                break;
                echo '-';
            }

?>
        </td>
      </tr>
      <?php
          } // end if
      } // end foreach
      } // end if ?>
      </tbody>
    </table>
  </div>
<?php
  } //end foreach
?>




</div>
</body>
</html>