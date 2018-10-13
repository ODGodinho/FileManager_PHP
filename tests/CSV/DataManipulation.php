<?php

include_once("../../src/autoload.php");
use FileConfiguration\CSVConfiguration;
use FileManipulation\Exceptions\FileManipulationExceptions;

// Apartir de uma certa posição
echo "<h1>Pular para alguma posição</h1>";

try {

  $file = new CSVConfiguration("CSVs/tests.csv", true, ',');

  $file->setData(5); // Inicia o data na 5 pessoa

  while ($reg = $file->getData()) {
    echo $reg['nome'] . "<br />";
  }

} catch (FileManipulationExceptions $e) {
  echo $e->getMessage();
}


// Traz para frente
echo "<h1>Do Ultimo ao primeiro</h1>";

try {

  $file = new CSVConfiguration("CSVs/tests.csv", true, ',');

  // Define o ponteiro na ultima posição
  $file->endData(); 

  // OU USE
  // $file->setData($POSITION); para comesar de uma certa posição

  while ($reg = $file->backData()) {
    echo $reg['nome'] . "<br />";
  }

} catch (FileManipulationExceptions $e) {
  echo $e->getMessage();
}


// Carregar apenas algumas posições


try {

  $file = new CSVConfiguration("CSVs/tests.csv", true, ',', 2, 5); // Começe 3 linhas abaixo e resgate apenas 5 valores

  echo "<h1>Apenas Algumas Posições</h1>";
  var_dump($file->getAllConfigurations());

  $file->setLimitLine(null); // resgate todos os valores possiveis
  $file->setStartLine(-7); // Começa das 7 ultimas pessoas

  $file->loadConfig(); // recarrega as configurações

  echo "<h1>Alterando opções</h1>";
  var_dump($file->getAllConfigurations());

} catch (FileManipulationExceptions $e) {
  echo $e->getMessage();
}

