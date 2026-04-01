<?php

namespace Drupal\advanced_search_tips\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * The RequestSubscriber class.
 */
class RequestSubscriber implements EventSubscriberInterface {

  // phpcs:ignore -- Missing member variable doc comment
  protected $routeMatch;

  /**
   * Constructs a new RequestSubscriber object.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * Registers the onRequest event handler.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 0];
    return $events;
  }

  /**
   * Checks if the query parameters have a valid format for advanced search.
   */
  private function hasValidQueryFormat(array $queryParams) {
    foreach ($queryParams as $key => $values) {
      // If not a, is hacked.
      if ($key != "a"  && is_array($values)) {
        if ($key == "f" && count($values) < 5) {
          return TRUE;
        }
        return FALSE;
      }

      // Check the value.
      if (is_array($values)) {
        $condition = TRUE;
        foreach ($values as $value) {
          if (array_key_exists('f', $value) && array_key_exists('v', $value)) {
            $condition &= TRUE;
          }
          else {
            $condition &= FALSE;
          }
        }
        return $condition;
      }

    }
    return FALSE;
  }

  /**
   * Determines if the given array is nested.
   */
  private function isNestedArray(array $array) {
    foreach ($array as $value) {
      if (is_array($value)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Handles the request event.
   */
  public function onRequest(RequestEvent $event) {
    // phpcs:ignore -- \Drupal calls should be avoided in classes, use dependency injection instead
    $config = \Drupal::config('advanced_search.settings');
    if (isset($config) && $config->get("search_request_validation") === 1) {
      $request = $event->getRequest();
      $referer = $request->headers->get('referer');

      // Not ajax request.
      if (!($request->isXmlHttpRequest())) {
        // Check if the current route is a view.
        $route_name = $this->routeMatch->getRouteName();
        if (strpos($route_name, 'view.') === 0) {
          if (explode('.', $route_name)[1] === "advanced_search") {
            // Your custom logic here.
            $query_params = $request->query->all();

            if ((!$referer) && !empty($query_params) && $this->isNestedArray($query_params)) {
              // Check if the required query parameters are present.
              if (!$this->hasValidQueryFormat($query_params)) {
                // Throw Exception.
                throw new BadRequestHttpException();
              }
            }
          }
        }
      }
    }
  }

}
