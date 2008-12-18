<?php
class HardwareController extends Zend_Controller_Action
{
    public function indexAction()
    {

        $soapClient = SoftLayer_Soap_Client::getSoapClient('SoftLayer_Account');

        $objectMask = new SoftLayer_Soap_ObjectMask();
        $objectMask->hardware->datacenter;

        $paginator = new SoftLayer_Paginator(new SoftLayer_Paginator_Adapter_Soap($soapClient, 'getHardware', $objectMask));
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $paginator->setItemCountPerPage(25);

        $this->view->paginator = $paginator;
    }
}
