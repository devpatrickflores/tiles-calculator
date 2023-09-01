<?php
namespace PF\TilesCalculator\Plugin;

class Item
{
    protected $_scopeConfig;
    protected $_productRepository;
    protected $_request;
    protected $_serializer;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\SerializerInterface $serializer
) {
        $this->_scopeConfig = $scopeConfig;
        $this->_productRepository = $productRepository;
        $this->_request = $request;
        $this->_serializer = $serializer;
    }

    public function afterSetProduct(\Magento\Quote\Model\Quote\Item $item, $product)
    {
        if (!$item || !$item->getProductId() || !$item->getQuote() || $item->getQuote()->getIsSuperMode())
        {
            return $this;
        }

        $_product = $this->_productRepository->getById($item->getProductId());

        if($_product->getEnableCalculator()==1){
            // prepare post data
            $post = $this->_request->getParams();
            if(!isset($post['flooring_input']) || $post['flooring_input'] == ''){
                $post = $item->getProduct()->getCustomOption('info_buyRequest')->getValue();
            }

            if($post){
                if (!is_array($post)){
                    $post =  $this->_serializer->unserialize($post);
                }

                if(isset($post['free_sample']) && $post['free_sample'] == 'on'){
                    $freeSampleWeight = floatval($this->_scopeConfig->getValue('pf_pricecalculator/general/weight'));
                    $item->setWeight($freeSampleWeight);
                }
            }
        }
    }
}
