<?php

namespace EbayEnterprise\Address\Model\Service;

use eBayEnterprise\RetailOrderManagement\Api\Exception\NetworkError;
use eBayEnterprise\RetailOrderManagement\Api\Exception\UnsupportedHttpAction;
use eBayEnterprise\RetailOrderManagement\Api\Exception\UnsupportedOperation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AddressValidationTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->validationResult = $this
            ->getMock('\EbayEnterprise\Address\Api\Data\ValidationResultInterface');
        $this->address = $this
            ->getMock('\EbayEnterprise\Address\Api\Data\AddressInterface');
        $this->resultFactory = $this
            ->getMockBuilder('\EbayEnterprise\Address\Api\Data\ValidationResultInterfaceFactory')
            ->setMockClassName('Success_ValidationResultIterfaceFactory_mock')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->exceptionResultFactory = $this
            ->getMockBuilder('\EbayEnterprise\Address\Api\Data\ValidationResultInterfaceFactory')
            ->setMockClassName('Exception_ValidationResultIterfaceFactory_mock')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestPayload = $this
            ->getMock('\eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationRequest');
        $this->replyPayload = $this
            ->getMock('\eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationReply');
        $this->sdkHelper = $this
            ->getMockBuilder('\EbayEnterprise\Address\Helper\Sdk')
            ->setMethods(['prepareSdkRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->session = $this
            ->getMockBuilder('\EbayEnterprise\Address\Model\Session')
            ->setMethods(['getResultForAddress', 'setResultForAddress', 'setCurrentResult'])
            ->disableOriginalConstructor()
            ->getMock();

        // Mock out the API and API factory.
        $this->api = $this
            ->getMock('\eBayEnterprise\RetailOrderManagement\Api\IBidirectionalApi');
        $this->apiFactory = $this
            ->getMockBuilder('\EbayEnterprise\Address\Model\HttpApiFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        // Given a scope config, the API factory will always return the API mock
        $this->apiFactory->expects($this->any())
            ->method('create')
            ->with($this->isInstanceOf('\Magento\Framework\App\Config\ScopeConfigInterface'))
            ->will($this->returnValue($this->api));

        $this->objectManager = new ObjectManager($this);
        $this->addressValidation = $this->objectManager->getObject(
            '\EbayEnterprise\Address\Model\Service\AddressValidation',
            [
                'sdkHelper' => $this->sdkHelper,
                'httpApiFactory' => $this->apiFactory,
                'resultFactory' => $this->resultFactory,
                'exceptionResultFactory' => $this->exceptionResultFactory,
                'session' => $this->session,
            ]
        );
    }

    /**
     * When the request is successful, return a new result object from the
     * result interface with the response body and the address that was
     * validated.
     */
    public function testValidateSuccess()
    {
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with($this->identicalTo(['replyPayload' => $this->replyPayload, 'originalAddress' => $this->address]))
            ->will($this->returnValue($this->validationResult));
        $this->exceptionResultFactory->expects($this->never())
            ->method('create');

        $this->session->expects($this->once())
            ->method('setResultForAddress')
            ->with($this->identicalTo($this->address), $this->identicalTo($this->validationResult))
            ->will($this->returnSelf());
        $this->session->expects($this->once())
            ->method('setCurrentResult')
            ->with($this->identicalTo($this->validationResult))
            ->will($this->returnSelf());
        $this->sdkHelper->expects($this->any())
            ->method('prepareSdkRequest')
            ->will($this->returnArgument(0));
        $this->api->expects($this->once())
            ->method('setRequestBody')
            ->with($this->identicalTo($this->requestPayload))
            ->will($this->returnSelf());
        $this->api->expects($this->once())
            ->method('getRequestBody')
            ->will($this->returnValue($this->requestPayload));
        $this->api->expects($this->once())
            ->method('send')
            ->will($this->returnSelf());
        $this->api->expects($this->once())
            ->method('getResponseBody')
            ->will($this->returnValue($this->replyPayload));

        $this->assertSame(
            $this->validationResult,
            $this->addressValidation->validate($this->address)
        );
    }

    /**
     * Provide expected exception types to be thrown by the SDK when attempting
     * to validate an address.
     *
     * @return array
     */
    public function provideSdkExceptions()
    {
        return [
            [new NetworkError,],
            [new UnsupportedOperation,],
            [new UnsupportedHttpAction,],
        ];
    }

    /**
     * When the request is successful, return a new result object from the
     * result interface with the response body and the address that was
     * validated.
     *
     * @param Exception $failureException Expected exception to be thrown by the SDK
     * @dataProvider provideSdkExceptions
     */
    public function testValidateNetworkError($failureException)
    {
        $this->resultFactory->expects($this->never())
            ->method('create');
        $this->exceptionResultFactory->expects($this->once())
            ->method('create')
            ->with($this->identicalTo(['originalAddress' => $this->address, 'failureException' => $failureException]))
            ->will($this->returnValue($this->validationResult));

        $this->sdkHelper->expects($this->any())
            ->method('prepareSdkRequest')
            ->will($this->returnArgument(0));
        $this->api->expects($this->once())
            ->method('getRequestBody')
            ->will($this->returnValue($this->requestPayload));
        $this->api->expects($this->once())
            ->method('setRequestBody')
            ->with($this->identicalTo($this->requestPayload))
            ->will($this->returnSelf());
        $this->api->expects($this->once())
            ->method('send')
            ->will($this->throwException($failureException));

        $this->assertSame(
            $this->validationResult,
            $this->addressValidation->validate($this->address)
        );
    }

    /**
     * When an address already has results stashed in the session, the previous
     * result should be returned and a new API request should not be made.
     */
    public function testValidateAddressWithStashedResult()
    {
        $this->session->expects($this->once())
            ->method('getResultForAddress')
            ->with($this->identicalTo($this->address))
            ->will($this->returnValue($this->validationResult));
        // When a result is already stashed for the address, no new api requests
        // should be sent. (If the session result isn't used, the test may actually
        // fail before the `send` method would be called. However, the important
        // assertion is that no new requests are made to the api.)
        $this->api->expects($this->never())
            ->method('send');

        $this->assertSame(
            $this->validationResult,
            $this->addressValidation->validate($this->address)
        );
    }
}
