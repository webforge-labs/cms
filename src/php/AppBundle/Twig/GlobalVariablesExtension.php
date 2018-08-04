<?php

namespace AppBundle\Twig;

use Twig_Extension;
use Twig_Extension_GlobalsInterface;

class GlobalVariablesExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    public function getGlobals()
    {
        $globals = [
            'frontendDebug' => true,

            'cms' => [
                'title' => 'Webforge Test-CMS',
                'xsTitle' => 'Test CMS',
                'version' => 'dev',

                'site' => [
                    'title' => 'zur Webseite',
                    'url' => '/'
                ]
            ]
        ];

        return $globals;
    }

    public function getName()
    {
        return 'appbundle_global_variables_extension';
    }
}
