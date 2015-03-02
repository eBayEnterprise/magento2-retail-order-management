<?php

namespace EbayEnterprise\Address\Model;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
    public function testDoSomething()
    {
        $validator = new Validation();
        $address = $this->getMockBuilder('\Magento\Customer\Model\Address');
        $this->assertNotNull($validator);
    }
}
