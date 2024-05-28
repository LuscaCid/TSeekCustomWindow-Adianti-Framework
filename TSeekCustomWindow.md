# TSeekCustomWindow

### usage

````php
class ...

$seekParaBuscaPatrimonio->setAction(new TAction(["TSeekCustomWindow", "onShow"], [
    "className" /***/ => "PatrimonioService", 
    "FORM_NAME" /***/ => "detail-transfer",
    "onSelectColumns" => ['cd_plaqueta', "nm_item", "vl_bem"],
    "TSeekButtonName" => "cd_plaqueta_busca",
    "windowTitle"     => "Selecão de Patrimônios",
    "isDisplayable"   => FALSE,                 
]));

...
````
### Parametros a serem passados...

-@param ClassName [String] Nome da classe de servico que vai implementar a interface ITSeekCustomWindow

-@param FORM_NAME [String] Nome do formulario ao qual serão enviados os dados apos a selecao do elemento na datagrid.

-@param onSelectColumns [array] [String] nomes das colunas que serao selecionadas no momento em que o usuario seleciona um elemento na datagrid, ou seja, os dados que serão retornados para serem lancados no formulario, precisando, entao, ter os mesmos nomes, tanto de campos, quanto dos names das columns na datagrid e o nome dos elementos que vêm do campo

-@param TSeekButtonName [string] Nome do componente para obtencao do que está escrito nele para fazer a busca a partir do metodo implementado pela interface para lidar com a selecao.

-@param WindowTitle [string] Nome da Janela do elemento, podendo ser null

-@param isDisplayable, boolean [optional] para mostrar o segundo nome no THead da datagrid, sendo ele relacionado ao nome da tabela no banco.

## Interface para implementar no service

````php
/**
 * @author :Lucas Felipe Lima Cid <lucasfelipaaa@gmail.com>
 * @summary: Interface para implementar no serviço que será enviado para o TSeekCustomWindow como parâmetro
 * @since 18/05/2024 1.1.0 : 
 */
interface ITSeekInterface 
{
    /**
     * @summary : Método que retorna um array de objetos contendo os dados que devem ser renderizados dentro do TSeekCustomWindow
     * @return array<object>|mixed
     */
    public static function loadDatagrid() : array;

    /**
     * @summary : Método que retorna um valor inteiro que representa o número de registros dentro deste datagrid para contagem no rodapé
     * @return int número de registros relacionado com o método loadDatagrid.
     */
    public static function countPaginated() : int;

    /**
     * @summary : Método que retorna o objeto encontrado por um identificador único no campo de busca do elemento TSeekButton adianti, após a busca e ao acionar o onBlur ao clicar no botão de busca, ele preencherá os campos passados como parâmetro no TSeekCustomWindow.
     * @param string $valueOfInputTSeekButton valor a ser procurado dentro da tabela relacionada ao TSeekCustomWindow
    */
    public static function TSeekButtonQuery(string $valueOfInputTSeekButton) : object; 
}

````