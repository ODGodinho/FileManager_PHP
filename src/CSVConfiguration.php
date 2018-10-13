<?php 

namespace FileConfiguration;

use FileManipulation\Exceptions\FileManipulationExceptions;
use FileManipulation\FileManipulation;
/**
 * Manipular arquivos .CSV
 */
class CSVConfiguration extends FileManipulation
{
  /**
   * Se verdadeiro a primeira linha do arquivo sera ignorada
   * 
   * Quando verdadeiro as configurações receberar o indice do array com cada valor
   * da primeira linha.
   *
   * @var boolean
   */
  private $useHeader;

  /**
   * Deve ser usado para definir em qual linha ele deve começar a ler o arquivo
   * 
   * Se o valor for menor que zero ele contara da ultima linha ate a primeira
   *
   * @var integer
   */
  private $startLine;

  /**
   * Deve ser informado qual é o numero maximo de linhas devem ser lidas apos a linha de inicio
   * 
   * @var integer|null
   */
  private $limitLine;

  /**
   * Devera conter o separador usado no seguinte arquivo
   * 
   * use "auto" ou null para verificar automaticamente
   *
   * @var string|null
   */
  private $delimitador;

  /**
   * Os Possiveis Delimitadores existentes em um arquivo CSV
   *
   * @var string
   */
  private $allDelimitadores = ",;|\t";

  /**
   * Quantas Linhas devem ser lida para que seja decidido um Delimitador
   *
   * @var int
   */
  private $maxLinesCheckDelimitador = 15;

  /**
   * Deve guardar a primeira linha do arquivo se o useHeader for verdadeiro
   *
   * @var array|null
   */
  private $Header = null;

  /**
   * Salva a configuração atual carregada
   *
   * @var array $_current
   */
  private $_current = null;

  /**
   * Todas as Linhas do arquivo e salva aqui
   * 
   * quando as alterações são salvas ela e enviada para ca e atualizada no arquivo
   *
   * @var array
   */
  private $_allLinesFile = null;

  /**
   * Construtor da Classe
   * 
   * @param string $fileLocation File Location: C:/Web/config.env
   * @param boolean $useHeader Se verdadeiro a primeira linha do arquivo sera pulada e usada como indice
   * @param string $delimitador Deve ser definido com o delimitador do arquivo
   * caso seja "auto" ou null ele irar procurar um automaticamente
   * @param int $startLine Informe em qual linha o arquivo deve começar a leitura
   * Se o valor for menor que zero ele contara do final para baixo
   * @param int $limitLine numero maximo de linhas que devem ser lidas
   * @param boolean $createFile Se for definido verdadeiro caso o arquivo informado não exista ele e criado
   */
  public function __construct($fileLocation = null, $useHeader = true, $delimitador = "auto", $startLine = 0, $limitLine = null, $createFile = true)
  {
    parent::__construct($fileLocation, false, $createFile);
    $this->useHeader = $useHeader;
    $this->startLine = $startLine;
    $this->limitLine = is_null($limitLine) ? null : $limitLine;
    $this->delimitador = $delimitador;
    $this->loadConfig();
  }

  /**
   * Carrega o arquivo em uma vetor com sub vetores com cada configuração
   *
   * Caso você execulte essa função mais de 1 vez ele ira restaurar todas as
   * configurações originais do arquivo 
   * @return array
   */
  public function loadConfig()
  {
    /* RESET Configuration Memory */
    $this->configuration = [];
    $this->_allLinesFile = null;
    /* Carrega todas as linhas do arquivo em uma array */
    $linhas = parent::readLines();
    $this->_validateLinesStartEnd($linhas);
    $this->_allLinesFile = $linhas;
    $limit = 0;
    foreach ($linhas as $key => $linha) {

      // Verifica se e a primeira linha e se ela deve ser pulada
      if ($key == 0 && $this->useHeader) {
        $this->Header = explode($this->_getDelimitador($linhas), $linha);
        continue;
      }
      
      // Verifica o StartLine
      if ($key < $this->startLine) {
        continue;
      }

      // Configuração CSV em array
      $config = explode($this->_getDelimitador($linhas), $linha);

      if (!is_null($this->limitLine) && $limit++ >= $this->limitLine) { // Numero maximo de linhas lida sai do foreach
        break;
      }

      // Verifica se o Header e ativo
      if (!is_null($this->getHeader())) { 
        // Crea um array com o numero de posissoes do head vazio para evitar erros
        // Define o indice do array com o nome do header e mescla o array vazio com os dados
        $ArrayEmpty = array_fill(0, count($this->getHeader()), null);
        $this->configuration[$key] = @array_combine($this->getHeader(), array_slice(array_merge($config, $ArrayEmpty), 0, count($this->getHeader())));
        continue;
      }

      $this->configuration[$key] = explode($this->_getDelimitador($linhas), $linha);
    }
    return $this->configuration;
  }

  /**
   * Ira Salvar as Modificações no arquivo
   *
   * Todas as Alterações serão salvas no arquivo para serem manipuladas novamente futuramente
   * 
   * @param string $newFile Deve conter o caminho para onde sera gravado o arquivo. 
   * Assim não sera modificado havera alterações no original
   * @param boolean $AllLines se for verdadeiro se salvar em outro arquivo salvara 
   * toda a configuração mas se for false salvara apenas as linhas que foram carregadas é o Header
   * @throws FileManipulationExceptions Caso não tenha permissão para gravar no disco
   * @return void
   **/
  public function saveConfig($newFile = null, $AllLines = false)
  {

    if (!is_null($newFile) && !$AllLines) {
      $CurrentLines = $this->_allLinesFile;
      $this->_allLinesFile = [];
    }

    if ($this->useHeader && !is_null($this->Header)) {
      $this->_allLinesFile[0] = implode($this->getDelimitador(), $this->Header);
    }

    foreach ($this->getAllConfigurations() as $key => $value) {
      $this->_allLinesFile[$key] = implode($this->getDelimitador(), $value);
    }

    $backupCurrentFile = $this->getFileLocation(); // Salva o arquivo original
    $this->setFileLocation($newFile);

    file_put_contents($this->getFileLocation(), implode("\r\n", $this->_allLinesFile)); // grava as alterações no arquivo
    $this->setFileLocation($backupCurrentFile); // restaura o arquivo original para file location

    if(isset($CurrentLines)){
      $this->_allLinesFile = $CurrentLines;
    }
  }

  /**
   * Valida se o paramentro startLine e Valido
   *
   * Valida se o parametro startline e valido caso contrario sera lançada uma excessão
   *
   * @param array $linhas Deve receber todas as configurações em uma array para que os valores sejam validados
   * @throws FileManipulationExceptions Se o startLine não for um numero e maior que o numero de linhas do arquivo
   * Se o LimitLine for menor que 1 e n for um numero
   * @return void
   **/
  private function _validateLinesStartEnd($linhas)
  {
    if ((!is_numeric($this->startLine))) {
      throw new FileManipulationExceptions(self::START_LINE_INVALID_MESSAGE, self::START_LINE_INVALID_CODE);
    }

    if ($this->startLine < 0) {
      // se menor que zero (0) contagem de traz para frente
      $this->startLine = (count($linhas) + $this->startLine) < 0 ? 0 : (count($linhas) + $this->startLine);
    }

    if (!is_null($this->limitLine) && ($this->limitLine < 1 || (!is_numeric($this->limitLine)))) {
      throw new FileManipulationExceptions(self::LIMIT_LINE_INVALID_MESSAGE, self::LIMIT_LINE_INVALID_CODE);
    }
  }

  /**
   * recupera o possivel delimitador do arquivo
   * 
   * Utiliza contador para saber qual o possivel delimitador do arquivo utilizando
   * uma contagem o maior sera o possivel delimitador
   *
   * @param array $loadLines Linhas do Arquivo
   *
   * @return string
   */
  private function _getDelimitador($loadLines)
  {
    $delimitadores = [];
    if ($this->delimitador == "auto" || is_null($this->delimitador)) {
      if (is_null($loadLines)) {
        return $this->delimitador = ",";
      }
      foreach ($loadLines as $index => $linha) {
        if ($index >= $this->maxLinesCheckDelimitador) {
          break;
        }
        for ($i = 0; $i < strlen($this->allDelimitadores); $i++) {
          @$delimitadores[$this->allDelimitadores[$i]] += substr_count($linha, $this->allDelimitadores[$i]);
        }
      }
      arsort($delimitadores);
      $this->delimitador = array_keys($delimitadores)[0];
    }
    return $this->delimitador;
  }

  /**
   * Verifica se a linha atual é um comentario
   *
   * @param string $line Linha do arquivo
   * @return boolean
   */
  public function isComments($line)
  {
    return false; // CVS não possue linhas de comentarios
  }

  /**
   * Retorna o Valor da atual configuração selecionada no getData
   *
   * @param string|integer $key 
   * Você pode usar essa função para regatar
   * um valor tanto pelo indice ou pelo nome cabeçalho do arquiv
   *
   * @return string|integer|float|double
   */
  public function getValue($key = null)
  {
    $current = $this->_current;

    if (is_null($key)) {
      return $current;
    }

    if (is_numeric($key) && $this->useHeader) {
      return array_values($current)[$key];
    }

    return $current[$key];
  }

  /**
   * Define o valor da linha atual
   *
   *
   * @param string|int $key Indice do array a ser mudado
   * @param string $value valor que deve ser defino ao indice informado
   * @return void
   **/
  public function setValue($key = null, $value = null)
  {
    $position = key(($this->configuration)) - 1;

    if (intval($position) === -1) {
      $position = count($this->configuration) - 1;
    }

    $keys = $this->Header; // Retorna todas as keys do item atual

    if (is_numeric($key) && isset($keys[$key])) { // se for um numero o indice substituira o key pelo seu nome
      $key = $keys[$key];
    }

    if (isset($this->_current[$key])) {
      $this->_current[$key] = $value;
      $this->configuration[$position][$key] = $value;
      return;
    }

  }

  /**
   * Rode o CSV com o While retornara falso sé n tiver mais linhas
   *
   * @return array
   */
  public function getData($position = null)
  {
    $this->_current = current(($this->configuration));
    next($this->configuration);
    return $this->_current;
  }

  /**
   * Use para definir para qual linha deve ser pulada
   *
   * @param int $position Deve receber para qual parte você do array você deseja ir
   * a contagem continuara a partir dali.
   *
   * @return array
   */
  public function setData($position = null)
  {
    if ($position != null && is_numeric($position)) {
      $this->resetData();
      for ($i = 0; $i < $position; $i++) {
        next($this->configuration);
      }
    }
    return current($this->configuration);
  }
  /**
   * Voltara o cursor para o primeiro item do array
   *
   * @return array
   **/
  public function resetData()
  {
    return reset($this->configuration);
  }

  /**
   * Definira o cursor na ultima posição
   *
   * @return array
   **/
  public function endData()
  {
    return end($this->configuration);
  }

  /**
   * Volta um item no Interator do CSV
   *
   * @return array
   **/
  public function backData()
  {
    return prev($this->configuration);
  }

  /**
   * Retorna a configuração do arquivo se carregado a carrega
   *
   * @return array
   */
  public function getAllConfigurations()
  {
    return $this->configuration ? : $this->loadConfig();
  }

  /**
   * Get startLine
   *
   * @return  integer
   */
  public function getStartLine()
  {
    return $this->startLine;
  }

  /**
   * Get LimitLine
   *
   * @return  integer
   */
  public function getLimitLine()
  {
    return $this->limitLine;
  }

  /**
   * Get Delimatador
   *
   * @return  string|null
   */
  public function getDelimitador()
  {
    return $this->delimitador;
  }

  /**
   * retorna a primeira linha do CSV se o "useHeader" estiver ativo
   *
   * @return array
   */
  public function getHeader()
  {
    return $this->Header;
  }

  /**
   * retorna useHeader
   *
   * @return array
   */
  public function getUseHeader()
  {
    return $this->Header;
  }

  /**
   * Set LimitLine
   *
   * @param  integer|null  $limitLine  Deve ser informado qual é o numero maximo de linhas devem ser lidas apos a linha de inicio
   * @return  self
   */
  public function setLimitLine($limitLine)
  {
    $this->limitLine = $limitLine;
    $this->_validateLinesStartEnd($this->_allLinesFile);

    return $this;
  }

  /**
   * Set StartLine
   *
   * @param  integer  $startLine  Se o valor for menor que zero ele contara da ultima linha ate a primeira
   * @return  self
   */
  public function setStartLine($startLine)
  {
    $this->startLine = $startLine;
    $this->_validateLinesStartEnd($this->_allLinesFile);

    return $this;
  }

  /**
   * Defina um novo delimitador para o arquivo
   *
   * @param  string  $delimitador novo delimitador usado
   * @return  self
   */
  public function setDelimitador($delimitador)
  {

    if ($delimitador != null && $delimitador != "auto") {
      $this->delimitador = $delimitador;
    }
    return $this;
  }

  /**
   * Adiciona um novo item na lista do CSV
   *
   * @param array $values Array com todas as configurações deve ser colocada na ordem do arquivo
   * @return self
   */
  public function addValue($values = null)
  {
    $configuration = implode($this->getDelimitador(), $values);
    if (!$this->useHeader) {
      $count = substr_count($this->_allLinesFile[0], $this->getDelimitador()) + 1;
    } else {
      $count = count($this->Header);
    }

    $arrayBase = array_fill(0, $count, null);

    foreach ($arrayBase as $key => $value) {
      $arrayBase[$key] = array_values($values)[$key];
    }

    $code = count($this->_allLinesFile);
    $this->_allLinesFile[$code] = $arrayBase;
    $this->configuration[$code] = $arrayBase;

    return $arrayBase;

  }
}