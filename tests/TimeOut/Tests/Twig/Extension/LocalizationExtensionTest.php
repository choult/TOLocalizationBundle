<?php

namespace TimeOut\Tests\Twig\Extension;

use TimeOut\Twig\Extension\LocalizationExtension;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Translation\TranslatorInterface;

class LocalizationExtensionTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public function testGetSetContainer()
    {
        $container = $this->getContainer();
        $extension = new LocalizationExtension();

        $this->assertNull( $extension->getContainer(), 'Extension container should be null initially' );

        $extension->setContainer( $container );

        $this->assertEquals( $container, $extension->getContainer() );

        $extension->setContainer( null );
        $this->assertNull( $extension->getContainer(), 'Extension container should be null initially' );
    }

    public function testGetSetConverters()
    {
        $extension = new LocalizationExtension();

        $this->assertEquals(array(), $extension->getConverters());
        $this->assertFalse($extension->getConverter('undefined'));

        $arr = array( 'a' => 'b', 'c' => 'd' );
        $extension->setConverters($arr);
        $this->assertEquals($arr, $extension->getConverters());
        $this->assertEquals($arr['a'], $extension->getConverter('a'));
        $this->assertFalse($extension->getConverter('e'));
    }

    public function testGetSetTranslator()
    {
        $translator = $this->getMock( 'Symfony\Component\Translation\TranslatorInterface' );
        $extension = new LocalizationExtension();

        $this->assertNull($extension->getTranslator());

        $extension->setTranslator($translator);
        $this->assertEquals($translator, $extension->getTranslator());

        $extension->setTranslator(null);
        $this->assertNull($extension->getTranslator());
    }

    public function testGetSetLocale()
    {
       $extension = new LocalizationExtension();

       $this->assertNull($extension->getLocale());

       $extension->setLocale('test');
       $this->assertEquals('test', $extension->getLocale());

       $extension->setLocale(null);
       $this->assertNull($extension->getLocale());
    }

    public function testGetSetConfiguration()
    {
        $this->markTestIncomplete();
    }

    public function testGetFilters()
    {
        $extension = new LocalizationExtension();
        $filters = $extension->getFilters();
        $filterNames = array('distance');
        foreach ($filterNames as $filterName)
        {
            $this->assertTrue(isset($filters[$filterName]));
            $this->assertTrue(($filters[$filterName] instanceof \Twig_Filter_Method) || ($filters[$filterName] instanceof \Twig_Filter_Function));
        }
    }

    public function testDistance()
    {
        $this->markTestIncomplete();
    }

    public function testUnitFilter()
    {
        $this->markTestIncomplete();
    }

    public function testGetName()
    {
        $extension = new LocalizationExtension();
        $this->assertEquals( 'to_localization', $extension->getName());
    }

    protected function getContainer()
    {
        return new Container( new ParameterBag() );
    }
}