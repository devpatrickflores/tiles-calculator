<?php
namespace PF\TilesCalculator\Api;
interface PostManagementInterface {


    /**
     * GET for Post api
     * @param int $productid
     * @param string $productprice
     * @param string $neededqty
     * @param string $coverage
     * @return string
     */

    public function pricecalculation($productid,$neededqty,$coverage);
}
