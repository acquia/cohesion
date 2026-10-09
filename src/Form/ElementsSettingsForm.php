<?php

namespace Drupal\cohesion\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\RoleStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the  form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class ElementsSettingsForm extends ConfigFormBase {

  /**
   * The role storage.
   *
   * @var \Drupal\user\RoleStorageInterface
   */
  protected $roleStorage;

  /**
   * Constructs a new UserPermissionsForm.
   *
   * @param \Drupal\user\PermissionHandlerInterface $permission_handler
   *   The permission handler.
   * @param \Drupal\user\RoleStorageInterface $role_storage
   *   The role storage.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(RoleStorageInterface $role_storage) {
    $this->roleStorage = $role_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self($container->get('entity_type.manager')->getStorage('user_role'));
  }

  /**
   * Gets the roles to display in this form.
   *
   * @return \Drupal\user\RoleInterface[]
   *   An array of role objects.
   */
  protected function getRoles() {
    return $this->roleStorage->loadMultiple();
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $group = 'elements';
    $type = '__ALL__';
    $assetLibrary = \Drupal::keyValue('cohesion.assets.' . $group);

    [$error, $data, $message] = \Drupal::service('settings.endpoint.utils')->getAssets(FALSE, $assetLibrary, $type, $group, TRUE);

    $config = $this->config('cohesion.settings');
    $perms = ($config && $config->get("elements_permissions")) ? $config->get("elements_permissions") : "{}";
    $perms = Json::decode($perms);

    $role_names = [];
    $admin_roles = [];
    foreach ($this->getRoles() as $role_name => $role) {
      // Retrieve role names for columns.
      $role_names[$role_name] = $role->label();
      $admin_roles[$role_name] = $role->isAdmin();
    }

    // Store $role_names for use when saving the data.
    $form['role_names'] = [
      '#type' => 'value',
      '#value' => $role_names,
    ];

    $form['permissions'] = [
      '#type' => 'table',
      '#header' => [$this->t('Permission')],
      '#id' => 'element_permissions',
      '#attributes' => ['class' => ['permissions', 'js-permissions']],
      '#sticky' => TRUE,
    ];
    foreach ($role_names as $name) {
      $form['permissions']['#header'][] = [
        'data' => $name,
        'class' => ['checkbox'],
      ];
    }

    foreach ($data['categories'] as $element_category) {
      $form['permissions'][$element_category['title']] = [
        [
          '#wrapper_attributes' => [
            'colspan' => count($role_names) + 1,
            'class' => ['module'],
            'id' => 'module-' . $element_category['title'],
          ],
          '#markup' => $element_category['label'],
        ],
      ];

      foreach ($element_category['children'] as $element) {
        $form['permissions'][$element['uid']]['description'] = [
          '#type' => 'inline_template',
          '#template' => '<div class="permission"><span class="title">{{ title }}</span></div>',
          '#context' => [
            'title' => $element['title'],
          ],
        ];
        foreach ($role_names as $rid => $name) {
          $perm = 0;
          if (isset($perms[$rid][$element['uid']])) {
            $perm = $perms[$rid][$element['uid']];
          }
          elseif ($rid != AccountInterface::ANONYMOUS_ROLE) {
            $perm = 1;
          }
          $form['permissions'][$element['uid']][$rid] = [
            '#title' => $name . ': ' . $element['title'],
            '#title_display' => 'invisible',
            '#wrapper_attributes' => [
              'class' => ['checkbox'],
            ],
            '#type' => 'checkbox',
            '#default_value' => $perm,
            '#attributes' => ['class' => ['rid-' . $rid, 'js-rid-' . $rid]],
            '#parents' => [$rid, $element['uid']],
          ];
          // Show a column of disabled but checked checkboxes.
          if ($admin_roles[$rid]) {
            $form['permissions'][$element['uid']][$rid]['#disabled'] = TRUE;
            $form['permissions'][$element['uid']][$rid]['#default_value'] = TRUE;
          }
        }
      }
    }

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = ['#type' => 'actions'];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save permissions'),
      '#button_type' => 'primary',
    ];

    $form['#attached']['library'][] = 'user/drupal.user.permissions';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cohesion.settings',
    ];
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller.  it must
   * be unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'cohesion_elements_settings_form';
  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $perms = [];
    foreach ($form_state->getValue('role_names') as $role_name => $name) {
      $perms[$role_name] = (array) $form_state->getValue($role_name);
    }

    if ($config = $this->config('cohesion.settings')) {
      $config->set('elements_permissions', Json::encode($perms))->save();
    }

    parent::submitForm($form, $form_state);
  }

}
