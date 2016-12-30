<?php
/**
 * MindArc_Inventory extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  MindArc
 *                     @package   MindArc_Inventory
 *                     @copyright Copyright (c) 2016
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace MindArc\Inventory\Block\Adminhtml\Receipt\Edit;

class Search extends \Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser
{
    protected function _prepareColumns()
    {

        parent::_prepareColumns();

        unset($this->_columns['entity_id']);

        $this->addColumn('qty_received', array(
            'filter'     => false,
            'header'     => __('Qty Received'),
            'name'       => 'qty_received',
            'type'       => 'input',
            'width'      => '80px'
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('mindarc_inventory/receipt/chooser', array(
            'products_grid' => true,
            '_current' => true,
            'uniq_id' => $this->getId(),
            'use_massaction' => $this->getUseMassaction(),
            'product_type_id' => $this->getProductTypeId()
        ));
    }
}
