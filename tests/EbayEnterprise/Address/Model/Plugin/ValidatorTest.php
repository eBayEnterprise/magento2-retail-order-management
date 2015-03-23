<?php

namespace EbayEnterprise\Address\Model\Plugin;

use eBayEnterprise\RetailOrderManagement\Api\HttpConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManager;
    /** @var \Magento\Customer\Model\Address\AbstractAddress (mock) */
    protected $abstractAddress;
    /** @var \Magento\Customer\Api\Data\AddressInterface (mock) */
    protected $addressDataModel;
    /** @var Validator */
    protected $validator;
    /** @var \EbayEnterprise\Address\Helper\Converter (mock) */
    protected $addressConverter;
    /** @var \EbayEnterprise\Address\Api\Data\ValidationResultInterface (mock) */
    protected $validationResults;

    public function setUp()
    {
        $this->abstractAddress = $this
            ->getMockBuilder('\Magento\Customer\Model\Address\AbstractAddress')
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressDataModel = $this
            ->getMockBuilder('EbayEnterprise\Address\Api\Data\AddressInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressValidation = $this
            ->getMock('EbayEnterprise\Address\Api\AddressValidationInterface');
        $this->addressConverter = $this
            ->getMockBuilder('EbayEnterprise\Address\Helper\Converter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationResults = $this
            ->getMock('EbayEnterprise\Address\Api\Data\ValidationResultInterface');

        $this->objectManager = new ObjectManager($this);
        $this->validator = $this->objectManager->getObject(
            'EbayEnterprise\Address\Model\Plugin\Validator',
            [
                'addressValidation' => $this->addressValidation,
                'addressConverter' => $this->addressConverter,
            ]
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
        $this->addressValidation->expects($this->never())
            ->method('validate');
        $this->assertSame(
            $error,
            $this->validator->afterValidate($this->abstractAddress, ['Some error'])
        );
    }

    /**
     * When an address has not yet been found invalid though local checks,
     * validate it via the SDK.
     */
    public function testValidateAddressSuccess()
    {
        $this->addressConverter->expects($this->once())
            ->method('convertAbstractAddressToDataAddress')
            ->with($this->identicalTo($this->abstractAddress))
            ->will($this->returnValue($this->addressDataModel));
        $this->addressValidation->expects($this->once())
            ->method('validate')
            ->with($this->identicalTo($this->addressDataModel))
            ->will($this->returnValue($this->validationResults));
        $this->validationResults->expects($this->any())
            ->method('isAcceptable')
            ->will($this->returnValue(true));

        $this->assertTrue($this->validator->afterValidate($this->abstractAddress, true));
    }

    /**
     * When an address has not yet been found invalid though local checks,
     * validate it via the SDK.
     */
    public function testValidateAddressFailure()
    {
        $this->addressConverter->expects($this->once())
            ->method('convertAbstractAddressToDataAddress')
            ->with($this->identicalTo($this->abstractAddress))
            ->will($this->returnValue($this->addressDataModel));
        $this->addressValidation->expects($this->once())
            ->method('validate')
            ->with($this->identicalTo($this->addressDataModel))
            ->will($this->returnValue($this->validationResults));
        $this->validationResults->expects($this->any())
            ->method('isAcceptable')
            ->will($this->returnValue(false));

        $this->assertTrue(is_array($this->validator->afterValidate($this->abstractAddress, true)));
    }
}
