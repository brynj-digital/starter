<?php
/**
 * @file
 *   Contains \Drupal\starter\Form\CopyRolePermissionsForm.
 */

namespace Drupal\starter\Form;

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
      '#required' => true,
    );

    $form['permissions']['destination_role'] = array(
      '#type' => 'select',
      '#title' => $this->t('Destination Role'),
      '#options' => $user_roles,
      '#empty_option' => '- None -',
      '#empty_value' => '',
      '#default_value' => null,
      '#required' => true,
    );


    $form['permissions']['preserve_permissions'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Preserve existing destination role permissions?'),
      '#default_value' => true,
    );

    // Submit button.
    $form['permissions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Copy role permissions'),
    );

    return $form;
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
    drupal_set_message($this->t('Role "'.$values['source_role'].'" successfully copied to "'.$values['destination_role'].'"'));
    return;
  }
}
