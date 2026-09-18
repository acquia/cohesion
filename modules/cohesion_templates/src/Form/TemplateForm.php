<?php

namespace Drupal\cohesion_templates\Form;

use Drupal\cohesion\Form\CohesionBaseForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;

/**
 * Class MenuTemplatesForm.
 *
 * @package Drupal\cohesion_templates\Form
 */
class TemplateForm extends CohesionBaseForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['#attached']['drupalSettings']['cohesion']['formGroup'] = 'template';
    $form['#attached']['drupalSettings']['cohesion']['formId'] = 'content_template';
    $form['#attached']['drupalSettings']['cohOnInitForm'] = \Drupal::service('settings.endpoint.utils')->getCohFormOnInit('template', 'content_template');

    // Set list of field to blank by default. Template form that inherit from
    // this one will override the variable.
    $language_none = \Drupal::languageManager()->getLanguage(LanguageInterface::LANGCODE_NOT_APPLICABLE);

    $form['#attached']['drupalSettings']['cohesion']['contextualKey'] = Url::fromRoute('cohesion.entity_fields', [
      'entity_type' => '__none__',
      'entity_bundle' => '__none__',
    ], ['language' => $language_none])->toString();

    // Attach all tokens by default.
    $form['cohesion']['#token_browser'] = '';

    unset($form['cohesion']['#json_mapper']);

    return $form;
  }

  /**
   * Validate the Template form.
   *
   * @inheritdoc
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Check if the machine name is empty.
    if (empty($form_state->getValue('machine_name'))) {
      $form_state->setErrorByName('machine_name', $this->t('The machine name cannot be empty.'));
    }

    // Note, the machine name check is performed automatically in
    // $this->>checkUniqueMachineName()
  }

}
