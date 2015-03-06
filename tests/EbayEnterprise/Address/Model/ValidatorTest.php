<?php

namespace EbayEnterprise\Address\Model;

use eBayEnterprise\RetailOrderManagement\Api\HttpConfig;
use Magento\TestFramework\Helper\ObjectManager;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Model\Address\AbstractAddress (mock) */
    protected $address;
    /** @var \Magento\Customer\Api\Data\AddressInterface (mock) */
    protected $addressDataModel;
    /** @var Validator */
    protected $validator;

    public function setUp()
    {
        $this->address = $this->getMock('\Magento\Customer\Model\Address\AbstractAddress');
        $this->addressDataModel = $this->getMock('Magento\Customer\Api\Data\AddressInterface');
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(
            'EbayEnterprise\Address\Model\Validator'
        );
    }

    public function testAlreadyInvalidAddressIsNotValidated()
    {
        $error = ['Some error'];
        $this->assertSame(
            $error,
            $this->validator->afterValidate($this->address, ['Some error'])
        );
    }
}
