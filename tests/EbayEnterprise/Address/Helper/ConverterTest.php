<?php

namespace EbayEnterprise\Address\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var \EbayEnterprise\Address\Api\Data\ApiInterfaceFactory (mock) */
    protected $addressFactory;
    /** @var \EbayEnterprise\Address\Api\Data\AddressInterface (mock) */
    protected $addressInterface;
    /** @var \Magento\Directory\Model\RegionFactory (mock) */
    protected $directoryRegionFactory;
    /** @var \Magento\Directory\Model\Region (mock) */
    protected $directoryRegion;
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
        $this->directoryRegionFactory = $this
            ->getMockBuilder('\Magento\Directory\Model\RegionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryRegion = $this
            ->getMockBuilder('\Magneto\Directory\Model\Region')
            ->setMethods(['load', 'getCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->converter = $this->objectManager->getObject(
            '\EbayEnterprise\Address\Helper\Converter',
            ['addressFactory' => $this->addressFactory, 'regionFactory' => $this->directoryRegionFactory]
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
        // When saving an address, new addresses will only have a region id.
        $regionData = ['region_id' => 51];
        $region = $this->objectManager->getObject(
            '\Magento\Customer\Model\Data\Region',
            ['data' => $regionData]
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

        // The region factory should be used to create a new directory region
        // model. The model should be loaded using the region id of the address
        // to get the region data needed, e.g. the region code.
        $this->directoryRegionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->directoryRegion));
        $this->directoryRegion->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($regionData['region_id']))
            ->will($this->returnSelf());
        $this->directoryRegion->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($this->addressData['region_code']));

        $this->addressFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['data' => $this->addressData]))
            ->will($this->returnValue($this->addressInterface));

        $this->converter->convertCustomerAddressToDataAddress($customerAddress);
    }

    /**
     * Test that when converting a customer address, if the address object
     * already has a region code, the directory region model isn't loaded - prevent
     * an unnecessary DB read when possible.
     */
    public function testConvertCustomerAddressAvoidingDbRead()
    {
        // Address region may already contain a region code, in which case
        // it should be used instead of loading the directory region model.
        $regionData = ['region_id' => 51, 'region_code' => $this->addressData['region_code']];
        $region = $this->objectManager->getObject(
            '\Magento\Customer\Model\Data\Region',
            ['data' => $regionData]
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

        // The region factory should be used to create a new directory region
        // model. The model should be loaded using the region id of the address
        // to get the region data needed, e.g. the region code.
        $this->directoryRegionFactory->expects($this->never())
            ->method('create');
        $this->directoryRegion->expects($this->never())
            ->method('load');

        $this->addressFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['data' => $this->addressData]))
            ->will($this->returnValue($this->addressInterface));

        $this->converter->convertCustomerAddressToDataAddress($customerAddress);
    }
}
