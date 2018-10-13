<?php

include_once("../../src/autoload.php");
use FileConfiguration\CSVConfiguration;
use FileManipulation\Exceptions\FileManipulationExceptions;

echo "<h1>O Valor Atualizado e igual o valor antigo?</h1>";
try {

  $file = new CSVConfiguration("CSVs/tests.csv", true, ',');

  while ($reg = $file->getData()) {

    if ($reg['id'] > 12) {
      $file->setValue("nome", $file->getValue("nome") . " - UPDATED "); // Valor em $reg não é alterado
    }

    echo json_encode($file->getValue("nome") == $reg['nome']) . "<br />"; // retornara falso para ids > 12
    
  }

  echo "<h1>Todos os Valores:</h1>";
  // retorna todas as configurações
  var_dump($file->getAllConfigurations()); 

  // salva as alterações em um novo arquivo
  $file->saveConfig("CSVs/tests-update.csv");


} catch (FileManipulationExceptions $e) {
  echo $e->getMessage();
}
