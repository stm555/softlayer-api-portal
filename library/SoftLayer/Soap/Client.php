<?php

class Softlayer_Soap_Client extends SoapClient
{

    const EXCEPTION_NO_API_KEY = 'EXCEPTION_NO_API_KEY';

    protected $_headers = array();
    protected $_serviceName;

    protected static $_user;
    protected static $_key;

    protected static $_endpoint;

    protected $_outputHeaders = array();

    private static $_processedObjects = array();

    public function __call($functionName, $arguments = null)
    {
        if (self::$_user == null && self::$_key == null) {
            throw new SoftLayer_Soap_Client_Exception(self::EXCEPTION_NO_API_KEY);
        }

        self::$_processedObjects = array();

        try {
            $result = parent::__call($functionName, $arguments, null, $this->_headers, $this->_outputHeaders);
        } catch (Exception $e) {
            if (property_exists($e, 'detail')) {
                self::_encapsulateArrays($e->detail);
            }
            throw $e;
        }

        self::_encapsulateArrays($result);

        return $result;
    }

    public static function setApiCredentials($apiUser, $apiKey)
    {
        self::$_user = $apiUser;
        self::$_key  = $apiKey;
    }

    public static function setEndpoint($endpoint)
    {
        self::$_endpoint = $endpoint;
    }

    public static function getSoapClient($serviceName, $id = null)
    {
        $soapClient = new SoftLayer_Soap_Client(self::$_endpoint.$serviceName.'?wsdl');
        $soapClient->addAuthenticationHeaders(self::$_user, self::$_key);
        $soapClient->_serviceName = $serviceName;

        if ($id != null) {
            $initParameters = new stdClass();
            $initParameters->id = $id;
            $soapClient->addHeader($serviceName.'InitParameters', $initParameters);
        }

        return $soapClient;
    }

    public function addHeader($headerName, $value)
    {
        $this->_headers[$headerName] = new SoapHeader('http://api.service.softlayer.com/soap/v3/', $headerName, $value);
    }

    public function getHeader($headerName)
    {
        return $this->_headers[$headerName];
    }

    public function addAuthenticationHeaders($username, $apiKey)
    {
        $header = new stdClass();
        $header->username = $username;
        $header->apiKey   = $apiKey;

        $this->addHeader('authenticate', $header);
    }

    public function setObjectMask($mask)
    {
        $objectMask = new stdClass();
        $objectMask->mask = $mask;

        $this->addHeader($this->_serviceName.'ObjectMask', $objectMask);
    }

    public function setObjectFilter($filter)
    {
        $this->addHeader($this->_serviceName.'ObjectFilter', $filter);
    }

    public function setResultLimitHeader($limit, $offset = 0)
    {
        $resultLimit = new stdClass();
        $resultLimit->limit = intval($limit);
        $resultLimit->offset = intval($offset);

        $this->addHeader('resultLimit', $resultLimit);
    }

    public function getOutputHeader($headerName)
    {
        if ($headerName == 'totalItems') {
            return $this->_outputHeaders[$headerName]->amount;
        }

        return $this->_outputHeaders[$headerName];
    }

    private static function _encapsulateArrays(&$item)
    {
        if (is_array($item)) {
            $childCount = count($item);
            reset($item);

            for ($i = 0; $i < $childCount; $i++) {
                $child = current($item);
                next($item);

                if (is_array($child) || (is_object($child) && !($child instanceof ArrayObject))) {
                    self::_encapsulateArrays($child);
                }
            }
            $item = new SoftLayer_Collection($item);

        } else if (is_object($item) && !($item instanceof ArrayObject)) {
            if (isset(self::$_processedObjects[spl_object_hash($item)])) return; // already processed this object
            self::$_processedObjects[spl_object_hash($item)] = true;
            foreach ($item AS $property => &$value) {
                if (is_array($value) || (is_object($value) && !($value instanceof ArrayObject))) {
                    self::_encapsulateArrays($value);
                }
            }
        }
    }

}
