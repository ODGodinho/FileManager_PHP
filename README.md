# ODG FileManager - PHP

Essa é uma simples biblioteca para manipular arquivos de configurações<br />
CSV,Properties,Env etc...

# Instalação

1º Baixe o repositório para o seu projeto.<br />
2º Insira o código a baixo.

```php
require_once("./vendor/FileManager_PHP/src/autoload.php");

use FileConfiguration\PropertiesConfiguration;
use FileManipulation\Exceptions\FileManipulationExceptions;
```

3º Não requer composer.

# Exemplos

## **Properties e Env:**

```php
...

try {

  $Config = new PropertiesConfiguration("./server.properties");

} catch (FileManipulationExceptions $e) {

}
```

---

##### _Properties Exemplo:_

```ini
snooper-enabled=false
level-type=DEFAULT
hardcore=false
enable-command-block=true
max-players=1070
network-compression-threshold=1030
```

---

##### _Get and Setters Configurações:_

```php
...

$max  = $Config->getValue("max-players"); // Resgata uma configuração
$Config->setValue("max-players", $max + 1); // Define uma nova configuração

$Config->saveConfig(); // Salva as modificações feitas no arquivo
```

---

##### _reload Config:_

```php
...
$Config->loadConfig();
```

_(As alterações feitas não salvas são perdidas é substituídas)_

---

##### _Salva as Modificações:_

```php
$file->saveConfig(); // Salva no arquivo atual
$file->saveConfig("./Properties/EnvFile.env"); // Salva em um novo arquivo
```

---

##### _Load $\_ENV e getenv():_

```php
$Config->loadEnv();

$max = $_ENV['max-players'];
$max = getenv("max-players");
```

---

## **CSV Files:**

```php
...

try {

  $file = new CSVConfiguration("./CSV/Exemplos.csv");

} catch (FileManipulationExceptions $e) {


}
```

##### _Default:_

```php
/**
 * @param string $fileLocation Local do Arquivo
 * @param boolean $useHeader Se verdadeiro a primeira linha do arquivo sera pulada e usada como indice
 * @param string $delimitador Deve ser definido com o delimitador do arquivo
 *    caso seja "auto" ou null ele irar procurar um automaticamente
 * @param int $startLine Informe em qual linha o arquivo deve começar a leitura
 *    Se o valor for menor que zero ele contara do final para baixo
 * @param int $limitLine numero maximo de linhas que devem ser lidas
 */
new CSVConfiguration("./CSV/Exemplos.csv", true, "auto", 0, null, true);
```

_(Instancia padrão da classe) - Configure como preferir_

---

##### _CSV Exemplo:_

```text
id,nome,telefone,email
10,DragonsGamers,(31) 90000-0000,contato@Dragons.com
11,Larissa Silveira,(31) 90000-0001,contato@larissa.com
12,Renato Oliveira,(31) 90000-0002,contato@renato.com
13,Diogo Nominato,(31) 90000-0003,contato@diogo.com
14,Lais Teixeira,(31) 90000-0004,contato@lais.com
15,Brena Coelho,(31) 90000-0005,contato@manu.com
16,Sabrina Almeida,(31) 90000-0006,contato@sa.com
17,Danilo Castro Reis,(31) 90000-0007,contato@danilo.com
18,Gabriel Salome,(31) 90000-0007,contato@solome.com
,,,
```

---

##### _Rode as configurações:_

```php
...

while ($reg = $file->getData()) {

}
```

---

- **_Valor Recebido por $reg no While():_**

```php
// se useHeader for Abilitado
[
  "id"       => "14",
  "nome"     => "Lais Teixeira",
  "telefone" => "(31) 90000-0004",
  "email"    => "contato@Dragons.com"
];
// Caso o useHeader estiver desabilitado
[
     0       => "14",
     1       => "Lais Teixeira",
     2       => "(31) 90000-0004",
     3       => "contato@lais.com"
];
```

_OBS: se o useHeader for desabilitado ele não ira pular a primeira linha então o primeiro registro seria (id,nome,telefone,email);_

---

##### _Get and Setters Configurações:_

```php
while ($reg = $file->getData()) {

  if($file->getValue("0") == $file->getValue("id")){
    $file->setValue("1",    "Heloisa"); // userHeader true or false
    $file->setValue("nome", "Heloisa"); // if UseHeader == true
    return true; // Condição verdadeira
  }

}
```

_Não use: ~~$reg['nome'] = "Heloisa"~~; <br /> Recupere uma configuração pelo nome ou indice_

---

##### _Salva as Modificações:_

```php
$file->saveConfig(); // Salva no arquivo atual
$file->saveConfig("./CSV/NewCSV.csv"); // Salva em um novo arquivo
```

---

##### _Pula para uma linha especifica:_

```php
$reg = $file->setData(5); // Começa na 5º posição
$reg = $file->backData(); // Volta 1 posição atraz
$reg = $file->resetData(); // Volta para a primeira posição
$reg = $file->endData(); // Vai para a ultima posição
```

_Se o userHeader for verdadeiro retornara o usuario ID 15, se não resgatara o id 14_

---

##### _get header:_

```php
$header = $file->getHeader();
// EXEMPLO
[
  0 => 'id',
  1 => 'nome',
  2 => 'telefone',
  3 => 'email'
];
```

_Se o useHeader for falso retornara nulo (null);_

---

##### _Manipular arquivos:_

```php
$file->setDelimitador(","); // Muda o Delimitador do Arquivo
$file->setStartLine(-5); // Começara no usario de ID 15
$file->setLimitLine(2); // Limite de usuarios carregados
```

_Requer: $file->loadConfig(); para recarregar as posições. <br /> Requer: $file->saveConfig(); para atualizar o delimitador._

---

##### _Recarrega as Configurações:_

```php
$file->loadConfig(); // Limpa as modificações feitas
```

_Todas as modificações não salvas são perdidas_;

# Tratando Erros:

```php
 catch (FileManipulationExceptions $e) {
  switch ($e->getCode()) {
    case '1': // O Local do arquivo é nulo
    case '2': // O Arquivo não existe
    case '3': // O Item informado nao e um arquivo
    case '4': // O arquivo não pode ser lido (SEM PERMISSÃO)
    case '5': // SYNTAX ERROR FILE
    case '6': // O arquivo não pode ser Alterado ou Criado
    case '7': // StartLine CSV Invalido
    case '8': // Limit de Linhas Invalidos CSV
      echo $e->getMessage();
      break;
  }
}
```

---

### **Codigos**

1. O Arquivo e Null (Nulo)
2. O Arquivo não existe, corrija com:

```php
   /**
   * @param string $fileLocation File Location: C:/Web/config.env
   * @param boolean $SyntaxErrorReport Se for verdadeiro ele ira lançar
   * uma exceção caso uma linha não seja valida
   * @param boolean $createFile Se for verdadeiro a classe ira criar o arquivo
   * caso ele não exista, se não for possivel cria-lo lançara uma exceção
   */
  new PropertiesConfiguration("server.properties", true, true /*DEFINA TRUE*/);

  // OR

  /**
   * @param string $fileLocation Local do Arquivo
   * @param boolean $useHeader Se verdadeiro a primeira linha do arquivo sera pulada e usada como indice
   * @param string $delimitador Deve ser definido com o delimitador do arquivo
   *    caso seja "auto" ou null ele irar procurar um automaticamente
   * @param int $startLine Informe em qual linha o arquivo deve começar a leitura
   *    Se o valor for menor que zero ele contara do final para baixo
   * @param int $limitLine numero maximo de linhas que devem ser lidas
   * @param boolean $createFile Se for definido verdadeiro caso o arquivo informado não exista ele e criado
   */
  new CSVConfiguration("./CSV/Exemplos.csv", true, "auto", 0, null, true /*DEFINA TRUE*/);
```

3. Não é um nome de um arquivo - (VAZIO ou PASTA),
4. Não é possivel ler o arquivo - (SEM PERMISSÕES)
5. Erro de syntax e reportado se for encontrado em alguma linha, Desabilite com:

```php
   /**
   * @param string $fileLocation File Location: C:/Web/config.env
   * @param boolean $SyntaxErrorReport Se for verdadeiro ele ira lançar
   * uma exceção caso uma linha não seja valida
   */
  new PropertiesConfiguration("server.properties", false);
```

6. Arquivo não pode ser criado ou editado, Exeção lançada se createFile for verdadeiro ou quando for chamada saveConfig();

```php
...

try {

  $Config->saveConfig();
  // or
  $Config->saveConfig("./newlocation.tmp");

} catch (FileManipulationExceptions $e) {
  echo $e->getMessage();
}
```

7. O Valor Informado não é um numero.
8. O Valor Informado não é um numero ou é Menor ou igual a Zero
