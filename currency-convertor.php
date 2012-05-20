<?php
/**
 * Renders a list of currencies and their GBP and EUR rates
 * Also offers a currency converter widget that allows users
 * to enter a value, from and to currencies and see the result.
 */


class CurrencyExchangePage  {

  /**
   * Holder for the list of exchange rates.
   *
   * @var array $aCurrencies
   **/
  var $aCurrencies;

  /**
   * as above but previous day's figures.
   * @var $aOldCurrencies
   */
  var $aOldCurrencies;

  /**
   * an array of the exchange rates
   * @var $aRates
   */
  var $aRates;

  /**
   * date the rate-update was last checked and saved.
   * @var $sLastCheckTimestamp
   */
  var $sLastCheckTimestamp;

  /**
   * Get list of currencies
   * @access
   * @param
   * @return
   */
  public function initCurrencies()
  {
    $aItems = array();

    // open the currencies file
    $sJSON = file_get_contents('currencies.json');
    $aJSON = json_decode($sJSON,true); //2nd param specifies an assoc array

    $sTimestamp = $aJSON['timestamp'];
    $aItems = $aJSON['aCurrencies'];

    $this->aCurrencies = $aItems;
    $this->aOldCurrencies = $aJSON['aOldCurrencies'];

    return $aItems;
  }


  /**
   * Performs the conversion from the widget/form POST
   *
   * @param number $nAmount
   * @param string $sFrom
   * @param string $sTo
   * @param string $sBase [eur or gbp]
   * @return number - the amount of $sFrom $nAmount in $sTo, converted via $sBase
   */
  private function performConversion($nAmount, $sFrom, $sTo, $sBase = 'eur')
  {
    $nAmount = floatval($nAmount);

    $nFromRate  = $this->getOneRate($sFrom, $sBase);
    $nToRate    = $this->getOneRate($sTo, $sBase);
    $nConverted = $nAmount / $nFromRate * $nToRate;

    return sprintf("%01.2f", $nConverted);
  }



  /**
   * gets the Rate of One currency against the base currency
   *
   * @param string $sCode
   * @param string $sBase
   * @return number - the amount of $sCode that is worth 1 $sBase
   */
  private function getOneRate($sCode, $sBase)
  {

    $nBase = $this->aCurrencies[$sCode][$sBase];

    return $nBase;
  }



  public function initRates()
  {
    $aItems = array();

    if(empty($this->aCurrencies) || !is_array($this->aCurrencies)) {
      $this->initCurrencies();
    }

    if (!empty($this->aCurrencies) && is_array($this->aCurrencies)) {
      foreach ($this->aCurrencies as $sCurrency => $aItem ) {
        $sCurrency = trim($sCurrency);
        if( empty( $sCurrency ) ) {
          continue ;
        }

        if(!empty($this->aOldCurrencies) && array_key_exists($sCurrency,$this->aOldCurrencies) ) {
          $nPrevRate = $this->aOldCurrencies[$sCurrency]['gbp'];
          // work out if today's rate is better or worse than the previous rate
          $nShift = $aItem['gbp'] - $this->aOldCurrencies[$sCurrency]['gbp'];
          switch (true) {
            case $nShift ==0:
              $bShiftGBP = '-';
              break;
            case $nShift >0:
              $bShiftGBP = '+ve';
              break;
            case $nShift <0:
              $bShiftGBP = '-ve';
              break;
          }

        } else {
          $bShiftGBP = '-';
          $nPrevRate = 0;
          $nShift = 0;
        }


        $aRates['GBP'][$sCurrency] = array('code'            => $sCurrency,
                                           'rate'            => $aItem['gbp'],
                                           'prev_rate'       => $nPrevRate,
                                           'nShift'          => $nShift,
                                           'sShiftDirection' => $bShiftGBP,
                                           'sLastCheck'      => $this->sLastCheckTimestamp
                                          ) ;

        // Basically the same again but measured against an EUR base rate.
        if(!empty($this->aOldCurrencies) && array_key_exists($sCurrency,$this->aOldCurrencies) ) {
          $nPrevRateEur = $this->aOldCurrencies[$sCurrency]['eur'];
          // work out if today's rate is better or worse than the previous rate
          $nShiftEur = $aItem['eur'] - $this->aOldCurrencies[$sCurrency]['eur'];
          switch (true) {
            case $nShiftEur ==0:
              $bShiftEUR = '-';
              break;
            case $nShiftEur >0:
              $bShiftEUR = '+ve';
              break;
            case $nShiftEur <0:
              $bShiftEUR = '-ve';
              break;
          }
        } else {
          $bShiftEUR = '-';
          $nPrevRateEur = 0;
          $nShiftEur = 0;
        }

        $aRates['EUR'][$aItem['code']] = array('code'            => $aItem['code'],
                                               'rate'            => $aItem['eur'],
                                               'prev_rate'       => $nPrevRateEur,
                                               'nShift'          => $nShiftEur,
                                               'sShiftDirection' => $bShiftEUR,
                                               'sLastCheck'      => $this->sLastCheckTimestamp
                                              );

      }
    }

    $this->aRates = $aRates;
    return $aRates;
  }



  /**
   *
   * @access
   * @param
   * @return
   */
  public function initPanel($nAmount=0.00,$sFrom='',$sTo='',$bProcess=false)
  {
    if (
        (is_numeric($nAmount) ) &&
        ($nAmount != '0.00') &&
        $bProcess
        ) {

        // conversion
        $nEURAmount = $this->performConversion($nAmount, $sFrom, $sTo, 'eur');
        $nGBPAmount = $this->performConversion($nAmount, $sFrom, $sTo, 'gbp');

        // $this->oSmarty->assign('nConversionAmount', $nEURAmount);
        // $this->oSmarty->assign('nConversionAmountGBPBase', $nGBPAmount);

        //echo $nGBPAmount;
        //exit();

    }

    $aDispRates = array('EUR'=>'Euro','GBP'=>'Sterling');

    return array('nConversionAmount' => $nEURAmount, 'nConversionAmountGBPBase' => $nGBPAmount);
    /*
    $this->oSmarty->assign('aDispRates', $aDispRates);
    $this->oSmarty->assign('aCurrencies', $this->aCurrencies());
    $this->oSmarty->assign('aRates', $this->aRates());
    */
  }



  function calculate($fAmount, $sFromCurrency, $sToCurrency)
  {
    //$nGBPAmount = $this->performConversion($_POST['amount'], $_POST['from'], $_POST['to'], 'gbp');
    $nGBPAmount = $this->performConversion($fAmount, $sFromCurrency, $sToCurrency, 'gbp');

    echo $nGBPAmount;

  }


}//end class