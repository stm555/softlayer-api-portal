<?php
class SoftLayer_Db_Exception_TableNotFound extends Zend_Exception
{
    public function __construct($tableName)
    {
        parent::__construct("The table class '{$tableName}' could not be found.");
    }
}
