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
namespace MindArc\Inventory\Block\Adminhtml\Item\Edit\Tab;

class Item extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

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
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_formFactory = $formFactory;
        $this->_productFactory = $productFactory;
        $this->_stockItemFactory = $stockItemFactory;
        parent::__construct($context, $registry, $formFactory);
    }
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \MindArc\Inventory\Model\Item $item */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $item = $this->_coreRegistry->registry('mindarc_inventory_item');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('item_');
        $form->setFieldNameSuffix('item');
        $stockFieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Stock Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        $productFieldset = $form->addFieldset(
            'product_info',
            [
                'legend' => __('Product Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        if ($item->getItemId()) {
            $data = array();
            // Initialize the product
            $productId   = $item->getProductId();
            $product     = $this->_productFactory->create()->load($productId);
            $data+= $product->getData();

            $data+= $item->getData();
            $data['qty_to_ship'] = $item->getQtyToShip();
            $data['qty_overall'] = $item->getQty() + $item->getQtyToShip();

            if ($data['type_id'] == 'configurable')
            {
                $productConfigurableFieldset = $form->addFieldset('sub_products_stock_info', array(
                      'legend'=>__('Sub Products Information')
                 ));

                $product = $this->_productFactory->create()->load($data['product_id']);
                $childProducts = $product->getTypeInstance()->getUsedProducts($product);

                $qty        = 0;
                $qtyToShip  = 0;

                foreach($childProducts as $child) {
                    $item = $this->_stockItemFactory->create()->load($child->getId(),'product_id');
                    $qty        += $item->getQty();
                    $qtyToShip  += $item->getQtyToShip();
                }

                $data['qty_overall'] = $qty;
                $data['qty_to_ship'] = $qtyToShip;
            }

            $stockFieldset->addField(
                'item_id',
                'hidden',
                ['name' => 'item_id']
            );

            $stockFieldset->addField(
                'product_id',
                'hidden',
                ['name' => 'product_id']
            );
            
            // Add fields to the product fieldset
            $productFieldset->addField('sku', 'label', array(
                'label'     => __('Sku'),
                'name'      => 'sku'
            ));

            $productFieldset->addField('name', 'label', array(
                'label'     => __('Name'),
                'name'      => 'name'
            ));

            if ( !empty($data['short_description']) ) {
                $productFieldset->addField('short_description', 'label', array(
                    'label'     => __('Short Description'),
                    'name'      => 'short_description'
                ));
            }

            if ( !empty($data['long_description']) ) {
                $productFieldset->addField('long_description', 'label', array(
                    'label'     => __('Long Description'),
                    'name'      => 'long_description'
                ));
            }

            // Add fields to the stock fieldset
            $stockFieldset->addField('qty', 'label', array(
                'label'     => __('Qty on Sale'),
                'name'      => 'qty_on_sale'
            ));

            $stockFieldset->addField('qty_to_ship', 'label', array(
                'label'     => __('Qty to Ship'),
                'name'      => 'qty_to_ship'
            ));

            $stockFieldset->addField('qty_overall', 'label', array(
                  'label'     => __('Qty Available'),
                  'name'      => 'qty_overall'
             ));

            if ($data['type_id'] != 'configurable')
            {
                $stockFieldset->addField('qty_adjustment', ($this->isEditable() ? 'text' : 'label'), array(
                    'label'     => __('Qty Adjustment'),
                    'name'      => 'qty_adjustment'
                ));


                $stockFieldset->addField('comment', 'text', array(
                    'label'     => __('Qty Change Comment'),
                    'name'      => 'comment'
                ));
            } else {
                $product = $this->_productFactory->create()->load($data['product_id']);
                $childProducts = $product->getTypeInstance()->getUsedProducts($product);

                foreach($childProducts as $child) {

                    $qty = 0;

                    $item = $this->_stockItemFactory->create()->load($child->getId(),'product_id');
                    $qty += $item->getQty();
                    $data['sku-'.$child->getId()] = $qty;
                    $productConfigurableFieldset->addField('sku-'.$child->getId(), 'label', array(
                         'label'        => $child->getSku(),
                         'name'         => 'sku-'.$child->getId(),
                    ));
                }
            }
        }

        if ($data['type_id'] != 'configurable')
        {
            $itemData = $this->_session->getData('mindarc_inventory_item_data', true);
            if ($itemData) {
                $data->addData($itemData);
            } else {
                if (!$item->getItemId()) {
                    $data->addData($item->getDefaultValues());
                }
            }
        }
        $form->addValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Item');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
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
}
