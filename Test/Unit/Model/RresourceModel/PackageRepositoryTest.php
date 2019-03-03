<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Test
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */

namespace Mageviper\Dpd\Test\Unit\Model\RresourceModel;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Mageviper\Dpd\Model\PackageFactory;
use Mageviper\Dpd\Model\ResourceModel\Package\CollectionFactory;
use Mageviper\Dpd\Model\ResourceModel\PackageRepository;
use Mageviper\Dpd\Model\Spi\PackageRegistryInterface;
use Mageviper\Dpd\Model\Spi\PackageResourceInterface;
use PHPUnit\Framework\TestCase;

class PackageRepositoryTest extends TestCase
{

    protected function setUp()
    {
        $this->mockPackageFactory            = $this->getMockBuilder(PackageFactory::class)
                                                    ->setMethods(['create'])
                                                    ->disableOriginalConstructor()
                                                    ->getMock();
        $this->mockPackeResource             = $this->getMockBuilder(PackageResourceInterface::class)
                                                    ->getMockForAbstractClass();
        $this->mockRegistry                  = $this->getMockBuilder(PackageRegistryInterface::class)
                                                    ->getMockForAbstractClass();
        $this->mockCollectionProcessor       = $this->getMockBuilder(CollectionProcessorInterface::class)
                                                    ->getMockForAbstractClass();
        $this->searchResultsFactory          = $this->createPartialMock(SearchResultFactory::class, ['create']);
        $this->extensibleDataObjectConverter = $this->createMock(ExtensibleDataObjectConverter::class);
        $this->collectionProcessor           = $this->getMockBuilder(CollectionFactory::class)
                                                    ->setMethods(['create'])
                                                    ->getMock();

        parent::setUp();
    }

    public function testSave()
    {


    }

    public function testDelete()
    {

    }
}
