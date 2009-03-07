<?php
class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
      /*
        $query = new SoftLayer_User();
        $query->roles->role->keyname = 'ADMIN';
        
        try {
        $users = SoftLayer_Db_Orm::findByObjectFilter($query);
        $users->roles->role;
        print_pre($users);
        //print_pre($users->roles->role, 'data');
        } catch (Exception $e) {
           print_pre($e);            
        }
        
        $user = $users->getFirstWhere('firstName', 'test');

        $user->roles;

        print_pre($user->roles->getOrmSelect()->__toString());

        $user->roles->role;

        print_pre($user);*/

    }
}

