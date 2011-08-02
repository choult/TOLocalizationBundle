<?php

namespace TimeOut\Localization\Converter;

use TimeOut\Localization\Converter;

class ConverterTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public function testConvert()
    {
        $c = new Converter();

        $this->assertEquals( 1, $c->convert( 1, 1, 1 ) );
        $this->assertEquals( 0.5, $c->convert( 1, 1, 2 ) );
        $this->assertEquals( 2, $c->convert( 1, 2, 1 ) );
    }

    public function testConvertBadFactors()
    {
        $c = new Converter();

        // 0 inputs
        $this->assertEquals( 0, $c->convert( 1, 1, 0 ) );

        try
        {
            $c->convert( 1, 0, 2 );
            $this->fail( 'An InvalidArgumentException should have been thrown for a 0 normalization factor' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a 0 normalization factor - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->convert( 1, '', 2 );
            $this->fail( 'An InvalidArgumentException should have been thrown for a blank normalization factor' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a blank normalization factor - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->convert( 1, 'as', 2 );
            $this->fail( 'An InvalidArgumentException should have been thrown for a non-numeric normalization factor' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a non-numeric normalization factor - ' . get_class( $e ) . ' thrown instead.' ); }
    }

    public function testNormalize()
    {
        $c = new Converter();

        $this->assertEquals( 1, $c->normalize( 1, 1 ) );
        $this->assertEquals( 0.5, $c->normalize( 1, 2 ) );
        $this->assertEquals( 0.5, $c->normalize( 1, 0.5, true ) );
        $this->assertEquals( 1, $c->normalize( 2, 0.5, true ) );

        $this->assertEquals( 2, $c->normalize( 1, function ( $a ) { return 2; } ) );
        $this->assertEquals( 4, $c->normalize( 2, function ( $a ) { return $a * 2; } ) );
        $this->assertTrue( $c->normalize( 2, function ( $a, $b ) { return $b; }, true ) );
    }

    public function testNormalizeBadArguments()
    {
        $c = new Converter();

        try
        {
            $c->normalize( 'a', 0 );
            $this->fail( 'An InvalidArgumentException should have been thrown for a non-numeric value' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a non-numeric value - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->normalize( 1, 0, true );
            $this->fail( 'An InvalidArgumentException should have been thrown for an inverted 0 normalization factor' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for an inverted 0 normalization factor - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->normalize( 1, '' );
            $this->fail( 'An InvalidArgumentException should have been thrown for a blank normalization factor' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a blank normalization factor - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->normalize( 1, 'as' );
            $this->fail( 'An InvalidArgumentException should have been thrown for a non-numeric normalization factor' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a non-numeric normalization factor - ' . get_class( $e ) . ' thrown instead.' ); }
    }

    public function testGetSetUnits()
    {
        $c = new Converter();

        $units = array( 'km' => 1, 'm' => 0.001 );

        $c->setUnits( $units );
        $this->assertEquals( $units, $c->getUnits() );
    }

    public function testGetSetUnit()
    {
        $c = new Converter();
        $units = array( 'km' => 1, 'm' => 0.001 );

        $c->setUnits( $units );

        $this->assertEquals( 1, $c->getUnit( 'km' ) );
        $this->assertEquals( 0.001, $c->getUnit( 'm' ) );

        $func = function ( $a, $b ) { return $a; };

        $c->setUnit( 'km', 2 );
        $this->assertEquals( 2, $c->getUnit( 'km' ) );

        $c->setUnit( 'km', $func );
        $this->assertEquals( $func, $c->getUnit( 'km' ) );

        $this->assertNull( $c->getUnit( 'nothing' ) );
    }

    public function testSetBadUnit()
    {
        $c = new Converter();
        try
        {
            $c->setUnit( '', 1 );
            $this->fail( 'An InvalidArgumentException should have been thrown for a blank unit name' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a blank unit name - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->setUnit( false, 1 );
            $this->fail( 'An InvalidArgumentException should have been thrown for a false unit name' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a false unit name - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->setUnit( 'a', 'b' );
            $this->fail( 'An InvalidArgumentException should have been thrown for a bad conversion factor' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a bad conversion factor - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->setUnit( 'a', '' );
            $this->fail( 'An InvalidArgumentException should have been thrown for a blank conversion factor' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a blank conversion factor - ' . get_class( $e ) . ' thrown instead.' ); }
    }

    public function testConstructor()
    {
        $units = array( 'km' => 1, 'm' => 0.001 );
        $c = new Converter( $units );
        $this->assertEquals( $units, $c->getUnits() );

        $c2 = new Converter();
        $this->assertEquals( array(), $c2->getUnits() );
    }

    public function testHasUnit()
    {
        $units = array( 'km' => 1, 'm' => 0.001 );
        $c = new Converter( $units );

        $this->assertTrue( $c->hasUnit( 'km' ) );
        $this->assertTrue( $c->hasUnit( 'm' ) );
        $this->assertFalse( $c->hasUnit( 'nothing' ) );
    }

    public function testConvertUnit()
    {
        $units = array( 'km' => 1, 'm' => 0.001, 'ft' => 0.0003048, 'yd' => 0.0009144, 'bla' => function( $a ) { return $a / 5; } );
        $c = new Converter( $units );

        $this->assertEquals( 3, $c->convertUnits( 1, 'yd', 'ft' ) );
        $this->assertEquals( 1000, $c->convertUnits( 1, 'km', 'm' ) );
        $this->assertEquals( 10, $c->convertUnits( 50, 'km', 'bla' ) );
    }

    public function testConvertUnitsBad()
    {
        $units = array( 'km' => 1, 'm' => 0.001, 'ft' => '0.0003048', 'yd' => '0.0009144', 'bla' => function( $a ) { return $a / 5; } );
        $c = new Converter( $units );

        try
        {
            $c->convertUnits( 'a', 'km', 'm' );
            $this->fail( 'An InvalidArgumentException should have been thrown for a non-numeric value' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a non-numeric value - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->convertUnits( '', 'km', 'm' );
            $this->fail( 'An InvalidArgumentException should have been thrown for a blank value' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a blank value - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->convertUnits( 1, 'notthere', 'km' );
            $this->fail( 'An InvalidArgumentException should have been thrown for a non-existent from unit' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a non-existent from unit - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->convertUnits( 1, 'km', 'notthere' );
            $this->fail( 'An InvalidArgumentException should have been thrown for a non-existent to unit' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a non-existent to unit - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->convertUnits( 1, '', 'km' );
            $this->fail( 'An InvalidArgumentException should have been thrown for a blank from unit' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a blank from unit - ' . get_class( $e ) . ' thrown instead.' ); }

        try
        {
            $c->convertUnits( 1, 'km', '' );
            $this->fail( 'An InvalidArgumentException should have been thrown for a blank to unit' );
        }
        catch ( \InvalidArgumentException $e ) { /* This should be the desired outcome */ }
        catch ( \Exception $e ) { $this->fail( 'An InvalidArgumentException should have been thrown for a blank to unit - ' . get_class( $e ) . ' thrown instead.' ); }

    }
}
