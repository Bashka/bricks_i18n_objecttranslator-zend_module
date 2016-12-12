<?php
namespace Bricks\I18n\ObjectTranslator\Translator\Loader;

/**
 * @author Artur Sh. Mamedbekov
 */
interface ObjectLoaderInterface{
  /**
   * @param string $locale
   * @param string $className
   * @param string|int $id
   */
  public function load($locale, $className, $id);
}
