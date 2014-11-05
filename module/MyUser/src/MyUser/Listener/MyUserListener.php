<?php
namespace MyUser\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;

class MyUserListener extends AbstractListenerAggregate
{
    public function attach(EventManagerInterface $events)
    {
        $sharedManager = $events->getSharedManager();
        $this->listeners[] = $sharedManager->attach('ZfcUser\Service\User', 'register', array($this, 'onRegister'));
        $this->listeners[] = $sharedManager->attach('ZfcUser\Service\User', 'register.post', array($this, 'onRegisterPost'));
    }

    public function onRegister(Event $e)
    {
        $sm = $e->getTarget()->getServiceManager();
        $em = $sm->get('doctrine.entitymanager.orm_default');
        $user = $e->getParam('user');
        $config = $sm->get('config');
        $criteria = array('roleId' => $config['zfcuser']['new_user_default_role']);
        $defaultUserRole = $em->getRepository('MyUser\Entity\Role')->findOneBy($criteria);

        if ($defaultUserRole !== null) {
            $user->addRole($defaultUserRole);
        }
    }

    public function onRegisterPost(Event $e)
    {
        $user = $e->getParam('user');
        $form = $e->getParam('form');

        // Do something after user has registered
    }
}
