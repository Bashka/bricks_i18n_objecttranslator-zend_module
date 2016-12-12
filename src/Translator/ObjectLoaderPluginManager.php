<?php
namespace Bricks\I18n\ObjectTranslator\Translator;

use Zend\I18n\Exception\RuntimeException;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\AbstractPluginManager;
use Bricks\I18n\ObjectTranslator\Translator\Loader\ObjectLoaderInterface;
use Bricks\I18n\ObjectTranslator\Translator\Loader\XmlObjectLoader;
use Bricks\I18n\ObjectTranslator\ServiceManager\Factory\XmlObjectLoaderFactory;

/**
 * @author Artur Sh. Mamedbekov
 */
class ObjectLoaderPluginManager extends AbstractPluginManager{
  protected $aliases = [
    'xmlobject'  => XmlObjectLoader::class,
  ];

  protected $factories = [
    XmlObjectLoader::class  => XmlObjectLoaderFactory::class,
    // Legacy (v2) due to alias resolution; canonical form of resolved
    // alias is used to look up the factory, while the non-normalized
    // resolved alias is used as the requested name passed to the factory.
    'bricksi18nobjecttranslatortranslatorloaderxmlobjectloader'  => XmlObjectLoaderFactory::class,
  ];

  /**
   * @param  mixed $plugin
   *
   * @throws InvalidServiceException
   *
   * @return void
   */
  public function validate($plugin){
    if($plugin instanceof ObjectLoaderInterface){
      return;
    }

    throw new InvalidServiceException(sprintf(
      'Plugin of type %s is invalid; must implement %s\Loader\ObjectLoaderInterface',
      (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
      __NAMESPACE__
    ));
  }

  /**
   * For v2.
   *
   * @param mixed $plugin
   *
   * @throws RuntimeException;
   */
  public function validatePlugin($plugin){
    try{
      $this->validate($plugin);
    }
    catch(InvalidServiceException $e){
      throw new RuntimeException(sprintf(
        'Plugin of type %s is invalid; must implement %s\Loader\ObjectLoaderInterface',
        (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
        __NAMESPACE__
      ));
    }
  }
}
