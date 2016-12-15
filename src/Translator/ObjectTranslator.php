<?php
namespace Bricks\I18n\ObjectTranslator\Translator;

use Zend\I18n\Translator\Translator as ZendTranslator;
use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\Event;
use Zend\I18n\Exception\RuntimeException;
use Zend\I18n\Exception\InvalidArgumentException;
use Bricks\I18n\ObjectTranslator\Translator\Loader\ObjectLoaderInterface;

class ObjectTranslator extends ZendTranslator implements ObjectTranslatorInterface{
  /**
   * @var string
   */
  private $defaultDomain = 'global';

  /**
   * Object locations for loading messages.
   *
   * @var array
   */
  protected $object = [];

  /**
   * @var ObjectPluginManager
   */
  protected $objectPluginManager;

  public static function factory($options){
    $translator = parent::factory($options);

    // objects
    if (isset($options['translation_objects'])) {
      if (!is_array($options['translation_objects'])) {
        throw new InvalidArgumentException(
          '"translation_objects" should be an array'
        );
      }

      $requiredKeys = ['type', 'class'];
      foreach ($options['translation_objects'] as $object) {
        foreach ($requiredKeys as $key) {
          if (!isset($object[$key])) {
            throw new InvalidArgumentException(
              "'{$key}' is missing for translation object options"
            );
          }
        }

        $translator->addTranslationObject(
          $object['class'],
          $object['type'],
          (isset($object['options'])? $object['options'] : [])
        );
      }
    }

    return $translator;
  }

  public function __construct(){
    $this->resetDefaultDomain();
  }

  /**
   * @param string $domain
   */
  public function setDefaultDomain($domain){
    $this->defaultDomain = $domain;
  }

  /**
   * @return string
   */
  public function getDefaultDomain(){
    return $this->defaultDomain;
  }

  public function resetDefaultDomain(){
    $this->defaultDomain = 'global';
  }

  /**
   * {@inheritdoc}
   */
  public function translate($message, $textDomain = 'default', $locale = null){
    if($textDomain == 'default'){
      $textDomain = $this->defaultDomain;
    }

    return parent::translate($message, $textDomain, $locale);
  }

  /**
   * {@inheritdoc}
   */
  public function translatePlural(
      $singular,
      $plural,
      $number,
      $textDomain = 'default',
      $locale = null
  ){
    if($textDomain == 'default'){
      $textDomain = $this->defaultDomain;
    }

    return parent::translatePlural($singular, $plural, $number, $textDomain, $locale);
  }

  /**
   * @param ObjectLoaderPluginManager $objectPluginManager
   *
   * @return ObjectTranslator
   */
  public function setObjectPluginManager(ObjectLoaderPluginManager $objectPluginManager){
    $this->objectPluginManager = $objectPluginManager;

    return $this;
  }

  /**
   * @return ObjectLoaderPluginManager
   */
  public function getObjectPluginManager(){
    if(!$this->objectPluginManager instanceof ObjectLoaderPluginManager){
      $this->setObjectPluginManager(new ObjectLoaderPluginManager(new ServiceManager));
    }

    return $this->objectPluginManager;
  }

  /**
   * Add object translations.
   *
   * @param string $class
   * @param string $type
   * @param array $options [optional]
   *
   * @return ObjectTranslator
   */
  public function addTranslationObject($class, $type, array $options = []){
    if(!isset($this->object[$class])){
      $this->object[$class] = [];
    }

    $this->object[$class][] = [
      'name' => $type,
      'options' => $options,
    ];

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function translateObject($getter, $id, $className, $locale = null){
    $locale = ($locale ?: $this->getLocale());
    $translation = $this->getObjectTranslatedMessage($getter, $id, $className, $locale);

    if($translation !== null){
      return $translation;
    }

    if(null !== ($fallbackLocale = $this->getFallbackLocale())
      && $locale !== $fallbackLocale
    ){
      return $this->translateObject($getter, $id, $className, $fallbackLocale);
    }

    return $getter;
  }

  protected function getObjectTranslatedMessage($message, $id, $class, $locale){
    $textDomain = $class . '::' . $id;

    if($message === '' || $message === null){
      return '';
    }

    if(!isset($this->messages[$textDomain][$locale])){
      $this->loadObjectMessages($id, $class, $locale);
    }

    if(isset($this->messages[$textDomain][$locale][$message])){
      return $this->messages[$textDomain][$locale][$message];
    }

    if($this->isEventManagerEnabled()){
      $until = function ($r){
        return is_string($r);
      };

      $event = new Event(self::EVENT_MISSING_TRANSLATION, $this, [
        'message'     => $message,
        'locale'      => $locale,
        'text_domain' => $textDomain,
      ]);

      $results = $this->getEventManager()->triggerEventUntil($until, $event);

      $last = $results->last();
      if(is_string($last)){
        return $last;
      }
    }

    return;
  }

  protected function loadObjectMessages($id, $class, $locale){
    $textDomain = $class . '::' . $id;
    if(!isset($this->messages[$textDomain])){
      $this->messages[$textDomain] = [];
    }

    if(null !== ($cache = $this->getCache())){
      $cacheId = 'Zend_I18n_Translator_Messages_' . md5($textDomain . $locale);

      if(null !== ($result = $cache->getItem($cacheId))){
        $this->messages[$textDomain][$locale] = $result;

        return;
      }
    }

    $messagesLoaded  = false;
    $messagesLoaded |= $this->loadMessagesFromObject($id, $class, $locale);

    if(!$messagesLoaded){
      $discoveredTextDomain = null;
      if($this->isEventManagerEnabled()){
        $until = function($r){
          return ($r instanceof TextDomain);
        };

        $event = new Event(self::EVENT_NO_MESSAGES_LOADED, $this, [
          'locale'      => $locale,
          'text_domain' => $textDomain,
        ]);

        $results = $this->getEventManager()->triggerEventUntil($until, $event);

        $last = $results->last();
        if($last instanceof TextDomain){
          $discoveredTextDomain = $last;
        }
      }

      $this->messages[$textDomain][$locale] = $discoveredTextDomain;
      $messagesLoaded = true;
    }

    if($messagesLoaded && $cache !== null){
      $cache->setItem($cacheId, $this->messages[$textDomain][$locale]);
    }
  }

  protected function loadMessagesFromObject($id, $class, $locale){
    $textDomain = $class . '::' . $id;
    $messagesLoaded = false;

    $loaders = [];
    if(isset($this->object[$class])){
      $loaders = array_merge($loaders, $this->object[$class]);
    }
    if(isset($this->object['*'])){
      $loaders = array_merge($loaders, $this->object['*']);
    }

    foreach($loaders as $loaderConfig){
      $loader = $this->getObjectPluginManager()->get($loaderConfig['name'], $loaderConfig['options']);
      if (!$loader instanceof ObjectLoaderInterface) {
        throw new RuntimeException('Specified loader is not a object loader');
      }

      $class = isset($loaderConfig['options']['class_name'])? $loaderConfig['options']['class_name'] : $class;
      $messages = $loader->load($locale, $class, $id);
      if(is_null($messages)){
        continue;
      }

      if(isset($this->messages[$textDomain][$locale])){
        $this->messages[$textDomain][$locale]->merge($messages);
      }
      else{
        $this->messages[$textDomain][$locale] = $messages;
      }
      
      $messagesLoaded = true;
    }

    return $messagesLoaded;
  }

  /**
   * @param object $object
   * @param string $locale [optional]
   *
   * @return ObjectTranslatorProxy
   */
  public function wrapObject($object, $locale = null){
    return new ObjectTranslatorProxy($this, $object, $locale);
  }
}
