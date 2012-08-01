<?php

namespace Sds\ModuleUnitTester\BaseTest;

use Sds\ModuleUnitTester\AbstractTest;

class ModuleTest extends AbstractTest{

    public function setUp(){
        parent::setUp();
    }

    protected function alterConfig(array $config) {
        $config['sds']['doctrineExtensions']['extensionConfigs'] = array(
            'Sds\DoctrineExtensions\AccessControl' => null,
            'Sds\DoctrineExtensions\Accessor' => null,
            'Sds\DoctrineExtensions\Annotation' => null,
            'Sds\DoctrineExtensions\Audit' => null,
            'Sds\DoctrineExtensions\DiscriminatorMap' => null,
            'Sds\DoctrineExtensions\DoNotHardDelete' => null,
            'Sds\DoctrineExtensions\Freeze' => null,
            'Sds\DoctrineExtensions\Readonly' => null,
            'Sds\DoctrineExtensions\Serializer' => null,
            'Sds\DoctrineExtensions\SoftDelete' => null,
            'Sds\DoctrineExtensions\Stamp' => null,
            'Sds\DoctrineExtensions\State' => null,
            'Sds\DoctrineExtensions\UiHints' => null,
            'Sds\DoctrineExtensions\Validator' => null,
            'Sds\DoctrineExtensions\Workflow' => null,
            'Sds\DoctrineExtensions\Zone' => null,
        );
        return $config;
    }

    public function testModule(){
        $documentManager = $this->serviceManager->get('doctrine.documentmanager.odm_default');
    }
}