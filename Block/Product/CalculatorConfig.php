<?php

namespace PF\TilesCalculator\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
class CalculatorConfig extends Template
{
    protected $_jsonEncoder;
    protected $_priceCurrency;
    protected $_coreRegistry;
    protected $_product;
    protected $_storeManager;
    protected $storeManager;
    protected $_flooringHelper;
    protected $scopeConfig;
    protected $storeid;
    protected $_productloader;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\DataObject $labelsData,
     * @param \Magento\Framework\Registry $registry,
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \PF\TilesCalculator\Helper\Data $flooringHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Helper\Data $taxHelper,
        array $data = array()
    ) {

        $this->_coreRegistry = $registry;
        $this->_storeManager = $context->getStoreManager();
        $this->_product = $this->_coreRegistry->registry('product');
        $this->_jsonEncoder = $jsonEncoder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_priceCurrency = $priceCurrency;
        $this->_flooringHelper = $flooringHelper;
        $this->_productloader = $_productloader;
        $this->taxHelper = $taxHelper;
        parent::__construct($context, $data);
    }



    public function formatPrice($price, $includeContainer = false)
    {
        return $this->_priceCurrency->format($price, $includeContainer);
    }

    public function getConfig($path, $bool = false , $storeid=null)
    {
        if($bool){
            return $this->_scopeConfig->isSetFlag($path);
        }

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeid);
    }
    public function getProduct()
    {
        return $this->_product;
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function isUnitPrice($storeid = null)
    {
        return $this->scopeConfig->getValue('pf_pricecalculator/general/unit_price', ScopeInterface::SCOPE_STORE, $storeid);
    }
     public function getCoverage()
     {
        return $this->_product->getCoverageArea();
     }

    public function isFlooring()
    {
        return $this->getProduct()->getEnableCalculator();
    }
    public function isEnabled($storeid = null)
    {
        return $this->_scopeConfig->isSetFlag('pf_pricecalculator/general/active', ScopeInterface::SCOPE_STORE, $storeid);
    }

    public function isSampleEnabled($storeid = null)
    {
        return $this->scopeConfig->getValue('pf_pricecalculator/general/free_sample', ScopeInterface::SCOPE_STORE, $storeid);
    }

    public function isEnabledNewTierPrice($storeid = null)
    {
        return $this->scopeConfig->getValue('pf_pricecalculator/general/new_tier', ScopeInterface::SCOPE_STORE, $storeid);
    }
    public function getTierPrices()
    {
        return $this->getProduct()->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
    }
 
    public function getFlooringConfig()
    {
        $product = $this->_product;
        if($this->isFlooring()){
            $config = array();
            $wastage = floatval($this->scopeConfig->getValue('pf_pricecalculator/general/wastage'));

            foreach ($product->getOptions() as $option) {
                if ($option->getType() == 'field') {
                    $config['sqinput'] = $option->getId();
                }elseif($option->getType() == 'radio'){
                    $config['options'] = array();
                    $count = 1;
                    foreach ($option->getValues() as $_value) {
                        $count++;
                        $config['options'][] = array('id'=>"options_".$option->getId()."_".$count,'price'=>$_value->getPrice(), 'type'=>$option->getType());
                    }
                }
            }

            $taxPercent = floatval($product->getTaxPercent());
            $currencyRate = floatval($this->_storeManager->getStore()->getCurrentCurrencyRate());

            $includeTax = $this->scopeConfig->getValue('tax/calculation/price_includes_tax');

            $taxDisplayType = $this->scopeConfig->getValue('tax/display/type');

            $unitPrice = $this->scopeConfig->isSetFlag('pf_pricecalculator/general/unit_price');
            $productUnitPrice = $product->getAttributeText('unit_price');
            if(!strpos(strtolower($productUnitPrice), "package")===false){
                $unitPrice = false;
            }

            
           
            $price = $this->taxHelper->getTaxPrice($product, $product->getFinalPrice(), true);
            $specialprice = $this->taxHelper->getTaxPrice($product, $product->getPrice(), true);

            $config['coverage'] = $product->getCoverageArea();
            $config['unit_price'] = $unitPrice;
            $config['id'] = $product->getId();
            $config['isflooring'] = $product->getEnableCalculator();
            //$config['price'] = $product->getFinalPrice();
            if($taxDisplayType == 3 ||  $taxDisplayType == 2){
                $config['price'] =  $price;
                $config['specialprice'] =  $specialprice;
            }
            else{
                $config['price'] = $product->getFinalPrice();
                $config['specialprice'] = $product->getPrice();
            }

           // $config['price'] = ( $taxDisplayType == 3 ||  $taxDisplayType == 2) ? $price : $product->getFinalPrice();
            $config['tax_percent']  = $taxPercent;
            $config['currency_rate'] = $currencyRate;
            $config['include_tax']  = $includeTax;
            $config['tax_display_type'] = $taxDisplayType;
            $config['wastage']  = $wastage;
            $config['sample_price']  = floatval($this->scopeConfig->getValue('pf_pricecalculator/general/sample_price'));;
            $config['new_tier'] = $this->isEnabledNewTierPrice();
            $config['prices'] = $this->getTierPrices();

            return $this->_jsonEncoder->encode($config);
        }

        return false;
    }

}
