<?php

namespace FileConfiguration;

use FileManipulation\Exceptions\FileLoadException;
use FileManipulation\FileManipulation;
/**
 * Manipular arquivos .env e .properties
 */
class PropertiesConfiguration extends FileManipulation
{
  /**
   * 
   * Manipule Arquivos .properties .env
   * 
   * @param string $fileLocation File Location: C:/Web/config.env
   * @param boolean $SyntaxErrorReport Se for verdadeiro ele ira lançar
   * uma exceção caso uma linha não seja valida
   * @param boolean $createFile Se for verdadeiro a classe ira criar o arquivo
   * caso ele não exista, se não for possivel cria-lo lançara uma exceção
   */
  public function __construct($fileLocation = null, $SyntaxErrorReport = false, $createFile = true)
  {
    parent::__construct($fileLocation, $SyntaxErrorReport, $createFile);
    $this->loadConfig();
  }

  /**
   * Verifica se a Linha Atual é um comentario
   *
   * @param string $linha Linha do arquivo
   * @return boolean
   */
  public function isComments($linha)
  {
    $linha = trim($linha);
    return isset($linha[0]) ? $linha[0] === "#" : false;
  }

  /**
   * Carrega o arquivo em um vetor com o indice e o valor da configuração
   *
   * Caso você execulte essa função mais de 1 vez ele ira restaurar todas as
   * configurações originais do arquivo 
   * @return void
   */
  public function loadConfig()
  {
    /* RESET Configuration Memory */
    $this->configuration = [];
    /* Get All Lines Array */
    $linhas = parent::readLines();
    foreach ($linhas as $key => $linha) {
      if ($this->isComments($linha)) {
        $this->configuration[] = $linha;
        continue;
      }

      if ($this->getSyntaxErrorException()) {
        $this->_lineConfigSyntax($linha, $key);
      }

      $CFG = explode("=", $linha, 2);
      $this->setValue(@$CFG[0], @$CFG[1]);
    }
    return $this->configuration;
  }

  /**
   * retorna a configuração informada pelo usuario
   *
   * @param string $key Configuração que deseja obter o valor
   * @return string
   */
  public function getValue($key = null)
  {
    return isset($this->configuration[$key]) ? $this->configuration[$key] : null;
  }

  /**
   * Define ou altera uma configuração na memoria
   *
   * @param string $key Nome da Configuração
   * @param string $value Valor da Configuração
   * @return array
   */
  public function setValue($key, $value)
  {
    $this->configuration[$key] = $value;
    return $this->configuration;
  }

  /**
   * Ira Salvar as Modificações no arquivo
   *
   * Todas as Alterações serão salvas no arquivo
   * para serem manipuladas novamente futuramente
   *
   * @return self
   * @throws conditon
   **/
  public function saveConfig()
  {
    $saveFile = "";
    foreach ($this->getAllConfigurations() as $key => $value) {
      if (!$this->isComments($value))
        $saveFile .= "{$key}={$value}\r\n";
      else
        $saveFile .= "{$value}\r\n";
    }
    if (!is_writable($this->fileLocation))
      throw new FileLoadException("FileManager : Não foi possivel modificar '{$this->fileLocation}' (NOT PERMISSION).", 6);

    file_put_contents($this->fileLocation, $saveFile);
  }

  /**
   * Verifica se a seguinte linha e uma configuração valida
   *
   * @param string $line
   * Linha a ser verificada
   *
   * @return boolean
   */
  private function _lineConfigSyntax($linha = null, $key = -1)
  {
    /* Verifica se e uma linha valida */
    if (!strpos($linha, "=")) {
      throw new FileLoadException(sprintf(self::SYNTAX_ERROR, $key + 1), 5);
    }

    return true;
  }

  public function loadEnv()
  {
    foreach ($this->configuration as $key => $value) {
      if (!is_numeric($key)) {
        $_ENV[$key] = $value;
        if (function_exists("apache_setenv")) {
          apache_setenv($key, $value);
        }
        if (function_exists("putenv")) {
          putenv("{$key}={$value}");
        }
      }
    }
  }
}
