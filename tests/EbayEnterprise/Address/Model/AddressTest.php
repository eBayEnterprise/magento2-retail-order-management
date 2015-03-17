<?php

namespace EbayEnterprise\Address\Model;

use Magento\TestFramework\Helper\ObjectManager;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->address = $this->objectManager->getObject(
            '\EbayEnterprise\Address\Model\Address'
        );
    }

    /**
     * Provide street data.
     *
     * @return array
     */
    public function provideStreetData()
    {
        return [
            ['123 Main St.',],
            [['123 Main St.', 'STE 3'],],
            [123,],
            [new \StdClass,],
        ];
    }

    /**
     * Test that, given any argument for a street address, an array is always
     * set as the street data.
     *
     * @param mixed
     * @dataProvider provideStreetData
     */
    public function testSetStreetDataIsAlwaysArray($streetData)
    {
        $this->address->setStreet($streetData);
        $this->assertTrue(is_array($this->address->getStreet()));
    }

    /**
     * If giving setStreet data that is already an array, the array should
     * not be wrapped in another array.
     */
    public function testSetStreetDataDoesNotDoubleWrapArrays()
    {
        $streetData = ['123 Main St.', 'STE 1', 'FL 2'];
        $this->address->setStreet($streetData);
        $this->assertSame($this->address->getStreet(), $streetData);
    }
}