<?php

namespace Webforge\Symfony\Form;

use Webforge\Common\DateTime\DateTime;
use Webforge\Symfony\DateTimeHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\CallbackTransformer;

class Iso8601DateTimeType extends AbstractType {

  public function buildForm(FormBuilderInterface $builder, array $options) {
    if ($options['mapped']) {
      $builder
        ->addViewTransformer(new CallbackTransformer(
          function ($dateTime) {
            if ($dateTime === NULL)
              return NULL;

            if (!($dateTime instanceof DateTime)) {
              throw new UnexpectedTypeException($dateTime, 'DateTime');
            }

            return DateTimeHandler::export($dateTime);
          },

          function ($formValue) {
            if ($formValue === NULL) {
              return NULL;
            }

            if (is_string($formValue)) {
              try {
                return DateTimeHandler::parse($formValue);

              } catch (Webforge\Common\DateTime\ParsingException $e) {
                throw new TransformationFailedException('Cannot convert data: "'.$formValue.'" to Webforge DateTime object: '.$e->getMessage(), 0, $e);
              }
            }
          }
        ))
      ;
    }
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver) {
    $resolver->setDefaults(array(
      'compound'=>false, // grmpf, if this is true somehow the dateTime from the entity is used as view data (which is complete nonsense)
      'csrf_protection' => false,
      'invalid_message' => 'Cannot convert data to Webforge DateTime object',
      'empty_data'=>function($form) {
        return NULL;
      }
    ));
  }

  public function getName() {
    return 'webforge_iso8601_date_time';
  }
}
