<?php
namespace Bricks\I18n\ObjectTranslator\Translator;

use Zend\I18n\Translator\TranslatorInterface;

/**
 * @author Artur Sh. Mamedbekov
 */
interface ObjectTranslatorInterface extends TranslatorInterface{
  /**
   * Translate a object property.
   *
   * @param string $getter
   * @param string|int $id
   * @param string $className
   * @param string $locale [optional]
   *
   * @return string
   */
  public function translateObject($getter, $id, $className, $locale = null);
}
