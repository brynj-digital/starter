<?php

namespace Drupal\starter\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class KernelEventSubscriber.
 *
 * @package Drupal\enhanced_link
 */
class KernelResponseSubscriber implements EventSubscriberInterface {

  private $config;

  /**
   * Get module configuration.
   */
  public function getConfig($config) {
    $this->config = $config->get('starter.settings');
  }

  /**
   * The subscribed events.
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => 'onKernelResponse',
    ];
  }

  /**
   * Restricts access on kernel response.
   */
  public function onKernelResponse(FilterResponseEvent $event) {

    if ($event->getRequest()->attributes->get('_route') == 'system.entity_autocomplete') {

      $json_suggested_values = $event->getResponse()->getContent();
      $suggested_values = json_decode($json_suggested_values);
      $return_values = [];

      if ($event->getRequest()->attributes->get('target_type') == 'taxonomy_term' && $this->config->get('term_autocomplete_display_vocabulary')) {

        $vocabularies = entity_load_multiple('taxonomy_vocabulary');

        foreach ($suggested_values as $value) {

          $value_parts = explode('(', $value->value);
          $tid = trim(end($value_parts), ' )');

          $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);

          $return_values[] = [
            'value' => $value->value,
            'label' => $value->label . (!empty($term) ? ' [' . $vocabularies[$term->getVocabularyId()]->label() . ']' : ''),
          ];
        }
      }
      elseif ($event->getRequest()->attributes->get('target_type') == 'node' && $this->config->get('node_autocomplete_display_bundle')) {

        foreach ($suggested_values as $value) {

          $value_parts = explode('(', $value->value);
          $nid = trim(end($value_parts), ' )');

          $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

          $return_values[] = [
            'value' => $value->value,
            'label' => $value->label . (!empty($node) ? ' [' . $node->type->entity->label() . ']' : ''),
          ];
        }
      }
      else {
        $return_values = $suggested_values;
      }

      $json_response = new JsonResponse($return_values);
      $event->setResponse($json_response);
    }
  }

}
