<?php
define("TAXI", 5);
define("PLATINUM", 4);
define("GOLD", 3);
define("SILVER", 2);
define("BRONZE", 1);
define("STANDARD", 0);
/**
 * 
 */
class TelephoneNumberGrader {
	private static $instance = null;

	function __construct() {
	}
	
	private static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new TelephoneNumberGrader();
		}

		return self::$instance;
	}

	public static function analyseNumber($number) {
		$functionArray = [
			'testRepeatingNumbers',
			'testNumberRuns',
			'testRepeatingPaternsWithGaps',
			'testMemorable',
		];

		/*	TODO: Extra modules
		 *
		 *	Keypad adjacency
		 *	Keypad spelling
		 *	Sylable Matching
		 *	Bond Check
		 *	Mirrored Phrase
		 *	Colloquilaisms
		 */

		$numberValue = array("value" => 0, "subValue" => 0, "reason" => []);

		if (substr($number, 0, 1) == "+")
			$number = substr($number, 1);
		foreach ($functionArray as $test) {
			TelephoneNumberGrader::getInstance()->$test($number, $numberValue);
		}

		return $numberValue;
	}
	
	private function testRepeatingNumbers($number, &$numberValue) {
		$valueNumberDuplicates = [
			[6, "value"=>TAXI],
			[3, 3, "value"=>PLATINUM],
			[2, 4, "value"=>GOLD],
			[4, "value"=>GOLD],
			[3, "value"=>GOLD],
			[2, 2, 2, "value"=>GOLD],
			[2, 2, "value"=>SILVER],
			[2, "value"=>BRONZE]
		];
		
		$lastNumber = substr($number, -1);
		$repeatedPhraseArray = array(0 => 1);
		$repeatedPhraseArrayPointer = 0;
		$tempNumber = substr($number,0, -1);

		while (strlen($tempNumber) > 0) {
			if (substr($tempNumber, -1) == $lastNumber) {
				$repeatedPhraseArray[$repeatedPhraseArrayPointer]++;
			} else {
				$lastNumber = substr($tempNumber, -1);
				$repeatedPhraseArrayPointer++;
				$repeatedPhraseArray[$repeatedPhraseArrayPointer]=1;
			}
			$tempNumber = substr($tempNumber, 0, -1);
		}

		foreach ($valueNumberDuplicates as $possibleRun) {
			$subValue = 0;
			for ($i=0; $i < sizeof($possibleRun)-1; $i++) { 
				if ($repeatedPhraseArray[$i] < $possibleRun[$i]) {
					continue 2;
				} elseif ($repeatedPhraseArray[$i] > $possibleRun[$i]) {
					$subValue +=  $repeatedPhraseArray[$i] - $possibleRun[$i];
				}
			}
			$this->updateValue($numberValue, $possibleRun["value"], $subValue, "Repeated digits");
		}
		return $numberValue;
	}

	private function testNumberRuns($number, &$numberValue) {
		$valueNumberRuns = [
			[7, "value"=>PLATINUM],
			[6, "value"=>GOLD],
			[3, 3, "value"=>SILVER],
			[3, "value"=>BRONZE],
			[3,2, "value"=>BRONZE],
		];

		$numberRunArray = [1];
		$numberRunArrayPointer = 0;
		$tempNumber = $number;

		$currentLast = substr($tempNumber, -1, 1);
		$currentEnd = substr($tempNumber, -2, 1);
		$runDirection = $currentEnd - $currentLast;

		//Identify runs and store them in $numberRunArray;
		while (strlen($tempNumber) > 1) {
			$tempNumber = substr($tempNumber, 0, -1);

			if ($currentEnd == ($currentLast+$runDirection)%10 && $runDirection != 0 && ($runDirection == -1 || $runDirection == 1)) {
				//Run occuring
				$numberRunArray[$numberRunArrayPointer]++;
				$currentLast = substr($tempNumber, -1, 1);
				$currentEnd = substr($tempNumber, -2, 1);
			} else {
				//Run not occuring
				$numberRunArrayPointer++;
				$numberRunArray[$numberRunArrayPointer] = 1;
				$currentLast = substr($tempNumber, -1, 1);
				$currentEnd = substr($tempNumber, -2, 1);
				$runDirection = $currentEnd - $currentLast;
			}
		}
		
		$areaCodeSplit = $this->formatNumber($number);

		foreach ($valueNumberRuns as $possibleRun) {
			$subValue = 0;
			$possibleRunOffset = 0;
			$areaCodeSplitOffset = 0;
			$areaCodeSplitPointer = 1;
			for ($i=0; $i < sizeof($possibleRun)-1; $i++) {
				if ($numberRunArray[$i] < $possibleRun[$i]) {
					continue 2; //If number isn't good enough, try a lower "value"
				} elseif ($numberRunArray[$i] > $possibleRun[$i]) {
					$subValue +=  $numberRunArray[$i] - $possibleRun[$i];
				}
				$possibleRunOffset += $numberRunArray[$i];
				if ($possibleRunOffset > $areaCodeSplitOffset) {
					$areaCodeSplitOffset += $areaCodeSplit[sizeof($areaCodeSplit)-$areaCodeSplitPointer];
				}
				if ($possibleRunOffset >= $areaCodeSplitOffset) {
					$subValue++;
				}
			}

			//Check for matching first and last digits
			$firstLastDigitsArray = array();
			for ($i=0; $i < 10; $i++) { 
				$firstLastDigitsArray[$i] = 0;
			}

			if (sizeof($possibleRun)-1 > 1) {
				$offset = 0;
				for ($i=0; $i < sizeof($possibleRun)-1; $i++) { 
					$firstLastDigitsArray[substr($number, -1-$offset, 1)]++;
					$firstLastDigitsArray[substr($number, -$offset-$possibleRun[$i], 1)]++;
					$offset += $possibleRun[$i];
				}
				$maxFirstLastDigit = 0;
				foreach ($firstLastDigitsArray as $key => $value) {
					if ($value > $maxFirstLastDigit) {
						$maxFirstLastDigit = $value;
					}
				}
				$subValue += $maxFirstLastDigit-1;
			}

			$this->updateValue($numberValue, $possibleRun["value"], $subValue, "Run");
		}
	}

	private function testRepeatingPaternsWithGaps($number, &$numberValue) {
		$valueRepeatingPatterns = [
			['offset' => 0, 'block' => 5, 'gap' => 0, 'count' => 2, 'value' => TAXI],	//vwxyzvwxyz
			['offset' => 0, 'block' => 4, 'gap' => 0, 'count' => 2, 'value' => TAXI],	//wxyzwxyz
			['offset' => 0, 'block' => 3, 'gap' => 0, 'count' => 2, 'value' => PLATINUM],	//xyzxyz
			['offset' => 0, 'block' => 1, 'gap' => 1, 'count' => 3, 'value' => GOLD],	//AxBxCx
			['offset' => 1, 'block' => 1, 'gap' => 1, 'count' => 3, 'value' => GOLD],	//xAxBxC
			['offset' => 0, 'block' => 5, 'gap' => 1, 'count' => 2, 'value' => SILVER],	//vwxyzAvwxyz
			['offset' => 1, 'block' => 5, 'gap' => null, 'count' => 2, 'value' => SILVER],	//
			['offset' => 0, 'block' => 4, 'gap' => 1, 'count' => 2, 'value' => SILVER],	//
			['offset' => 0, 'block' => 3, 'gap' => 2, 'count' => 2, 'value' => SILVER],	//xyzABxyz
			['offset' => 0, 'block' => 3, 'gap' => 3, 'count' => 2, 'value' => SILVER],	//xyzABCxyz
			['offset' => 2, 'block' => 3, 'gap' => 2, 'count' => 2, 'value' => SILVER],	//xyzABxyzCD
			['offset' => 2, 'block' => 2, 'gap' => 0, 'count' => 2, 'value' => SILVER],	//xyxyAB
			['offset' => 0, 'block' => 2, 'gap' => 1, 'count' => 2, 'value' => SILVER],	//AxyBxy
			['offset' => 1, 'block' => 2, 'gap' => 1, 'count' => 2, 'value' => SILVER],	//xyAxyB
			['offset' => 0, 'block' => 2, 'gap' => 2, 'count' => 2, 'value' => SILVER],	//xyABxy
			['offset' => 0, 'block' => 2, 'gap' => 0, 'count' => 2, 'value' => SILVER],	//xyxy
		];

		$bestValue = null;

		//Slowly increase the offset from the right hand side
		for ($initialOffset=0; $initialOffset < 3; $initialOffset++) {

			//Change the block size we're searching for
			for ($block=1; $block <= floor((strlen($number)-$initialOffset)/2); $block++) { 
				$blockValue = substr($number, -$block-$initialOffset, $block);

				//Keep widening the gap between the first block and the search block
				for ($gap=0; $gap <= strlen($number)-(2*$block)-$initialOffset; $gap++) { 
					//Until we find a matching block
					if (substr($number, strlen($number)-(2*$block+$gap)-$initialOffset, strlen($blockValue)) == $blockValue) {
						$count=2;

						//The search again at the same interval to see if the patern repeats
						for ($search=1; $search <= floor((strlen($number)-(2*$block+$gap)-$initialOffset)/($block+$gap)); $search++) { 
							if (substr($number, strlen($number)-(2*$block+$gap)-$initialOffset-($search*($block+$gap)), strlen($blockValue)) == $blockValue) {
								//It does, increase count and loop again
								$count++;
							} else {
								//It doesn't, exit loop
								break;
							}
						}

						foreach ($valueRepeatingPatterns as $potentialValue) {
							if (
								($potentialValue['offset'] === NULL || $potentialValue['offset'] === $initialOffset) &&
								($potentialValue['block'] === $block) &&
								($potentialValue['gap'] === NULL || $potentialValue['gap'] === $gap) &&
								($count >= $potentialValue['count'])
							) {
								$blockRepresentation = 'vwxyz';
								if (!isset($bestValue) || $potentialValue['value'] > $bestValue['value'] || ($potentialValue['value'] == $bestValue['value'] && $block > $bestValue['block'])) {
									$bestValue = [
										'value' => $potentialValue['value'],
										'block' => $block,
										'count' => $count,
										'string' => "Repeating pattern ".substr(str_repeat(str_repeat("-", $gap).substr($blockRepresentation, -$block), $count).str_repeat("-", $initialOffset), $gap)
									];
								}
							} else {
								continue;
							}
						}
					} else {
					}
				}
			}
		}

		if (isset($bestValue['value'])) {
			$this->updateValue($numberValue, $bestValue["value"], $bestValue['count'], $bestValue['string']);
		}
	}

	private function testMemorable($number, &$numberValue) {
		if (substr($number, -6, 3) == (substr($number, -3) + 1) || substr($number, -6, 3) == (substr($number, -3) - 1))
			$this->updateValue($numberValue, PLATINUM, 0, "Last 2 triplets consecutive");
		
		if (substr($number, -4, 2) == (substr($number, -2) + 1) % 100 && substr($number, -6, 2) == (substr($number, -4, 2) + 1) % 100)
			$this->updateValue($numberValue, PLATINUM, 0, "Last 3 pairs consecutive");
		
		if (substr($number, -4, 2) == (substr($number, -2) - 1) % 100 && substr($number, -6, 2) == (substr($number, -4, 2) - 1) % 100)
			$this->updateValue($numberValue, PLATINUM, 0, "Last 3 pairs consecutive");
		
		if (substr($number, -1) === substr($number, -6, 1) && substr($number, -2, 1) === substr($number, -5, 1) && substr($number, -3, 1) === substr($number, -4, 1))
			$this->updateValue($numberValue, GOLD, 0, "Ending xyzzyx");

		if (substr($number, -1) === substr($number, -3, 1) && substr($number, -4, 1) === substr($number, -6, 1))
			$this->updateValue($numberValue, SILVER, 0, "Ending xyxzwz");
		
		if (substr($number, -1) === substr($number, -5, 1) && substr($number, -2, 1) === substr($number, -4, 1))
			$this->updateValue($numberValue, SILVER, 0, "Ending xyzyx");

		if (substr($number, -2) === "00") {
			$this->updateValue($numberValue, STANDARD, 0, "Ending 00");
		}
	}

	private function updateValue(&$numberValue, $newValue, $newSubValue, $newValueReason) {
	  if ($newValue > $numberValue["value"] || ($newValue == $numberValue["value"] && $newSubValue > $numberValue["subValue"])) {
	    $numberValue["value"] = $newValue;
	    array_push($numberValue["reason"], $newValueReason);
	  }
		$numberValue["subValue"] += $newSubValue;
	}

	public function formatNumber($number){
		//Returns an array representing the format for displaying a UK number.

		//Cut leading 44/0 off
		if (substr($number, 0, 1) == "0")
			$number = substr($number, 1);
		if (substr($number, 0, 2) == "44")
			$number = substr($number, 2);

		//Splits first on length, then by starting digits
		switch (strlen((string)$number)) {
			case '9':
				if (substr($number, 0, 5) == "16977") {
					// 1### #####
					return [5, 4];
				} elseif (substr($number, 0, 3) == "800") {
					// 800 ######
					return [3, 6];
				} elseif (substr($number, 0, 1) == "1") {
					// 16977 ####
					return [4, 5];
				} else {
					error_log("ERROR: formatNumber - Unrecognised Number: 0$number");
					exit;
				}
				break;

			case '10':
				// $fNo = substr($number, 0, 1);
				switch (substr($number, 0, 1)) {
					case '5':
					case '7':
						// 5### ######
						// 7### ######
						return [4,6];
						break;
					
					case '3':
					case '8':
					case '9':
						// 3## ### ####
						// 8## ### ####
						// 9## ### ####
						return [3, 3, 4];
						break;

					case '2':
						// 2# #### ####
						return [2, 4, 4];
						break;

					case '1':
						if (substr($number, 1, 1) == "1" || substr($number, 2, 1) == "1") {
							// 1#1 ### ####
							// 11# ### ####
							return [3, 3, 4];
						}

						switch (substr($number, 0, 5)) {
							case '13397':
							case '13398':
							case '13873':
							case '15242':
							case '15394':
							case '15395':
							case '15396':
							case '16973':
							case '16974':
							case '16977':
							case '17683':
							case '17684':
							case '17687':
							case '19467':
							case '19755':
							case '19756':
								// 13397 #####
								// 13398 #####
								// 13873 #####
								// 15242 #####
								// 15394 #####
								// 15395 #####
								// 15396 #####
								// 16973 #####
								// 16974 #####
								// 16977 #####
								// 17683 #####
								// 17684 #####
								// 17687 #####
								// 19467 #####
								// 19755 #####
								// 19756 #####
								return [5, 5];
								break;
							
							default:
								//This should match everything beginning with 1 but not matching any of the other rules
								// 1### ######
								return [4, 6];
								break;
						}
						//Just incase
						return [4, 6];
						break;

					default:
						error_log("ERROR: formatNumber - Unrecognised Number: '$number'");
						break;
				}
				break;
			
			default:
				error_log("ERROR: formatNumber - The number '$number' was not recognised");
				exit;
				break;
		}
	}
}



