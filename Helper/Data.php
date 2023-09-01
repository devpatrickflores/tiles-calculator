<?php
namespace PF\TilesCalculator\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ProductRepository;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PRICECALCULATOR_GENERAL_ONCATEGORYSQMPRICE = 'pf_pricecalculator/general/on_category_sqm_price';

    protected $storeManager;
    protected $taxHelper;
    protected $catalogHelper;
    protected $priceCurrency;
    protected $_productRepository;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Helper\Data $catalogHelper,
        ProductRepository $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->taxHelper = $taxHelper;
        $this->catalogHelper = $catalogHelper;
        $this->priceCurrency = $priceCurrency;
        $this->_productRepository = $productRepository;
        parent::__construct($context);
    }

    public function formatPrice($price, $includeContainer = false)
    {
        return $this->priceCurrency->format($price, $includeContainer);
    }

    public function getStoreid()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function displaySqmPrice()
    {
        return $this->scopeConfig->getValue(
            self::PRICECALCULATOR_GENERAL_ONCATEGORYSQMPRICE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid()
        );
    }
}
