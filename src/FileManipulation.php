<?php

namespace FileManipulation;

use FileManipulation\Exceptions\FileLoadException;

/**
 * Interface para manipulação de arquivos
 */
abstract class FileManipulation
{
  const IS_NULL_MESSAGE = "FileManager : O Arquivo é nulo.";
  const NOT_EXIST_MESSAGE = "FileManager : O arquivo '%s' não existe.";
  const NOT_PERM_WRITE = "FileManager : Não foi possivel criar o arquivo '%s' (NOT PERMISSION).";
  const NOT_FILE = "FileManager : '%s' não é um arquivo.";
  const NOT_PERM_READ = "FileManager : Não foi possivel ler '%s' (NOT PERMISSION).";
  const SYNTAX_ERROR = "FileManager : Erro de syntax proximo da linha %s.";
  /**
   * Cotem o local do arquivo a ser manipulado
   * 
   * @var string $fileLocation
   */
  protected $fileLocation;
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
  public function checkFile($createFile)
  {
    $w = is_writable($this->fileLocation);

    if ($this->fileLocation == null)
      throw new FileLoadException(sprintf(self::IS_NULL_MESSAGE, $this->fileLocation), 1);
    if (!file_exists($this->fileLocation) && !$createFile)
      throw new FileLoadException(sprintf(self::NOT_EXIST_MESSAGE, $this->fileLocation), 2);
    if (!file_exists($this->fileLocation) && $createFile)
      @fopen($this->fileLocation, "w");
    if (!file_exists($this->fileLocation) && $createFile)
      throw new FileLoadException(sprintf(self::NOT_PERM_WRITE, $this->fileLocation), 6);
    if (!is_file($this->fileLocation))
      throw new FileLoadException(sprintf(self::NOT_FILE, $this->fileLocation), 3);
    if (!is_readable($this->fileLocation))
      throw new FileLoadException(sprintf(self::NOT_PERM_READ, $this->fileLocation), 4);

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
  protected function getFileLocation()
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
   * @param string $line
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
}
