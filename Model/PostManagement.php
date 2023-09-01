<?php
namespace PF\TilesCalculator\Model;
use PF\TilesCalculator\Api\PostManagementInterface;
use \Magento\Catalog\Model\ProductRepository;

class PostManagement implements PostManagementInterface
{
    protected $_productRepository;

    public function __construct(
        ProductRepository $productRepository
    )
    {
        $this->_productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function pricecalculation($productid,$neededqty,$coverage)
    {
        try{
            $product = $this->_productRepository->getById($productid);
            $productprice = $product->getPrice();
            $specialprice = $product->getSpecialPrice();
            if($specialprice){
             $yourprice = ($specialprice/$coverage);
            }else{
              $yourprice = ($productprice/$coverage);
            }
           
           if($specialprice){
            $boxprice = $specialprice;
        }else{
            $boxprice = $productprice;
        }
            

            $totalboxes = ceil($neededqty/$coverage);
            $qty =  ceil($neededqty/$coverage);
            $actualproduct = $coverage *$totalboxes;
            $updatedproductprice = $totalboxes*$boxprice;
            $response = [
                'yourprice' => $yourprice,
                'actualproduct' =>$actualproduct,
                'boxprice'=>$boxprice,
                'totalboxes'=>$totalboxes,
                'totalprice'=>$updatedproductprice,
                'quantity'=>$qty
            ];
        }
        catch(\Exception $e) {
            $response=['error' => $e->getMessage()];
        }
        return json_encode($response);
    }

}
