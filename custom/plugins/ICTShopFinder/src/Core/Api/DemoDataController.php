<?php declare(strict_types=1);

namespace ICTShopFinder\Core\Api;

use Faker\Factory;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Country\Exception\CountryNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;

/**
 * @RouteScope(scopes={"api"})
 */
class DemoDataController extends AbstractController
{
    /**
     * @var EntityRepositoryInterface
     */
    private $countryRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $shopFinderRepository;


    public function __construct(EntityRepositoryInterface $countryRepository,
                                EntityRepositoryInterface $shopFinderRepository)
    {
        $this->countryRepository = $countryRepository;
        $this->shopFinderRepository = $shopFinderRepository;
    }

    /**
     * @Route("/api/v{version}/_action/ict-shop-finder/generate", name="api.custom.ict_shop_finder.generate", methods={"POST"})
     * @param Context $context
     * @return Response
     * @throws CountryNotFoundException
     * @throws InconsistentCriteriaIdsException
     */
    public function generate(Context $context,Request $request):Response
    {
        $faker= Factory::create();
        $country=$this->getCountry($context);

        $data=[];
        for($i = 0; $i < 5; $i++ )
        {
            $data[] = [
                'id' => Uuid::randomHex(),
                'active'=>true,
                'name'=>$faker->name,
                'description'=>$faker->text,
                'street' => $faker->streetAddress,
                'city'=>$faker->city,
                'telephone'=>$faker->phoneNumber,
                'postCode' => $faker->postcode,
                'url' => $faker->url,
                'openTimes' => $faker->time,
                'countryId'=>$country->getId(),
            ];
        }
//        echo "<pre>";
//        print_r($data);
//        exit;
        $this->shopFinderRepository->create($data,$context);
        return new Response('',Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Context $context
     * @return CountryEntity
     * @throw CountryNotFoundException
     */
    private function getCountry(Context $context):CountryEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active','1'));
        $criteria->setLimit(1);

        $country = $this->countryRepository->search($criteria,$context)->getEntities()->first();

        if($country === null){
            throw new CountryNotFoundException('');
        }

        return $country;
    }
}
