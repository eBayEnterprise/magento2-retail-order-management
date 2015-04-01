<?php

namespace EbayEnterprise\Address\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RegionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Directory\Model\RegionFactory (mock) */
    protected $directoryRegionFactory;
    /** @var \Magento\Directory\Model\Region (mock) */
    protected $directoryRegion;
    /** @var Region */
    protected $regionHelper;

    public function setUp()
    {
        $this->directoryRegionFactory = $this
            ->getMockBuilder('\Magento\Directory\Model\RegionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryRegion = $this
            ->getMockBuilder('\Magneto\Directory\Model\Region')
            ->setMethods(['load', 'loadByCode', 'loadByName'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->regionHelper = $this->objectManager->getObject(
            '\EbayEnterprise\Address\Helper\Region',
            ['regionFactory' => $this->directoryRegionFactory]
        );
    }

    /**
     * When all data for the region is already available, the data should
     * be set on a new region model instance directly instead of loading
     * a new instance from the DB.
     */
    public function testLoadRegionHaveAllData()
    {
        $regionId = 42;
        $regionCode = 'NJ';
        $regionName = 'New Jersey';
        $countryId = 'US';

        $this->directoryRegionFactory->expects($this->once())
            ->method('create')
            ->with($this->identicalTo(['data' => ['id' => $regionId, 'code' => $regionCode, 'name' => $regionName, 'country_id' => $countryId]]))
            ->will($this->returnValue($this->directoryRegion));
        $this->directoryRegion->expects($this->never())
            ->method('load');
        $this->directoryRegion->expects($this->never())
            ->method('loadByCode');
        $this->directoryRegion->expects($this->never())
            ->method('loadByName');

        $this->assertSame(
            $this->directoryRegion,
            $this->regionHelper->loadRegion($regionId, $regionCode, $regionName, $countryId)
        );
    }

    /**
     * Provide data scenarios in which there will be insufficient data to load
     * a region model.
     *
     * @return array
     */
    public function provideInsufficientRegionData()
    {
        return [
            [null, null, null, null,],
            [null, null, 'North Dakota', null,],
            [null, 'ND', null, null,],
        ];
    }

    /**
     * When there is insufficient data to load a region model, one should not
     * be returned.
     *
     * @param string|null
     * @param string|null
     * @param string|null
     * @param string|null
     * @dataProvider provideInsufficientRegionData
     */
    public function testLoadRegionHaveInsufficientData($regionId, $regionCode, $regionName, $countryId)
    {
        $this->directoryRegionFactory->expects($this->never())
            ->method('create');
        $this->directoryRegion->expects($this->never())
            ->method('load');
        $this->directoryRegion->expects($this->never())
            ->method('loadByCode');
        $this->directoryRegion->expects($this->never())
            ->method('loadByName');

        $this->assertSame(
            null,
            $this->regionHelper->loadRegion($regionId, $regionCode, $regionName, $countryId)
        );
    }

    /**
     * When a region model needs to be loaded and a region id is available,
     * a region model should be created and loaded using the region id.
     */
    public function testLoadRegionById()
    {
        $regionId = 42;
        $regionCode = null;
        $regionName = null;
        $countryId = null;

        $this->directoryRegionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->directoryRegion));
        $this->directoryRegion->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($regionId))
            ->will($this->returnSelf());
        $this->directoryRegion->expects($this->never())
            ->method('loadByCode');
        $this->directoryRegion->expects($this->never())
            ->method('loadByName');

        $this->assertSame(
            $this->directoryRegion,
            $this->regionHelper->loadRegion($regionId, $regionCode, $regionName, $countryId)
        );
    }

    /**
     * When a region model needs to be loaded and there is no region id but a
     * region code and country id are vailable, a new region model should be
     * created and loaded with the region code and country id.
     */
    public function testLoadRegionByCode()
    {
        $regionId = null;
        $regionCode = 'NJ';
        $regionName = null;
        $countryId = 'US';

        $this->directoryRegionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->directoryRegion));
        $this->directoryRegion->expects($this->never())
            ->method('load');
        $this->directoryRegion->expects($this->once())
            ->method('loadByCode')
            ->with($this->identicalTo($regionCode), $this->identicalTo($countryId))
            ->will($this->returnSelf());
        $this->directoryRegion->expects($this->never())
            ->method('loadByName');

        $this->assertSame(
            $this->directoryRegion,
            $this->regionHelper->loadRegion($regionId, $regionCode, $regionName, $countryId)
        );
    }

    /**
     * When a region model needs to be loaded and there is no region id or region
     * code, but there is a region name and a country code, a new region model
     * should be created and loaded using the regin name and country id.
     */
    public function testLoadRegionByName()
    {
        $regionId = null;
        $regionCode = null;
        $regionName = 'New Jersey';
        $countryId = 'US';

        $this->directoryRegionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->directoryRegion));
        $this->directoryRegion->expects($this->never())
            ->method('load');
        $this->directoryRegion->expects($this->never())
            ->method('loadByCode');
        $this->directoryRegion->expects($this->once())
            ->method('loadByName')
            ->with($this->identicalTo($regionName), $this->identicalTo($countryId))
            ->will($this->returnSelf());

        $this->assertSame(
            $this->directoryRegion,
            $this->regionHelper->loadRegion($regionId, $regionCode, $regionName, $countryId)
        );
    }
}
