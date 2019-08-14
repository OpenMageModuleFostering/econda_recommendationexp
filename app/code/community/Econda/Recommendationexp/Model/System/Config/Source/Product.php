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
 */

class Econda_Recommendationexp_Model_System_Config_Source_Product
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>'Magento Product ID'),
            array('value'=>'1', 'label'=>'SKU')
        );
    }
}
