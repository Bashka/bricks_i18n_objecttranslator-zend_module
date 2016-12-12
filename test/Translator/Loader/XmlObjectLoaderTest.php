<?php
namespace Bricks\I18n\ObjectTranslator\UnitTest\Translator\Loader;

use Bricks\I18n\ObjectTranslator\Translator\Loader\XmlObjectLoader;

/**
 * @author Artur Sh. Mamedbekov
 */
class XmlObjectLoaderTest extends \PHPUnit_Framework_TestCase{
  public function testLoad(){
    $loader = new XmlObjectLoader(__DIR__ . '/data');

    $textDomain = $loader->load('ru_RU', 'Article', 1);

    $this->assertEquals('Тестовый заголовок', $textDomain['getTitle']);
    $this->assertEquals('Тестовый контент', $textDomain['getContent']);
  }
}
