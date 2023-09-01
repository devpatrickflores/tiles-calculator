<?php
namespace PF\TilesCalculator\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class CustomAttributeOptions extends AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options=[
                ['label' => __('Use default setting'), 'value' => 0],
                ['label' => __('sqm2'), 'value' => "/sqm2"],
                ['label' => __('pc'), 'value' => "/pc"],
                ['label' => __('sheet'), 'value' => "/sheet"]
            ];
        }
        return $this->_options;
    }
}
