<?php

/**
 * @author :Lucas Felipe Lima Cid <lucasfelipaaa@gmail.com>
 * @summary: Interface to implements in the service that will be sent to the TSeekCustomWindow as parameter
 * @since 18/05/2024 1.1.0 : 
 */
interface ITSeekInterface 
{
    /**
     * @summary : Method that returns an array of objects containing the data that should be rendered inside the in TSeekCustomWindow 
     * @return array<object>|mixed
     */
    public static function loadDatagrid() : array;
    /**
     * @summary : Method that returns an int value that represents the  number of records inside this datagrid for count in footer
     * @return int number of records that is relationated with loadDatagrid method.
     */
    public static function countPaginated() : int;
    /**
     * @summary : Method that return the object find by an unique identifier in the search input field of the TSeekButton adianti element, after searh and makes the onBlur on click in search button, it 'll populate the fields passed as parameter in TSeekCustomWindow.
     * @param string $valueOfInputTSeekButton, value that will be searched inside corresponding to the table of TSeekCustomWindow
     */
    public static function TSeekButtonQuery(string $valueOfInputTSeekButton) : object; 
}