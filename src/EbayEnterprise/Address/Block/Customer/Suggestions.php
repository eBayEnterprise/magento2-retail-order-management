<?php

namespace EbayEnterprise\Address\Block\Customer;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Helper\Data as AddressHelper;
use EbayEnterprise\Address\Model\Session as AddressSession;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\View\Element\Template as TemplateBlock;
use Psr\Log\LoggerInterface;

class Suggestions extends TemplateBlock
{
    /** @var AddressSession */
    protected $addressSession;
    /** @var ValidationResultInterface */
    protected $result;

    /**
     * @param TemplateContext
     * @param AddressSession
     * @param array
     */
    public function __construct(
        TemplateContext $context,
        AddressSession $addressSession,
        AddressHelper $addressHelper,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->addressSession = $addressSession;
        $this->addressHelper = $addressHelper;
        $this->result = $this->addressSession->getCurrentResult(true);
        $this->logger = $logger;
    }

    /**
     * Get the form post url for handling suggestions.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'addressvalidation/address/suggestionFormPost',
            ['_secure' => true, 'id' => $this->getRequest()->getParam('id')]
        );
    }

    public function isCorrectionRequired()
    {
        return $this->result && !$this->result->isAcceptable();
    }

    public function getAddressHtml(AddressInterface $address)
    {
        return sprintf(
            '%s<br/>%s %s %s %s',
            implode('<br/>', $address->getStreet()),
            $address->getCity(),
            $address->getRegionCode(),
            $address->getCountryId(),
            $address->getPostcode()
        );
    }

    public function hasCorrectedAddress()
    {
        $correctedAddress = $this->getCorrectedAddress();
        return $correctedAddress && !$this->addressHelper->compareAddresses($correctedAddress, $this->getOriginalAddress());
    }

    public function getCorrectedAddress()
    {
        return $this->result ? $this->result->getCorrectedAddress() : null;
    }

    public function hasOriginalAddress()
    {
        return $this->result && $this->result->getOriginalAddress();
    }

    public function getOriginalAddress()
    {
        return $this->result ? $this->result->getOriginalAddress() : null;
    }

    public function getSuggestions()
    {
        return $this->result ? $this->result->getSuggestions() : [];
    }

    public function getValidationId()
    {
        return $this->result ? $this->result->getId() : null;
    }
}
