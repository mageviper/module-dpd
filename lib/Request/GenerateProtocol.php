<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   lib
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\lib\Request;

use T3ko\Dpd\Objects\Enum\FileType;
use T3ko\Dpd\Objects\Enum\LabelPrintingPolicy;
use T3ko\Dpd\Objects\Enum\PageSize;
use T3ko\Dpd\Objects\Enum\SessionType;
use T3ko\Dpd\Request\GenerateProtocolRequest;
use T3ko\Dpd\Soap\Types\DpdServicesParamsV1;
use T3ko\Dpd\Soap\Types\GenerateProtocolV2Request;
use T3ko\Dpd\Soap\Types\OutputDocFormatDSPEnumV1;
use T3ko\Dpd\Soap\Types\OutputDocPageFormatDSPEnumV1;
use T3ko\Dpd\Soap\Types\PackageDSPV1;
use T3ko\Dpd\Soap\Types\ParcelDSPV1;
use T3ko\Dpd\Soap\Types\PolicyDSPEnumV1;
use T3ko\Dpd\Soap\Types\SessionDSPV1;
use T3ko\Dpd\Soap\Types\SessionTypeDSPEnumV1;

/**
 * Class GenerateProtocol
 */
class GenerateProtocol extends GenerateProtocolRequest
{
    protected $sessionType;
    private   $pageFormat;
    private   $pageSize;
    private   $parcelIds;
    private   $references;
    private   $waybills;
    private   $printingPolicy;

    /**
     * GenerateProtocolRequest constructor.
     *
     * @param $parcelIds
     * @param $references
     * @param $waybills
     */
    protected function __construct(array $parcelIds, array $references, array $waybills)
    {
        $this->parcelIds      = $parcelIds;
        $this->references     = $references;
        $this->waybills       = $waybills;
        $this->pageFormat     = FileType::PDF();
        $this->pageSize       = PageSize::A4();
        $this->sessionType    = SessionType::DOMESTIC();
        $this->printingPolicy = LabelPrintingPolicy::IGNORE_ERRORS();
    }

    /**
     * @param array $parcelIds
     * @return GenerateProtocol
     */
    public static function fromParcelIds(array $parcelIds): GenerateProtocolRequest
    {
        return new static($parcelIds, [], []);
    }

    public static function fromReferences(array $references): GenerateProtocolRequest
    {
        return new static([], $references, []);
    }

    public static function fromWaybills(array $waybills): GenerateProtocolRequest
    {
        return new static([], [], $waybills);
    }

    /**
     * @return GenerateProtocolV2Request
     */
    public function toPayload(): GenerateProtocolV2Request
    {
        $request = new GenerateProtocolV2Request();
        $request->setOutputDocFormat(new OutputDocFormatDSPEnumV1((string)$this->pageFormat));
        $request->setOutputDocPageFormat(new OutputDocPageFormatDSPEnumV1((string)$this->pageSize));

        $serviceParams = new DpdServicesParamsV1();
        $serviceParams->setPolicy(new PolicyDSPEnumV1((string)$this->printingPolicy));

        $session = new SessionDSPV1();
        $session->setSessionType(new SessionTypeDSPEnumV1((string)$this->getSessionType()));
        $parcels = [];
        if (!empty($this->parcelIds)) {
            foreach ($this->parcelIds as $parcelId) {
                $parcel = new ParcelDSPV1();
                $parcel->setParcelId($parcelId);
                $parcels[] = $parcel;
            }
        }

        if (!empty($this->references)) {
            foreach ($this->references as $reference) {
                //$package = new PackageDSPV1();
                $parcel = new ParcelDSPV1();
                $parcel->setReference($reference);
                $parcels[] = $parcel;
            }
        }

        if (!empty($this->waybills)) {
            foreach ($this->waybills as $waybill) {
                //$package = new PackageDSPV1();
                $parcel = new ParcelDSPV1();
                $parcel->setWaybill($waybill);
                $parcels[] = $parcel;
            }
        }
        $package = new PackageDSPV1();
        $package->setParcels($parcels);
        $session->setPackages([$package]);
        $serviceParams->setSession($session);
        $request->setDpdServicesParams($serviceParams);

        return $request;
    }

    public function setSessionType($type)
    {
        if ($type === 'INTERNATIONAL') {
            $this->sessionType = SessionType::INTERNATIONAL();
        } else {
            $this->sessionType = SessionType::DOMESTIC();
        }

        return $this;
    }

    public function getSessionType()
    {
        return $this->sessionType;
    }

}