<?php

namespace PF\TilesCalculator\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class  CheckoutCartProductAddUpdateAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $_request;
    protected $_flooringHelper;
    protected $_productRepository;
    protected $_scopeConfig;
    protected $_session;
    protected $_productConfigurationHelper;
    protected $_serializer;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \PF\TilesCalculator\Helper\Data $flooringHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $session,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Helper\Product\Configuration $configuration,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_flooringHelper = $flooringHelper;
        $this->_productRepository = $productRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->_session = $session;
        $this->_productConfigurationHelper = $configuration;
        $this->_serializer = $serializer;
    }
    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /* @var \Magento\Quote\Model\Quote\Item $item */
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if (!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote() || $quoteItem->getQuote()->getIsSuperMode()) return $this;

        $product = $observer->getEvent()->getProduct();

        $new_product = $quoteItem->getProduct();
        $typeId = $new_product->getTypeId();

        if ($typeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $simpleProductSelected = $product->getCustomOption('simple_product')->getProductId();
        }
        else{
            $simpleProductSelected = $quoteItem->getProduct()->getId();
        }

        $_product = $this->_productRepository->getById($simpleProductSelected);

        if ($_product->getEnableCalculator() == 1) {
            $unitPrice = $this->_scopeConfig->isSetFlag('pf_pricecalculator/general/unit_price');
            $productUnitPrice = $_product->getAttributeText('unit_price');
            if (!strpos(strtolower($productUnitPrice), "package") === false) {
                $unitPrice = false;
            }

            $qty = floatval($quoteItem->getQty());
            if ($qty == 0) {
                $qty = $quoteItem->getProduct()->getQty();
            }

            if ($qty < 1) {
                $qty = 1;
            }

            $coverage = floatval($_product->getCoverageArea());
            $unitprice =  $_product->getUnitPrice();
          
          	echo "<pre>";
			var_dump($unitprice);
          	echo "</pre>";
          	die;
          
            if(isset($unitprice))
            {
                if($unitprice=='1')
                {
                    $unit='Sqm/m²';
                } elseif ($unitprice=='2')
                {
                    $unit='Box/Package';
                } else
                {
                    $unit = $this->_scopeConfig->getValue('pf_pricecalculator/general/unit');
                }

            }
            else{
                $unit = $this->_scopeConfig->getValue('pf_pricecalculator/general/unit');
            }

            $samplePrice = floatval($this->_scopeConfig->getValue('pf_pricecalculator/general/sample_price'));

            $boxPrice = $product->getTierPrice($qty);
            $sqmPriceDisplay = $this->_flooringHelper->formatPrice($boxPrice / $coverage, false);

            $boxPriceDisplay = $this->_flooringHelper->formatPrice(55, false);
          
            if ($unitPrice) {
                $total = $qty * $coverage;

                $finalPrice = $product->getTierPrice($total);
                $sqmPriceDisplay = $this->_flooringHelper->formatPrice($finalPrice, false);
                $boxPrice = floatval($finalPrice) * $coverage;

                  if($product->getSpecialPrice()){
                    $boxPrice = floatval($product->getSpecialPrice()) * $coverage;
                }
                
                
                $boxPriceDisplay = $this->_flooringHelper->formatPrice($boxPrice, false);

                $quoteItem->setCustomPrice($boxPrice);
                $quoteItem->setOriginalCustomPrice($boxPrice);
            }

            // prepare post data
            $post = $this->_request->getParams();

            if (!isset($post['flooring_input']) || $post['flooring_input'] == '') {
                $post = $quoteItem->getProduct()->getCustomOption('info_buyRequest')->getValue();
            }

            if ($post) {
                if (!is_array($post)) {
                    $post = $this->_serializer->unserialize($post);
                }

                if (isset($post['options'])) $options = $post['options']; else $options = false;

                $additionalOptions = array();

                if ($additionalOption = $quoteItem->getOptionByCode('additional_options')){
                     $additionalOptions = (array) $this->_serializer->unserialize($additionalOption->getValue());
                }



                if(isset($post['free_sample']) && $post['free_sample'] == 'on'){
                     $quoteItem->setCustomPrice($samplePrice);
                     $quoteItem->setOriginalCustomPrice($samplePrice);
                     $freeSampleWeight = floatval($this->_scopeConfig->getValue('pf_pricecalculator/general/weight'));
                     $quoteItem->setWeight($freeSampleWeight);

                     if($samplePrice == 0){
                         $note =  __('Free sample');
                     }else {
                         $note =  __('Sample');
                     }

                     $additionalOptions[] =
                         array(
                             'label' => __('Note'),
                             'value' => $note,
                         );
                }elseif(isset($post['flooring_input'])){
#                     $additionalOptions[] =
#                         array(
#                             'label' => __('Your required %1', $unit),
#                             'value' => $post['flooring_input'],
#                         );

                     if(isset($post['wastage']) && $post['wastage'] == 'on'){
                         $additionalOptions[] =
                             array(
                                 'label' => __('Include wastage'),
                                 'value' => $this->_scopeConfig->getValue('pf_pricecalculator/general/wastage').'%',
                             );
                     }

#                     $additionalOptions[] = array(
#                         'label' => __('Box/Packet price'),
#                         'value' => $boxPriceDisplay,
#                     );

#                     $additionalOptions[] = array(
#                         'label' => __('%1 price', $unit),
#                         'value' => $sqmPriceDisplay,
#                     );
                }

                $quoteItem->addOption(
                    array(
                     'product_id' => $simpleProductSelected,
                     'code' => 'additional_options',
                     'value' => $this->_serializer->serialize($additionalOptions),
                    )
                );
            }
        }
    }
}
