<?php 

namespace Webcreta\OrdersMessage\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class Message implements ObserverInterface
{
    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;
	protected $_orderCollectionFactory;
    /** @var \Magento\Framework\UrlInterface */
    protected $url;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $managerInterface,
        \Magento\Framework\UrlInterface $url,
		 \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->messageManager = $managerInterface;
        $this->url = $url;
		$this->_orderCollectionFactory = $orderCollectionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
		
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
		$conf = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ordermessage/general/enable'); 
		//echo"<pre>";print_r($conf);die();
		if($conf == 1){
		
			$customerSession = $objectManager->create('Magento\Customer\Model\Session'); 
			if ($customerSession->isLoggedIn()) { 
			
			$id = $customerSession->getCustomer()->getId();
			$current_product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');
			//$product_sku = $current_product->getSku();
			$product_Id = $current_product->getId();
			
			
			$orders = $objectManager->create('Magento\Sales\Model\Order')->getCollection()->addFieldToFilter("customer_id", $id)->setOrder('created_at','DESC')->addFilter('status', 'complete');
			//echo"<pre>";print_r($orders->getData() );die();

			$date = array();
			foreach ($orders as $order) { 
				$items = $order->getAllVisibleItems();
				foreach ($items as $item) {
					if($product_Id == $item->getProductId()){
						$date[] = date('d-m-Y', strtotime($item->getCreatedAt()));
					}
				}
			}
			 
			if(!empty($date)){
			$created_date = date('d M Y', strtotime($date[0]));
			//echo"<pre>";print_r($created_date);die();
			
				$messageCollection = $this->messageManager->getMessages(true);
				$this->messageManager->addSuccess(__("You last bought this product on" . ' ' . $created_date . "."));
				  }
			}
		}
    }
	
}