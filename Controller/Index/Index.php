<?php
namespace PF\TilesCalculator\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Index extends Action
{
    protected $pageFactory;
    protected $resultJsonFactory;
    protected $_productloader;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        JsonFactory $resultJsonFactory,
        \PF\TilesCalculator\Block\Product\CalculatorConfig $blockname,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_resultPageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->blockname = $blockname;
        $this->_productloader = $_productloader;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $product_id = $this->getRequest()->getParam('pfcalculator');
        $product = $this->_productloader->create()->load($product_id);
        $product_price = $product->getPrice();
        $product_special_price = $product->getSpecialPrice();
        if($product_special_price){
            if($product_special_price < $product_price)
            {
                $product_final_price = $product_special_price;
            }
            else{
                $product_final_price = $product_price;
            }
        }
        else{
            $product_final_price = $product_price;
        }

        $product_covrage_price = $product->getCoverageArea();
        $product_tax_per = $product->getTaxPercent();
        $unitPrice = $this->_scopeConfig->getValue('pf_pricecalculator/general/unit_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $result = $this->resultJsonFactory->create();
        $result->setData(['product_price' => $product_final_price,'product_covrage_price' => $product_covrage_price,'product_tax_per'=>$product_tax_per,'unit_price'=>$unitPrice]);
        return $result;
    }
}

