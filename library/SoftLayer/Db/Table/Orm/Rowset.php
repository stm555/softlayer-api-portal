<?php

class SoftLayer_Db_Table_Orm_Rowset extends Zend_Db_Table_Rowset_Abstract
{
    public function setRow($position, $value, $seek = false)
    {
        $key = $this->key();
        try {
            $this->seek($position);
            $this->_rows[$this->_pointer] = $value;
        } catch (Zend_Db_Table_Rowset_Exception $e) {
            require_once 'Zend/Db/Table/Rowset/Exception.php';
            throw new Zend_Db_Table_Rowset_Exception('No row could be found at position ' . (int) $position);
        }
        if ($seek == false) {
            $this->seek($key);
        }
        return $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
        unset($this->_rows[$offset]);
    }
}