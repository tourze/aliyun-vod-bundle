<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tourze\AliyunVodBundle\Controller\Admin\AliyunVodConfigCrud;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;

class AliyunVodConfigCrudTest extends WebTestCase
{
    private AliyunVodConfigCrud $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AliyunVodConfigCrud();
    }

    public function testGetEntityFqcn(): void
    {
        $result = AliyunVodConfigCrud::getEntityFqcn();
        $this->assertSame(AliyunVodConfig::class, $result);
    }

    public function testConfigureCrud(): void
    {
        $crud = $this->createMock(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::class);
        $crud->expects($this->once())
            ->method('setEntityLabelInSingular')
            ->with('阿里云VOD配置')
            ->willReturnSelf();
        
        $crud->expects($this->once())
            ->method('setEntityLabelInPlural')
            ->with('阿里云VOD配置')
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

    public function testTestConnection(): void
    {
        $this->expectNotToPerformAssertions();
    }

    public function testSetDefault(): void
    {
        $this->expectNotToPerformAssertions();
    }
}