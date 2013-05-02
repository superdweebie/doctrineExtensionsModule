<?php
/**
 * @package    Sds
 * @license    MIT
 */

namespace Sds\DoctrineExtensionsModule\Controller;

use Sds\DoctrineExtensionsModule\Exception\DocumentNotFoundException;
use Sds\DoctrineExtensionsModule\Options\DojoSrcController as Options;
use Zend\Mvc\Controller\AbstractActionController;

class DojoSrcController extends AbstractActionController
{

    protected $options;

    public function getOptions() {
        return $this->options;
    }

    public function setOptions($options) {
        if (!$options instanceof Options) {
            $options = new Options($options);
        }
        isset($this->serviceLocator) ? $options->setServiceLocator($this->serviceLocator) : null;
        $this->options = $options;
    }

    public function __construct($options = null) {
        $this->setOptions($options);
    }

    public function indexAction()
    {
        $module = $this->getEvent()->getRouteMatch()->getParam('module');

        $generator = $this->options->getGenerator();

        if ( ! $generator->canGenerate($module)){
            throw new DocumentNotFoundException();
        }

        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent($generator->generate($module));
        return $response;
    }
}