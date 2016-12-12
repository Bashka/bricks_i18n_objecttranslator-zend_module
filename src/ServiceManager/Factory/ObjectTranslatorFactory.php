<?php
namespace Bricks\I18n\ObjectTranslator\ServiceManager\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Bricks\I18n\ObjectTranslator\Translator\ObjectTranslator;

/**
 * @author Artur Sh. Mamedbekov
 */
class ObjectTranslatorFactory implements FactoryInterface{
  /**
   * For v3.
   *
   * {@inheritdoc}
   */
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
    $config = $container->get('Configuration');
    $trConfig = isset($config['translator']) ? $config['translator'] : [];

    return ObjectTranslator::factory($trConfig);
  }

  /**                                                                                                                                              
   * For v2.                                                                                                   
   *                                                                                                            
   *  {@inheritdoc}                                                                                             
   */                                                                                                          
  public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null){ 
    return $this($container, $requestedName?: ConverterInterface::class, []);   
  } 
}
