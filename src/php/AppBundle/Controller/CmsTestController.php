<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Webforge\CmsBundle\Controller\CommonController;

class CmsTestController extends CommonController
{

    /**
     * @Route("/cms/", name="test_index", methods={"GET"})
     */
    public function indexAction()
    {
        return $this->render('WebforgeCmsBundle::base.html.twig', array(
            'data' => '{}',

            'user' => array(
                'displayName' => $this->getUser()->getDisplayName()
            ),

            'sidebar' => array(
                'activeGroup' => 1,
                'groups' => array(
                    'CMS' => array(
                        array(
                            'label' => 'Benutzer verwalten',
                            'tab' => array(
                                'label' => 'Benutzer verwalten',
                                'id' => 'users-list',
                                'url' => '/cms/users/list'
                            )
                        ),
                        array(
                            'label' => 'Bilder verwalten',
                            'tab' => array(
                                'label' => 'Datei-Manager',
                                'id' => 'file-manager',
                                'url' => '/cms/media/file-manager'
                            )
                        ),
                    ),
                    'Webseite' => array(
                        array(
                            'label' => 'Seiten verwalten',
                            'tab' => array(
                                'label' => 'Seiten verwalten',
                                'id' => 'pages-list',
                                'url' => '/cms/pages/list'
                            )
                        )
                    ),
                    'Navigation' => array(
                        array(
                            'label' => 'Hauptnavigation pflegen',
                            'tab' => array(
                                'label' => 'Hauptnavigation',
                                'id' => 'main-navigation',
                                'url' => '/cms/navigation/main'
                            )
                        )
                    )
                )
            )
        ));
    }

    /**
     * @Route("/cms/dashboard", name="test_dashboard", methods={"GET"})
     */
    public function dashboardAction()
    {
        $user = $this->getUser();

        return $this->render('AppBundle:test:dashboard.html.twig', array(
            'user' => array(
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
            ),

            'tapir' => 'tapire',
            'tapir' => 'mini-single'
        ));
    }

    /**
     * @Route("/cms/users/list", name="test_users_list", methods={"GET"})
     */
    public function usersListAction()
    {
        return $this->render('WebforgeCmsBundle:test:users/list.html.twig', array());
    }

    /**
     * @Route("/cms/pages/list", name="test_pages_list", methods={"GET"})
     */
    public function pagesListAction()
    {
        $navigationRepository = $this->getRepository('NavigationNode');

        $data = array('navigation' => $navigationRepository->getRootNode()->exportWithChildren());

        return $this->render('WebforgeCmsBundle:test:navigation-nodes/list.html.twig', array(
            'tabId' => 'pages-list',
            'data' => json_encode($data, JSON_PRETTY_PRINT)
        ));
    }

    /**
     * @Route("/prototypes/content-manager", methods={"GET"})
     */
    public function protoContentManagerAction()
    {
        return $this->render('AppBundle:test/prototypes:content-manager.html.twig', array());
    }

    /**
     * @Route("/prototypes/file-manager", methods={"GET"})
     */
    public function protoFileManagerAction()
    {
        return $this->render('AppBundle:test/prototypes:file-manager.html.twig', array());
    }
}
