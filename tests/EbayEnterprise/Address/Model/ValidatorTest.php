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

        $this->request = $this->getMock('eBayEnterpries\RetailOrderManagement\Payload\Address\IValidationRequest');
        $this->response = $this->getMock('eBayEnterpries\RetailOrderManagement\Payload\Address\IValidationReply');
        $this->api = $this->getMock('eBayEnterprise\RetailOrderManagement\Api\IBidirectionalApi');
        $this->api->expects($this->any())
            ->method('getRequestBody')
            ->will($this->returnValue($this->request));
        $this->api->expects($this->any())
            ->method('getResponseBody')
            ->will($this->returnValue($this->response));

        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(
            'EbayEnterprise\Address\Model\Validator'
        );
    }

    /**
     * When an address has already been found to be invalid, an API request
     * to validate the address should not be made and the results of the
     * previous validation should be returned.
     */
    public function testAlreadyInvalidAddressIsNotValidated()
    {
        $error = ['Some error'];
        $this->assertSame(
            $error,
            $this->validator->afterValidate($this->address, ['Some error'])
        );
    }

    /**
     * When an address has not yet been found invalid though local checks,
     * validate it via the SDK.
     */
    public function testMakeSdkRequestToValidateAddress()
    {
        $this->api->expects($this->once())
            ->method('send')
            ->will($this->returnSelf());
        $this->validator->afterValidate($this->address, true);
    }
}
