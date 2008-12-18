<?php

/**
 * @see Zend_Paginator_Adapter_Interface
 */
require_once 'Zend/Paginator/Adapter/Interface.php';

class SoftLayer_Paginator_Adapter_Soap implements Zend_Paginator_Adapter_Interface
{
    protected $_soapClient = null;
    protected $_soapMethod = null;
    protected $_objectMask = null;
    protected $_objectFilter = null;
    protected $_itemCache = array();
    protected $_paginator = null;

    /**
     * Item count
     *
     * @var array
     */
    protected $_count = array();

    /**
     * Constructor.
     *
     * @param  SoftLayer_Soap_Client $soapClient Soap client to execute on
     * @param string $soapMethod Soap method to execute
     * @throws Zend_Paginator_Exception
     */
    public function __construct(SoftLayer_Soap_Client $soapClient, $soapMethod, $objectMask = null, $objectFilter = null)
    {
        if (!$soapClient instanceof SoftLayer_Soap_Client) {
            /**
             * @see Zend_Paginator_Exception
             */
            require_once 'Zend/Paginator/Exception.php';

            throw new Zend_Paginator_Exception('SoapClient must be provided');
        }

        $this->_soapClient      = $soapClient;
        $this->_soapMethod      = $soapMethod;
        $this->_objectMask      = $objectMask;
        $this->_objectFilter    = $objectFilter;
    }

    /**
     * Returns an iterator of items for a page, or an empty array.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return LimitIterator|array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        if ($this->_itemCache[$offset.'/'.$itemCountPerPage] == null) {
            $this->_soapClient->setResultLimitHeader($itemCountPerPage, $offset);

            if ($this->_objectMask != null) {
                $this->_soapClient->setObjectMask($this->_objectMask);
            }

            if ($this->_objectFilter != null) {
                $this->_soapClient->setObjectFilter($this->_objectFilter);
            }

            $this->_itemCache[$offset.'/'.$itemCountPerPage] = $this->_soapClient->{$this->_soapMethod}();

            $this->_count[$this->_paginator->getCurrentPageNumber()] = $this->_soapClient->getOutputHeader('totalItems');
        }

        return $this->_itemCache[$offset.'/'.$itemCountPerPage];
    }

    /**
     * Returns the total number of rows in the collection.
     *
     * @return integer
     */
    public function count()
    {
        if ($this->_paginator == null) {
            throw new Exception("Unable to perform query as the paginator has not been assigned to this adapter.");
        }

        if (is_null($this->_count[$this->_paginator->getCurrentPageNumber()]) {
            // execute the soap request
            $this->_paginator->getIterator();
        }

        return $this->_count[$this->_paginator->getCurrentPageNumber()];
    }

    public function setPaginator(SoftLayer_Paginator $paginator)
    {
        $this->_paginator = $paginator;
    }
}
