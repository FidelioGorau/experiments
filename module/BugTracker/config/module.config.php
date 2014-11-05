<?php
return array(
    'doctrine' => array(
        'driver' => array(
            'bugtracker_entity' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => array(__DIR__ . '/../src/BugTracker/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'BugTracker\Entity' => 'bugtracker_entity',
                )
            )
        )
    ),

    'controllers' => array(
        'invokables' => array(
            'BugTracker\Controller\BugTracker' => 'BugTracker\Controller\TrackerController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'bugtracker' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/bugtracker[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'BugTracker\Controller\BugTracker',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'service_manager' => array(
        'factories' => array(
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        ),
    ),

    'navigation' => array(
        'default' => array(
            array(
                'label' => 'Bugtracker',
                'route' => 'bugtracker',
                'pages' => array(
                    array(
                        'label' => 'Asigned to me',
                        'route' => 'bugtracker',
                        'action' => 'active',

                    ),
                    array(
                        'label' => 'Resolved',
                        'route' => 'bugtracker',
                        'action' => 'resolved',
                    ),
                    array(
                        'label' => 'Closed',
                        'route' => 'bugtracker',
                        'action' => 'closed',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    )
);
