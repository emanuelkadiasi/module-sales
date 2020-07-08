<?php 
namespace Emanuel\ModuleSales\Plugin;

class SalesOrderProductCollection
{
	
	/**
     * @var \Emanuel\ModuleSales\Model\Sales\TemporaryStorageFactory
     */
    protected $temporaryStorageFactory;

    public function __construct(
        \Emanuel\ModuleSales\Model\Sales\TemporaryStorageFactory $temporaryStorageFactory,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->context = $context;
    }

    public function beforeLoad(
        \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject
    ) {

        $temporaryStorage = $this->temporaryStorageFactory->create();
        $tempCollection = $subject->getConnection()->select()->from('sales_order_item')
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(
                [
                    'order_id',
                    'sku' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT sku SEPARATOR \',\')'),
                ]
            )->group('order_id');



        $table= $temporaryStorage->storeOrderItem($subject->getConnection()->fetchAll($tempCollection));
        $subject->getSelect()->joinLeft(
            [
                'temp_table' => $table->getName(),
            ],
            'main_table.entity_id = temp_table.' . \Emanuel\ModuleSales\Model\Sales\TemporaryStorage::FIELD_ORDER_ID,
            ['sku']
        );
        $subject->setPageSize($subject->getPageSize());
        $subject->setCurPage($subject->getCurPage());
    }

}