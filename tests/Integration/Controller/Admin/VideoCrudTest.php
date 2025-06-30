<?php

namespace Tourze\AliyunVodBundle\Tests\Integration\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tourze\AliyunVodBundle\Controller\Admin\VideoCrud;
use Tourze\AliyunVodBundle\Entity\Video;

class VideoCrudTest extends WebTestCase
{
    private VideoCrud $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new VideoCrud();
    }

    public function testGetEntityFqcn(): void
    {
        $result = VideoCrud::getEntityFqcn();
        $this->assertSame(Video::class, $result);
    }

    public function testConfigureCrud(): void
    {
        $crud = $this->createMock(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::class);
        $crud->expects($this->once())
            ->method('setEntityLabelInSingular')
            ->with('视频')
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setEntityLabelInPlural')
            ->with('视频')
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

    public function testPlayVideo(): void
    {
        $this->expectNotToPerformAssertions();
    }

    public function testGenerateSnapshot(): void
    {
        $this->expectNotToPerformAssertions();
    }

    public function testSubmitTranscode(): void
    {
        $this->expectNotToPerformAssertions();
    }

    public function testViewStats(): void
    {
        $this->expectNotToPerformAssertions();
    }
}