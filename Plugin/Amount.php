<?php
namespace PF\TilesCalculator\Plugin;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface; 

class Amount
{
    protected $_productRepository;
    protected $_scopeConfig;
    protected $saleableItem;
    protected $httpRequest;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductRepository $productRepository,
        RequestInterface $httpRequest // Use RequestInterface here
    ) {
        $this->_productRepository = $productRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->httpRequest = $httpRequest;
    }

    public function afterHasAdjustmentsHtml(\Magento\Framework\Pricing\Render\Amount $subject, $result)
    {
        $zone = $subject->getZone();//item_list
        $product = $subject->getSaleableItem();
        $productFull = $this->_productRepository->getById($product->getId());
        $isFlooring = $productFull->getEnableCalculator();

        $isShow = $this->_scopeConfig->isSetFlag('pf_pricecalculator/general/on_category');

        if($isFlooring && $isShow && $zone == "item_list"){
            return true;
        }
    }

    public function afterGetAdjustmentsHtml($subject, $result)
    {
        $product = $subject->getSaleableItem();
        $productFull = $this->_productRepository->getById($product->getId());
        $isFlooring = $productFull->getEnableCalculator();
        $unitprice =  $productFull->getUnitPrice();
        $isShow = $this->_scopeConfig->isSetFlag('pf_pricecalculator/general/on_category');
        $unit = $this->_scopeConfig->getValue('pf_pricecalculator/general/unit');
        if($isFlooring && $isShow){
            if(isset($unitprice))
            {
                if($unitprice=='1')
                {
                    return $result.__('Sqm/m²');
                } elseif ($unitprice=='2')
                {
                    return $result.__('Box/Package');
                } else
                {
                    return $result.__('/%1', $unit);
                }

            }
            return $result.__('/%1', $unit);
        }
    }

    public function afterGetDisplayValue($subject, $result)
    {
        $product = $subject->getSaleableItem();
        $productFull = $this->_productRepository->getById($product->getId());
        $isFlooring = $productFull->getEnableCalculator();
        $productprice = $productFull->getPrice();
        $pageName = $this->httpRequest->getFullActionName(); // Use $this->httpRequest

        $isFlooringCoverage =  $productFull->getCoverageArea();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $taxHelper = $objectManager->get('Magento\Catalog\Helper\Data');
        $price = $taxHelper->getTaxPrice($product, $product->getFinalPrice(), true);
        if ($isFlooringCoverage > 0){
            //$final = $productprice / $isFlooringCoverage;
            $final = $price ;
        }

        if($pageName == "catalog_category_view"){
            if($isFlooring){
                return $final;
            }
            else{
                return $subject->getAmount()->getValue();
            }
        }
        else{
            return $subject->getAmount()->getValue();
        }
    }
}
