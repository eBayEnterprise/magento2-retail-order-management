<?php

namespace EbayEnterprise\Address\Model\Validation;

use eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationReply;

class Result
{
    /** @var \EbayEnterprise\Address\Helper\Sdk */
    protected $sdkHelper;
    /** @var \eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationReply */
    protected $replyPayload;

    /**
     * @param \EbayEnterprise\Address\Helper\Sdk
     * @param \eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationReply
     */
    public function __construct(
        Address\Helper\Sdk $sdkHelper,
        IValidationReply $replyPayload,
        \Magento\Customer\Api\Data\AddressDataBuilderFactory $AddressDataBuilderFactory
    ) {
        $this->sdkHelper = $sdkHelper;
        $this->replyPayload = $replyPayload;
        $this->addressBuilderFactory = $addressBuilderFactory;
    }

    /**
     * Return if the address is acceptable based upon the reponse from the
     * address validation service.
     *
     * @return bool
     */
    public function isAcceptable()
    {
        return $this->replyPayload->isAcceptable();
    }

    /**
     * Return if the address was found to be valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->replyPayload->isValid();
    }

    /**
     * Get address objects for suggestions returned in the API reply.
     *
     * @return \Generator \eBayEnterprise\RetailOrderManagement\Payload\Address\ISuggestedAddress
     *                        => \Magento\Customer\Api\Data\AddressInterface
     */
    public function getSuggestions()
    {
        foreach ($this->replyPayload->getSuggestedAddresses() as $suggestedAddress) {
            yield $suggestedAddress => $this->sdkHelper->transferPhysicalAddressPayloadToAddress(
                $suggestedAddress,
                $this->addressDataBuilderFactory->create()
            );
        }
    }
}
