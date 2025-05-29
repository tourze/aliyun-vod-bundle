<?php

namespace Tourze\AliyunVodBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\AliyunVodBundle\Entity\PlayRecord;

/**
 * 播放记录管理
 */
class PlayRecordCrud extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlayRecord::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('播放记录')
            ->setEntityLabelInPlural('播放记录')
            ->setPageTitle('index', '播放记录管理')
            ->setPageTitle('detail', '播放记录详情')
            ->setDefaultSort(['playTime' => 'DESC'])
            ->setPaginatorPageSize(50);
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewStats = Action::new('viewStats', '查看统计', 'fa fa-chart-bar')
            ->linkToCrudAction('viewStats')
            ->addCssClass('btn btn-primary');

        return $actions
            ->add(Crud::PAGE_INDEX, $viewStats)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('video', '视频'))
            ->add(TextFilter::new('ipAddress', 'IP地址'))
            ->add(TextFilter::new('deviceType', '设备类型'))
            ->add(TextFilter::new('playQuality', '播放质量'))
            ->add(DateTimeFilter::new('playTime', '播放时间'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            AssociationField::new('video', '视频'),
            TextField::new('ipAddress', 'IP地址'),
            TextField::new('deviceType', '设备类型'),
            TextField::new('playQuality', '播放质量'),
            IntegerField::new('playDuration', '播放时长(秒)'),
            IntegerField::new('playPosition', '播放进度(秒)'),
            TextField::new('userAgent', '用户代理')->hideOnIndex(),
            TextField::new('referer', '来源页面')->hideOnIndex(),
            TextField::new('playerVersion', '播放器版本')->hideOnIndex(),
            DateTimeField::new('playTime', '播放时间')->setFormat('yyyy-MM-dd HH:mm:ss'),
        ];
    }

    public function viewStats(): void
    {
        $this->addFlash('info', '查看统计功能待实现');
    }
} 