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
namespace MindArc\Inventory\Block\Adminhtml\Transaction\Grid;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
  /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_collectionFactory;

    protected $_objectManager;

    protected $_registry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MindArc\Inventory\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        /* @var $collection Metrik_Inventory_Model_Mysql4_Stock_Transaction_Collection */
        $collection = $this->_collectionFactory->create();
        $collection
            ->addExpressionFieldToSelect('adjustment_positive','(IF(adjustment > 0,abs({{adjustment}}),0))','adjustment')
            ->addExpressionFieldToSelect('adjustment_negative','(IF(adjustment < 0,abs({{adjustment}}),0))','adjustment');

        // Get the product name attribute to join
        $collection->addAttributeToSelect('name','product_name');

        if ( $item = $this->_registry->registry('current_item') ) {
            $collection->addFieldToFilter('main_table.item_id',$item);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn( 'id', array(
            'filter_index' => 'main_table.transaction_id',
            'header'   => __('Txn ID'),
            'index'    => 'transaction_id',
            'width'    => '70px'
        ));

        $this->addColumn( 'created_at', array(
            'filter_index' => 'main_table.created_at',
            'header'   => __('Timestamp'),
            'index'    => 'created_at',
            'type'     => 'datetime',
            'width'    => '180px'
        ));

        $this->addColumn( 'product_name', array(
            'header'   => __('Product Name'),
            'index'    => 'product_name',
            'filter_index' => '_table_name.value'
        ));

        $this->addColumn( 'product_sku', array(
            'header'   => __('Sku'),
            'index'    => 'sku',
            'width'    => '120px'
        ));

        $this->addColumn( 'parent_type', array(
            'header'   => __('Type'),
            // 'getter'   => array($this,'getParentType'),
            'index'    => 'parent_type',
            'filter'   => 'MindArc\Inventory\Block\Adminhtml\Transaction\Grid\Filter\Types',
            'renderer'  => 'MindArc\Inventory\Block\Adminhtml\Transaction\Grid\Renderer\Types',
            'width'    => '100px'
        ));

        $this->addColumn( 'adjustment_positive', array(
            'filter_index' => 'adjustment',
            'header'   => __('Adjustment (+)'),
            'index'    => 'adjustment_positive',
            'type'     => 'number',
            'width'    => '120px'
        ));

        $this->addColumn( 'adjustment_negative', array(
            'filter_index' => 'adjustment',
            'header'   => __('Adjustment (-)'),
            'index'    => 'adjustment_negative',
            'type'     => 'number',
            'width'    => '120px'
        ));

        $this->addColumn( 'balance', array(
            'header'   => __('Balance'),
            'index'    => 'balance',
            'type'     => 'number',
            'width'    => '120px'
        ));

        $this->addColumn('action', array(
            'header'  => 'Action',
            'filter'  => false,
            'renderer'=> 'MindArc\Inventory\Block\Adminhtml\Transaction\Grid\Renderer\Actions',
            'width'   => '120px',
        ));
    }

    protected function _setCollectionOrder($column)
    {
        switch ( $column->getId() ) {
            // Reverse sort adjustment_negative because we are taking the absolute value
            case 'adjustment_negative':
                $collection = $this->getCollection();
                if ($collection) {
                    $direction = $column->getDir();

                    // Flip the direction
                    $direction = (strtoupper($direction) == Varien_Data_Collection_Db::SORT_ORDER_ASC) ?
                        Varien_Data_Collection_Db::SORT_ORDER_DESC : Varien_Data_Collection_Db::SORT_ORDER_ASC;

                    $columnIndex = $column->getFilterIndex() ?
                        $column->getFilterIndex() : $column->getIndex();
                    $collection->setOrder($columnIndex, $direction);
                }
                return $this;
                break;

            // Default behavior
            default:
                return parent::_setCollectionOrder($column);
                break;
        }
    }

    protected function _addColumnFilterToCollection($column)
    {

        $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
        $cond  = $column->getFilter()->getCondition();

        // Make sure adjustment_positive is not including negative
        if ( $column->getId() == 'adjustment_positive' ) {
            $cond['from'] = isset($cond['from']) ? $cond['from'] : 0;

            $this->getCollection()->addFieldToFilter($field,$cond);

        // Swap 'from' and 'to' for adjustment_negative, make sure to not include positives
        } else if ( $column->getId() == 'adjustment_negative' ) {
            if ( isset($cond['from']) && isset($cond['to']) ) {
                $from  = 0 - $cond['from'];
                $to    = 0 - $cond['to'];
                $cond['from'] = min($from, $to);
                $cond['to']   = max($from, $to);
            } else if ( isset($cond['from']) ) {
                $from         = 0 - $cond['from'];
                $cond['to']   = $from;
                unset($cond['from']);
            } else {
                $to           = 0 - $cond['to'];
                $cond['from'] = $to;
                $cond['to']   = 0;
            }

            $this->getCollection()->addFieldToFilter($field,$cond);

        // Filtering parent types is tricky
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('mindarc_inventory/item/grid',array('_current'=>true));
    }

    public function getAbsoluteGridUrl($params = array())
    {
        return $this->getUrl('mindarc_inventory/item/grid',$params);
    }
}
