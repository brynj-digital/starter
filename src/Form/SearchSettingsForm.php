<?php

/**
 * @file
 * Contains \Drupal\starter\Form\SearchSettingsForm.
 */

namespace Drupal\starter\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SearchSettings.
 *
 * @package Drupal\starter\Form
 */
class SearchSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exclude_content_types_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'starter.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('starter.settings');
    $configured_bundles = $config->get('bundles') ? $config->get('bundles') : [];

    // get list of current Content Types
    $node_bundles = \Drupal::service('entity.manager')->getStorage('node_type')->loadMultiple();

    $options = [];
    foreach ($node_bundles as $bundle) {
      $options[$bundle->id()] = $bundle->label();
    }

    $form['bundles'] = array(
      '#type' => 'checkboxes',
      '#options' => $options,
      '#title' => t('Content types to exclude from Drupal core search'),
      '#default_value' => $configured_bundles,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('starter.settings')
      ->set('bundles', $form_state->getValue('bundles'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
