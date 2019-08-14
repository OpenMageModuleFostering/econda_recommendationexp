<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Econda
 * @package     Econda_Recommendationexp
 * @copyright   Copyright (c) 2014 econda GmbH (http://www.econda.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Edgar Gaiser <info@econda.de>
 */

class Econda_Recommendationexp_Helper_Recommendation
{

    public function __construct() 
    {
    }

    public function getProducts($store)
    {
        $storeId = $store;
        $products = Mage::getModel('catalog/product')->getCollection();
        $products->addAttributeToSelect('*');
        $products->addStoreFilter($storeId);
        $products->addAttributeToFilter('status', 1);
        return $products;
    }

    public function getProductCsv($product, $store)
    {
        $separator = "|";
        $csv = "";
        $csv .= trim($this->getProductId($product, $store)).$separator;
        $csv .= trim($this->getProductName($product)).$separator;
        $csv .= trim($this->getProductDescription($product)).$separator;
        $csv .= trim($product->getProductUrl()).$separator;
        $csv .= trim($this->getProductImage($product)).$separator;
        $csv .= trim($this->getProductPrice($product)).$separator;
        $csv .= trim($this->getProductPriceOld($product)).$separator;
        $csv .= trim($this->getProductNew($product)).$separator;
        $csv .= trim($this->getProductQty($product)).$separator;  
        $csv .= trim($product->getSKU()).$separator;              
        $csv .= trim($this->getProductBrand($product)).$separator;
        $csv .= trim($this->getProductCategoriesCsv($product));
        return $csv;
    }        
    
    public function getCategoriesCsv($store)
    {
        $storeId = $store;
        $collection = Mage::getModel('catalog/category')->getCollection()->setStoreId($storeId);
        $catIds = $collection->getAllIds();
        $separator = "|";
        $cat = Mage::getModel('catalog/category');
        $csv = "";
        
        foreach ($catIds as $catId) {
            $category = $cat->load($catId);
            if($category->getLevel() != 0) {
                $catPath = explode('/',$category->getPath());
                $catParent = $catPath[sizeof($catPath)-2];          
                $csv .= $category->getId().$separator;            
                if($category->getLevel() == 1) {
                    $csv .= "".$separator;    
                }
                else {
                    $csv .= $catParent.$separator;    
                }
                $csv .= trim($category->getName())."\n";
            }
        }
        return $csv;
    }    

    public function getProductCategoriesCsv($product) 
    {
        $separator = "^^";
        $catIds = $product->getCategoryIds();
        $csv = "";
        foreach ($catIds as $catId) {
            $csv .= $separator.$catId; 
        }
        return substr($csv, 2);
    }

    public function getProductName($product)
    {
        $product_name = $product->getName();
        $product_name = str_replace("\n", "", strip_tags($product_name));
        $product_name = str_replace("\r", "", strip_tags($product_name));
        $product_name = str_replace("\t", " ", strip_tags($product_name));
        return $product_name;
    }
    
    public function getProductId($product, $store)
    {
        $idType = Mage::getStoreConfig('recommendationexp/recommendationexp_settings/recommendationexp_productid', $store);
        if($idType == '1') {
            $product_id = $product->getSku();
        }
        else {
            $product_id = $product->getId();
        }
        return $product_id;
    }

    public function getProductImage($product)
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "catalog/product" . $product->getImage();
    }

    public function getProductPrice($product) 
    {
        $taxHelper  = Mage::helper('tax');

        if ( $product->getSpecialPrice() && (date("Y-m-d G:i:s") > $product->getSpecialFromDate() || !$product->getSpecialFromDate()) &&  (date("Y-m-d G:i:s") < $product->getSpecialToDate() || !$product->getSpecialToDate())){
            $price = $product->getSpecialPrice();
        } 
        else {
            $price = $product->getPrice();
        }
        $priceTax = $taxHelper->getPrice($product, $price, true);
        return $priceTax;
    }
    
    public function getProductPriceOld($product) 
    {
        $taxHelper = Mage::helper('tax');
        $price = $product->getPrice();
        $priceTax = $taxHelper->getPrice($product, $price, true);
        return $priceTax;
    }   
    
    public function getProductNew($product) 
    {
        $now = date("Y-m-d");
        $newsFrom = substr($product->getData('news_from_date'),0,10);
        $newsTo = substr($product->getData('news_to_date'),0,10);
        if($now >= $newsFrom && $now <= $newsTo) { 
            return '1';           
        }
        return '0';
    }
    
    public function getProductBrand($product)
    {
        if ($product->getResource()->getAttribute('manufacturer')) {
            $manufacturer = $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
            if (strtolower($manufacturer) == "no") {
                $manufacturer = "";
                return $manufacturer;
            }
            else {
                return $manufacturer;
            }
        }
        else {
            return $manufacturer = "";
        }
    }    
    
    public function getProductDescription($product) 
    {
        $description = strip_tags($product->getData('short_description'));
        $description = preg_replace("/\r|\n/s", "", $description);
        return substr($description, 0, 100);
    }
    
    public function getProductQty($product) 
    {
        $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();    
        return (int)$qty;    
    }
    
    public function getStoreList() 
    {
        $separator = "|";
        $csv = "";
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $eachStoreId => $val)
        {
            $storeCode = Mage::app()->getStore($eachStoreId)->getCode();
            $storeName = Mage::app()->getStore($eachStoreId)->getName();
            $storeId = Mage::app()->getStore($eachStoreId)->getId();
            $storeActiv = Mage::app()->getStore($eachStoreId)->getIsActive();
            $storeUrl = Mage::app()->getStore($eachStoreId)->getHomeUrl();
            $csv .= $storeId.$separator;
            $csv .= $storeName.$separator;
            $csv .= $storeCode.$separator;
            $csv .= $storeActiv.$separator;
            $csv .= $storeUrl.$separator;
            $csv .= "\n";
        }   
        return $csv;     
    }
}
