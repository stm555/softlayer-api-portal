<?php

class SoftLayer_Db_Orm_Exception_UnknownSourceObject extends Zend_Exception
{
    public function __construct($object)
    {
        parent::__construct("The object of type '" . get_class($object) . "' could not be resolved.");
    }
}
