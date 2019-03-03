<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Model
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\ShipmentRepository;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection;
use Magento\Shipping\Model\Order\Track;
use Magento\Shipping\Model\ShipmentNotifier;
use Mageviper\Dpd\Api\ManifestRepositoryInterface;
use Mageviper\Dpd\Api\PackageRepositoryInterface;
use Mageviper\Dpd\Exception\NoOrdersException;
use Mageviper\Dpd\Helper\Data;
use Mageviper\Dpd\Model\Carrier\Dpd;

/**
 * Class DataManagement
 */
class DataManagement
{

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var Data
     */
    protected $config;
    /**
     * @var ManifestRepositoryInterface
     */
    protected $manifestRepository;
    /**
     * @var PackageRepositoryInterface
     */
    protected $packageRepository;
    /**
     * @var Order
     */
    protected $orderConverter;
    /**
     * @var Shipment\TrackFactory
     */
    protected $trackFactory;
    /**
     * @var ShipmentRepository
     */
    protected $shipmentRepository;
    /**
     * @var ShipmentNotifier
     */
    protected $shipmentNotifier;

    /**
     * DataManagement constructor.
     * @param OrderRepositoryInterface    $orderRepository
     * @param ManifestRepositoryInterface $manifestRepository
     * @param PackageRepositoryInterface  $packageRepository
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder
     * @param Order                       $orderConverter
     * @param Shipment\TrackFactory       $trackFactory
     * @param ShipmentRepository          $shipmentRepository
     * @param ShipmentNotifier            $shipmentNotifier
     * @param Data                        $config
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ManifestRepositoryInterface $manifestRepository,
        PackageRepositoryInterface $packageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Order $orderConverter,
        Shipment\TrackFactory $trackFactory,
        ShipmentRepository $shipmentRepository,
        ShipmentNotifier $shipmentNotifier,
        Data $config
    ) {
        $this->orderRepository       = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config                = $config;
        $this->manifestRepository    = $manifestRepository;
        $this->packageRepository     = $packageRepository;
        $this->orderConverter        = $orderConverter;
        $this->trackFactory          = $trackFactory;
        $this->shipmentRepository    = $shipmentRepository;
        $this->shipmentNotifier      = $shipmentNotifier;
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface[]
     * @throws NoOrdersException
     */
    public function prepareOrders()
    {
        $orderStatuses  = $this->config->getOrderStatusesToSend();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('shipping_method', 'dpd_%', 'like')
            ->addFilter('status', $orderStatuses, 'in')
            ->addFilter('dpd_flag', '0')
            ->create();

        $searchResults = $this->orderRepository->getList($searchCriteria);

        if (empty($searchResults->getItems())) {
            throw new NoOrdersException((string)__('No orders to shipment'));
        }

        return $searchResults->getItems();

    }

    /**
     * @return bool|\Mageviper\Dpd\Api\Data\ManifestInterface
     */
    public function checkManifest()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', Manifest::PREPARE_TO_APPLY)
            ->create();
        $searchResult   = $this->manifestRepository->getList($searchCriteria)->getItems();

        if (empty($searchResult)) {
            return false;
        }

        return current($searchResult);
    }

    /**
     * @return bool
     */
    public function checkManifestSchedule()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', Manifest::QUEUE)
            ->addFilter('status', Manifest::PROCESSING)
            ->create();
        $searchResult   = $this->manifestRepository->getList($searchCriteria)->getItems();

        if (empty($searchResult)) {
            return false;
        }

        return true;
    }

    public function getQueuedManifest()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', Manifest::QUEUE)
            ->create();
        $searchResult   = $this->manifestRepository->getList($searchCriteria)->getItems();

        if (empty($searchResult)) {
            return false;
        }

        return current($searchResult);
    }

    /**
     * @param $orderId
     * @return bool|\Mageviper\Dpd\Api\Data\PackageInterface
     */
    public function checkPackage($orderId)
    {
        $package = $this->packageRepository->getByOrderId((int)$orderId);

        if (!empty($package->getId())) {
            return $package;
        }

        return false;
    }

    /**
     * @param $manifestId
     * @return bool|\Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function prepareAllPackagesFromManifest($manifestId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('manifest_id', $manifestId)->create();
        $searchResult   = $this->packageRepository->getList($searchCriteria)->getItems();
        if (empty($searchResult)) {
            return false;
        }

        return $searchResult;
    }

    /**
     * @param int $orderId
     * @return ShipmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareShipment(int $orderId): ShipmentInterface
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);
        /** @var Collection $shipmentCollection */
        if (($shipmentCollection = $order->getShipmentsCollection()) && $shipmentCollection->getSize()) {
            return $shipmentCollection->getFirstItem();
        }

        if (!$order->canShip()) {
            throw new Exception('Order cannot be shipped.');
        }

        /** @var Shipment $shipment */
        $shipment = $this->orderConverter->toShipment($order);
        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }
            /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
            $shipmentItem = $this->orderConverter->itemToShipmentItem($orderItem)
                                                 ->setQty($orderItem->getQtyToShip());

            $shipment->addItem($shipmentItem);
        }
        $shipment->register();
        $order->setIsInProcess(true);
        $this->orderRepository->save($order);
        return $shipment;
    }

    /**
     * @param Shipment $shipment
     * @param string   $trackingNumber
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function addTrack(Shipment $shipment, string $trackingNumber): void
    {
        $track = $this->trackFactory->create();
        if (!empty($shipment->getAllTracks())) {
            /** @var Track $track */
            $track = $shipment->getTracksCollection()->getFirstItem();
        }
        $track->setCarrierCode(Dpd::CODE);
        $track->setTitle($this->config->getCarrierTitle());
        $track->setDescription(__('Tracking number was added')->render());
        $track->setTrackNumber($trackingNumber);
        $track->setQty($shipment->getTotalQty());

        $shipment->addTrack($track);
        try {
            $this->shipmentRepository->save($shipment);
            $this->shipmentNotifier->notify($shipment);
        } catch (CouldNotSaveException $e) {
            throw new CouldNotSaveException($e->getMessage());
        }
    }

}
