<?php

namespace EbayEnterprise\Address\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface (mock) */
    protected $scopeConfig;
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
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->addressData = $this->getMock('EbayEnterprise\Address\Api\Data\AddressInterface');

        $this->objectManager = new ObjectManager($this);
        $this->romFactory = $this->objectManager->getObject(
            '\eBayEnterprise\RetailOrderManagement\Payload\PayloadFactory'
        );
        $this->context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            ['scopeConfig' => $this->scopeConfig,]
        );
        $this->sdkHelper = $this->objectManager->getObject(
            'EbayEnterprise\Address\Helper\Sdk',
            ['context' => $this->context]
        );
    }

    /**
     * Test transferring data from an address object to a SDK physical address
     * payload.
     */
    public function testTransferAddressToPhysicalAddressPayload()
    {
        $this->addressData->expects($this->any())
            ->method('getStreet')
            ->will($this->returnValue($this->street));
        $this->addressData->expects($this->any())
            ->method('getCity')
            ->will($this->returnValue($this->city));
        $this->addressData->expects($this->any())
            ->method('getRegionCode')
            ->will($this->returnValue($this->regionCode));
        $this->addressData->expects($this->any())
            ->method('getCountryId')
            ->will($this->returnValue($this->countryId));
        $this->addressData->expects($this->any())
            ->method('getPostcode')
            ->will($this->returnValue($this->postcode));

        $payload = $this->romFactory->buildPayload('\eBayEnterprise\RetailOrderManagement\Payload\Address\SuggestedAddress');

        $this->sdkHelper->transferAddressToPhysicalAddressPayload(
            $this->addressData,
            $payload
        );
        $this->assertSame(implode("\n", $this->street), $payload->getLines());
        $this->assertSame($this->city, $payload->getCity());
        $this->assertSame($this->regionCode, $payload->getMainDivision());
        $this->assertSame($this->countryId, $payload->getCountryCode());
        $this->assertSame($this->postcode, $payload->getPostalCode());
    }

    /**
     * Test creating an address object from a SDK physical address payload.
     * Should fill out the builder object and then create a data object
     * containing the payload data.
     */
    public function testTransferPhysicalAddressPayloadToAddress()
    {
        $payload = $this->romFactory->buildPayload('\eBayEnterprise\RetailOrderManagement\Payload\Address\SuggestedAddress');
        $payload->setLines(implode("\n", $this->street))
            ->setCity($this->city)
            ->setMainDivision($this->regionCode)
            ->setCountryCode($this->countryId)
            ->setPostalCode($this->postcode);

        $this->addressData->expects($this->once())
            ->method('setStreet')
            ->with($this->identicalTo($this->street))
            ->will($this->returnSelf());
        $this->addressData->expects($this->once())
            ->method('setCity')
            ->with($this->identicalTo($this->city))
            ->will($this->returnSelf());
        $this->addressData->expects($this->once())
            ->method('setCountryId')
            ->with($this->identicalTo($this->countryId))
            ->will($this->returnSelf());
        $this->addressData->expects($this->once())
            ->method('setRegionCode')
            ->with($this->identicalTo($this->regionCode))
            ->will($this->returnSelf());
        $this->addressData->expects($this->once())
            ->method('setPostcode')
            ->with($this->identicalTo($this->postcode))
            ->will($this->returnSelf());

        $this->sdkHelper->transferPhysicalAddressPayloadToAddress(
            $payload,
            $this->addressData
        );
    }
}
