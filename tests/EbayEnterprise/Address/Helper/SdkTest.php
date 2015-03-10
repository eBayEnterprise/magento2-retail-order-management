<?php

namespace EbayEnterprise\Address\Helper;

use Magento\Framework\App\ScopeInterface;
use Magento\TestFramework\Helper\ObjectManager;

class SdkTest extends \PHPUnit_Framework_TestCase
{
    /** @var Sdk */
    protected $sdkHelper;
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManager;
    /** @var \Magento\Framework\App\Helper\Context (mock) */
    protected $context;
    /** @var \Magento\Customer\Model\Address\AbstractAddress (mock) */
    protected $address;
    /** @var \Magento\Customer\Api\Data\AddressInterface (mock) */
    protected $addressData;
    /** @var \Magento\Customer\Api\Data\AddressDataBuilder (mock) */
    protected $addressDataBuilder;
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface (mock) */
    protected $scopeConfig;
    /** @var \Magento\Customer\Api\Data\RegionInterface (mock) */
    protected $region;
    /** @var string[] */
    protected $street = ['123 Main St', 'Ste 3', 'Bldg 5'];
    /** @var string */
    protected $city = 'King of Prussia';
    /** @var string */
    protected $regionCode = 'PA';
    /** @var string */
    protected $countryId = 'US';
    /** @var string */
    protected $postcode = '19406';

    public function setUp()
    {
        $this->addressData = $this->getMock('\Magento\Customer\Api\Data\AddressInterface');
        $this->scopeConfig = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->region = $this->getMock('\Magento\Customer\Api\Data\RegionInterface');
        $this->regionFactory = $this->getMock('\Magento\Customer\Api\Data\RegionInterfaceFactory');

        $this->objectManager = new ObjectManager($this);
        $this->addressDataBuilder = $this->objectManager->getObject(
            '\Magento\Customer\Api\Data\AddressDataBuilder'
        );
        $this->romFactory = $this->objectManager->getObject(
            '\eBayEnterprise\RetailOrderManagement\Payload\PayloadFactory'
        );
        $this->context = $this->objectManager->getObject(
            '\Magento\Framework\App\Helper\Context',
            ['scopeConfig' => $this->scopeConfig,]
        );
        $this->sdkHelper = $this->objectManager->getObject(
            '\EbayEnterprise\Address\Helper\Sdk',
            [
                'context' => $this->context,
            ]
        );
    }

    public function testTransferAddressToPhysicalAddressPayload()
    {
        $this->addressData->expects($this->any())
            ->method('getStreet')
            ->will($this->returnValue($this->street));
        $this->addressData->expects($this->any())
            ->method('getCity')
            ->will($this->returnValue($this->city));
        $this->addressData->expects($this->any())
            ->method('getRegion')
            ->will($this->returnValue($this->region));
        $this->addressData->expects($this->any())
            ->method('getCountryId')
            ->will($this->returnValue($this->countryId));
        $this->addressData->expects($this->any())
            ->method('getPostcode')
            ->will($this->returnValue($this->postcode));
        $this->region->expects($this->any())
            ->method('getRegionCode')
            ->will($this->returnValue($this->regionCode));

        $payload = $this->romFactory->buildPayload('\eBayEnterprise\RetailOrderManagement\Payload\Address\SuggestedAddress');

        $this->sdkHelper->transferAddressToPhysicalAddressPayload(
            $this->addressData,
            $payload
        );
        $this->assertSame(
            implode("\n", $this->street),
            $payload->getLines()
        );
        $this->assertSame($this->city, $payload->getCity());
        $this->assertSame($this->regionCode, $payload->getMainDivision());
        $this->assertSame($this->countryId, $payload->getCountryCode());
        $this->assertSame($this->postcode, $payload->getPostalCode());
    }

    public function testTransferPhysicalAddressPayloadToAddress()
    {
        $this->markTestIncomplete('Need to get region factory/injection working');

        $payload = $this->romFactory->buildPayload('\eBayEnterprise\RetailOrderManagement\Payload\Address\SuggestedAddress');
        $payload->setLines(implode("\n", $this->street))
            ->setCity($this->city)
            ->setMainDivision($this->regionCode)
            ->setCountryCode($this->countryId)
            ->setPostalCode($this->postcode);

        $this->regionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->region));
        $this->region->expects($this->once())
            ->method('setRegionCode')
            ->will($this->returnSelf());

        $address = $this->sdkHelper->transferPhysicalAddressPayloadToAddress(
            $payload,
            $this->addressDataBuilder
        );
        $this->assertSame($this->street, $address->getStreet());
        $this->assertSame($this->city, $address->getCity());
        $this->assertSame($this->regionCode, $address->getRegion()->getRegionCode());
        $this->assertSame($this->countryId, $address->getCountryId());
        $this->assertSame($this->postcode, $address->getPostcode());
    }
}
