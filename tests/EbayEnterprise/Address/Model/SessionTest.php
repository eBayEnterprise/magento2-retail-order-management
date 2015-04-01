<?php

namespace EbayEnterprise\Address\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Session\SessionManager (mock) */
    protected $sessionManager;
    /** @var \EbayEnterprise\Address\Api\Data\AddressInterface (mock) */
    protected $address;
    /** @var \EbayEnterprise\Address\Api\Data\ValidationResultInterface' (mock) */
    protected $result;
    /** @var \EbayEnterprise\Address\Model\AddressResultPair (mock) */
    protected $addressResultPair;
    /** @var \EbayEnterprise\Address\Model\AddressResultPairFactory (mock) */
    protected $addressResultPairFactory;
    /** @var ObjectManager */
    protected $objectManager;
    /** @var Session */
    protected $session;

    public function setUp()
    {
        $this->sessionManager = $this
            ->getMockBuilder('\Magento\Framework\Session\SessionManager')
            ->setMethods(['getData', 'setData', 'getAddressResultPairs', 'setAddressResultPairs', 'start'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->address = $this
            ->getMock('\EbayEnterprise\Address\Api\Data\AddressInterface');
        $this->result = $this
            ->getMock('\EbayEnterprise\Address\Api\Data\ValidationResultInterface');
        $this->addressResultPair = $this
            ->getMockBuilder('\EbayEnterprise\Address\Model\AddressResultPair')
            ->setMethods(['getResult', 'setResult', 'getAddress', 'setAddress', 'compareAddress'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressResultPairFactory = $this
            ->getMockBuilder('\EbayEnterprise\Address\Model\AddressResultPairFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        // Mock default behaviour for the factory to always return an address result pair
        $this->addressResultPairFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->addressResultPair));

        $this->objectManager = new ObjectManager($this);
        $this->session = $this->objectManager->getObject(
            '\EbayEnterprise\Address\Model\Session',
            [
                'sessionManager' => $this->sessionManager,
                'addressResultPairFactory' => $this->addressResultPairFactory,
            ]
        );
    }

    /**
     * When the are no results in the session storage, there should be no
     * results returned for the address.
     */
    public function testGetResultsForAddressNoResults()
    {
        $this->sessionManager->expects($this->any())
            ->method('getAddressResultPairs')
            ->will($this->returnValue(null));

        $this->assertNull($this->session->getResultForAddress($this->address));
    }

    /**
     * When the collection of results in the session storage is empty, there
     * should be no results returned for the address.
     */
    public function testGetResultsForAddressEmptyResults()
    {
        $this->sessionManager->expects($this->any())
            ->method('getAddressResultPairs')
            ->will($this->returnValue([]));

        $this->assertNull($this->session->getResultForAddress($this->address));
    }

    /**
     * When a matching address result pair for the address is found, the result
     * from that pair should be returned.
     */
    public function testGetResultsForAddressMatchingAddressInAddressResultPairs()
    {
        // Set the address result pair to match the address and return the result.
        $this->addressResultPair->expects($this->any())
            ->method('compareAddress')
            ->with($this->identicalTo($this->address))
            ->will($this->returnValue(true));
        $this->addressResultPair->expects($this->any())
            ->method('getResult')
            ->will($this->returnValue($this->result));
        // Place the address result pair in the array of pairs returned from the
        // session storage.
        $this->sessionManager->expects($this->any())
            ->method('getAddressResultPairs')
            ->will($this->returnValue(
                [$this->addressResultPair]
            ));

        $this->assertSame(
            $this->result,
            $this->session->getResultForAddress($this->address)
        );
    }

    /**
     * When an address result pair for the address already exists, the result
     * in the pair should be updated to match the given result.
     */
    public function testSetResultForExistingAddressResultPair()
    {
        // Make the result pair match the given address - pair exists for address.
        $this->addressResultPair->expects($this->any())
            ->method('compareAddress')
            ->with($this->identicalTo($this->address))
            ->will($this->returnValue(true));
        // Expect the result pair results to be set to the given result.
        $this->addressResultPair->expects($this->once())
            ->method('setResult')
            ->with($this->identicalTo($this->result))
            ->will($this->returnSelf());
        // Make sure a new address result pair is not created - existing one
        // should end up getting modified.
        $this->addressResultPairFactory->expects($this->never())
            ->method('create');
        // Make sure the expected address result pair is returned from the
        // session storage.
        $this->sessionManager->expects($this->any())
            ->method('getAddressResultPairs')
            ->will($this->returnValue([$this->addressResultPair]));

        $this->assertSame(
            $this->session,
            $this->session->setResultForAddress($this->address, $this->result)
        );
    }

    /**
     * When an address result pair for the address already exists, the result
     * in the pair should be updated to match the given result.
     */
    public function testSetResultForNewAddressResultPair()
    {
        $resultId = 'AVR-12345';
        $this->result->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($resultId));

        // Make sure a new address result pair is created for the address
        // and result.
        $this->addressResultPairFactory->expects($this->once())
            ->method('create')
            ->with($this->identicalTo(['address' => $this->address, 'result' => $this->result]))
            ->will($this->returnValue($this->addressResultPair));
        // Ensure none of the address result pairs in the session storage already
        // match the provided address - nonexistent set will not include a match.
        $this->sessionManager->expects($this->any())
            ->method('getAddressResultPairs')
            ->will($this->returnValue(null));
        $this->sessionManager->expects($this->once())
            ->method('setAddressResultPairs')
            ->with($this->identicalTo([$resultId => $this->addressResultPair]))
            ->will($this->returnSelf());

        $this->assertSame(
            $this->session,
            $this->session->setResultForAddress($this->address, $this->result)
        );
    }
}
