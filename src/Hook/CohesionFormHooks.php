<?php

namespace Drupal\cohesion\Hook;

use Drupal\cohesion\Services\TextFormatStyles;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\editor\EditorInterface;

/**
 * Form Hook implementations for Cohesion.
 */
class CohesionFormHooks {

  public function __construct(
    protected TextFormatStyles $text_format_styles,
  ) {}

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_filter_format_form_alter')]
  public function form_filter_format_form_alter(&$form, FormStateInterface $form_state) {
    $editor = $form_state->get('editor');
    if ($editor && $editor->getEditor() == 'ckeditor') {
      $warning['ssa_warning'] = [
        '#theme' => 'status_messages',
        '#message_list' => [
          'warning' => [t('Site Studio recommends text formats to use CKEditor 5.')],
        ],
        '#status_headings' => [
          'warning' => t('Warning message'),
        ],
      ];

      $form['editor']['settings']['subform'] = array_merge($warning, $form['editor']['settings']['subform']);
    }

  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_filter_format_edit_form_alter')]
  public function form_filter_format_edit_form_alter(&$form, FormStateInterface $form_state) {
    $editor = $form_state->get('editor');
    if ($editor instanceof EditorInterface && $editor->getEditor() == 'ckeditor5' && isset($form['editor']['settings']['subform']['plugins']['ckeditor5_style']['styles'])) {
      $form['editor']['settings']['subform']['plugins']['ckeditor5_style']['styles']['#after_build'][] = '_sitestudio_ckeditor_styles_after_build_callback';
      $form['editor']['settings']['subform']['plugins']['ckeditor5_style']['ssa_enabled'] = [
        '#type' => 'checkbox',
        '#title' => t('Add Site Studio styles'),
        '#default_value' => (bool) $editor->getThirdPartySetting('cohesion', 'ssa_enabled', FALSE),
        '#ajax' => [
          'callback' => '_update_ckeditor5_html_filter',
          'event' => 'change',
          'trigger_as' => ['name' => 'editor_configure'],
        ],
      ];
      $form['#entity_builders'][] = '_cohesion_form_filter_format_edit_form_builder';
    }
  }

}
