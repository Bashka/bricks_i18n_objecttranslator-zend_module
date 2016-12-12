<?php
namespace Bricks\I18n\ObjectTranslator\ServiceManager\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Bricks\I18n\ObjectTranslator\Translator\Loader\ObjectLoaderInterface;
use Bricks\I18n\ObjectTranslator\Translator\Loader\XmlObjectLoader;

/**
 * @author Artur Sh. Mamedbekov
 */
class XmlObjectLoaderFactory implements FactoryInterface{
  /**
   * zend-servicemanager v2 support for invocation options.
   *
   * @var array
   */
  private $creationOptions;

  /**
   * For v3.
   *
   * {@inheritdoc}
   */
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
    return new XmlObjectLoader($options['storage_path']);
  }

  /**
   * For v2.
   *
   * {@inheritdoc}
   */
  public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null){
    return $this($container, $requestedName?: ObjectLoaderInterface::class, $this->creationOptions);
  }

  /**
   * @param array $creationOptions
   */
   public function setCreationOptions($creationOptions){
     $this->creationOptions = $creationOptions;
   }
}
