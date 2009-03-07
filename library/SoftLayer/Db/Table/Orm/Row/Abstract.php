<?php

abstract class SoftLayer_Db_Table_Orm_Row_Abstract
{
    /**
     * Row entity
     *
     * @var Zend_Db_Table_Row
     */
    protected $_row;

    public function __construct($row = null)
    {

        if ($row instanceof Zend_Db_Table_Row) {
            $this->_row = $row;
            $this->init();

            $columnMap = $this->_row->getTable()->getColumnMap();
            $columnCount = count($columnMap);

            // @todo: throw exception when column map is empty

            reset($columnMap);
            for ($i = 0; $i < $columnCount; $i++) {
                $key = key($columnMap);
                $value = current($columnMap);
                next($columnMap);
                $this->{$key} = $this->_row->{$value};
            }
        }
    }

    public function init()
    {}

    public function authenticateActiveUser()
    {
        return true;
    }

    public static function factory(Zend_Db_Table_Row $row, $className = __CLASS__)
    {
        $ormRowClass = $row->getTable()->getOrmRowClass();
        if ($className == __CLASS__) {
            return call_user_func_array(array($ormRowClass, __FUNCTION__), array($row, $ormRowClass));
        }

        return new $ormRowClass($row);
    }

    public function getRow()
    {
        return $this->_row;
    }

    public function __get($name)
    {
        return SoftLayer_Db_Orm::get($this, $name);
    }
}