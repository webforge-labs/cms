<?php

namespace Webforge\CmsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class TestController extends CommonController {

  /**
   * @Route("/", name="test_index")
   * @Method("GET")
   */
  public function indexAction() {
    return $this->render('WebforgeCmsBundle::base.html.twig', array(
      'data'=>'{}',

      'sidebar'=>array(
        'groups'=>array(
          'CMS'=>array(
            array(
              'label'=>'Benutzer verwalten',
              'tab'=>array(
                'label'=>'Benutzer verwalten',
                'id'=>'users-list',
                'url'=>'/cms/users/list'
              )
            )
          ),
          'Webseite'=>array(
            array(
              'label'=>'Seiten verwalten',
              'tab'=>array(
                'label'=>'Seiten verwalten',
                'id'=>'pages-list',
                'url'=>'/cms/pages/list'
              )
            )
          ),
          'Navigation'=>array(
            array(
              'label'=>'Hauptnavigation pflegen',
              'tab'=>array(
                'label'=>'Hauptnavigation',
                'id'=>'main-navigation',
                'url'=>'/cms/navigation/main'
              )
            )
          )
        )
      )
    ));
  }

  /**
   * @Route("/cms/dashboard", name="test_dashboard")
   * @Method("GET")
   */
  public function dashboardAction() {
    return $this->render('WebforgeCmsBundle:test:dashboard.html.twig', array(
      'user'=>array(
        'name'=>'Imme'
      ),
 
      'tapir'=>'tapire',
      'tapir'=>'mini-single'
    ));
  }

  /**
   * @Route("/cms/users/list", name="test_users_list")
   * @Method("GET")
   */
  public function usersListAction() {
    return $this->render('WebforgeCmsBundle:test:users/list.html.twig', array());
  }
}
