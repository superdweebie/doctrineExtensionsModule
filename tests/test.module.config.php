<?php
return [
    'sds' => [
        'doctrineExtensions' => [
            'extensionConfigs' => [
                'Sds\DoctrineExtensions\AccessControl' => true,
                'Sds\DoctrineExtensions\Annotation' => true,
                'Sds\DoctrineExtensions\Crypt' => true,
                'Sds\DoctrineExtensions\Dojo' => [
                    'filePaths' => [
                        'all' => [
                            'filter' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document',
                            'path' => 'data'
                        ],
                    ],
                ],
                'Sds\DoctrineExtensions\Freeze' => true,
                'Sds\DoctrineExtensions\Readonly' => true,
                'Sds\DoctrineExtensions\Rest' => true,
                'Sds\DoctrineExtensions\Serializer' => [
                    'maxNestingDepth' => 2
                ],
                'Sds\DoctrineExtensions\SoftDelete' => true,
                'Sds\DoctrineExtensions\Stamp' => true,
                'Sds\DoctrineExtensions\State' => true,
                'Sds\DoctrineExtensions\Validator' => true,
                'Sds\DoctrineExtensions\Zone' => true,
            ],
        ],
    ],

    'doctrine' => [
        'configuration' => [
            'odm_default' => [
                'default_db' => 'doctrineExtensionsModuleTest',
                'proxy_dir'    => __DIR__ . '/Proxy',
                'hydrator_dir' => __DIR__ . '/Hydrator',
            ],
        ],
        'driver' => [
            'odm_default' => [
                'drivers' => [
                    'Sds\DoctrineExtensionsModule\Test\TestAsset\Document' => 'test'
                ],
            ],
            'test' => [
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'paths' => [
                    __DIR__.'/Sds/DoctrineExtensionsModule/Test/TestAsset/Document'
                ],
            ],
        ],
    ],

    'view_manager' => array(
        'display_exceptions' => true,
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/view/layout/layout.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/view',
        ),
    ),
];
