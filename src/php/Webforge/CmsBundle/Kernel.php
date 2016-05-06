<?php

namespace Webforge\CmsBundle;

use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Webforge\Common\System\Dir;

class Kernel extends SymfonyKernel
{
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

        return $loader;
    }

    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \Webforge\CmsBundle\WebforgeCmsBundle(),
            new \Liip\ImagineBundle\LiipImagineBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),
            new \FOS\UserBundle\FOSUserBundle(),
            new \Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            //$bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            //$bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new \h4cc\AliceFixturesBundle\h4ccAliceFixturesBundle();
            $bundles[] = new \Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
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

    protected function getEnvParameters() 
    {
        $env = parent::getEnvParameters();

        // please note: this is executed BEFORE the config is loaded, so these parameters are just a default
        // => everything is overwritable by the config.yml
        return array_merge(
          $env,
          array(
              'root_directory'=>$GLOBALS['env']['root']->wtsPath()
          )
        );
    }


    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/../etc/symfony/config_'.$this->getEnvironment().'.yml');
    }
}
