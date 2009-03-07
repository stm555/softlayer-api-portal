<?php
class HardwareController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $soapClient = $this->_helper->soapClient('SoftLayer_Account');

        $objectMask = new SoftLayer_Soap_ObjectMask();
        $objectMask->hardware->datacenter;

        $this->view->paginator = $this->_helper->soapPaginator($this->_getParam('page'), $soapClient, 'getHardware', $objectMask);
    }

    public function viewAction()
    {
        $soapClient = $this->_helper->soapClient('SoftLayer_Hardware', $this->_getParam('hardwareId'));

        $objectMask = new SoftLayer_Soap_ObjectMask();
        $objectMask->components;
        $objectMask->softwareComponents;
        $objectMask->frontendNetworkComponents->networkVlan->primaryRouter;
        $objectMask->backendNetworkComponents->networkVlan->primaryRouter;
        $objectMask->serverRoom;
        $objectMask->datacenter;
        $objectMask->provisionDate;
        $objectMask->lastTransaction;

        $soapClient->setObjectMask($objectMask);

        $hardware = $soapClient->getObject();

        $this->view->hardware = $hardware;
    }
}
