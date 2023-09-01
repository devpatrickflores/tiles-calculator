<?php
namespace PF\TilesCalculator\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CheckProductCart implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        $product = $item->getProduct();
        $qty = floatval($item->getQty());
        $coverage_area = null;
        $product = $item->getProduct();

        if ($product) {
            $customAttribute = $product->getCustomAttribute('coverage_area');

            if ($customAttribute) {
                $coverage_area = $customAttribute->getValue();
            }
        }

        if ($coverage_area !== null) {
            $boxPrice = $product->getFinalPrice();
            $newPrice = $boxPrice * $coverage_area;
          	$item->setCustomPrice($newPrice);
            $item->setOriginalCustomPrice($newPrice);
            $item->getProduct()->setIsSuperMode(true);
        }
    }
}

