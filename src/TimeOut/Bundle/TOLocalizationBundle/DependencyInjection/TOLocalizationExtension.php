<?php

namespace TimeOut\Bundle\TOLocalizationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\Config\FileLocator;

class TOLocalizationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $converters = array('distance');
        $base = __DIR__.'/../Resources/config/';
        $config = array();
        foreach ($converters as $converter)
        {
            $config[$converter] = Yaml::parse($base.$converter.'.yml');
            $container->get('to.twig.localization.'.$converter)->setUnits($config[$converter]['units']);
        }

        $config['locale_path'] = $base.'locales/';
        $container->setParameter('to.twig.localization.configuration', $config);
    }
}