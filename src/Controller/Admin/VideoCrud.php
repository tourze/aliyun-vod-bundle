<?php

namespace Tourze\AliyunVodBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Tourze\AliyunVodBundle\Entity\Video;

/**
 * 视频管理
 */
class VideoCrud extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Video::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('视频')
            ->setEntityLabelInPlural('视频')
            ->setPageTitle('index', '视频管理')
            ->setPageTitle('new', '新增视频')
            ->setPageTitle('edit', '编辑视频')
            ->setPageTitle('detail', '视频详情')
            ->setDefaultSort(['createdTime' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        $playVideo = Action::new('playVideo', '播放', 'fa fa-play')
            ->linkToCrudAction('playVideo')
            ->addCssClass('btn btn-success');

        $generateSnapshot = Action::new('generateSnapshot', '生成截图', 'fa fa-camera')
            ->linkToCrudAction('generateSnapshot')
            ->addCssClass('btn btn-info');

        $submitTranscode = Action::new('submitTranscode', '提交转码', 'fa fa-cogs')
            ->linkToCrudAction('submitTranscode')
            ->addCssClass('btn btn-warning');

        $viewStats = Action::new('viewStats', '播放统计', 'fa fa-chart-bar')
            ->linkToCrudAction('viewStats')
            ->addCssClass('btn btn-primary');

        return $actions
            ->add(Crud::PAGE_INDEX, $playVideo)
            ->add(Crud::PAGE_INDEX, $generateSnapshot)
            ->add(Crud::PAGE_INDEX, $submitTranscode)
            ->add(Crud::PAGE_INDEX, $viewStats)
            ->add(Crud::PAGE_DETAIL, $playVideo)
            ->add(Crud::PAGE_DETAIL, $generateSnapshot)
            ->add(Crud::PAGE_DETAIL, $submitTranscode)
            ->add(Crud::PAGE_DETAIL, $viewStats);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('config', '配置'))
            ->add(ChoiceFilter::new('status', '状态')->setChoices([
                '上传中' => 'Uploading',
                '上传完成' => 'UploadSucc',
                '转码中' => 'Transcoding',
                '转码完成' => 'TranscodeSucc',
                '审核中' => 'Checking',
                '审核通过' => 'Normal',
                '审核失败' => 'Blocked',
            ]))
            ->add(BooleanFilter::new('valid', '有效状态'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')
                ->hideOnForm(),

            AssociationField::new('config', '配置')
                ->setRequired(true)
                ->setHelp('选择使用的阿里云VOD配置'),

            TextField::new('videoId', '视频ID')
                ->setRequired(true)
                ->setHelp('阿里云视频ID'),

            TextField::new('title', '标题')
                ->setRequired(true)
                ->setHelp('视频标题'),

            TextareaField::new('description', '描述')
                ->setRequired(false)
                ->setHelp('视频描述')
                ->hideOnIndex(),

            IntegerField::new('duration', '时长(秒)')
                ->setRequired(false)
                ->setHelp('视频时长，单位：秒')
                ->hideOnForm(),

            IntegerField::new('size', '文件大小(字节)')
                ->setRequired(false)
                ->setHelp('文件大小，单位：字节')
                ->hideOnIndex()
                ->hideOnForm(),

            TextField::new('status', '状态')
                ->setRequired(true)
                ->setHelp('视频状态'),

            ImageField::new('coverUrl', '封面')
                ->setBasePath('/')
                ->setUploadDir('public/')
                ->setRequired(false)
                ->hideOnIndex(),

            UrlField::new('playUrl', '播放地址')
                ->setRequired(false)
                ->hideOnIndex(),

            TextField::new('tags', '标签')
                ->setRequired(false)
                ->setHelp('视频标签，多个标签用逗号分隔'),

            BooleanField::new('valid', '有效状态')
                ->setHelp('是否有效'),

            DateTimeField::new('createdTime', '创建时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss'),

            DateTimeField::new('updatedTime', '更新时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->hideOnIndex(),
        ];
    }

    /**
     * 播放视频
     */
    public function playVideo(): void
    {
        // TODO: 实现播放视频功能
        $this->addFlash('info', '播放视频功能待实现');
    }

    /**
     * 生成截图
     */
    public function generateSnapshot(): void
    {
        // TODO: 实现生成截图功能
        $this->addFlash('info', '生成截图功能待实现');
    }

    /**
     * 提交转码
     */
    public function submitTranscode(): void
    {
        // TODO: 实现提交转码功能
        $this->addFlash('info', '提交转码功能待实现');
    }

    /**
     * 查看播放统计
     */
    public function viewStats(): void
    {
        // TODO: 实现查看播放统计功能
        $this->addFlash('info', '播放统计功能待实现');
    }
} 