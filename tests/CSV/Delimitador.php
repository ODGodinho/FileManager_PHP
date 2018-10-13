<?php

include_once("../../src/autoload.php");
use FileConfiguration\CSVConfiguration;
use FileManipulation\Exceptions\FileManipulationExceptions;

echo "<h1>Atualizando delimitador do arquivo!</h1>";
try {

  $file = new CSVConfiguration("CSVs/tests.csv");

  $file->setDelimitador(";");
  $file->saveConfig("./CSVs/tests-Change-Delimitador.csv");

} catch (FileManipulationExceptions $e) {
  echo $e->getMessage();
}


/**
 * 
 * SE você mudar o delimitador do arquivo carregando apenas um pedaço dele pode causar problemas
 * defina o startLine para 0 e o setLimit null atualize o delimitador e salve as alterações
 * 
 */



try {

  $startLine = 3;
  $file = new CSVConfiguration("CSVs/tests.csv", true, "auto", $startLine); // começa de linha 3

  $NewLocation = "./CSVs/tests-Change-Delimitador-2.csv";

  
  $file->getData();
  $file->setValue("id", 100);

  $file->saveConfig($NewLocation); // salva as alterações antes de recarregar

  // Nao mude o delimitador se não carregou todas as linhas salve as alterações e use o codigo abaixo

  $file->setFileLocation($NewLocation); // Defina que agora ira mecher no novo arquivo
  $file->setLimitLine(null); // resgate todos os valores possiveis
  $file->setStartLine(0); // do começo
  $file->loadConfig(); // recarrega as configurações com as alterações feitas

  $file->setDelimitador(";"); // mude o delimitador
  $file->saveConfig(); // salve a configuração


} catch (FileManipulationExceptions $e) {
  echo $e->getMessage();
}
