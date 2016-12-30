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
namespace MindArc\Inventory\Block\Adminhtml\Item\Grid;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * Catalog product model factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Stock item factory
     *
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory
     */
    protected $_stockItemFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_objectManager = $objectManager;
        $this->_authSession = $authSession;
        $this->_productFactory = $productFactory;
        $this->_stockItemFactory = $stockItemFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('stockItemGrid');
        $this->setDefaultSort('item_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        // $this->setUseAjax(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $stockFields = array(
            'qty_on_sale' => 'qty',
            'item_id'     => 'item_id'
        );
        $collection = $this->_collectionFactory->create();
        $collection->addAttributeToSelect('name')->addAttributeToSelect('short_description')
        ->getSelect()->joinLeft(
           ['cataloginventory_stock_item'=>$collection->getTable('cataloginventory_stock_item')],
           'e.entity_id = cataloginventory_stock_item.product_id',$stockFields);
         
        if ($this->IsAdmin())
        {
            $collection->addAttributeToFilter('type_id', 'configurable');
        }         

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare default grid column
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();


        $this->addColumn(
            'product_sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'width' => '120'
            ]
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Product Name'),
                'index' => 'name'
            ]
        );

        if ($this->IsAdmin())
        {
            $this->addColumn(
                'qty_on_sale',
                [
                    'header' => __('Qty Available'),
                    'getter'   => [$this, 'getQtyOnHandConfigurable'],
                    'index' => 'qty_on_sale',
                    'type'     => 'number',
                    'filter' => false,
                    'width' => '120'
                ]
            );
        } else {
            $this->addColumn(
                'qty_on_sale',
                [
                    'header' => __('Qty Available'),
                    'index' => 'qty_on_sale',
                    'type'     => 'number',
                    'filter' => false,
                    'width' => '120'
                ]
            );
        }


        if ($this->IsAdmin())
        {
            $this->addColumn(
                'cost_price',
                [
                    'header' => __('Cost Price'),
                    'getter'   => array($this,'getCostPrice'),
                    'index' => 'cost_price',
                    'column_css_class' => 'a-right',
                    'filter' => false,
                    'sortable' => false
                ]
            );
        }

        $this->addColumn(
            'action',
            [
                'column_css_class' => 'a-center',
                'header'  => __('Action'),
                'filter'  => false,
                'renderer'=> 'MindArc\Inventory\Block\Adminhtml\Item\Grid\Renderer\Actions',
                'sortable'=> false,
                'width'   => '50px'
            ]
        );

        return $this;
    }


    /**
     * Setup the massaction header
     */
    protected function _prepareMassaction()
    {
        /*
         * $item = array(
         *      'label'    => string,
         *      'complete' => string, // Only for ajax enabled grid (optional)
         *      'url'      => string,
         *      'confirm'  => string, // text of confirmation of this action (optional)
         *      'additional' => string|array|Mage_Core_Block_Abstract // (optional)
         * );
         */
        // if ( $this->isEditable() ) {
        //     $this->getMassactionBlock()->addItem('update',array(
        //         'label'    => __('Update Qty on Hand'),
        //         'complete' => "function(grid,massaction,transport){
        //                 massaction.unselectAll();
        //                 grid.reload();
        //             }
        //         }",
        //         'url'      => $this->getUrl('*/item/massUpdate'),
        //         'confirm'  => __('Are you sure you want to update the selected quantities?')
        //     ));

        //     $this->getMassactionBlock()
        //         //->setUseAjax(true)
        //         ->setUseSelectAll(false);

        //     $this->setMassactionIdField('item_id');

        //     // Synchronize the hidden input in the massaction form!
        //     if ( ($parent = $this->getParentBlock()) && ($serializer = $parent->getChild( 'serializer' )) ) {
        //         $serializer->setFormId( $this->getMassactionBlock()->getHtmlId().'-form' );
        //     }
        // }
    }

    /**
     * Grid row URL getter
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * Define row click callback
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/item/index', ['_current' => true]);
    }

    protected function IsAdmin()
    {
        $user = $this->_authSession->getUser()->getRole();
        if ($user->getRoleName() == 'Administrators')
            return true;
        return false;
    }

    /**
     * Does the current user have access to edit the quantities?
     *
     * @return bool
     */
    public function isEditable()
    {
        return $this->_authorization->isAllowed('MindArc_Inventory::item_update');
    }

    /**
     * The row class (pointer if editable)
     *
     * @param $row Mage_Catalog_Model_Product
     */
    public function getRowClass( $row )
    {
        $class = array();

        if ( $this->isEditable() ) $class['pointer'] = true;

        /* @var $item Metrik_Inventory_Model_Stock_Item */
        $item = $this->_stockItemFactory->create()->load($row->getId(),'product_id');
        if ( $item->getQty() <= $item->getMinQty() ) {
            if ($row['type_id'] != 'configurable'){
                $class['invalid'] = true;
            } else {
                $qty = $this->getQtyOnHandConfigurable($row);
                if ($qty <= 0){
                    $class['invalid'] = true;
                }
            }
        } elseif ( $item->getQty() <= $item->getNotifyStockQty() ) {
            $class['emph']    = true;
        }

        return implode(' ',array_keys($class));
    }

    public function getCostPrice($row)
    {
        $_product = $this->_productFactory->create()->load($row->getId());

        return '$'.number_format(floatval($_product->getCost()),2);
    }

    public function getQtyOnHandConfigurable( $row )
    {
        $product = $this->_productFactory->create()->load($row->getId());
        $childProducts = $product->getTypeInstance()->getUsedProducts($product);

        $qty = 0;

        foreach($childProducts as $child) {
            $item = $this->_stockItemFactory->create()->load($child->getId(),'product_id');
            $qty += $item->getQty();
        }

        return $qty;
    }
}
