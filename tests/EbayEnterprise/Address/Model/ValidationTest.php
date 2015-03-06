<?php

namespace EbayEnterprise\Address\Model;

use eBayEnterprise\RetailOrderManagement\Api\HttpConfig;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
    public function testDoSomething()
    {
        $validator = new Validation();
        $address = $this->getMockBuilder('\Magento\Customer\Model\Address')->getMock();
        $this->assertNotNull($address);
        $this->assertInstanceOf('Magento\Customer\Model\Address', $address);
    }

    public function testGetSdk()
    {
        $apiConfig = new HttpConfig('key', 'example.com', '1', '0', 'ID', 'address', 'validate');
        $this->assertInstanceOf('eBayEnterprise\RetailOrderManagement\Api\IHttpConfig', $apiConfig);
    }
}
