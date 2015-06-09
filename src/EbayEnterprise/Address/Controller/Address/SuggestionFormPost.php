<?php

namespace EbayEnterprise\Address\Controller\Address;

use EbayEnterprise\Address\Model\Session as AddressSession;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Controller\Address\FormPost as CustomerAddressFormPost;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SuggestionFormPost extends CustomerAddressFormPost
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param AddressSession
     * @param ValidationResultInterfaceFactory
     * @param AddressConverter
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        AddressSession $addressSession
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $formKeyValidator,
            $formFactory,
            $addressRepository,
            $addressDataFactory,
            $regionDataFactory,
            $dataProcessor,
            $dataObjectHelper,
            $resultForwardFactory,
            $resultPageFactory
        );
        $this->addressSession = $addressSession;
    }

    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('customer/address/');
        }

        if (!$this->getRequest()->isPost()) {
            $this->_getSession()->setAddressFormData($this->getRequest()->getPostValue());
            return $this->resultRedirectFactory->create()->setUrl(
                $this->_redirect->error($this->_buildUrl('customer/address/edit'))
            );
        }

        try {
            $address = $this->_updateAddressWithSuggestion($this->_extractAddress());
            $this->_addressRepository->save($address);
            $this->messageManager->addSuccess(__('The address has been saved.'));
            $url = $this->_buildUrl('customer/address/index', ['_secure' => true]);
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->success($url));
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($error->getMessage());
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot save address.'));
        }

        $this->_getSession()->setAddressFormData($this->getRequest()->getPostValue());
        $url = $this->_buildUrl('customer/address/edit', ['id' => $this->getRequest()->getParam('id')]);
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
    }

    /**
     * Get the validation suggestion option chosen, if there is one, and update
     * the address to be saved with the validation address data.
     *
     * @TODO Clean this up.
     *
     * @param CustomerAddressInterface
     * @return CustomerAddressInterface
     */
    protected function _updateAddressWithSuggestion(CustomerAddressInterface $address)
    {
        $selectedSuggestion = $this->getRequest()->getParam('suggestion');
        $resultId = $this->getRequest()->getParam('validation-id');
        if ($selectedSuggestion && $selectedSuggestion !== 'new-address' && $resultId) {
            $result = $this->addressSession->getResultById($resultId);
            $originalAddress = $this->addressSession->getOriginalCustomerAddress();
            if ($result && $originalAddress) {
                switch ($selectedSuggestion) {
                    case 'normalized':
                        $selectedAddress = $result->getCorrectedAddress();
                        break;
                    case 'original':
                        $selectedAddress = $result->getOriginalAddress();
                        break;
                    default:
                        $selectedAddress = $result->getSuggestions()[$selectedSuggestion];
                        break;
                }
                $address = $this->addressSession->confirmSelection(
                    $originalAddress,
                    $selectedAddress
                );
            }
        }
        return $address;
    }
}
