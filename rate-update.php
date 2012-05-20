<?php
/**
 * IMPORT daily spot rates from the European Central Bank
 * The Rates are changed daily between 2:30 and 3pm C.E.T  (GMT + 1)
 * So this script should really be used to be update rates every day at 3:15pm
 */




/**
 * Parses the ECB daily currency conversion XML File and stores the results locally
 *
 */
class RateUpdate {

  protected $sXmlLocationation = 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml';
  protected $sCacheFile = 'currencies.json'; // local file for storing the array of spot rates.
  public $aCurrencies; // holder for the fetched data.
  protected $bForce = true ; //when set the script will update the currencies cache json file regardless of the time it was last run.




  /**
   * This Method can ideally be used as a scheduled task/cron job.
   * 1. Parses XML file into array of currencies
   * 2. Stores Currencies locally.
   */
  public function getNewRates()
  {
    $sXML = file_get_contents($this->sXmlLocationation);
    $oXML = simplexml_load_string($sXML);

    // Populate Array
    foreach($oXML->Cube->Cube->Cube as $oRate) {
      foreach ($oRate->attributes() as $sKey => $sAttribute) {

        if($sKey == 'currency') {

          $sCurrency = (string)$sAttribute;

        } else if($sKey == 'rate') {

          $nRate = (string)$sAttribute;

        }

      }

      $aCurrencies[$sCurrency] = array('eur' => $nRate, 'gbp' => 0);
    }

    // Use the Eur to GBP rate to work out other currency to GBP rates.
    $aCurrencies['EUR'] = array('eur' => 1, 'gbp' => 0);

    $nGbpRate = $aCurrencies['GBP']['eur'];

    foreach ($aCurrencies as $sCurrency => $aRates) {

      $aCurrencies[$sCurrency]['gbp'] = round($aRates['eur'] / $nGbpRate,6);

    }

    // Have we previously written a file? If so grab those aCurrencies and set them as aOldCurrencies
    if(is_file($this->sCacheFile) ) {
      $sOldJson = file_get_contents($this->sCacheFile);
      try {
        $aOld = json_decode($sOldJson,true);
        // What's eth timestamp on the old file?
        $nOldTime = $aOld['timestamp'];
        if ((time() - $nOldTime) > (60*60*23) || $this->bForce === true) {
          $aOldCurrencies = $aOld['aCurrencies'];
        } else {
         // Last fetch was less than 23hrs ago - so don't update the figures.
          return;
        }
      } Catch (Exception $e) {
        $aOld = null;
        $aOldCurrencies = null;
      }
    }

    // write the data to the local flat file
    $sJSON = json_encode( array('timestamp'=> time(), 'aCurrencies'=>$aCurrencies, 'aOldCurrencies'=>$aOldCurrencies) );
    $h = fopen($this->sCacheFile,'w');
    @fwrite($h,$sJSON);
    fclose($h);

    //store the array as a class property
    $this->aCurrencies  = $aCurrencies;


  }



} // end class
 
$oRU = new RateUpdate();
$oRU->getNewRates();
