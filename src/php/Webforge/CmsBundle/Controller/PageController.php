<?php

namespace Webforge\CmsBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Webforge\Doctrine\CMS\NavigationBridge as DoctrineBridge;
use stdClass;

class PageController extends CommonController {

  /**
   * @Route("/pages/list", name="pages_list", methods={"GET"})
   */
  public function pagesListAction() {
    $navigationRepository = $this->getRepository('NavigationNode');

    $data = array('navigation'=>$navigationRepository->getRootNode()->exportWithChildren());

    return $this->render('WebforgeCmsBundle:test:navigation-nodes/list.html.twig', array(
      'tabId'=>'pages-list',
      'data'=>json_encode($data, JSON_PRETTY_PRINT)
    ));
  }

  /**
   * @Route("/pages/list", name="save_pages_list", methods={"POST"})
   */
  public function postPagesAction(Request $request) {
    $navigation = $this->retrieveJsonBody($request)->navigation;

    $logger = $this->get('logger');
    $em = $this->dc->getEntityManager();

    try {
      $em->getConnection()->beginTransaction();
      
      $nodeRepository = $this->getRepository('NavigationNode');
      //$pageRepository = $this->getRepository('Page');
      $pageRepository = NULL;
      $controller = $this;
      
      $bridge = new DoctrineBridge($em);
      $bridge->beginTransaction();
      
      $jsonNodes = array(); // all nodes in the gui index by an unique identifier (which is independend from the id from database)

      $persistNode = function (Entity $node, $jsonNode) use ($bridge, $pageRepository, $nodeRepository, &$jsonNodes, $logger, $controller, $em) {
        $node->setI18nTitle((array) $jsonNode->title);
        $node->setParent(isset($jsonNode->parent) ? $jsonNodes[$jsonNode->parent->guid] : NULL); // parent is always defined before (because of order of the navigation array)
        
        $logger->debug(sprintf(
          "persist %snode: '%s'",
          $node->isNew() ? 'new ' : ':'.$node->getIdentifier().' ',
          $node->getTitle()
        ));

        if (isset($jsonNode->pageId) && $jsonNode->pageId > 0) {
          $page = $pageRepository->hydrate($jsonNode->pageId);
          $node->setPage($page);
          $logger->debug('  page: '.$node->getPage()->getSlug());
        } else {
          $defaultSlug = current($node->getI18nSlug());  // no matter what current language is, this is the default language
          $page = $controller->createNewPage($defaultSlug);
          $node->setPage($page);
          
          $em->persist($page);
        }
        
        // flat ist von oben nach unten sortiert:
        // wenn wir also oben anfangen müssen wir die weiteren immmer nach unten anhängen
        if ($node->getParent() != NULL) {
          $logger->debug('  parent: '.$node->getParent()->getTitle());
        }

        $bridge->persist($node);

        // index nach guid damit wir sowohl neue als auch bestehende haben
        $jsonNodes[$jsonNode->guid] = $node;
      };

      /* synchronize */

      $nodesToDelete = array();
      foreach ($nodeRepository->findAllNodes() as $node) {
        $nodesToDelete[$node->getId()] = $node;
      }

      foreach ($navigation as $jsonNode) {
        $jsonNode->id = (int) $jsonNode->id;

        // this will insert silently if wrong id is passed from frontend
        $node = $jsonNode->id > 0 && array_key_exists($jsonNode->id, $nodesToDelete) ? $nodesToDelete[$jsonNode->id] : NULL;

        if ($node === NULL) {
          $persistNode(
            $node = $this->createNewNode($jsonNode),
            $jsonNode
          );
        } else {
          $persistNode($node, $jsonNode);

          unset($nodesToDelete[$node->getId()]);
        }
      }

      foreach ($nodesToDelete as $node) {
        $logger->debug(sprintf("remove node: '%s'", $node->getTitle()));
        $em->remove($node);
      }
      
      $bridge->commit();
      $em->flush();
      $em->getConnection()->commit();

    } catch (\Exception $e) {
      $em->getConnection()->rollback();
      throw $e;
    }
  }

  
  /**
   * Just create one, the attributes will be set automatically
   * 
   * @return Webforge\CMS\Navigation\Node
   */
  public function createNewNode(stdClass $jsonNode) {
    $nodeClass = $this->getEntityFQN('NavigationNode');
    $node = new $nodeClass();
    $node->setI18nTitle((array) $jsonNode->title);
    $node->generateSlugs();

    /*
    $defaultSlug = current($node->getI18nSlug());  // not matter what current language is, this is the default language
    $page = $this->createNewPage($defaultSlug);

    $node->setPage($page);
    */

    return $node;
  }
}
