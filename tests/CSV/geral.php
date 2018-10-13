<?php

include_once("../../src/autoload.php");
use FileConfiguration\CSVConfiguration;
use FileManipulation\Exceptions\FileManipulationExceptions;

echo "<h1>UseHeader == true</h1>";
try {

  $file = new CSVConfiguration("CSVs/tests.csv", true, ',');

  while ($reg = $file->getData()) {
    echo $reg['nome'] . "<br />";
  }

} catch (FileManipulationExceptions $e) {
  echo $e->getMessage();
}

// UseHeader Disable
echo "<h1>UseHeader == False</h1>";

try {

  $file = new CSVConfiguration("CSVs/tests.csv", false, ',');

  while ($reg = $file->getData()) {
    echo $reg[1] . "<br />";
  }

} catch (FileManipulationExceptions $e) {
  echo $e->getMessage();
}


// Pesquisa Avançada
echo "<h1>GetValue Class</h1>";
try {

  $file = new CSVConfiguration("CSVs/tests.csv", true, ',');

  while ($reg = $file->getData()) {
    echo $file->getValue("nome") . " - " . $file->getValue("1") . "<br />"; 
  }

} catch (FileManipulationExceptions $e) {
  echo $e->getMessage();
}