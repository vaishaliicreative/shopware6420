<?php declare(strict_types=1);

namespace ICTECHMigration\Controller;

use mysqli;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepositoryInterface $languageRepository,
        EntityRepositoryInterface $propertyRepository,
        EntityRepositoryInterface $productsRepository,
        EntityRepositoryInterface $propertyOptionsRepository,
        EntityRepositoryInterface $productPropertyRepository,
        EntityRepositoryInterface $categoryRepository
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->languageRepository = $languageRepository;
        $this->propertyRepository = $propertyRepository;
        $this->productsRepository = $productsRepository;
        $this->propertyOptionsRepository = $propertyOptionsRepository;
        $this->productPropertyRepository = $productPropertyRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/api/_action/migration/property",name="api.custom.migration.property", methods={"POST"})
     */
    public function property(\Symfony\Component\HttpFoundation\Request $request): Response
    {
//        $servername = "amargo-preview.de";
//        $username = "amanwyeh5";
//        $password = "3R7hZEy!kW7O";
        $context = Context::createDefaultContext();
        $servername = $this->systemConfigService->get('ICTECHMigration.config.databaseHost');
        $username = $this->systemConfigService->get('ICTECHMigration.config.databaseUser');
        $password = $this->systemConfigService->get('ICTECHMigration.config.databasePassword');
        $database = 'usrdb_amanwyeh5';

//        $this->propertyInsert($context);

        $conn = new mysqli($servername, $username, $password, $database);
//        $p_sql = "SELECT * FROM product_category WHERE selectable='0'";
//        $c_sql = "SELECT * FROM product_category WHERE selectable='1'";
//        $result = mysqli_query($conn, $p_sql);
//        $c_result = mysqli_query($conn, $c_sql);
//        $parentDataArray = [];
//
//        if (mysqli_num_rows($result) > 0) {
//            while($row = mysqli_fetch_assoc($result)) {
//                $parentDataArray[$row['name']] = $row['pc_id'];
//            }
//        } else {
//            dd("0 results");
//        }
//        $childDataArray = [];
//        if (mysqli_num_rows($c_result) > 0) {
//            while($row = mysqli_fetch_assoc($c_result)) {
//                $childDataArray[$row['name']] = $row['referto_pc_id'];
//            }
//        } else {
//            dd("0 results");
//        }
//        dump($parentDataArray);
//dd($childDataArray);



        return new JsonResponse([
            'type' => 'Success',
            'message' => 'Properties Imported'
        ]);
    }

    private function propertyInsert($context): void
    {
        $data = [
            'id' => Uuid::randomHex(),
            'name' => 'Shopware Administration',
        ];
        $this->categoryRepository->upsert([$data], $context);
    }




}
