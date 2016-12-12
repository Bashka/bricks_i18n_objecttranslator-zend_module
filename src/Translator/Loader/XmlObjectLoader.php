<?php
namespace Bricks\I18n\ObjectTranslator\Translator\Loader;

use DOMDocument;
use Zend\I18n\Translator\TextDomain;

/**
 * @author Artur Sh. Mamedbekov
 */
class XmlObjectLoader implements ObjectLoaderInterface{
  /**
   * @var string Путь до каталога хранилища.
   */
  private $storagePath;

  /**
   * @param string $storagePath Путь до каталога хранилища.
   */
  public function __construct($storagePath){
    $this->storagePath = $storagePath;
  }

  /**
   * {@inheritdoc}
   */
  public function load($locale, $className, $id){
    $path = $this->storagePath . '/' . $locale;
    if(!is_dir($path)){
      return null;
    }

    $path .= '/' . str_replace('\\', '_', $className);
    if(!is_dir($path)){
      return null;
    }

    $path .= '/' . $id . '.xml';
    if(!is_file($path)){
      return null;
    }

    $textDomain = new TextDomain;
    $document = new DOMDocument('1.0', 'utf-8');
    $document->load($path);
    foreach($document->documentElement->getElementsByTagName('message') as $messageNode){
      $textDomain[$messageNode->getAttribute('name')] = $messageNode->nodeValue;
    }

    return $textDomain;
  }
}
