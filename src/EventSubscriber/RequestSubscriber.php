<?php 

namespace Drupal\advanced_search_tips\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Drupal\Core\Routing\RouteMatchInterface;

class RequestSubscriber implements EventSubscriberInterface {

  protected $routeMatch;

  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 0];
    return $events;
  }

  private function hasValidQueryFormat(array $queryParams) {
    foreach ($queryParams as $key => $values) {
      // if not a, is hacked
      if ($key != "a"  && is_array($values)) {
        if ($key == "f" && count($values) < 2) {
          return TRUE;
        }
        return FALSE;
      }
      
      // check the value
      if (is_array($values)) {
        $condition = 0; 
        foreach ($values as $value) {
          if (array_key_exists('f', $value) && array_key_exists('v', $value)) {
            $condition++;
          }
        }
        if ($condition % 2 == 0) {
          return TRUE;
        }
        else {
          return FALSE;
        }
      }
      
    }
    return false;
  }

  private function isNestedArray(array $array) {
    foreach ($array as $value) {
      if (is_array($value)) {
        return true;
      }
    }
    return false;
  }

  public function onRequest(RequestEvent $event) {
    $config = \Drupal::config('advanced_search.settings');
    if (isset($config) && $config->get("search_request_validation") === 1) {
      $request = $event->getRequest();
      // not ajax request
      if (!($request->isXmlHttpRequest())) {
        // Check if the current route is a view.
        $route_name = $this->routeMatch->getRouteName();
        if (strpos($route_name, 'view.') === 0) {
            // Extract the view ID from the route name.
            $parts = explode('.', $route_name);
            $view_id = $parts[1];

            if ($view_id === "advanced_search") {
              // Your custom logic here.
              $query_params = $request->query->all();
              
              if (!empty($query_params) && $this->isNestedArray($query_params)) { 
                // Check if the required query parameters are present.
                if (!$this->hasValidQueryFormat($query_params)) {
                    // Throw Exception 
                    throw new BadRequestHttpException();
                }
              }
            }
        }
      }
    }
  }
}