<?php
namespace MyUser;

use Zend\Mvc\MvcEvent;
use MyUser\Listener\MyUserListener;

class Module
{
    public function onBootstrap(MvcEvent $mvcEvent)
    {
        $em = $mvcEvent->getApplication()->getEventManager();
        $em->attach(new MyUserListener());
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

}