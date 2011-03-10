<?php

/**
 * 
 * CREDIT CARD VALIDATION SCRIPT
 * 
 * @author Jonathan W. Kelly <jkelly@paramoreredd.com>
 * @version 1.0
 * 
 * 
 * ABOUT
 * --------------------------------------------------------------------
 * This class is written to be used as a credit card utility, allowing
 * you to do validate the format of certain card types.
 * 
 * Much of the information used to form the logic of this 
 * class was sourced from Michael Gilleland's Java class, 
 * which can be found at: http://www.merriampark.com/anatomycc.htm
 * See his page(s) for his sources, and the RESOURCES section below
 * for more on my resources.
 * 
 * 
 * SOURCE
 * --------------------------------------------------------------------
 * https://github.com/jonathanwkelly/PHP-Credit-Card-Validation-Script
 * 
 * The github repo also contains a GUI test interface.
 *
 * 
 * TERMS
 * --------------------------------------------------------------------
 * IIN: Issuer Identification Number
 * 
 * MII: Major Industry Identifier
 * 
 * Check Digit: The last digit of a card number, that is used in the 
 * 		Luhn Algorithm (see below) to determine if the number is a 
 * 		valid format.
 * 
 * Luhn Algorithm: The algorithm used my most major card issuers to 
 * 		determine if a card format is valid.
 * 
 * 
 * RESOURCES
 * --------------------------------------------------------------------
 * IIN and MII: http://en.wikipedia.org/wiki/Bank_card_number
 * 
 * Luhn Algorithm: http://en.wikipedia.org/wiki/Luhn_algorithm
 * 
 * ISO/IEC 7812 Standards: http://en.wikipedia.org/wiki/ISO/IEC_7812
 * 
 * 
 * CUSTOMIZATION
 * --------------------------------------------------------------------
 * To make this class work for your application, you should
 * review and make appropriate adjustments to:
 * 		var arrCardTypes
 *		var arrAcceptedMII
 * 
 * 
 * RISK
 * --------------------------------------------------------------------
 * This script has been thoroughly vetted with dozens of real VISA, AMEX
 * and MC numbers (I didn't have accessibility to any DISCOVER cards), 
 * with legitimate responses. However, you too should test before 
 * implementing into your application. The source files are available
 * at my github (see SOURCE section above). You should also review the 
 * IIN values at the RESOURCES listed above to ensure they are still 
 * accurate at the time of implementation.
 * 
 * 
 * LIABILITY
 * --------------------------------------------------------------------
 * You are solely responsible for any loss or security issues caused by
 * any implementation of this script, either as a whole or any part(s).
 * 
 **/
class CreditCardValidator {
	
	
	/**
	 * Elements include:
	 *   	'status'   	=> 'valid' / 'invalid'
	 *   	'type' 		=> 'visa'
	 *   	'substring'	=> '***1234'
	 * 	 	'reason'   	=> 'iin' / 'mii' / 'format' / 'cardtype' / 'algorithm'
	 * @var array arrCardInfo
	 */
	private $arrCardInfo = array(
		'status' 	=> null, 
		'type' 		=> null, 
		'substring' => null, 
		'reason' 	=> null
	);
	
	
	/**
	 * Contains the accepted card types that this class knows about / uses
	 * Elements include:
	 * 		'name'		=>	string,
	 * 		'active'	=>	true / false
	 * 		'iinrange'	=>	n-n / n,n / n-n,n,n,n-n
	 * 		'length'	=>	n
	 * @var array arrCardTypes
	 */
	private $arrCardTypes = array(
		'amex' => array(
			'name'		=>	'American Express',
			'active'	=> 	true,
			'iinrange' 	=> 	'34,37',
			'length'	=> 	15
		), 
		'discover' => array(
			'name'		=>	'Discover',
			'active'	=> 	true,
			'iinrange' 	=> 	'6011,622126-622925,644-649,65',
			'length'	=> 	16
		), 
		'mastercard' => array(
			'name'		=>	'MasterCard',
			'active'	=> 	true,
			'iinrange' 	=> 	'51-55',
			'length'	=> 	16
		), 
		'visa' => array(
			'name'		=>	'VISA',
			'active'	=> 	true,
			'iinrange' 	=> 	'4',
			'length'	=> 	16
		)
	);
	
	
	/**
	 * Holds the acceptable MII types that will be considered by this
	 * class as acceptable. See the MII resource listed at the top of this
	 * document for info on these values.
	 * @var array arrAcceptedMII
	 */
	private $arrAcceptedMII = array(3, 4, 5, 6);
	
	
	/**
	 * Will determine if the card number passed is legitimately 
	 * formatted. If the strCardType param is passed, it will only
	 * check against that format. Otherwise, it will check against
	 * all possible card formats. In addition to returning a straight
	 * boolean true / false, this method will also set more details 
	 * about the card to the classes arrCardInfo property.
	 * 
	 * If the validation fails, one of the following reasons will be 
	 * assigned to the arrCardInfo['reason'] property. Possible values
	 * are listed below, with the explanation of each:
	 * 		iin				The IIN was not valid, according to CheckIIN()
	 * 		mii				The IIN was not valid, according to CheckMII()
	 * 		format			The format (e.g. length or characters) was invalid
	 * 		cardtype		The card type argument was invalid or not accepted
	 * 		algorithm		The format was found to be invalid, according to CheckLuhn()
	 * 
	 * @param string strCardNumber The raw number
	 * @param string optional strCardType Pass in to validate against a certain card type / format
	 * @return boolean True if is valid, False otherwise
	 * @access public
	 */
	public function Validate($strCardNumber=null, $strCardType=null) {
		
		// We need a valid string passed in
		if($strCardNumber === null) {
			$this->arrCardInfo['failure'] = 'format';
			$this->arrCardInfo['status'] = 'invalid';
			return false;
		}
		
		// We either need no card type passed, or a valid card type passed
		if(($strCardType !== null) && !in_array($strCardType, $this->arrCardTypes)) {
			$this->arrCardInfo['failure'] = 'cardtype';
			$this->arrCardInfo['status'] = 'invalid';
			return false;
		}

		// Check the MII
		if(!$this->CheckMII($strCardNumber)) {
			$this->arrCardInfo['failure'] = 'mii';
			$this->arrCardInfo['status'] = 'invalid';
			return false;
		}
		
		// Check the IIN
		if(!$this->CheckIIN($strCardNumber)) {
			$this->arrCardInfo['failure'] = 'iin';
			$this->arrCardInfo['status'] = 'invalid';
			return false;
		}

		// Check the Luhn Algorithm
		if(!$this->CheckLuhn($strCardNumber)) {
			$this->arrCardInfo['failure'] = 'algorithm';
			$this->arrCardInfo['status'] = 'invalid';
			return false;
		}
		
		// If we get here, it's valid and we go ahead and set the arrCardInfo details
		$this->arrCardInfo['status'] = 'valid';
		$this->arrCardInfo['substring'] = $this->GetCardSubstring($strCardNumber);
		
		return true;
		
	} # END METHOD Validate()

	
	/**
	 * Gets rid of any non numeric characters in the param string
	 * @param string strCardNumber The raw card number
	 * @return string
	 * @access private
	 */
	private function CleanCardNumber($strCardNumber=null) {
		
		// Get rid of any dashes, spaces or alpha chars
		return preg_replace('/[^0-9]/', '', $strCardNumber);
		
	} # END METHOD CleanCardNumber()


	/**
	 * Will take the raw card number and return the ***1234 format
	 * @param string strCardNumber The raw card number
	 * @return string
	 * @access private
	 */
	private function GetCardSubstring($strCardNumber=null) {
		
		// If we got passed the already truncated / short form card number,
		// then just send that back. But before we do, make sure we're not 
		// sending back the whole number!
		if(strstr($strCardNumber, '*') && (substr($strCardNumber) < 10)) return $strCardNumber;

		// Clean the card number before we substring it
		$strCardNumber = $this->CleanCardNumber($strCardNumber);
		
		// Return the truncated card number, or just an empty string if the param was null
		return $strCardNumber ? '***'.substr($strCardNumber, (strlen($strCardNumber) - 4), 4) : '';
		
	} # END METHOD GetCardSubstring()


	/**
	 * Will check the first digit of a card number, or the "MII" - 
	 * Major Industry Identifier. Below are the options. The only
	 * ones that will cause this method to return a True response
	 * are those configured in the arrAcceptedMII property.
	 * 
	 * MII Digit Value	Issuer Category
	 * 	0 : ISO/TC 68 and other industry assignments
	 * 	1 : Airlines
	 * 	2 : Airlines and other industry assignments
	 * 	3 : Travel and entertainment
	 * 	4 : Banking and financial
	 * 	5 : Banking and financial
	 * 	6 : Merchandizing and banking
	 * 	7 : Petroleum
	 * 	8 : Telecommunications and other industry assignments
	 * 	9 : National assignment
	 * 
	 * @param string strCardNumber The card number to be checked
	 * @return boolean True if it is an acceptable MII type
	 * @access private
	 */
	private function CheckMII($strCardNumber=null) {
		
		// Clean the card number before we eval it
		$strCardNumber = $this->CleanCardNumber($strCardNumber);

		// Gotta have anumber to eval!
		if(!$strCardNumber) return false;
		
		// Get the first digit and see if it is in the whitelist
		$intFirstDigit = (int) substr($strCardNumber, 0, 1);
		
		if(!in_array($intFirstDigit, $this->arrAcceptedMII)) return false;
		
		// If we get here, it is legit so return true
		return true;

	} # END METHOD CheckMII()
	
	
	/**
	 * Will check the card number format based on the mod 10 Luhn algorithm.
	 * See the RESOURCES section at the top of the document for more details.
	 * @param string strCardNumber The raw number to check
	 * @return boolean True if it checks out
	 * @access private
	 */
	private function CheckLuhn($strCardNumber=null) {

		// Clean the number passed in
		$strCardNumber = (string) $this->CleanCardNumber($strCardNumber);
		
		// First, get the check digit (the last digit)
		$strCheckDigit = substr($strCardNumber, (strlen($strCardNumber) - 1), 1);
		
		// Now reverse the card number, double every second values (and combine their digits), and tally them all
		$strCardNumberReverse = strrev($strCardNumber);

		$intTotal = 0;
		for($i = 1; $i <= strlen($strCardNumberReverse); $i++) {

			// Double every other number
			$intVal = (int) ($i % 2) ? $strCardNumberReverse[$i-1] : ($strCardNumberReverse[$i-1] * 2);

			// Sum any double digits
			if($intVal > 9) {
				$strVal = (string) $intVal;
				$intVal = (int) ($strVal[0] + $strVal[1]);
			}
			
			// Throw it in the array to be tallied
			$intTotal += $intVal;
		}
		
		// Now check to see if our sum mod 10 == 0
		return (($intTotal % 10) == 0) ? true : false;
		
	} # END METHOD CheckLuhn()
	
	
	/**
	 * Will check to see if the first six digits (including the MII) is  
	 * within the valid range(s) for the types of cards we are accepting.
	 * @param string strCardNumber The card number to be checked
	 * @return boolean True if it is an acceptable IIN
	 * @access private
	 */
	private function CheckIIN($strCardNumber=null) {

		// Clean the number passed in
		$strCardNumber = $this->CleanCardNumber($strCardNumber);
		
		// Gotta have a number passed in
		if(!$strCardNumber) return false;
		
		// This will hold any matches. Hopefully we'll only have one!
		$arrCardTypePossibilities = array();
		
		// Loop through all the accepted card types, and check our num against them
		foreach($this->arrCardTypes as $strShortName => $arrCardType) {
			
			// Only if it's active!
			if($arrCardType['active'] === true) {
				
				// First, do the easy job of checking the length
				$strLen = strlen($strCardNumber);
				if($strLen == $arrCardType['length']) {
					
					// Now, unpack the IINs and compare against them

					// This will get all the "range sets", which are the comma delimited items
					$arrRangeSets = explode(',', $arrCardType['iinrange']);
					foreach($arrRangeSets as $strRangeSetItem) {
						
						// Get any ranges that are hyphen delimited items, denoting ranges
						$arrStrRanges = explode('-', $strRangeSetItem);
						
						// arrStrRanges should contain either an array (if it was a hyphenated range)
						// or a single number. If it's an array, we need to check overy value in the range.
						
						// Check every value in the range
						if(count($arrStrRanges) > 1) {

							for($i = $arrStrRanges[0]; $i <= $arrStrRanges[1]; $i++) {
						
								if(
									(strpos((string) $strCardNumber, (string) $i) === 0) &&
									!in_array($strShortName, $arrCardTypePossibilities)
								) $arrCardTypePossibilities[] = $strShortName; 
						
							}
						
						// Check against one value
						} else {

							if(
								(strpos((string) $strCardNumber, (string) trim($arrStrRanges[0])) === 0) &&
								!in_array($strShortName, $arrCardTypePossibilities)
							) $arrCardTypePossibilities[] = $strShortName; 
							
						}
						
					} # end range sets foreach loop 
					
				} # end length conditional
				
			} # end active conditional
			
		} # end card types loop
		
		// Now assign the possible card type values to the arrCardInfo property
		$this->arrCardInfo['type'] = implode('|', $arrCardTypePossibilities);
		
		// Return true if we found at least one possibile type
		return count($arrCardTypePossibilities) ? true : false;
		
	} # END METHOD CheckIIN()

	
	/**
	 * Simply returns the otherwise inaccessible protected 
	 * property arrCardInfo
	 * @param void
	 * @return array The arrCardInfo property
	 * @access public
	 */
	public function GetCardInfo() {

		return $this->arrCardInfo;

	} # END METHOD GetCardInfo()
	
	
	/**
	 * Will return the literal / friendly name value for the card
	 * @param string strCardShortName E.g. "amex" or "visa"
	 * @return string
	 * @access public
	 */
	public function GetCardName($strCardShortName=null) {
		
		return isset($this->arrCardTypes[$strCardShortName]['name']) ? $this->arrCardTypes[$strCardShortName]['name'] : '';
		
	} # END METHOD GetCardName()
	
	
} # END CLASS CreditCardValidator()

?>
