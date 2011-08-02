<?php

namespace TimeOut\Localization;

/**
 * A class to convert from one base unit to another
 *
 * Conversion factors should be relative to a base unit; it is recommended that conversion factors should be in
 * SI terms, eg. 'metre' => 1, 'kilometre' => 1000, 'yard' => 0.9144, 'foot' => 0.3048
 *
 * @package TOLocalizationBundle
 * @subpackage Localization
 */
class Converter
{

    /**
     * An array of unit => conversionFactor
     *
     * @var array
     */
    private $units = array();

    /**
     * Constructs a new Converter
     *
     * @param array $units An optional array of unit => conversionFactor
     */
    public function __construct( array $units = array() )
    {
        $this->setUnits( $units );
    }

    /**
     * Sets the conversion factor for the given unit name
     *
     * Values will be divided by $factor for normalization, and multiplied by $factor for conversion
     *
     * @param string $unit
     * @param float $factor
     *
     * @throws InvalidArgumentException If $unit is blank
     * @throws InvalidArgumentException If $factor is non-numeric and not callable
     */
    public function setUnit( $unit, $factor )
    {
        if ( !$unit )
        {
            throw new \InvalidArgumentException( 'The unit name must not be blank' );
        }

        if ( !is_numeric( $factor ) && !is_callable( $factor ) )
        {
            throw new \InvalidArgumentException( 'The conversion factor must be numeric or callable' );
        }
        $this->units[ $unit ] = $factor;
    }

    /**
     * Returns the given $unit's conversion factor
     *  - if the unit is undefined, returns null
     *
     * @param string $unit
     *
     * @return mixed Either a float or a callable
     */
    public function getUnit( $unit )
    {
        if ( $this->hasUnit( $unit ) )
        {
            return $this->units[ $unit ];
        }
        return null;
    }

    /**
     * Gets the list of units to use with Converter::convertUnits()
     *
     * @return array An associative array of unitName => normalization factor
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * Sets the list of units to use with Converter::convertUnits()
     *
     * @param array $units An associative array of unitName => normalization factor
     *
     * @throws InvalidArgumentException If $unit is blank
     * @throws InvalidArgumentException If $factor is non-numeric and not callable
     */
    public function setUnits( array $units )
    {
        $this->units = array();
        foreach ( $units as $unit => $factor )
        {
            try
            {
                $this->setUnit( $unit, $factor );
            }
            catch ( \InvalidArgumentException $e )
            {
                throw new \InvalidArgumentException( 'There was an error setting the unit "' . $unit . '" to "' . $factor . '"', $e->getCode(), $e );
            }
        }
    }

    /**
     * Returns whether this Converter has the given unit
     *
     * @param string $unit
     *
     * @return bool
     */
    public function hasUnit( $unit )
    {
        return isset( $this->units[ $unit ] );
    }

    /**
     * Converts $value from a named base unit to another named unit
     *
     * @param float $value
     * @param string $fromUnits
     * @param string $toUnits
     *
     * @throws InvalidArgumentException If $value is not numeric
     * @throws InvalidArgumentException If $fromUnits or $toUnits are not known
     *
     * @return float
     */
    public function convertUnits( $value, $fromUnits, $toUnits )
    {
        if ( !is_numeric( $value ) )
        {
            throw new \InvalidArgumentException( 'Value is not numeric' );
        }

        if ( !$this->hasUnit( $fromUnits ) || !$this->hasUnit( $toUnits ) )
        {
            throw new \InvalidArgumentException( "Unknown convesion $fromUnits => $toUnits" );
        }

        return $this->convert( $value, $this->getUnit( $fromUnits ), $this->getUnit( $toUnits ) );
    }

    /**
     * Converts $value from one base factor to another
     *
     * @param float $value
     * @param mixed $normalizationFactor
     * @param mixed $conversionFactor
     *
     * @throws InvalidArgumentException If $value is non-numeric or if there is an issue with normalization (see Converter::normalize())
     *
     * @return float
     */
    public function convert( $value, $normalizationFactor, $conversionFactor )
    {
        if ( !is_callable( $conversionFactor ) && $conversionFactor == 0 )
        {
            return 0;
        }
        try
        {
            $normalized = $this->normalize( $value, $normalizationFactor, true );
            return $this->normalize( $normalized, $conversionFactor );
        }
        catch ( \InvalidArgumentException $e )
        {
            throw new \InvalidArgumentException( 'Normalization error', $e->getCode(), $e );
        }
    }

    /**
     * Normalizes the passed $value by the factor $normalizationFactor
     *
     * @param float $value
     * @param mixed $normalizationFactor - In the case of a float, $value is divided by $normalizationFactor. A closure will be called with $value and $invert
     * @param bool $invert Set to true, inverts $normalizationFactor - a zero $normalizationFactor is allowed with this. Defaults to false
     *
     * @throws InvalidArgumentException If $value is non-numeric
     * @throws InvalidArgumentException If $normalizationFactor is non-numeric, or zero when $invert is false
     *
     * @return float
     */
    public function normalize( $value, $normalizationFactor, $invert = false )
    {
        if ( !is_numeric( $value ) )
        {
            throw new \InvalidArgumentException( 'The value to normalize must be numeric' );
        }

        if ( is_callable( $normalizationFactor ) )
        {
            return $normalizationFactor( $value, $invert );
        }
        elseif ( !is_numeric( $normalizationFactor ) || ( $normalizationFactor == 0 && $invert === true ) )
        {
            throw new \InvalidArgumentException( 'The normalization factor must be a non-zero number or callable' );
        }

        if ( $invert )
        {
            $normalizationFactor = 1 / $normalizationFactor;
        }

        return $value / $normalizationFactor;
    }

}
