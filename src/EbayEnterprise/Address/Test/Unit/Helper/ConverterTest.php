<?php

namespace EbayEnterprise\Address\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var \EbayEnterprise\Address\Api\Data\ApiInterfaceFactory (mock) */
    protected $addressFactory;
    /** @var \EbayEnterprise\Address\Api\Data\AddressInterface (mock) */
    protected $addressInterface;
    /** @var \Magento\Directory\Model\Region (mock) */
    protected $directoryRegion;
    /** @var ObjectManager */
    protected $objectManager;
    /** @var Converter */
    protected $converter;
    /** @var array */
    protected $validationAddressData = [
        'street' => ['123 Main St', 'STE 1', 'FL 3', 'BLDG 8'],
        'city' => 'Anytown',
        'region_code' => 'PA',
        'region_id' => 51,
        'region_name' => 'Pennsylvania',
        'country_id' => 'US',
        'postcode' => '12345',
    ];
    /** @var array */
    protected $customerAddressData = [
        'street' => ['123 Main St', 'STE 1', 'FL 3', 'BLDG 8'],
        'city' => 'Anytown',
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
        $this->regionHelper = $this
            ->getMockBuilder('\EbayEnterprise\Address\Helper\Region')
            ->setMethods(['loadRegion'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryRegion = $this
            ->getMockBuilder('\Magento\Directory\Model\Region')
            ->setMethods(['getCode', 'getId', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();

        // Pre-mock region getters to return region code, id and name
        $this->directoryRegion->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($this->validationAddressData['region_code']));
        $this->directoryRegion->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($this->validationAddressData['region_id']));
        $this->directoryRegion->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($this->validationAddressData['region_name']));

        $this->objectManager = new ObjectManager($this);
        $this->converter = $this->objectManager->getObject(
            '\EbayEnterprise\Address\Helper\Converter',
            ['addressFactory' => $this->addressFactory, 'regionHelper' => $this->regionHelper]
        );
    }

    /**
     * Test converting a customer address object to a data address results in
     * a new data address object with matching address data.
     */
    public function testConvertCustomerAddress()
    {
        // When saving an address, new addresses may not have full region data.
        $regionData = ['region_id' => 51];
        $region = $this->objectManager->getObject(
            '\Magento\Customer\Model\Data\Region',
            ['data' => $regionData]
        );

        // Add the region model to the customer address data.
        $addressData = $this->customerAddressData;
        $addressData['region'] = $region;

        $customerAddress = $this->objectManager->getObject(
            '\Magento\Customer\Model\Data\Address',
            ['data' => $addressData]
        );

        // The region factory should be used to create a new directory region
        // model. The model should be loaded using the region id of the address
        // to get the region data needed, e.g. the region code.
        $this->regionHelper->expects($this->once())
            ->method('loadRegion')
            ->will($this->returnValue($this->directoryRegion));
        $this->addressFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['data' => $this->validationAddressData]))
            ->will($this->returnValue($this->addressInterface));

        $this->converter->convertCustomerAddressToDataAddress($customerAddress);
    }
}
