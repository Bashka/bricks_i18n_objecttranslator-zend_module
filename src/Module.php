<?php
namespace Bricks\I18n\ObjectTranslator;

/**
 * @author Artur Sh. Mamedbekov
 */
class Module{
  public function getConfig(){
    return include(__DIR__ . '/../config/module.config.php');
  }
}
