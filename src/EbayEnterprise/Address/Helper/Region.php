<?php

namespace EbayEnterprise\Address\Helper;

use Magento\Directory\Model\RegionFactory;

class Region
{
    /** @var RegionFactory */
    protected $regionFactory;

    public function __construct(
        RegionFactory $regionFactory
    ) {
        $this->regionFactory = $regionFactory;
    }

    /**
     * Get a region model with at least a region id, region code and region name.
     *
     * @param int|null
     * @param string|null
     * @param string|null
     * @param string|null
     * @return \Magento\Directory\Model\Region|null
     */
    public function loadRegion($regionId = null, $regionCode = null, $regionName = null, $countryId = null)
    {
        // If all of the necessary data is already available, create and return
        // a new model with the provided data.
        if ($regionId && $regionCode && $regionName) {
            return $this->regionFactory->create(
                ['data' => ['id' => $regionId, 'code' => $regionCode, 'name' => $regionName, 'country_id' => $countryId]]
            );
        }
        // Cannot load a region if there is not enough data to use to find it.
        // Need either a region id or a region code and country id or
        // region name and country id.
        if (!$this->canRegionBeLoaded($regionId, $regionCode, $regionName, $countryId)) {
            return null;
        }

        $region = $this->regionFactory->create();
        // Load the region by whatever data is available. Assume these to be
        // in order of most to least performant but
        if ($regionId) {
            $region->load($regionId);
        } elseif ($regionCode) {
            $region->loadByCode($regionCode, $countryId);
        } elseif ($regionName) {
            $region->loadByName($regionName, $countryId);
        }
        return $region;
    }

    /**
     * Check if there is enough region data to load a directory region model.
     * Must have a region id, or region code and country id, or region name
     * and country id.
     *
     * @param int|null
     * @param string|null
     * @param string|null
     * @param string|null
     * @return bool
     */
    protected function canRegionBeLoaded($regionId = null, $regionCode = null, $regionName = null, $countryId = null)
    {
        return $regionId || ($countryId && ($regionCode || $regionName));
    }
}
