<?php
namespace Bricks\I18n\ObjectTranslator\Translator;

use Zend\EventManager\EventInterface;
use Zend\I18n\Translator\Translator;

/**
 * @author Artur Sh. Mamedbekov
 */
class ObjectTranslatorProxy{
  /**
   * @var ObjectTranslatorInterface
   */
  private $translator;

  /**
   * @var object
   */
  private $object;

  /**
   * @var string|null
   */
  private $locale;

  /**
   * @param ObjectTranslatorInterface $translator
   * @param object $object
   * @param string $locale [optional]
   */
  public function __construct(ObjectTranslatorInterface $translator, $object, $locale = null){
    $this->translator = $translator;
    $this->object = $object;
    $this->locale = $locale;
  }

  public function __call($getter, $arguments){
    $rawResult = $this->object->$getter();

    if(is_object($rawResult) || is_array($rawResult)){
      return $rawResult;
    }

    $isEventManagerEnabled = $this->translator->isEventManagerEnabled();
    $this->translator->enableEventManager();
    $listener = $this->translator->getEventManager()->attach(Translator::EVENT_MISSING_TRANSLATION, function(EventInterface $event) use($rawResult){
      return (string) $rawResult;
    }, 100);

    $domain = get_class($this->object) . '::' . $this->object->getId();
    $locale = is_null($this->locale)? $this->translator->getLocale() : $this->locale;
    $result = $this->translator->translateObject($getter, $this->object->getId(), get_class($this->object), $locale);

    $this->translator->getEventManager()->detach($listener);
    if(!$isEventManagerEnabled){
      $this->translator->disableEventManager();
    }

    return $result;
  }
}
