<?php

namespace EbayEnterprise\Address\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \EbayEnterprise\Address\Api\Data\AddressInterface (mock) */
    protected $addressDataModel;
    /** @var \EbayEnterprise\Address\Api\AddressValidationInterface (mock) */
    protected $addressValidation;
    /** @var \EbayEnterprise\Address\Helper\Converter (mock) */
    protected $addressConverter;
    /** @var \EbayEnterprise\Address\Api\Data\ValidationResultInterface (mock) */
    protected $validationResults;
    /** @var \Magento\Customer\Api\AddressRepositoryInterface (mock) */
    protected $addressRepository;
    /** @var \Magento\Framework\PhraseFactory (mock) */
    protected $phraseFactory;
    /** @var \Magento\Framework\Phrase (mock) */
    protected $phrase;
    /** @var ObjectManager */
    protected $objectManager;
    /** @var \Magento\Customer\Model\Data\Address */
    protected $customerAddress;
    /** @var Validator */
    protected $validator;

    public function setUp()
    {
        $this->addressDataModel = $this
            ->getMock('\EbayEnterprise\Address\Api\Data\AddressInterface');
        $this->addressValidation = $this
            ->getMock('\EbayEnterprise\Address\Api\AddressValidationInterface');
        $this->addressConverter = $this
            ->getMockBuilder('\EbayEnterprise\Address\Helper\Converter')
            ->setMethods(['convertCustomerAddressToDataAddress'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationResults = $this
            ->getMock('\EbayEnterprise\Address\Api\Data\ValidationResultInterface');
        $this->addressRepository = $this
            ->getMock('\Magento\Customer\Api\AddressRepositoryInterface');
        $this->phraseFactory = $this
            ->getMockBuilder('\Magento\Framework\PhraseFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->phrase = $this
            ->getMockBuilder('\Magento\Framework\Phrase')
            ->setMethods(['render'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->customerAddress = $this->objectManager->getObject(
            '\Magento\Customer\Model\Data\Address'
        );
        $this->validator = $this->objectManager->getObject(
            '\EbayEnterprise\Address\Model\Plugin\Validator',
            [
                'addressValidation' => $this->addressValidation,
                'addressConverter' => $this->addressConverter,
                'phraseFactory' => $this->phraseFactory,
            ]
        );
    }

    /**
     * When validation is successful, the original address should be returned.
     */
    public function testValidateAddressSuccess()
    {
        $this->addressConverter->expects($this->once())
            ->method('convertCustomerAddressToDataAddress')
            ->with($this->identicalTo($this->customerAddress))
            ->will($this->returnValue($this->addressDataModel));
        $this->addressValidation->expects($this->once())
            ->method('validate')
            ->with($this->identicalTo($this->addressDataModel))
            ->will($this->returnValue($this->validationResults));
        $this->validationResults->expects($this->any())
            ->method('isAcceptable')
            ->will($this->returnValue(true));

        $this->assertSame(
            [$this->customerAddress],
            $this->validator->beforeSave($this->addressRepository, $this->customerAddress)
        );
    }

    /**
     * When validation fails, an exception should be thrown.
     */
    public function testValidateAddressFailure()
    {
        $errorMessage = 'Invalid address';

        $this->addressConverter->expects($this->once())
            ->method('convertCustomerAddressToDataAddress')
            ->with($this->identicalTo($this->customerAddress))
            ->will($this->returnValue($this->addressDataModel));
        $this->addressValidation->expects($this->once())
            ->method('validate')
            ->with($this->identicalTo($this->addressDataModel))
            ->will($this->returnValue($this->validationResults));
        $this->validationResults->expects($this->any())
            ->method('isAcceptable')
            ->will($this->returnValue(false));
        $this->validationResults->expects($this->any())
            ->method('getFailureReason')
            ->will($this->returnValue($errorMessage));
        $this->phraseFactory->expects($this->once())
            ->method('create')
            ->with($this->identicalTo(['text' => $errorMessage]))
            ->will($this->returnValue($this->phrase));
        $this->phrase->expects($this->any())
            ->method('render')
            ->will($this->returnValue($errorMessage));

        $this->setExpectedException('\Magento\Framework\Exception\InputException', $errorMessage);
        $this->validator->beforeSave($this->addressRepository, $this->customerAddress);
    }
}
