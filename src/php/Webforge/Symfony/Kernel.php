<?php

namespace Webforge\Symfony;

use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Webforge\Common\System\Dir;
use Dotenv\Dotenv;

class Kernel extends SymfonyKernel
{
    protected $configDirectory = 'etc/symfony';

    /**
     * Bootstrap code from project
     *
     * this is called from a project that has the cms installed, from its own bootstrap:
     *
     * ```php
     *     $loader = require __DIR__.'/vendor/autoload.php';
     *     return Webforge\CmsBundle\Kernel::bootstrap(__DIR__, $loader);
     * ```
     *
     * this keeps the bootstrap from installed edition very small and gives us power to refactor the bootstrapping process (been there with psc-cms)
     * 
     * @param  string $rootDir the dir where the project is installed
     * @param  Composer\Autoload\ClassLoader $loader
     * @return mixed
     */
    public static function bootstrap($rootDir, $loader)
    {
        AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

        $GLOBALS['env']['root'] = Dir::factoryTS($rootDir);

        require $GLOBALS['env']['root']->getFile('app/AppKernel.php');

        if (getenv('SYMFONY_ENV') == FALSE) {
            $dotenv = new Dotenv($GLOBALS['env']['root']->wtsPath());
            $dotenv->overload();
            $dotenv->required('SYMFONY_ENV')->notEmpty()->allowedValues(['production', 'staging', 'dev', 'test']);
            $dotenv->required('SYMFONY_DEBUG')->isInteger()->allowedValues([0,1]);
        }

        return $loader;
    }

    public function registerBundles()
    {
        $bundles = array();
        
        $bundles[] = new \Symfony\Bundle\FrameworkBundle\FrameworkBundle();
        $bundles[] = new \Symfony\Bundle\SecurityBundle\SecurityBundle();
        $bundles[] = new \Symfony\Bundle\TwigBundle\TwigBundle();
        $bundles[] = new \Symfony\Bundle\MonologBundle\MonologBundle();
        $bundles[] = new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle();
        $bundles[] = new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle();
        $bundles[] = new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle();
        $bundles[] = new \FOS\UserBundle\FOSUserBundle();
        $bundles[] = new \Webforge\UserBundle\WebforgeUserBundle();
        $bundles[] = new \JMS\SerializerBundle\JMSSerializerBundle();
        $bundles[] = new \Knp\Bundle\GaufretteBundle\KnpGaufretteBundle();
        $bundles[] = new \Knp\Bundle\MarkdownBundle\KnpMarkdownBundle();
        $bundles[] = new \Jb\Bundle\PhumborBundle\JbPhumborBundle();
        $bundles[] = new \Webforge\CmsBundle\WebforgeCmsBundle();


        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();

            $bundles[] = new \Liip\FunctionalTestBundle\LiipFunctionalTestBundle();

            $bundles[] = new \Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle();
            $bundles[] = new \Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle();
        }

        return $bundles;
    }
    
    public function getCacheDir()
    {
        return $this->rootDir.'/../files/cache/symfony-'.$this->environment;
    }

    public function getLogDir()
    {
        return $this->rootDir.'/../files/logs/symfony';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // this does not work as prepend extension config, because we need to load configuration from files for a lot of extensions (the symfony api is not very enhanced there)
        try {
          $loader->load('@WebforgeCmsBundle/Resources/config/prepend-configuration.yml');
        } catch (\InvalidArgumentException $e) {
        }
        $loader->load($this->getProjectDir().'/'.$this->configDirectory.'/config_'.$this->getEnvironment().'.yml');
    }
}
