<?php

namespace EbayEnterprise\Address\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var \EbayEnterprise\Address\Api\Data\ApiInterfaceFactory (mock) */
    protected $addressFactory;
    /** @var \EbayEnterprise\Address\Api\Data\AddressInterface (mock) */
    protected $addressInterface;
    /** @var ObjectManager */
    protected $objectManager;
    /** @var Converter */
    protected $converter;
    /** @var array */
    protected $addressData = [
        'street' => ['123 Main St', 'STE 1', 'FL 3', 'BLDG 8'],
        'city' => 'Anytown',
        'region_code' => 'PA',
        'country_id' => 'US',
        'postcode' => '12345',
    ];

    public function setUp()
    {
        $this->addressFactory = $this
            ->getMockBuilder('\EbayEnterprise\Address\Api\Data\AddressInterfaceFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressInterface = $this
            ->getMockForAbstractClass('\EbayEnterprise\Address\Api\Data\AddressInterface');

        $this->objectManager = new ObjectManager($this);
        $this->converter = $this->objectManager->getObject(
            '\EbayEnterprise\Address\Helper\Converter',
            ['addressFactory' => $this->addressFactory]
        );
    }

    /**
     * Test converting an abstract address object to a data address results in
     * a new data address object with matching address data.
     */
    public function testConvertAbstractAddress()
    {
        $addressData = $this->addressData;
        $abstractAddress = $this->objectManager->getObject(
            '\Magento\Customer\Model\Address\AbstractAddress',
            ['data' => $addressData]
        );
        $this->addressFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['data' => $addressData]))
            ->will($this->returnValue($this->addressInterface));

        $this->converter->convertAbstractAddressToDataAddress($abstractAddress);
    }

    /**
     * Test converting a customer address object to a data address results in
     * a new data address object with matching address data.
     */
    public function testConvertCustomerAddress()
    {
        $regionData = ['region_code' => 'PA'];
        $region = $this->objectManager->getObject(
            '\Magento\Customer\Model\Data\Region',
            ['data' => ['region_code' => 'PA']]
        );
        $addressData = $this->addressData;
        // Customer address has no region code. Includes a "region" containing
        // a region object instead.
        unset($addressData['region_code']);
        $addressData['region'] = $region;

        $customerAddress = $this->objectManager->getObject(
            '\Magento\Customer\Model\Data\Address',
            ['data' => $addressData]
        );

        $this->addressFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['data' => $this->addressData]))
            ->will($this->returnValue($this->addressInterface));

        $this->converter->convertCustomerAddressToDataAddress($customerAddress);
    }
}
