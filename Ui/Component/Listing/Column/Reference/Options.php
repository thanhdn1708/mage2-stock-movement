<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MindArc\Inventory\Ui\Component\Listing\Column\Reference;

use Magento\Framework\Data\OptionSourceInterface;
/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;


    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options[] = ['value' => 'shipment', 'label' => 'Shipment'];
            $this->options[] = ['value' => 'production', 'label' => 'Print Job'];
            $this->options[] = ['value' => 'returned_item', 'label' => 'Loaned Item Return'];
        }
        return $this->options;
    }
}
