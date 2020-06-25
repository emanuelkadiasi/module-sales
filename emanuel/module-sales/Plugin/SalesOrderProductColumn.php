<?php 
namespace Emanuel\ModuleSales\Plugin;
 
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;
 
class SalesOrderProductColumn
{
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * AddDataToOrdersGrid constructor.
     *
     * @param \Psr\Log\LoggerInterface $customLogger
     * @param array $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $customLogger,
        array $data = []
    ) {
        $this->logger = $customLogger;
    }

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param OrderGridCollection $collection
     * @param $requestName
     * @return mixed
     */
    public function afterGetReport($subject, $collection, $requestName)
    {
        if ($requestName !== 'sales_order_grid_data_source') {
            return $collection;
        }

        if ($collection->getMainTable() === $collection->getResource()->getTable('sales_order_grid')) {
            try {

                $orderItemsTableName = $collection->getResource()->getTable('sales_order_item');
                $itemsTableSelectGrouped = $collection->getConnection()->select();
                $itemsTableSelectGrouped->from(
                $orderItemsTableName,
                    [
                        'sku'     => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT sku SEPARATOR \',\')'),
                        'order_id' => 'order_id'
                    ]
                );

                $itemsTableSelectGrouped->group('order_id');

                $collection->getSelect()
                           ->joinLeft(
                               ['soi' => $itemsTableSelectGrouped],
                               'soi.order_id = main_table.entity_id',
                               ['sku']
                           );

            } catch (\Zend_Db_Select_Exception $selectException) {
                // Do nothing in that case
                $this->logger->log(100, $selectException);
            }
        }

        return $collection;
    }
}