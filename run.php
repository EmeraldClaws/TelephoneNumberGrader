#!/usr/bin/env php
<?php
require_once 'TelephoneNumberGrader.php';

$versionNumber = "1.0.0";

$helpString = 
"Number Value Analyser ".$versionNumber."\n\n".
"Usage:\n  command [options] [arguments]\n\n".
"Options\n".
"  --help\t\tThis help\n".
"  --json\t\tForce output as json\n".
"  --csv\t\t\tForce output as csv\n".
"\n".
"Arguements\n".
"  Comma seperated list of numbers:\n".
"    number[,number]*".
"\n\n".
"    ".$argv[0]." [NUMBER]\n      ".$argv[0]." +441619682868\n      ".$argv[0]." 441619682868\n      ".$argv[0]." 01619682868\n\n".
"  \\n seperated list of numbers:\n".
"    +441619682867\n    1619682868\n    01619682869";

if(defined('STDIN') || (empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0)) {
  //Command Line
  switch (sizeof($argv)) {
    case '1':
      echo $helpString;
      break;

    default:
      $outputType = 'csv';

      foreach ($argv as $key => $value) {
        switch ($value) {
          case '--json':
            $outputType = 'json';
            break;
          
          case '--csv':
            $outputType = 'csv';
            break;

          default:
            break;
        }
      }

      $lastArg = $argv[sizeof($argv)-1];
      if (file_exists($lastArg)) {
        $numbers = explode("\n", file_get_contents($lastArg));
      } else {
        $numbers = explode(",", str_replace(" ", "", $argv[sizeof($argv)-1]));
      }

      //Manually constructing output as buffering would be bad
      //Before processing loop
      switch ($outputType) {
          case 'csv':
            echo "number,value,subValue,reasons\n";
            break;

          case 'json':
            echo "{";
            break;
          
          default:
            echo "No output type set";
            exit;
            break;
      }

      foreach ($numbers as $key => $number) {
        $result = TelephoneNumberGrader::analyseNumber($number);

        if ($number == "") {
          continue;
        }

        //During processing loop
        switch ($outputType) {
          case 'csv':
            echo $number.",".$result['value'].",".$result['subValue'].',"'.implode(",", $result['reason'])."\"\n";
            break;

          case 'json':
            echo "\"".$number."\":".json_encode($result);
            if ($key != sizeof($numbers) -1) {
              echo ",";
            }
            break;
          
          default:
            echo "No output type set";
            exit;
            break;
        }
      }

      //After processing loop
      switch ($outputType) {
        case 'csv':
          break;

        case 'json':
          echo "}";
          break;
        
        default:
          echo "No output type set";
          exit;
          break;
      }
      break;
  }

} else {
  //TODO: Via http request
}