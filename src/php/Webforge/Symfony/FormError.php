<?php

namespace Webforge\Symfony;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

class FormError {

  public function wrap(FormInterface $form) {
    $exception = array();
    $exception['validation'] = $this->convertFormErrors($form);

    return $exception;
  }

  public function convertFormErrors(FormInterface $form) {
    // dont use object here because serializer is to stupid for it
    $info = array( 
      'errors'=>array()
    );

    foreach (($errors = $form->getErrors($deep = true, $flatten = true)) as $key => $error) {
      //\Doctrine\Common\Util\Debug::dump($error, 3);

      $info['errors'][$key] = array(
        'message'=>$error->getMessage(), // will be translated
        'field'=>$this->convertField($error),
        'params'=>$error->getMessageParameters()
      );
    }

    return $info;
  }

  protected function convertField($error) {
    if (!$error->getCause()) {
      return NULL;
    }

    $stringPath = $error->getCause()->getPropertyPath();

    if (empty($stringPath)) {
      return NULL;
    }

    $path = new PropertyPath($stringPath);

    $elements = $path->getElements();
    array_shift($elements);

    // somehow data needs to be stripped too because of this path: contentStreams.0.entries.data.0.linkLabel where does this data come from?
    $textPath = implode('.', array_filter($elements, function($elem) { return $elem !== 'children' && $elem !== 'data'; }));

    return array(
      'path'=>$textPath,
      'name'=>$path->getElement($path->getLength()-1)
    );
  }
}
