<?php
/**
 * @file
 *   Contains \Drupal\starter\Form\StarterSettingsForm.
 */

namespace Drupal\starter\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Configure site information settings for this site.
 */
class StarterSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['starter.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'starter_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $starter_config = $this->config('starter.settings');

    $form['description'] = array(
      '#type' => 'item',
      '#title' => $this->t('Starter settings'),
      '#description' => $this->t('Set up various configuration options.'),
    );

    $form['security'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Security settings'),
    );

    $form['security']['login_path'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Login Path'),
      '#default_value' => $starter_config->get('paths.login'),
      '#description' => $this->t('Change the Drupal user login path. (Cache clear required.)'),
    );

    $form['security']['logout_path'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Logout Path'),
      '#default_value' => $starter_config->get('paths.logout'),
      '#description' => $this->t('Change the Drupal user logout path.  (Cache clear required.)'),
    );

    $form['disable_access'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Disable direct access settings'),
    );

    $form['disable_access']['disable_access_path'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Disabled Path'),
      '#default_value' => $starter_config->get('paths.disable_access'),
      '#description' => $this->t('Entities that contain this string as part of their path alias will only be directly accessible to logged in users.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('starter.settings')
      ->set('paths.login', $form_state->getValue('login_path'))
      ->set('paths.logout', $form_state->getValue('logout_path'))
      ->set('paths.disable_access', str_replace('/','-',$form_state->getValue('disable_access_path')))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
