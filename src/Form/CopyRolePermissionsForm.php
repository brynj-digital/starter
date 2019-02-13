<?php

namespace Drupal\starter\Form;

use Drupal\user\Entity\Role;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

/**
 * Configure site information settings for this site.
 */
class CopyRolePermissionsForm extends FormBase {

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

    $form['permissions'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Copy Role Permissions'),
    ];

    $form['permissions']['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Copy the permissions belonging to the source role to the destination role, optionally preserving the destination role\'s existing permissions.'),
    ];

    // Get user roles.
    $user_roles = user_role_names(TRUE);
    // Remove following special case roles.
    unset($user_roles['administrator']);
    unset($user_roles['authenticated']);

    $form['permissions']['source_role'] = [
      '#type' => 'select',
      '#title' => $this->t('Source Role'),
      '#options' => $user_roles,
      '#empty_option' => '- None -',
      '#empty_value' => '',
      '#default_value' => NULL,
      '#required' => TRUE,
    ];

    $form['permissions']['destination_role'] = [
      '#type' => 'select',
      '#title' => $this->t('Destination Role'),
      '#options' => $user_roles,
      '#empty_option' => '- None -',
      '#empty_value' => '',
      '#default_value' => NULL,
      '#required' => TRUE,
    ];

    $form['permissions']['preserve_permissions'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Preserve existing destination role permissions?'),
      '#default_value' => TRUE,
    ];

    // Submit button.
    $form['permissions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Copy role permissions'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Extract the values submitted by the user.
    //
    $values = $form_state->getValues();

    // Do we need to copy role permissions from one role to another?
    if (!empty($values['source_role']) && !empty($values['destination_role'])) {

      $source_role = Role::load($values['source_role']);
      $destination_role = Role::load($values['destination_role']);

      // Remove existing permissions?
      if (!$values['preserve_permissions']) {
        // Remove existing permissions from destination role.
        foreach ($destination_role->getPermissions() as $permission) {
          $destination_role->revokePermission($permission);
        }
        // Save role with revoked permissions.
        $destination_role->save();
      }

      // Add source role permissions to destination role.
      foreach ($source_role->getPermissions() as $permission) {
        if (!$destination_role->hasPermission($permission)) {
          $destination_role->grantPermission($permission);
        }
      }
      // Save role with granted permissions.
      $destination_role->save();
    }
    drupal_set_message($this->t('Role "' . $values['source_role'] . '" successfully copied to "' . $values['destination_role'] . '"'));
    return;
  }

}
