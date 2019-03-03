<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Service
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\Service;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Mageviper\Dpd\Api\Data\PackageInterface;
use Mageviper\Dpd\Helper\Data;
use T3ko\Dpd\Api;
use T3ko\Dpd\Objects\Package;
use T3ko\Dpd\Objects\Parcel;
use T3ko\Dpd\Objects\Receiver;
use T3ko\Dpd\Objects\RegisteredParcel;
use T3ko\Dpd\Objects\Sender;
use T3ko\Dpd\Request\FindPostalCodeRequest;
use T3ko\Dpd\Request\GenerateLabelsRequest;
use T3ko\Dpd\Request\GeneratePackageNumbersRequest;
use T3ko\Dpd\Request\GenerateProtocolRequest;
use T3ko\Dpd\Soap\Types\FindPostalCodeV1Request;

/**
 * Class Dpd
 */
class Dpd
{
    /**
     * @var Data
     */
    protected $data;
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * Dpd constructor.
     * @param Data            $data
     * @param OrderRepository $orderRepository
     */
    public function __construct(Data $data, OrderRepository $orderRepository)
    {
        $this->data            = $data;
        $this->orderRepository = $orderRepository;
    }

    /**
     * connection method
     */
    protected function connection()
    {
        /** @var Api $api */
        $api = new Api($this->data->getLogin(), $this->data->getPassword(), $this->data->getManifestFid());
        $api->setSandboxMode($this->data->isTestMode());

        return $api;
    }

    /**
     * @param Order $data
     * @return Parcel
     */
    public function prepareParcel(OrderInterface $data): Parcel
    {
        if ($this->data->isTestMode()) {
            return new Parcel(
                0,
                0,
                0,
                $this->data->getMaxParcelWeight(),
                $data->getId() . rand(100000, 999999999999),
                $data->getIncrementId()
            );
        }

        return new Parcel(
            0,
            0,
            0,
            $this->data->getMaxParcelWeight(),
            $data->getId(),
            $data->getIncrementId()
        );
    }

    /**
     * @param Order $order
     * @return Receiver
     */
    public function prepareReceiver(OrderInterface $order): Receiver
    {
        /** @var \Magento\Sales\Model\Order\Address $address */
        $address = $order->getShippingAddress();

        return new Receiver(
            $address->getTelephone(),
            $address->getName() . ' ' . $address->getLastname(),
            implode(',', $address->getStreet()),
            $this->data->sanitizePostCode($address->getPostcode()),
            $address->getCity(),
            $address->getCountryId(),
            null,
            $address->getEmail()
        );

    }

    /**
     * @return Sender
     */
    protected function prepareSender(): Sender
    {
        return new Sender(
            $this->data->getShopData('fid'),
            $this->data->getShopData('phone'),
            $this->data->getShopData('name'),
            $this->data->getShopData('address'),
            $this->data->sanitizePostCode($this->data->getShopData('postalCode')),
            $this->data->getShopData('city'),
            $this->data->getShopData('countryCode'),
            $this->data->getShopData('company'),
            $this->data->getShopData('email')
        );
    }

    /**
     * @param PackageInterface $package
     * @return RegisteredParcel
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function preparePackage(PackageInterface $package): RegisteredParcel
    {
        $connection = $this->connection();
        $order      = $this->orderRepository->get((int)$package->getOrderId());
        /** @var Package $packageObject */
        $packageObject = new Package(
            $this->prepareSender(),
            $this->prepareReceiver($order),
            [$this->prepareParcel($order)]
        );
        $package       = GeneratePackageNumbersRequest::fromPackage($packageObject);
        $response      = $connection->generatePackageNumbers($package);
        list($package) = $response->getPackages();
        list($parcel) = $package->getParcels();

        return $parcel;

    }

    /**
     * @param array $parcelsId
     * @return mixed
     */
    public function generateLabelsFromParcelsId(array $parcelsId)
    {
        $connection    = $this->connection();
        $labelsRequest = GenerateLabelsRequest::fromParcelIds($parcelsId);
        $response      = $connection->generateLabels($labelsRequest);

        return $response->getFileContent();

    }

    /**
     * @param array $parcelsId
     * @return string
     */
    public function generateProtocolFromParcelsId(array $parcelsId): string
    {
        $connection      = $this->connection();
        $protocolRequest = GenerateProtocolRequest::fromParcelIds($parcelsId);
        $response        = $connection->generateProtocol($protocolRequest);

        return $response->getFileContent();
    }

    /**
     * @param $code
     * @param $region
     * @return \T3ko\Dpd\Response\FindPostalCodeResponse|FindPostalCodeV1Request
     */
    public function checkPostalCode(string $code, string $region = 'PL')
    {
        $login      = $this->connection();
        $postalCode = FindPostalCodeRequest::from($code, $region);

        return $login->findPostalCode($postalCode);
    }
}
