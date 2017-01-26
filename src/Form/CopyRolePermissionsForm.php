<?php
/**
 * @file
 *   Contains \Drupal\starter\Form\CopyRolePermissionsForm.
 */

namespace Drupal\starter\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Configure site information settings for this site.
 */
class CopyRolePermissionsForm extends ConfigFormBase {

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
    return 'starter_copy_form_permissions';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['permissions'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Copy Role Permissions'),
    );

    $form['permissions']['description'] = array(
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Copy the permissions belonging to the source role to the destination role, optionally preserving the destination role\'s existing permissions.'),
    );

    // get user roles
    $user_roles = user_role_names(true);
    // remove following special case roles
    unset($user_roles['administrator']);
    unset($user_roles['authenticated']);

    $form['permissions']['source_role'] = array(
      '#type' => 'select',
      '#title' => $this->t('Source Role'),
      '#options' => $user_roles,
      '#empty_option' => '- None -',
      '#empty_value' => '',
      '#default_value' => null,
    );

    $form['permissions']['destination_role'] = array(
      '#type' => 'select',
      '#title' => $this->t('Destination Role'),
      '#options' => $user_roles,
      '#empty_option' => '- None -',
      '#empty_value' => '',
      '#default_value' => null,
    );


    $form['permissions']['preserve_permissions'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Preserve existing destination role permissions?'),
      '#default_value' => true,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // Extract the values submitted by the user.
    $values = $form_state->getValues();#

    // check user has selected both source and destination roles
    if(!empty($values['source_role']) && empty($values['destination_role'])) {
      $form_state->setErrorByName('destination_role', $this->t('Please select a destination role.'));
    }
    elseif(empty($values['source_role']) && !empty($values['destination_role'])) {
      $form_state->setErrorByName('source_role', $this->t('Please select a source role.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Extract the values submitted by the user.
    $values = $form_state->getValues();#

    // do we need to copy role permissions from one role to another?
    if(!empty($values['source_role']) && !empty($values['destination_role'])) {

      $source_role = \Drupal\user\Entity\Role::load($values['source_role']);
      $destination_role = \Drupal\user\Entity\Role::load($values['destination_role']);

      // remove existing permissions?
      if(!$values['preserve_permissions']) {
        // remove existing permissions from destination role
        foreach($destination_role->getPermissions() as $permission) {
          $destination_role->revokePermission($permission);
        }
        // save role with revoked permissions
        $destination_role->save();
      }

      // add source role permissions to destination role
      foreach($source_role->getPermissions() as $permission) {
        if(!$destination_role->hasPermission($permission)) {
          $destination_role->grantPermission($permission);
        }
      }
      // save role with granted permissions
      $destination_role->save();
    }
    parent::submitForm($form, $form_state);
  }

}
