<?php

class SoftLayer_Paginator extends Zend_Paginator
{
    private $_currentPageNumberSet = false;

    public function __construct(Zend_Paginator_Adapter_Interface $adapter)
    {
        parent::__construct($adapter);

        if (method_exists($adapter, 'setPaginator')) {
            $adapter->setPaginator($this);
        }
    }

    public function normalizePageNumber($pageNumber)
    {
        /**
         * The default action of this method is to get the total number of items in the "set" and then
         * make sure that the page number is within range.  Because we are using SOAP and don't want to
         * make multiple requests, we're not going to allow this right now. We'll just throw an error
         * when it's out of range.
         */
        return $pageNumber;
    }

    public function setCurrentPageNumber($pageNumber)
    {
        $this->_currentPageNumberSet = true;

        return parent::setCurrentPageNumber($pageNumber);
    }

    public function setItemCountPerPage($itemCountPerPage)
    {
        if ($this->_currentPageNumberSet === false) {
            throw new Exception("You must first set the current page number for the paginator to prevent duplicate SOAP requests.");
        }

        return parent::setItemCountPerPage($itemCountPerPage);
    }
}