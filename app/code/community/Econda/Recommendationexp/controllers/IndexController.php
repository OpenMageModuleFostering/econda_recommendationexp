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
 * @package     Econda_Recommendationexp
 * @copyright   Copyright (c) 2014 econda GmbH (http://www.econda.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Edgar Gaiser <info@econda.de>
 */

class Econda_Recommendationexp_IndexController extends Mage_Core_Controller_Front_Action 
{
    public function preDispatch()
    {
        parent::preDispatch();
    }

    public function indexAction() 
    {
        if(!isset($_GET['store']) && !isset($_GET['type'])) {
            die; 
        }
        
        $storeId = $_GET['store'];
        $eEnabled = Mage::getStoreConfig('recommendationexp/recommendationexp_settings/recommendationexp_export', $storeId);
        $remote = Mage::getStoreConfig('recommendationexp/recommendationexp_settings/recommendationexp_remote', $storeId); 
        
        if(trim($remote) != "") {
            $remoteAdress = $_SERVER['REMOTE_ADDR'] == $remote ? true : false;    
        }
        else {
            $remoteAdress = true;    
        }
        if($eEnabled != '1' || !$remoteAdress) {
            die; 
        }
        if($_GET['type'] != '1' && $_GET['type'] != '2' && $_GET['type'] != '0') {
            die;  
        }
        
        $actStore = null;
        $stores = Mage::app()->getStores();
        foreach ($stores as $store => $val)
        {
            $storeIdShop = Mage::app()->getStore($store)->getId();
            if($storeIdShop == $storeId) {
                $actstore = $storeId; 
            }
        } 
        if($actstore == null) {
            die;    
        }
        $cross = Mage::helper('recommendationexp/recommendation');
        $csv = "";

        // Products
        if($_GET['type'] == '1') { 
            $filename = "products.csv"; 
            $csv .= "ID|Name|Description|ProductURL|ImageURL|Price|OldPrice|New|Stock|SKU|Brand|ProductCategory\n";        
            $products = $cross->getProducts($actstore);
            foreach ($products as $product) {
                $csv .= $cross->getProductCsv($product, $actstore)."\n";
            }
        }
        
        // Categories
        else if($_GET['type'] == '2') {
            $filename = "categories.csv"; 
            $csv .= "ID|ParentID|Name\n";
            $csv .= $cross->getCategoriesCsv($actstore);
        }
        
        // Get Store list
        else if($_GET['type'] == '0') {
            $filename = "stores.csv";
            $csv .= "ID|Name|Code|isActive|homeUrl\n";
            $csv .= $cross->getStoreList();
        }
        else {
             die;   
        }
      
        $csv = trim($csv);
        
        header("Content-Type: text/csv"); 
        header("Content-Disposition: attachment; filename=$filename"); 
        header("Content-Description: csv File"); 
        header("Pragma: no-cache"); 
        header("Expires: 0");
        echo $csv; 
    }
}
