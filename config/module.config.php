<?php
namespace Bricks\I18n\ObjectTranslator;

use Zend\I18n\Translator\LoaderPluginManager;
use Bricks\I18n\ObjectTranslator\Translator\ObjectTranslatorInterface;
use Bricks\I18n\ObjectTranslator\ServiceManager\Factory\ObjectTranslatorFactory;

return [
  'service_manager' => [
    'factories' => [
      ObjectTranslatorInterface::class => ObjectTranslatorFactory::class,
    ],
  ],
  'translator' => [
    // Objects translators.
    'translation_objects' => [
      // Example.
      // [
      //   'class' => '*', // For all classes.
      //   'type' => 'xmlobject', // Use XmlObjectLoader.
      //   'options' => [
      //     'storage_path' => 'data/object_translator', // Path to data storage.
      //   ],
      // ]
    ],
  ],
];
