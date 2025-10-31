<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\AliyunVodBundle\Controller\Statistics\CleanupController;
use Tourze\AliyunVodBundle\Controller\Statistics\IndexController;
use Tourze\AliyunVodBundle\Controller\Statistics\PopularController;
use Tourze\AliyunVodBundle\Controller\Statistics\RangeController;
use Tourze\AliyunVodBundle\Controller\Statistics\UserBehaviorController;
use Tourze\AliyunVodBundle\Controller\Statistics\VideoDetailController;
use Tourze\AliyunVodBundle\Controller\Statistics\VideoPlayController;
use Tourze\AliyunVodBundle\Controller\Statistics\VideoStatController;
use Tourze\AliyunVodBundle\Controller\Statistics\VideoViewController;
use Tourze\AliyunVodBundle\Controller\VideoUpload\AuthController;
use Tourze\AliyunVodBundle\Controller\VideoUpload\IndexController as VideoUploadIndexController;
use Tourze\AliyunVodBundle\Controller\VideoUpload\ProgressController;
use Tourze\AliyunVodBundle\Controller\VideoUpload\RefreshAuthController;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

#[AutoconfigureTag(name: 'routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->autoload();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return false;
    }

    public function autoload(): RouteCollection
    {
        $collection = new RouteCollection();

        // Statistics controllers
        $collection->addCollection($this->controllerLoader->load(IndexController::class));
        $collection->addCollection($this->controllerLoader->load(VideoDetailController::class));
        $collection->addCollection($this->controllerLoader->load(RangeController::class));
        $collection->addCollection($this->controllerLoader->load(UserBehaviorController::class));
        $collection->addCollection($this->controllerLoader->load(PopularController::class));
        $collection->addCollection($this->controllerLoader->load(CleanupController::class));
        $collection->addCollection($this->controllerLoader->load(VideoPlayController::class));
        $collection->addCollection($this->controllerLoader->load(VideoStatController::class));
        $collection->addCollection($this->controllerLoader->load(VideoViewController::class));

        // Video upload controllers
        $collection->addCollection($this->controllerLoader->load(VideoUploadIndexController::class));
        $collection->addCollection($this->controllerLoader->load(AuthController::class));
        $collection->addCollection($this->controllerLoader->load(RefreshAuthController::class));
        $collection->addCollection($this->controllerLoader->load(ProgressController::class));

        return $collection;
    }
}
