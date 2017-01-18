<?php
namespace Drupal\starter\EventSubscriber;

use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\Core\Routing\RouteBuildEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RouteSubscriber implements EventSubscriberInterface {

  private $config;

  /*
   * get module configuration
   */
  public function getConfig($config) {
    $this->config = $config->get('starter.settings');
  }

  /**
  * {@inheritdoc}
  */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::ALTER] = 'alterRoutes';
    return $events;
  }

  /**
  * Alters existing routes.
  *
  * @param \Drupal\Core\Routing\RouteBuildEvent $event
  *   The route building event.
  */
  public function alterRoutes(RouteBuildEvent $event) {

    // Fetch the collection which can be altered.
    $collection = $event->getRouteCollection();
    // The event is fired multiple times so ensure that the user_page route
    // is available.
    // alter user login url
    if ($route = $collection->get('user.login')) {
      $login = $this->config->get('paths.login');
      // make sure path starts with /
      if(strpos($login,'/')!==0) {
          $login = '/'.$login;
      }
      $route->setPath($login);
    }
    // alter user logout url
    if ($route = $collection->get('user.logout')) {
      $logout = $this->config->get('paths.logout');
      // make sure path starts with /
      if(strpos($logout,'/')!==0) {
          $logout = '/'.$logout;
      }
      $route->setPath($logout);
    }
    // prevent access to default front page view
    if ($route = $collection->get('view.frontpage.page_1')) {
      $route->setRequirement('_access', 'false');
    }
  }
}
