<?php
/**
 * Module: CCCC\TranslationManager\UI\Component\Listing\Column
 * Copyright: (c) 2020 cccc.de
 * Date: 15.04.20 15:45
 *
 *
 */

namespace CCCC\TranslationManager\UI\Component\Listing\Column;

use Magento\Framework\App\AreaList;
use Magento\Framework\Data\OptionSourceInterface;

class AreaSelectOptions implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /** @var AreaList  */
    protected $areaList;

    public function __construct(AreaList $areaList)
    {
        $this->areaList = $areaList;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [];

        /** @var  $entries */
        $entries = $this->areaList->getCodes();
        sort($entries);

        foreach ($entries as $entry) {
            $data[$entry] = [
                'label' => __($entry),
                'value' => $entry
            ];
        }

        return $data;
    }
}