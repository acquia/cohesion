<?php

namespace Drupal\cohesion\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Element\MachineName as CoreMachineName;

/**
 * Extends the core machine name functionalities.
 *
 * @FormElement("ajax_machine_name")
 */
class MachineName extends CoreMachineName {

  /**
   *
   */
  public static function processMachineName(&$element, FormStateInterface $form_state, &$complete_form) {
    // We need to pass the langcode to the client.
    $language = \Drupal::languageManager()->getCurrentLanguage();

    // Apply default form element properties.
    $element += [
      '#title' => t('Machine-readable name'),
      '#description' => t('A unique machine-readable name. Can only contain lowercase letters, numbers, and underscores.'),
      '#machine_name' => [],
      '#field_prefix' => '',
      '#field_suffix' => '',
      '#suffix' => '',
      '#entity_type_id' => FALSE,
      '#entity_id' => NULL,

    ];
    // A form element that only wants to set one #machine_name property (usually
    // 'source' only) would leave all other properties undefined, if the
    // defaults were defined by an element plugin. Therefore, we apply the
    // defaults here.
    $element['#machine_name'] += [
      'source' => ['label'],
      'target' => '#' . $element['#id'],
      'label' => t('Machine name'),
      'replace_pattern' => '[^a-z0-9_]+',
      'replace' => '_',
      'standalone' => FALSE,
      'field_prefix' => $element['#field_prefix'],
      'field_suffix' => $element['#field_suffix'],
      'entity_type_id' => $element['#entity_type_id'],
      'entity_id' => $element['#entity_id'],
    ];

    // Store the initial value in form state. The machine name needs this to
    // ensure that the exists function is not called for existing values when
    // editing them.
    $initial_values = $form_state->get('machine_name.initial_values') ?: [];
    // Store the initial values in an array so we can differentiate between a
    // NULL default value and a new machine name element.
    if (!array_key_exists($element['#name'], $initial_values)) {
      $initial_values[$element['#name']] = $element['#default_value'];
      $form_state->set('machine_name.initial_values', $initial_values);
    }

    // By default, machine names are restricted to Latin alphanumeric
    // characters. So, default to LTR directionality.
    if (!isset($element['#attributes'])) {
      $element['#attributes'] = [];
    }
    $element['#attributes'] += ['dir' => LanguageInterface::DIRECTION_LTR];

    // The source element defaults to array('name'), but may have been
    // overridden.
    if (empty($element['#machine_name']['source'])) {
      return $element;
    }

    // Retrieve the form element containing the human-readable name from the
    // complete form in $form_state. By reference, because we may need to append
    // a #field_suffix that will hold the live preview.
    $key_exists = NULL;
    $source = NestedArray::getValue($form_state->getCompleteForm(), $element['#machine_name']['source'], $key_exists);
    if (!$key_exists) {
      return $element;
    }

    $suffix_id = $source['#id'] . '-machine-name-suffix';
    $element['#machine_name']['suffix'] = '#' . $suffix_id;

    if ($element['#machine_name']['standalone']) {
      $element['#suffix'] = $element['#suffix'] . ' <small id="' . $suffix_id . '">&nbsp;</small>';
    }
    else {
      // Append a field suffix to the source form element, which will contain
      // the live preview of the machine name.
      $source += ['#field_suffix' => ''];
      $source['#field_suffix'] = $source['#field_suffix'] . ' <small id="' . $suffix_id . '">&nbsp;</small>';

      $parents = array_merge($element['#machine_name']['source'], ['#field_suffix']);
      NestedArray::setValue($form_state->getCompleteForm(), $parents, $source['#field_suffix']);
    }

    $element['#attached']['library'][] = 'cohesion/drupal.machine-name';
    $options = [
      'replace_pattern',
      'replace_token',
      'replace',
      'maxlength',
      'target',
      'label',
      'field_prefix',
      'field_suffix',
      'suffix',
      'entity_type_id',
      'entity_id',
    ];

    /** @var \Drupal\Core\Access\CsrfTokenGenerator $token_generator */
    $token_generator = \Drupal::service('csrf_token');
    $element['#machine_name']['replace_token'] = $token_generator->get($element['#machine_name']['replace_pattern']);

    $element['#attached']['drupalSettings']['machineName']['#' . $source['#id']] = array_intersect_key($element['#machine_name'], array_flip($options));
    $element['#attached']['drupalSettings']['langcode'] = $language->getId();

    return $element;
  }

}
