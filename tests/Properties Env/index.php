<?php

include_once("../../src/autoload.php");
use FileConfiguration\PropertiesConfiguration;
use FileManipulation\Exceptions\FileManipulationExceptions;

try {
  $Config = new PropertiesConfiguration("./Properties/server.properties", false, false); // ABRE server.properties
  $slots = $Config->getValue("max-players"); // RECUPERA numero maximo de players
  $Config->setValue("max-players", $slots + 1); // DEFINE o max-players para VALOR_ATUAL + 1
  $Config->saveConfig(); // Salva as modificações feitas no arquivo

  $Config->setValue("max-players", 3000); // Define o max-players como 3000
  $Config->loadConfig(); // Limpa as configurações da memoria e usa as do arquivo o setValue acima foi perdido e restaurado ao original definido na linha 10 e 11
  $Config->loadEnv(); // Adiciona as configurações $_ENV, getenv(), apache_getenv()
} catch (FileManipulationExceptions $e) {
  switch ($e->getCode()) {
    case '1': // O Local do arquivo é nulo
    case '2': // O Arquivo não existe
    case '3': // O Item informado nao e um arquivo
    case '4': // O arquivo não pode ser lido (SEM PERMISSÃO)
    case '5': // SYNTAX ERROR FILE 
    case '6': // O arquivo não pode ser Alterado ou Criado
      echo $e->getMessage();
      break;
  }
}

// ! as

