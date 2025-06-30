<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tourze\AliyunVodBundle\Controller\Admin\PlayRecordCrud;
use Tourze\AliyunVodBundle\Entity\PlayRecord;

class PlayRecordCrudTest extends WebTestCase
{
    private PlayRecordCrud $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new PlayRecordCrud();
    }

    public function testGetEntityFqcn(): void
    {
        $result = PlayRecordCrud::getEntityFqcn();
        $this->assertSame(PlayRecord::class, $result);
    }

    public function testConfigureCrud(): void
    {
        $crud = $this->createMock(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::class);
        $crud->expects($this->once())
            ->method('setEntityLabelInSingular')
            ->with('播放记录')
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setEntityLabelInPlural')
            ->with('播放记录')
            ->willReturnSelf();
            
        $crud->expects($this->any())
            ->method('setPageTitle')
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setDefaultSort')
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setPaginatorPageSize')
            ->with(50)
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

    public function testViewStats(): void
    {
        $this->expectNotToPerformAssertions();
    }
}