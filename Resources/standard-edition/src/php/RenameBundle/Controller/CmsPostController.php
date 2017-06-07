<?php

namespace %project.bundle_namespace%\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Webforge\Common\DateTime\DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Webforge\Symfony\FormError;
use %project.bundle_namespace%\Entity\Post;
use %project.bundle_namespace%\Entity\PostImage;
use %project.bundle_namespace%\Entity\Binary;
use Symfony\Component\Validator\ExecutionContextInterface;
use Webforge\Common\ArrayUtil as A;

class CmsPostController extends \Webforge\CmsBundle\Controller\CommonController {

  /**
   * @Route("/cms/posts/list", name="cms_posts_list")
   * @Method("GET")
   */
  public function listAction() {
    $user = $this->getUser();

    $qb = $this->dc->getRepository('Post')->createQueryBuilder('p');
    $qb->orderBy('p.created', 'DESC');

    $exportedPosts = $this->get('object_graph')->serializePosts($qb->getQuery()->getResult(), array('post-list'));

    return $this->render('%project.bundle_name%:admin:post/list.html.twig', array(
      'id'=>'posts-list-context',
      'data'=>array('posts'=>$exportedPosts)
    ));
  }

  /**
   * @Route("/cms/posts")
   * @Method("GET")
   */
  public function newFormAction() {
    $user = $this->getUser();

    $data = array('isNew'=>true, 'entity'=>NULL);

    return $this->postForm('post-form-create-context', $data);
  }

  /**
   * @Route("/cms/posts/{postId}")
   * @Method("GET")
   */
  public function editFormAction($postId) {
    $user = $this->getUser();
    $og = $this->get('object_graph');

    $data = array(
      'isNew'=>false,
      'entity'=>$og->serializePost($this->dc->getRepository('Post')->findOneBy(array('id'=>$postId)), array('post-details'))
    );
    unset($data['entity']->contentsJson);

    return $this->postForm('post-form-'.$postId.'-context', $data);
  }

  protected function postForm($id, Array $data) {
    $og = $this->get('object_graph');
    $data['categories']  = $og->serializeCategories($this->dc->getRepository('Category')->findAll());
    $data['allPosts']  = $og->serializePosts($this->dc->getRepository('Post')->findAll(), array('post-list'));

    return $this->render('%project.bundle_name%:admin:post/form.html.twig', array(
      'id'=>$id,
      'data'=>$data
    ));
  }

  /**
   * @Route("/cms/posts")
   * @Method("POST")
   */
  public function createAction(Request $request) {
    $post = new Post();
    $post->setCreated(DateTime::now());
    $this->em->persist($post);

    return $this->updatePostAction($post, $request);
  }

  /**
   * @Route("/cms/posts/{postId}")
   * @Method("PUT")
   */
  public function putAction($postId, Request $request) {
    $post = $this->dc->getRepository('Post')->findOneBy(array('id'=>$postId));

    return $this->updatePostAction($post, $request);
  }

  protected function updatePostAction(Post $post, Request $request) {
    $user = $this->getUser();
    $this->convertJsonToForm($request);

    $manager = $this->get('webforge.media.manager');
    $dc = $this->get('dc');

    $form = $this->get('form.factory')->createNamedBuilder(null, 'form', $post, array('csrf_protection'=>false))
      ->add('id', 'integer', array(
        'mapped'=>false
      ))
      ->add('published', 'webforge_iso8601_date_time', array(
        //'constraints'=>array(new Assert\NotBlank())
      ))
      ->add('title', 'text', array(
        'constraints'=>array(new Assert\NotBlank())
      ))
      ->add('slug', 'text', array(
        'mapped'=>false
      ))
      ->add('url', 'text', array(
        'mapped'=>false
      ))
      ->add('contentsJson', 'text', array(
        'mapped'=>false
      ))
      ->add('contents', 'text', array(
      ))
      ->add('teaserMarkdown', 'text', array(
        'constraints'=>array(
          //new Assert\NotBlank()
        )
      ))
      ->add('categories', 'text', array(
        'mapped'=>false,
        'constraints'=>array(
           new Assert\NotBlank(array(
             'message'=>'categories.empty'
           )),
           new Assert\Callback(function($categoriesJson, ExecutionContextInterface $context) use ($dc, $post) {
             $synchronizer = $dc->getCollectionSynchronizer('Post', 'categories');

             $synchronizer->setHydrator(function(\stdClass $jsonCategory) use ($dc) {
               return $dc->hydrate('Category', array('id'=>$jsonCategory->id));
             });

             $synchronizer->setMerger(function() {}); // we wont need to update the category

             $synchronizer->process($post, $post->getCategories(), $categoriesJson);
           })
        )
      ))
      ->add('images', 'text', array(
        'invalid_message'=>'images.error',
        'mapped'=>false, // we do this in the callback of the validator
        'constraints' => array(

            new Assert\Callback(function($fileItems, ExecutionContextInterface $context) use ($dc, $post, $manager) {
              // find binaries for images
              $keys = array();
              foreach ((array) $fileItems as $fileItem) {
                $keys[] = $fileItem->key;
              }

              $binaries = $manager->findFiles($keys);
              $binaries = A::indexBy($binaries, 'getMediaFileKey');

              // hydrate child objects for PostImage and set position explicit
              $imagesJson = array();
              $position = 1;
              foreach ((array) $fileItems as $image) {
                if (array_key_exists($image->key, $binaries)) {
                  $binary = $binaries[$image->key];

                  $imagesJson[] = (object) [
                    'binary'=>$binary,
                    'position'=>$position++
                  ];
                }
              }

              $dbImages = $post->getImages()->toArray();
              $dbImages = A::indexBy($dbImages, function($image) {
                return $image->getBinary()->getMediaFileKey();
              });

              $synchronizer = $dc->getCollectionSynchronizer('Post', 'images');

              $synchronizer->setCreater(function($image, $post) {
                return new PostImage($image->binary, $post, $image->position);
              });
              $synchronizer->setAdder(function($post, $image) {
                $post->addImage($image);
              });
              $synchronizer->setRemover(function($post, $postImage) use ($dc) {
                $post->removeImage($postImage);
                $dc->remove($postImage);
              });

              $synchronizer->setHydrator(function (\stdClass $image) use ($dbImages) {
                if (array_key_exists($image->binary->getMediaFileKey(), $dbImages)) {
                  return $dbImages[$image->binary->getMediaFileKey()];
                }

                return NULL;
              });


              $synchronizer->process($post, $post->getImages(), $imagesJson);
            })
          )
        )
      )
      ->getForm();

    $form->bind($request);

    if ($form->isValid()) {
      if (trim($post->getSlug()) == "") {
        $post->updateSlug();
      }
      $post->setUpdated(DateTime::now());

      $this->em->flush();
      
      $exportedPost = $this->get('object_graph')->serializePost($post, array('post-details'));
      return new JsonResponse($exportedPost, 200);
    }

    $formError = new FormError();

    return new JsonResponse(
      (object) $formError->wrap($form),
      400
    );
  }

  /**
   * @Route("/cms/posts/delete")
   * @Method("POST")
   */
  public function deleteAction(Request $request) {
    $user = $this->getUser();
    
    $json = $this->retrieveJsonBody($request);
    $postRepo = $this->dc->getRepository('Post');
    $removed = array();
    foreach ($json->ids as $id) {
      $post = $postRepo->findOneBy(array('id'=>$id));

      if ($post) {
        $removed[] = $post->getId();
        $this->em->remove($post);
      }
    }
    $this->em->flush();

    return new JsonResponse((object) array('removed'=>$removed), 200);
  }
}
