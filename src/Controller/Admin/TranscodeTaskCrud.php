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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;

/**
 * 转码任务监控
 */
class TranscodeTaskCrud extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TranscodeTask::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('转码任务')
            ->setEntityLabelInPlural('转码任务')
            ->setPageTitle('index', '转码任务监控')
            ->setPageTitle('detail', '转码任务详情')
            ->setDefaultSort(['createdTime' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        $refreshStatus = Action::new('refreshStatus', '刷新状态', 'fa fa-sync')
            ->linkToCrudAction('refreshStatus')
            ->addCssClass('btn btn-info');

        return $actions
            ->add(Crud::PAGE_INDEX, $refreshStatus)
            ->add(Crud::PAGE_DETAIL, $refreshStatus)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('video', '视频'))
            ->add(ChoiceFilter::new('status', '状态')->setChoices([
                '处理中' => 'Processing',
                '成功' => 'TranscodeSuccess',
                '失败' => 'TranscodeFail',
                '取消' => 'TranscodeCancel',
            ]));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            AssociationField::new('video', '视频'),
            TextField::new('taskId', '任务ID'),
            TextField::new('templateId', '模板ID'),
            TextField::new('status', '状态'),
            IntegerField::new('progress', '进度(%)'),
            TextField::new('errorCode', '错误代码')->hideOnIndex(),
            TextareaField::new('errorMessage', '错误信息')->hideOnIndex(),
            DateTimeField::new('createdTime', '创建时间')->setFormat('yyyy-MM-dd HH:mm:ss'),
            DateTimeField::new('completedTime', '完成时间')->setFormat('yyyy-MM-dd HH:mm:ss')->hideOnIndex(),
        ];
    }

    public function refreshStatus(): void
    {
        $this->addFlash('info', '刷新状态功能待实现');
    }
} 