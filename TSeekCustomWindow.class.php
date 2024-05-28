<?php

use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TToast;
use Adianti\Widget\Form\TForm;
use Adianti\Wrapper\BootstrapDatagridWrapper;

 /**
     * @author: Lucas Cid
     * @created: 10/05/2024
     * @summary: Classe customizada para facilitar o processo de criacao de um Twindow para seekButton
     */
class TSeekCustomWindow extends TWindow 
{
    private $TSeekButtonName;
    private  $ServiceClass;
    private string $className;
    private string $FORM;
    private bool $isDisplayable = false;
    private $datagrid;
    private $datagridColumns = [];
    private $onSelectColumns = [];
    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * @author: Lucas Cid
     * @created: 11/05/2024
     * @summary: Substitui a funcao __construct criando a tela   e adicionando parametros.
     * @param : className: Classe servico para realizar o carregamento da datagrid.
     * @param : isDisplayable: boolean que informa se pode mostrar o nome da tabela no thead.
     * @param : columnKey: coluna responsável por guardar o dado que será enviado para o form mestre.
     * @param : FORM: Nome do formulário para enviar os dados.
     * @param : windowTitle: Nome da janela, podendo ser null.
     * @param : searchInputs: Array de nomes para inputs de busca que serão feitas em cima do service passado, o nome do campo precisa ser o mesmo que vai ser buscado na query, podendo ser nulo.
     * @param : onSelectColumns : array de colunas que serao enviadas ao selecionar algum elemento da lista para preencher o formulário.
     */
    public function onShow($param = NULL) 
    {
        parent::setTitle($param["windowTitle"] ?? "");
        
        $this->className        = $param["className"];
        $this->isDisplayable    = $param["isDisplayable"];
        $this->FORM             = $param["FORM_NAME"];
        $this->onSelectColumns  = $param["onSelectColumns"];
        $this->inputsToSearch   = $param["searchInputs"] ?? null;
        $this->TSeekButtonName  = $param["TSeekButtonName"];
        $this->searchableInputs = $param["searchableInputs"] ?? null;

        $test = new ReflectionClass($this->className);
        $test->getMethod('loadDatagrid');
        
        TSession::setValue("TSeekCustomWindow", $param);
       
        if(class_exists($this->className)) 
        {
            $this->ServiceClass = new $this->className();
        }
        else 
        {
            return new TMessage("error", "Classe^ passada como service não existe.");
        }
        
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);

        $data = $this->ServiceClass->loadDatagrid();
        //if data is an array with 0 pos, equals to an error

        if(count($data) > 0) 
        {

            $this->datagridColumns = array_keys((array) $data[0]);

            foreach ($this->datagridColumns as $column) 
            {
                $label = $this->dictionaryColumnNames($column);
                $dataGridColumn = new TDataGridColumn($column, $label, "left", 200 );
                $this->datagrid->addColumn($dataGridColumn);
            }
            $parametters = [];

            foreach($this->onSelectColumns as $column) 
            {
                $index = array_search( $column ,$this->datagridColumns);
                $field = $this->datagridColumns[$index];
                $parametters += ["$column" => "{{$field}}"];
            }
         
            $datagridAction = new TDataGridAction([$this, "onSelect"],$parametters);
            $this->datagrid->addAction($datagridAction, null, 'far:hand-pointer blue' );
            $datagridAction->setUseButton(true);
        
            
            $this->datagrid->createModel();

            $panel = new TPanelGroup;
            $panel->add($this->datagrid);  

            $this->pageNavigation = new TPageNavigation();
            $this->pageNavigation->setAction(new TAction([$this,'onReload'] ));

            $panel->addFooter($this->pageNavigation);

            $this->onReload($param);
            parent::add($panel);
        }   
    }
    /**
     * @author: Lucas Cid
     * @created: 11/05/2024
     * @summary: adiciona dados na datagrid paginada de acordo com o service enviado nos parametros na chamda em TAction(["TSeekCustomWindow", [<params>]]).
     */
    public function onReload($param = NULL) 
    {
        //validar se o valor que entra no input é o mesmo de alguma propriedade dos elementos da lista do setor especifico
        $params = TSession::getValue("TSeekCustomWindow");

        $actualPage = 0;
        if(isset($param["offset"])) 
        {
            $actualPage = $param["offset"];
        }
        
        //when the data is lost, it needed to instanciate again ;-;
        if(!isset($this->ServiceClass)) 
        {
            $this->onShow($params);
        }   

        $this->datagrid->clear();
        $countItems = call_user_func([$this->ServiceClass, 'countPaginated']);
        $items      = call_user_func([$this->ServiceClass, 'loadDatagrid'],["offset" => $actualPage]);

        $this->pageNavigation->setCount($countItems);
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setProperties($param ?? ['offset' => 0]);
        $this->pageNavigation->setLimit(5);
        //fixed limit, can be aloc with pass of params  
        foreach ( $items as $item) 
        {
            $this->datagrid->addItem($item);
        }
    }
    /**
     * @author: Lucas Cid
     * @created: 10/05/2024
     * @summary: adiciona um dicionario para interpretar os nomes das colunas no banco e formatar o thead das tabelas do TSeekCustomWindow
     * @param: $items : array de itens que sao os mesmos que estao sendo renderizados na datagrid
     * @param: $value : valor a ser encontrado dentro do array de itens que é passado no primeiro argumento, se encontrar, retorna true
     */
    //param pode ser tanto quando clica num item da datagrid quanto 
    public function onSelect($param = NULL) 
    {
        //TSession::setValue("TSeekCustomWindow-onSelect", $param);
        
        $dataAsArrayFromParams = TSession::getValue("TSeekCustomWindow");

        $ButtonName = $param["TSeekButtonName"] ?? $dataAsArrayFromParams["TSeekButtonName"];
        $this->FORM = $param["FORM_NAME"] ?? $dataAsArrayFromParams["FORM_NAME"];
        //takes value inside the input of tSeekButton
        
        $valueOfInputTSeekButton = $param[$ButtonName] ?? NULL;

        $class = $param["className" ] ?? $dataAsArrayFromParams["className"];

        $this->ServiceClass = new $class();
        if(isset($param["className"]) && $valueOfInputTSeekButton == "") 
        {   
            $columns = $param["onSelectColumns"];
            foreach($columns as $column) 
            {
                $columns[$column] = null;
            }
            TForm::sendData($this->FORM, (object) $columns);
            return parent::closeWindow();
        }
        if(isset($valueOfInputTSeekButton) && strlen($valueOfInputTSeekButton) > 0 && isset($param["TSeekButtonName"])) 
        {
            $itemFound = call_user_func([$this->ServiceClass,"TSeekButtonQuery"],["query" => $valueOfInputTSeekButton]);
            if($itemFound) {
                TForm::sendData($this->FORM, $itemFound);
                return parent::closeWindow();
            } else 
            {
                return TToast::show("error", "Valor não encontrado", "top right");
            }
        }
        TForm::sendData($this->FORM, (object) $param);
        parent::closeWindow();
    }
   
    /**
     * @aut hor: Lucas Cid
     * @created: 10/05/2024
     * @summary: adiciona um dicionario para interpretar os nomes das colunas no banco e formatar o thead das tabelas do TSeekCustomWindow
     */
    private function dictionaryColumnNames (string $columnName) 
    {
        $explodedString = explode("_", $columnName);

        $prefix = $explodedString[0];
        $secondPartOfString = $explodedString[1]; 
        
        $secondPartOfString =  ucfirst($secondPartOfString);
        
        if(!$this->isDisplayable) 
        {
            $secondPartOfString = "";
        }
        switch ($columnName) 
        {
            case $prefix == 'nm':
                return "Nome " . $secondPartOfString;
            case $prefix == 'dt':
                return "Data ". $secondPartOfString;
            case $prefix == 'tp':
                return "Tipo ". $secondPartOfString;
            case $prefix == 'fl':
                return "Status ".  $secondPartOfString;
            case $prefix == 'id':
                return "Id ".  $secondPartOfString;
            case $prefix == 'sg':
                return "Sigla ". $secondPartOfString;
            case $prefix == 'vl':
                return "Valor ".  $secondPartOfString;
            case $prefix == 'qt':
                return "Quantidade ". $secondPartOfString;
            case $prefix == 'cd':
                return "Código ". $secondPartOfString;
            case $prefix == 'ds':
                return "Descrição ". $secondPartOfString;
        }
    }
}
