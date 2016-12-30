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
namespace MindArc\Inventory\Model\ResourceModel\Transaction;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * ID Field Name
     * 
     * @var string
     */
    protected $_idFieldName = 'transaction_id';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'mindarc_inventory_transaction_collection';

    /**
     * Event object
     * 
     * @var string
     */
    protected $_eventObject = 'transaction_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MindArc\Inventory\Model\Transaction', 'MindArc\Inventory\Model\ResourceModel\Transaction');
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }
    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray($valueField = 'transaction_id', $labelField = 'item_id', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    public function addAttributeToSelect($code, $alias = false)
    {
        $alias = $alias ? $alias : $code;

        /* @var $attribute Mage_Catalog_Model_Entity_Attribute */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $attribute = $objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->loadByCode('catalog_product',$code);
        $fkTable   = '_table_'.$attribute->getAttributeCode();

        // Magento 2 default is row_id, but Whitefox is entity_id test
        $this->joinProducts()->getSelect()->joinLeft(
            array($fkTable => $attribute->getBackendTable()),
            $fkTable.'.entity_id = `cataloginventory_stock_item`.product_id AND '.
            $fkTable.'.attribute_id = '.$attribute->getAttributeId().' AND '.
            $fkTable.'.store_id = 0',
            array($alias => 'value')
        );

        return $this;
    }

    /**
     * Joins stock items to collection
     *
     * @return Metrik_Inventory_Model_Mysql4_Stock_Transaction_Collection
     */
    public function joinStockItems()
    {
        $stockItemTable = array('cataloginventory_stock_item' => $this->getTable('cataloginventory_stock_item'));

        $fromPart = $this->getSelect()->getPart('from');
        if (! isset($fromPart['cataloginventory_stock_item'])) {
            $this->getSelect()->joinLeft(
                $stockItemTable,
                '`cataloginventory_stock_item`.item_id = main_table.item_id'
            );
        }
        return $this;
    }

    /**
     * Joins catalog products to collection
     *
     * @return Metrik_Inventory_Model_Mysql4_Stock_Transaction_Collection
     */
    public function joinProducts()
    {
        $productTable   = array('catalog_product_entity'  => $this->getTable('catalog_product_entity'));

        $fromPart = $this->getSelect()->getPart('from');
        if (! isset($fromPart['catalog_product_entity'])) {
            $this->joinStockItems()->getSelect()->joinLeft(
                $productTable,
                '`catalog_product_entity`.entity_id = `cataloginventory_stock_item`.product_id',
                array('sku')
            );
        }
        return $this;
    }

    /**
     * Add website filter to collection
     *
     * @param Mage_Core_Model_Website|int|string|array $websites
     * @return Metrik_Inventory_Model_Mysql4_Stock_Transaction_Collection
     */
    public function addWebsiteFilter($websites = null)
    {
        if (!is_array($websites)) {
            $websites = array($websites->getId());
        }

        $this->_productLimitationFilters['website_ids'] = $websites;
        $this->_productLimitationJoinWebsite();

        return $this;
    }

   /**
     * Join website product limitation
     *
     * @return Metrik_Inventory_Model_Mysql4_Stock_Transaction_Collection
     */
    protected function _productLimitationJoinWebsite()
    {
        $joinWebsite = false;
        $filters     = $this->_productLimitationFilters;
        $conditions  = array(
            'product_website.product_id=`cataloginventory_stock_item`.product_id'
        );
        if (isset($filters['website_ids'])) {
            $joinWebsite = true;
            if (count($filters['website_ids']) > 1) {
                $this->getSelect()->distinct(true);
            }
            $conditions[] = $this->getConnection()
                ->quoteInto('product_website.website_id IN(?)', $filters['website_ids']);
        }

        $fromPart = $this->getSelect()->getPart('from');
        if (isset($fromPart['product_website'])) {
            if (!$joinWebsite) {
                unset($fromPart['product_website']);
            }
            else {
                $fromPart['product_website']['joinCondition'] = join(' AND ', $conditions);
            }
            $this->getSelect()->setPart('from', $fromPart);
        }
        elseif ($joinWebsite) {
            $this->joinProducts()->getSelect()->join(
                array('product_website' => $this->getTable('catalog_product_website')),
                join(' AND ', $conditions),
                array()
            );
        }

        return $this;
    }
}
