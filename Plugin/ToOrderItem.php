<?php

namespace PF\TilesCalculator\Plugin;


class ToOrderItem
{
    protected $_serializer;
    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->_serializer = $serializer;
    }
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = array()
    ) {
        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
        $productOptions = $orderItem->getProductOptions();
        if(isset($item->getProduct()->getCustomOptions()['additional_options'])) {
            $additionalOptions = $this->_serializer->unserialize($item->getProduct()->getCustomOptions()['additional_options']->getValue());
            if (!isset($productOptions['options'])) {
                $productOptions['options'] = array();
            }

            $newOptions = array_merge($additionalOptions, $productOptions['options']);
            $productOptions['options'] = $newOptions;
            $orderItem->setProductOptions($productOptions);
        }

        return $orderItem;
    }
}
