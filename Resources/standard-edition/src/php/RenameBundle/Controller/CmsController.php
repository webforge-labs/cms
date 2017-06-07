<?php

namespace %project.bundle_namespace%\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class CmsController extends \Webforge\CmsBundle\Controller\CommonController {

  /**
   * @Route("/cms/", name="cms_home")
   * @Method("GET")
   */
  public function indexAction() {
    return $this->render('WebforgeCmsBundle::base.html.twig', array(
      'data'=>'{}',

      'user'=>array(
        'displayName'=>$this->getUser()->getDisplayName()
      ),

      'sidebar'=>array(
        'activeGroup'=>1,
        'groups'=>array(
          /*
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
          */
          'Seite'=>array(
            array(
              'label'=>'Posts verwalten',
              'tab'=>array(
                'label'=>'Posts verwalten',
                'id'=>'posts-list',
                'url'=>'/cms/posts/list'
              )
            ),
            array(
              'label'=>'Bilder verwalten',
              'tab'=>array(
                'label'=>'Datei-Manager',
                'id'=>'file-manager',
                'url'=>'/cms/media/file-manager'
              )
            ),
          )
        )
      )
    ));
  }

  /**
   * @Route("/cms/dashboard", name="cms_dashboard")
   * @Method("GET")
   */
  public function dashboardAction() {
    $user = $this->getUser();

    return $this->render('WebforgeCmsBundle:test:dashboard.html.twig', array(
      'user'=>array(
        'firstName'=>$user->getFirstName(),
        'lastName'=>$user->getLastName(),
      ),
 
      'tapir'=>'tapire',
//      'tapir'=>'mini-single'
    ));
  }
}
