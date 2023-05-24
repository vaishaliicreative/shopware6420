<?php declare(strict_types=1);

namespace ICTECHMigration\Controller;

use mysqli;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class PropertyController extends AbstractController
{
    /*** @var SystemConfigService */
    private $systemConfigService;

    /*** @var EntityRepositoryInterface */
    private $languageRepository;

    /*** @var EntityRepositoryInterface */
    private $propertyRepository;

    /*** @var EntityRepositoryInterface */
    private $productsRepository;

    /*** @var EntityRepositoryInterface */
    private $propertyOptionsRepository;

    /*** @var EntityRepositoryInterface */
    private $productPropertyRepository;
    private $categoryRepository;

    /*** @var EntityRepositoryInterface */
    private $seoUrlRepository;

    /*** @var EntityRepositoryInterface */
    private $salesChannelRepository;


    public function __construct(
        SystemConfigService       $systemConfigService,
        EntityRepositoryInterface $languageRepository,
        EntityRepositoryInterface $propertyRepository,
        EntityRepositoryInterface $productsRepository,
        EntityRepositoryInterface $propertyOptionsRepository,
        EntityRepositoryInterface $productPropertyRepository,
        EntityRepositoryInterface $categoryRepository,
        EntityRepositoryInterface $seoUrlRepository,
        EntityRepositoryInterface $salesChannelRepository
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->languageRepository = $languageRepository;
        $this->propertyRepository = $propertyRepository;
        $this->productsRepository = $productsRepository;
        $this->propertyOptionsRepository = $propertyOptionsRepository;
        $this->productPropertyRepository = $productPropertyRepository;
        $this->categoryRepository = $categoryRepository;
        $this->seoUrlRepository = $seoUrlRepository;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    /**
     * @Route("/api/_action/migration/property",name="api.custom.migration.property", methods={"POST"})
     */
    public function property(\Symfony\Component\HttpFoundation\Request $request): Response
    {
//        $servername = "amargo-preview.de";
//        $username = "amanwyeh5";
//        $password = "3R7hZEy!kW7O";

        //     create Context
        $context = Context::createDefaultContext();

        //     get parent data
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('level', '1'));
        $getParentDataId = $this->categoryRepository->search($criteria, $context)->first();

        //     get Configuration data
        $servername = $this->systemConfigService->get('ICTECHMigration.config.databaseHost');
        $username = $this->systemConfigService->get('ICTECHMigration.config.databaseUser');
        $password = $this->systemConfigService->get('ICTECHMigration.config.databasePassword');
        $database = 'usrdb_amanwyeh5';

        //     Connection With MySQL and Get Data
        $conn = new mysqli($servername, $username, $password, $database);
        $startFromData = (int)$request->get('startingValue');
        $sql = 'SELECT cms_art_menu_text,cms_art_menu_speaking_url,cms_art_article_browser_title,cms_art_article_description,cms_art_article_content,cms_art_article_content_title FROM cms_articles LIMIT 1';
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $i = (int)$request->get('startingValue');
            while ($row = mysqli_fetch_assoc($result)) {
                $i = $i + 1;
                $categoryId = Uuid::randomHex();
                $this->categoryInsert($getParentDataId->id, $categoryId, $row, $context);
                if ($i % 10 == 0) {
                    return new JsonResponse([
                        'type' => 'Pending',
                        'message' => $i
                    ]);
                }
            }
        }
        return new JsonResponse([
            'type' => 'Success',
            'message' => 'Categories Imported'
        ]);
    }

    private function categoryInsert($getParentDataId, $categoryId, $row, $context): void
    {
        $data = [
            'id' => $categoryId,
            'parentId' => $getParentDataId,
            'name' => $row['cms_art_menu_text'] == '' ? '' : $row['cms_art_menu_text'],
            'metaTitle' => $row['cms_art_article_browser_title'] == '' ? '' : $row['cms_art_article_browser_title'],
            'metaDescription' => $row['cms_art_article_description'] == '' ? '' : $row['cms_art_article_description'],
            'description' => $row['cms_art_article_content'] == '' ? '' : $row['cms_art_article_content'],
            'customFields' => ["custom_category_has_migration" => $row['cms_art_article_content_title']]
        ];
        $this->categoryRepository->create([$data], $context);
    }

}
