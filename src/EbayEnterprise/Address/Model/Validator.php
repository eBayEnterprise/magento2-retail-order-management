<?php

namespace EbayEnterprise\Address\Model;

use Magento\Customer\Model\Address\AbstractAddress;

class Validator
{
    public function afterValidate(AbstractAddress $address, $result)
    {
        if ($result === true) {

        }
        return $result;
    }
}
