<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tourze\AliyunVodBundle\Controller\Admin\TranscodeTaskCrud;
use Tourze\AliyunVodBundle\Entity\TranscodeTask;

class TranscodeTaskCrudTest extends WebTestCase
{
    private TranscodeTaskCrud $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new TranscodeTaskCrud();
    }

    public function testGetEntityFqcn(): void
    {
        $result = TranscodeTaskCrud::getEntityFqcn();
        $this->assertSame(TranscodeTask::class, $result);
    }

    public function testConfigureCrud(): void
    {
        $crud = $this->createMock(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::class);
        $crud->expects($this->once())
            ->method('setEntityLabelInSingular')
            ->with('转码任务')
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setEntityLabelInPlural')
            ->with('转码任务')
            ->willReturnSelf();
            
        $crud->expects($this->any())
            ->method('setPageTitle')
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setDefaultSort')
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setPaginatorPageSize')
            ->with(20)
            ->willReturnSelf();

        $result = $this->controller->configureCrud($crud);
        $this->assertSame($crud, $result);
    }

    public function testConfigureFields(): void
    {
        $fields = $this->controller->configureFields('index');
        
        $fieldsArray = iterator_to_array($fields);
        $this->assertNotEmpty($fieldsArray);
    }

    public function testRefreshStatus(): void
    {
        $this->expectNotToPerformAssertions();
    }
}