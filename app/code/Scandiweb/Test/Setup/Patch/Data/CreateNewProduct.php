<?php
/**
 * @category    Scandiweb_Test
 * @author      Vladislavs Zimnikovs <vladislavs.zimnikovs@scandiweb.com | info@scandiweb.com>
 * @copyright   Copyright (c) 2022 Scandiweb, Inc (http://scandiweb.com)
 * @license     http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class CreateNewProduct implements DataPatchInterface
{
    /**
     * @var State $state
     */
    private State $state;

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var ProductInterfaceFactory $productFactory
     */
    private ProductInterfaceFactory $productFactory;

    /**
     * @var ProductRepositoryInterface $productRepository
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var CategoryLinkManagementInterface $categoryLinkManagement
     */
    private CategoryLinkManagementInterface $categoryLinkManagement;

    /**
     * @var CategoryCollectionFactory $categoryCollectionFactory
     */
    private CategoryCollectionFactory $categoryCollectionFactory;

    /**
     * CreateNewProduct constructor.
     * @param State $state
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        State $state,
        ModuleDataSetupInterface $moduleDataSetup,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CategoryLinkManagementInterface $categoryLinkManagement,
        CategoryCollectionFactory $categoryCollectionFactory
    )
    {
        $this->state = $state;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function apply()
    {
        $this->state->emulateAreaCode(
            Area::AREA_ADMINHTML,
            [$this, 'execute']
        );
    }

    /**
     * @throws StateException
     * @throws CouldNotSaveException
     * @throws InputException|LocalizedException
     */
    public function execute(): void
    {
        $this->moduleDataSetup->startSetup();

        $product = $this->productFactory->create();

        $product
            ->setName('Test Product')
            ->setSku('test-product')
            ->setPrice(100)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setTypeId(Type::TYPE_SIMPLE)
            ->setStatus(Status::STATUS_ENABLED)
            ->setUrlKey('test-product');

        $this->productRepository
            ->save($product);

        $categoryCollection = $this->categoryCollectionFactory->create();

        $categoryIds = $categoryCollection
            ->addAttributeToFilter('name', 'Men')
            ->getAllIds();

        $this->categoryLinkManagement
            ->assignProductToCategories(
                $product->getSku(),
                $categoryIds
            );

        $this->moduleDataSetup->endSetup();
    }
}
