<?php

declare(strict_types=1);

namespace Tourze\AliyunVodBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;

/**
 * 阿里云VOD配置管理
 *
 * @extends AbstractCrudController<AliyunVodConfig>
 */
#[AdminCrud(
    routePath: '/aliyun-vod/config',
    routeName: 'aliyun_vod_config',
)]
final class AliyunVodConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AliyunVodConfig::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('阿里云VOD配置')
            ->setEntityLabelInPlural('阿里云VOD配置')
            ->setPageTitle('index', '阿里云VOD配置管理')
            ->setPageTitle('new', '新增配置')
            ->setPageTitle('edit', '编辑配置')
            ->setPageTitle('detail', '配置详情')
            ->setDefaultSort(['isDefault' => 'DESC', 'createdTime' => 'DESC'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $testConnection = Action::new('testConnection', '测试连接', 'fa fa-plug')
            ->linkToCrudAction('testConnection')
            ->addCssClass('btn btn-info')
        ;

        $setDefault = Action::new('setDefault', '设为默认', 'fa fa-star')
            ->linkToCrudAction('setDefault')
            ->addCssClass('btn btn-warning')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $testConnection)
            ->add(Crud::PAGE_INDEX, $setDefault)
            ->add(Crud::PAGE_DETAIL, $testConnection)
            ->add(Crud::PAGE_DETAIL, $setDefault)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')
                ->hideOnForm(),

            TextField::new('name', '配置名称')
                ->setRequired(true)
                ->setHelp('用于标识不同的配置，如：生产环境、测试环境等'),

            TextField::new('accessKeyId', '访问密钥ID')
                ->setRequired(true)
                ->setHelp('阿里云AccessKey ID'),

            TextareaField::new('accessKeySecret', '访问密钥Secret')
                ->setRequired(true)
                ->setHelp('阿里云AccessKey Secret，将加密存储')
                ->hideOnIndex(),

            TextField::new('regionId', '地域ID')
                ->setRequired(true)
                ->setHelp('阿里云地域ID，如：cn-shanghai、cn-beijing等'),

            TextField::new('templateGroupId', '转码模板组ID')
                ->setRequired(false)
                ->setHelp('默认转码模板组ID，可为空'),

            TextField::new('storageLocation', '存储地址')
                ->setRequired(false)
                ->setHelp('视频存储地址，可为空使用默认'),

            TextField::new('callbackUrl', '回调URL')
                ->setRequired(false)
                ->setHelp('上传完成后的回调地址'),

            BooleanField::new('isDefault', '默认配置')
                ->setHelp('是否为默认配置，只能有一个默认配置'),

            BooleanField::new('valid', '启用状态')
                ->setHelp('是否启用此配置'),

            DateTimeField::new('createdTime', '创建时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss'),

            DateTimeField::new('updatedTime', '更新时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '配置名称'))
            ->add(TextFilter::new('regionId', '地域ID'))
            ->add(BooleanFilter::new('isDefault', '默认配置'))
            ->add(BooleanFilter::new('valid', '启用状态'))
            ->add(DateTimeFilter::new('createdTime', '创建时间'))
            ->add(DateTimeFilter::new('updatedTime', '更新时间'))
        ;
    }

    /**
     * 测试连接
     */
    #[AdminAction(routeName: 'test_connection', routePath: '/test-connection')]
    public function testConnection(): RedirectResponse|Response
    {
        // TODO: 实现测试连接功能
        $this->addFlash('success', '连接测试功能待实现');

        return $this->redirect($this->getContext()->getRequest()->headers->get('referer') ?: $this->generateUrl('easyadmin', [
            'action' => 'index',
            'entity' => 'AliyunVodConfig'
        ]));
    }

    /**
     * 设为默认配置
     */
    #[AdminAction(routeName: 'set_default', routePath: '/set-default')]
    public function setDefault(): RedirectResponse|Response
    {
        // TODO: 实现设为默认配置功能
        $this->addFlash('success', '设为默认配置功能待实现');

        return $this->redirect($this->getContext()->getRequest()->headers->get('referer') ?: $this->generateUrl('easyadmin', [
            'action' => 'index',
            'entity' => 'AliyunVodConfig'
        ]));
    }
}
