<?php
namespace PF\TilesCalculator\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class CalculatorGroupOption extends AbstractSource
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
                ['label' => __('/sqm2'), 'value' => 1],
                ['label' => __('/pc'), 'value' => 2],
                ['label' => __('/sheet'), 'value' => 3]
            ];
        }
        return $this->_options;
    }
}
