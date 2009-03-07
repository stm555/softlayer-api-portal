<?php
abstract class SoftLayer_Db_Table_Orm_Table_Abstract extends Zend_Db_Table_Abstract
{
    /**
     * SoftLayer_Db_Table_Orm_Row_Abstract class name.
     *
     * @var string
     */
    protected $_ormRowClass;

    /**
     * Classname for rowset
     *
     * @var string
     */
    protected $_ormRowsetClass = 'SoftLayer_Collection_Orm';

    protected $_columnMap = array();

    protected $_relationMap = array();

    /**
     * Fetches all rows.
     *
     * Honors the Zend_Db_Adapter fetch mode.
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = parent::fetchAll($where, $order, $count, $offset);

        // go through fetched rows and run them through the abstract factory, then check authentication

        $result = new $this->_ormRowsetClass();
        $result->setOrmSelect($where);
        $totalRows = $rows->count();
        if ($totalRows > 0) {
            Zend_Loader::loadClass($this->getOrmRowClass());

            $rows->rewind();
            for ($position = 0; $position < $totalRows; $position++) {
                $row = SoftLayer_Db_Table_Orm_Row_Abstract::factory($rows->offsetGet($position));

                if ($row->authenticateActiveUser()) {
                    $result[] = $row;
                }
            }
        }

        return $result;
    }

    /**
     * Fetches one row in an object of type Zend_Db_Table_Row_Abstract,
     * or returns Boolean false if no row matches the specified criteria.
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @return Zend_Db_Table_Row_Abstract The row results per the
     *     Zend_Db_Adapter fetch mode, or null if no row found.
     */
    public function fetchRow($where = null, $order = null)
    {
        $row = parent::fetchRow($where, $order);

        $row = SoftLayer_Db_Table_Orm_Row_Abstract::factory($row);

        if ($row->authenticateActiveUser()) {
            return $row;
        }

        return null;
    }

    /**
     * Return the orm row class name
     *
     * @return string
     */
    public function getOrmRowClass()
    {
        return substr(get_class($this), 0, -6);
    }

    public function &getColumnMap()
    {
        return $this->_columnMap;
    }

    public function &getRelationMap()
    {
        return $this->_relationMap;
    }

    public function getName()
    {
        return $this->_name;
    }
}