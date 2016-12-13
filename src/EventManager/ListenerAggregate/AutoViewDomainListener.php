<?php
namespace Bricks\I18n\ObjectTranslator\EventManager\ListenerAggregate;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventInterface;
use Bricks\I18n\ObjectTranslator\Translator\ObjectTranslatorInterface;

/**
 * @author Artur Sh. Mamedbekov
 */
class AutoViewDomainListener extends AbstractListenerAggregate{
  /**
   * @var ObjectTranslatorInterface
   */
  private $translator;

  /**
   * @var string
   */
  private $defaultDomain;

  /**
   * @param ObjectTranslatorInterface $translator
   * @param string $defaultDomain [optional]
   */
  public function __construct(ObjectTranslatorInterface $translator, $defaultDomain = 'global'){
    $this->translator = $translator;
    $this->defaultDomain = $defaultDomain;
  }

  /**
   * {@inheritdoc}
   */
  public function attach(EventManagerInterface $events, $priority = 1){
    $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch']);
  }

  /**
   * @param EventInterface $e
   */
  public function onDispatch(EventInterface $e){
    $routeMatch = $e->getRouteMatch();
    $this->translator->setDefaultDomain($routeMatch->getParam('controller', $this->defaultDomain));
  }
}
