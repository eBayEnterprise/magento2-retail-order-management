<?php

namespace EbayEnterprise\Address\Model;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\ValidationResultInterface;
use EbayEnterprise\Address\Helper\Data as AddressHelper;

class AddressResultPair
{
    /** @var AddressInterface */
    protected $address;
    /** @var ValidationResultInterface */
    protected $result;

    /**
     * @param AddressInterface
     * @param ValidationREsultInterface
     */
    public function __construct(
        AddressHelper $addressHelper,
        AddressInterface $address = null,
        ValidationResultInterface $result = null
    ) {
        $this->addressHelper = $addressHelper;
        $this->address = $address;
        $this->result = $result;
    }

    /**
     * Compare an address' data to the address' data in this pair.
     *
     * @param AddressInterface
     * @return bool
     */
    public function compareAddress(AddressInterface $address)
    {
        // Loose comparison will enforce same type and same property values.
        // @TODO Should not enforce same type so the loose comparison will need
        //       to be replaced by checks of just the object data.
        return $this->addressHelper->compareAddresses($this->address, $address);
    }

    /**
     * Get the address in the pair.
     *
     * @return AddressInterface
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param AddressInterface $address
     * @return self
     */
    public function setAddress(AddressInterface $address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Get the validation result in the pair.
     *
     * @return ValidationResultInterface
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param ValidationResultInterface
     * @return self
     */
    public function setResult(ValidationResultInterface $result)
    {
        $this->result = $result;
        return $this;
    }
}
