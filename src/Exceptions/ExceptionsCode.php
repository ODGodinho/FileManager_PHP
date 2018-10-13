<?php

namespace FileManipulation\Exceptions;
/**
 * Codigo de Erros exceções
 */
class ExceptionsCode
{
  const IS_NULL_CODE            = 1;
  const NOT_EXIST_CODE          = 2;
  const NOT_FILE_CODE           = 3;
  const NOT_READ_CODE           = 4;
  const SYNTAX_ERROR_CODE       = 5;
  const NOT_CHANGE_CODE         = 6;

  const START_LINE_INVALID_CODE = 7;
  const LIMIT_LINE_INVALID_CODE = 8;


  const IS_NULL_MESSAGE            = "ODG FileManager: O Arquivo é nulo.";
  const NOT_EXIST_MESSAGE          = "ODG FileManager: O arquivo '%s' não existe.";
  const NOT_FILE_MESSAGE           = "ODG FileManager: '%s' não é um arquivo.";
  const NOT_READ_MESSAGE           = "ODG FileManager: Não foi possivel ler '%s' (NOT PERMISSION).";
  const SYNTAX_ERROR_MESSAGE       = "ODG FileManager: Erro de syntax proximo da linha %s.";
  const NOT_CHANGE_MESSAGE         = "ODG FileManager: Não foi possivel criar o arquivo '%s' (NOT PERMISSION)."; 
  
  const START_LINE_INVALID_MESSAGE = "ODG FileManager: O 'Start Line' não é um numero valido.";
  const LIMIT_LINE_INVALID_MESSAGE = "ODG FileManager: O Numero maximo de linhas informado é invalido.";
}
