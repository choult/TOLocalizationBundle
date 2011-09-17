<?php

namespace TimeOut\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * A Twig Extension that provides
 */
class LocalizationExtension extends \Twig_Extension implements ContainerAwareInterface
{

    /**
     * The container for this Extension
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * The configuration for this Extension
     *
     * @var array
     */
    private $configuration;

    /**
     * The parameter key for this Extentions configuration
     *
     * @var string
     */
    private $configKey;

    /**
     * A list of converters for this Extension
     *
     * @var array
     */
    private $converters = array();

    /**
     * The locale for this Extension to use
     *
     * @var string
     */
    private $locale;

    /**
     * Constructs a new LocalizationExtension
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null, $converters = array(), $configKey = null, $translator = null )
    {
        $this->setContainer($container);
        $this->setConverters($converters);
        $this->configKey = $configKey;
        $this->setTranslator($translator);
        if ($container !== null && $container->has('session'))
        {
            $session = $this->getContainer()->get('session');
            $locale = $session->get('locale');
            $this->setLocale($locale);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns this Extension's Container
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Sets the configuration for this Extension
     *
     * @param array $configuration
     */
    public function setConfiguration($configuration = array())
    {
        $this->configuration = $configuration;
        foreach ( $this->getConverters() as $name => $converter )
        {
            $converter->setUnits($configuration[$name]['units']);
        }
    }

    /**
     * Gets the configuration for this Extension
     *
     * @param string $name If set, returns the configuration for the named key
     *
     * @return array
     */
    public function getConfiguration($name = false)
    {
        if ($this->configuration === null)
        {
            $this->setConfiguration($this->loadConfiguration());
        }

        if ($name!==false)
        {
            return (isset($this->configuration[$name])) ? $this->configuration[$name] : false;
        }
        return $this->configuration;
    }

    /**
     * Loads the configuration for this Extension
     */
    private function loadConfiguration()
    {
        $config = $this->getContainer()->getParameter($this->configKey);
        if (isset($config['locale_path']))
        {
            $path = $config['locale_path'];
            $locale = $this->getLocale();
            $filename = $path.$locale.'.yml';
            if (!file_exists($filename) && ($locale != \locale_get_primary_language($locale)))
            {
                $filename = $path.\locale_get_primary_language($locale).'.yml';
                if (!file_exists($filename))
                {
                    return $config;
                }
            }
            $newConfig = Yaml::parse($filename);
            $config = \array_replace_recursive($config,Yaml::parse($filename));
        }
        return $config;
    }

    /**
     * Sets the Converters for this Extension
     *
     * @param array $converters
     */
    public function setConverters($converters=array())
    {
        $this->converters = $converters;
    }

    /**
     * Gets the Converters for this Extension
     *
     * @return array
     */
    public function getConverters()
    {
        return $this->converters;
    }

    /**
     * Gets a Converter by name
     *
     * @return Converter
     */
    public function getConverter($name)
    {
        $converters = $this->getConverters();
        return (isset($converters[$name])) ? $converters[$name] : false;
    }

    /**
     * Sets the Translator for this Extension
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator = null)
    {
        $this->translator = $translator;
    }

    /**
     * Gets the Translator for this Extension
     *
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Sets the locale for this Extension
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Gets the locale for this Extension
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters() {
        return array(
            'distance' => new \Twig_Filter_Method($this, 'distance'),
            'formatDate' => new \Twig_Filter_Method($this, 'formatDate'),
        );
    }

    /**
     * Formats a date by th
     * @param type $value
     * @param array $arguments
     * @param type $domain
     * @param type $locale
     * @return string
     */
    public function formatDate( $value, $format = null, array $arguments = array(), $domain = "messages", $locale = null )
    {

        $dt = new \DateTime( $value );

        if ( $format == null )
        {
            $config = $this->getConfiguration( 'date' );
            $format = $config[ 'format' ];
            if ( isset( $config[ 'short_format' ] ) && (int)$dt->format( 'Y' ) == (int)date( 'Y' ) )
            {
                $format = $config[ 'short_format' ];
            }
        }

        $str = $dt->format( $format );

        $parts = explode( ' ', $str );
        $ret = '';

        foreach ( $parts as $part )
        {
            $ret .= ( ( $ret ) ? ' ' : '' ) . $this->getTranslator()->trans( $part, $arguments, $domain, $locale );
        }

        return $ret;
    }

    /**
     * A filter to output distance
     *
     * @param float $value The distance to convert
     * @param array $parameters Parameters for the conversion
     */
    public function distance($value, $parameters = array())
    {
        $config = $this->getConfiguration('distance');
        $converter = $this->getConverter('distance');
        return $this->unitFilter($value, 'distance', $parameters);
    }

    /**
     * Returns a converted unit, along with translation
     *
     * @param float $value
     * @param string $domain The domain of the conversion
     * @param array $parameters Parameters to the conversion, overriding the configuration
     * @param string $alternative The optional fallback for the case where there is no Translator for this Extension
     *
     * @return string
     */
    public function unitFilter($value, $domain, $parameters = array(), $alternative='%value%%units%')
    {
        $config = $this->getConfiguration($domain);
        if ($config === false)
        {
            throw new \InvalidArgumentException('Could not load configuration for '.$domain);
        }

        $converter = $this->getConverter($domain);
        if ($converter === false)
        {
            throw new \InvalidArgumentException('Could not load converter for '.$domain);
        }

        $parameters = array_merge($config, $parameters);

        $value = $converter->convertUnits($value, $parameters['from'], $parameters['to']);
        if (isset($parameters[ 'min' ], $parameters[ 'fallback' ]) && $value < $parameters[ 'min' ])
        {
            $parameters = array_merge($parameters, $parameters[ 'fallback' ]);
            $value = $converter->convertUnits($value, $parameters['from'], $parameters['to']);
        }

        $translationString = (isset($config['translation'])) ? $config['translation'] : 'TOLocalization.'.$domain;

        return $this->translate($translationString, $alternative, array('%value%' => round($value, $parameters['rounding']), '%units%' => $parameters['to']));
    }

    /**
     * Translates the given $string if this Extension's Translator is set, otherwise uses $alternative
     *
     * @param string $string The string to translate
     * @param string $alternative Should the Translator not be set, use this pattern instead
     * @param array $parameters Replacements for the translation
     * @param string $domain The domain of the translations - defaults to TOLocalization
     * @param string $locale The optional locale to translate to
     *
     * @return string The translated string
     */
    private function translate($string, $alternative, $parameters = array(), $domain = 'TOLocalization', $locale = null)
    {
        $translator = $this->getTranslator();
        if ($translator !== null)
        {
            return $translator->trans($string, $parameters, $domain, $locale);
        }
        else
        {
            return \strtr($alternative, $parameters);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'to_localization';
    }
}