<?php

namespace EbayEnterprise\TestFramework\Bootstrap;

use Magento\TestFramework\Application;
use Magento\TestFramework\Bootstrap\DocBlock;

class BehatDocBlock extends DocBlock
{
    public function __construct($fixturesBaseDir)
    {
        $this->_fixturesBaseDir = $fixturesBaseDir;
    }

    public function registerAnnotations(Application $application)
    {
        $eventManager = new \Magento\TestFramework\EventManager($this->_getSubscribers($application));
        \Magento\TestFramework\Event\Magento::setDefaultEventManager($eventManager);
    }
}
