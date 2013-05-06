<?php

namespace Sds\DoctrineExtensionsModule\Test\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentType;

class DojoSrcControllerTest extends AbstractHttpControllerTestCase{

    protected static $staticDcumentManager;

    protected static $dbDataCreated = false;

    public function setUp(){

        $this->setApplicationConfig(
            include __DIR__ . '/../../../../test.application.config.php'
        );

        parent::setUp();
    }

    public function testGetDojoSrc(){

        $accept = new Accept;
        $accept->addMediaType('application/javascript');

        $this->getRequest()
            ->setMethod('GET')
            ->getHeaders()->addHeader($accept);

        $this->dispatch('/dojo_src/Sds/DoctrineExtensionsModule/Test/TestAsset/Document/Author.js');

        $this->assertContains('This code generated by Sds\DoctrineExtensions\Dojo', $this->getResponse()->getContent());
        $this->assertResponseStatusCode(200);
    }
}
