<?php declare(strict_types=1);

namespace ICTShopFinder\Storefront\Subscriber;

use ICTShopFinder\Core\Content\ShopFinder\ShopFinderCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Pagelet\Footer\FooterPageletLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FooterSubscriber implements EventSubscriberInterface
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var EntityRepositoryInterface
     */
    private $shopFinderRepository;

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepositoryInterface $shopFinderRepository
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->shopFinderRepository = $shopFinderRepository;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            FooterPageletLoader::class => 'onFooterPageletLoaded',
        ];
    }
    public function onFooterPageletLoaded(FooterPageletLoader $event):void
    {
//        if(!$this->systemConfigService->get('ICTShopFinder.config.showInStoreFront')){
//            return;
//        }

        $shops = $this->fetchShops($event->getContext());
        $event->getPagelet()->addExtension('ict_shop_finder',$shops);
    }

    /**
     * @param Context $context
     * @return ShopFinderCollection
     */
    private function fetchShops(Context $context):ShopFinderCollection
    {
        $criteria = new Criteria();
        $criteria->addAssociation('country');
        $criteria->addFilter(new EqualsFilter('active','1'));
        $criteria->setLimit(5);

        $shopFinderCollection = $this->shopFinderRepository->search($criteria,$context)->getEntities();
        return $shopFinderCollection;
    }
}
