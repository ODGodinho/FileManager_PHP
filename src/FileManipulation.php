<?php

namespace FileManipulation;

use FileManipulation\Exceptions\ExceptionsCode;

/**
 * Interface para manipulação de arquivos
 */
abstract class FileManipulation extends ExceptionsCode
{

  /**
   * Cotem o local do arquivo a ser manipulado
   * 
   * @var string $fileLocation
   */
  private $fileLocation;
  /**
   * Configurações do arquivo apos ser carregada para a memoria
   *
   * @var array
   */
  protected $configuration = [];
  /**
   * Se verdadeiro sera lançado uma exceção caso seja encontrado um problema na syntax do arquivo
   *
   * @var boolean
   */
  private $SyntaxErrorException = true;
  /**
   * Construtor da classe
   *
   * @param string $fileLocation local e nome do arquivo a ser manipulado
   * ex: settings/server.properties
   * @param string $SyntaxErrorException Deve ser lançada uma exceção caso
   * seja encontrado um erro de syntax?
   */
  public function __construct($fileLocation = null, $SyntaxErrorException = true, $createFile = true)
  {
    $this->fileLocation = $fileLocation;
    $this->SyntaxErrorException = $SyntaxErrorException;
    $this->checkFile($createFile);
  }

  /**
   * Verifica se o caminho informado 
   * Existe, se é um arquivo, se ele pode ser lido
   *
   * @return boolean
   */
  protected function checkFile($createFile)
  {
    $w = is_writable($this->fileLocation);

    if ($this->fileLocation == null)
      throw new FileLoadException(sprintf(self::IS_NULL_MESSAGE, $this->fileLocation), self::IS_NULL_CODE);

    if (!file_exists($this->fileLocation) && !$createFile)
      throw new FileLoadException(sprintf(self::NOT_EXIST_MESSAGE, $this->fileLocation), self::NOT_EXIST_CODE);


    if (!file_exists($this->fileLocation) && $createFile) {
      // REMOVE FILE NAME ao criar as pastas
      $folder = explode("/", $this->fileLocation);
      array_pop($folder);

      // Se existir pasta a ser criada entao crie
      if (count($folder) > 0) {
        @mkdir(implode(DIRECTORY_SEPARATOR, $folder), 0777, true);
      }
      @fopen($this->fileLocation, "w");
    }

    if (!file_exists($this->fileLocation) && $createFile)
      throw new FileLoadException(sprintf(self::NOT_CHANGE_MESSAGE, $this->fileLocation), self::NOT_CHANGE_CODE);


    if (!is_file($this->fileLocation))
      throw new FileLoadException(sprintf(self::NOT_FILE_MESSAGE, $this->fileLocation), self::NOT_FILE_CODE);

    if (!is_readable($this->fileLocation))
      throw new FileLoadException(sprintf(self::NOT_READ_MESSAGE, $this->fileLocation), self::NOT_READ_CODE);

    return true;
  }
  /**
   * Retorna todas as linhas do documentos em um vetor
   *
   * @return array
   */
  protected function readLines()
  {
    $lines = file($this->fileLocation, FILE_IGNORE_NEW_LINES);
    return $lines;
  }
  /**
   * Get fileLocation
   *
   * @return string
   */
  public function getFileLocation()
  {
    return $this->fileLocation;
  }
  /**
   * Get SyntaxErrorException
   *
   * @return  boolean
   */
  public function getSyntaxErrorException()
  {
    return $this->SyntaxErrorException;
  }
  /**
   * Retorna a configuração do arquivo se carregado a carrega
   *
   * @return  array
   */
  public function getAllConfigurations()
  {
    return $this->configuration ? : $this->loadConfig();
  }

  /**
   * Deve verificar se a seginte linha e um comentario
   *
   * @param string $line Linha do arquivo
   * @return boolean
   */
  abstract public function isComments($line);
  /**
   * Deve retornar o valor de uma determinada configuração
   *
   * @param string $key
   * @return mixed
   */
  abstract public function getValue($key);
  /**
   * Deve transformar o readLine em um array ou objeto para resgatar as configurações
   *
   * @return boolean
   */
  abstract public function loadConfig();
  /**
   * Deve salvar as alteração no arquivo de configuração
   *
   * @return self
   */
  abstract public function saveConfig();

  /**
   * Set $fileLocation
   *
   * @param  string  $fileLocation Novo local do arquivo
   * @return  self
   */ 
  public function setFileLocation($fileLocation = null)
  {
    if (!is_null($fileLocation)) {
      $this->fileLocation = $fileLocation;
      $this->checkFile(true);
    }

    return $this;
  }

}
