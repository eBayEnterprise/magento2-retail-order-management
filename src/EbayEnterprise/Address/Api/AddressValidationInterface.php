<?php

namespace EbayEnterprise\Address\Api;

use EbayEnterprise\Address\Api\Data\AddressInterface;

interface AddressValidationInterface
{
    /**
     * Validate the address. Returned result will contiain the results of the
     * address validation request, the original address, a corrected/normalized
     * address if available, and any suggestions to correct the address if
     * available.
     *
     * @param \EbayEnterprise\Address\Api\Data\AddressInterface $address
     * @return \EbayEnterprise\Address\Api\Data\ResultInterface
     */
    public function validate(AddressInterface $address);
}
