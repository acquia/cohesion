<?php

/**
 * @file
 * Content administration and module settings user interface.
 *
 * Based on: core/modules/node/node.admin.inc
 */

use Drupal\cohesion_elements\ComponentContentInterface;
use Drupal\Core\Link;

/**
 * Updates all component content in the passed-in array with the passed-in field values.
 *
 * IMPORTANT NOTE: This function is intended to work when called from a form
 * submission handler. Calling it outside of the form submission process may not
 * work correctly.
 *
 * @param array $component_contents
 *   Array of $component_content ids to update.
 * @param array $updates
 *   Array of key/value pairs with component content field names and the value to update that
 *   field to.
 * @param string $langcode
 *   (optional) The language updates should be applied to. If none is specified
 *   all available languages are processed.
 * @param bool $load
 *   (optional) TRUE if $component_contents contains an array of component content IDs to be loaded, FALSE
 *   if it contains fully loaded component contents. Defaults to FALSE.
 * @param bool $revisions
 *   (optional) TRUE if $component_contents contains an array of revision IDs instead of
 *   component content IDs. Defaults to FALSE; will be ignored if $load is FALSE.
 */
function component_contents_mass_update(array $component_contents, array $updates, $langcode = NULL, $load = FALSE, $revisions = FALSE) {
  // We use batch processing to prevent timeout when updating a large number
  // of component contents.
  if (count($component_contents) > 10) {
    $batch = [
      'operations' => [
        ['_component_content_mass_update_batch_process', [$component_contents, $updates, $langcode, $load, $revisions]],
      ],
      'finished' => '_component_content_mass_update_batch_finished',
      'title' => t('Processing'),
      // We use a single multi-pass operation, so the default
      // 'Remaining x of y operations' message will be confusing here.
      'progress_message' => '',
      'error_message' => t('The update has encountered an error.'),
      // The operations do not live in the .module file, so we need to
      // tell the batch engine which file to load before calling them.
      'file' => \Drupal::service('extension.path.resolver')->getPath('module', 'cohesion_elements') . '/cohesion_elements.admin.inc',
    ];
    batch_set($batch);
  }
  else {
    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
    $storage = \Drupal::entityTypeManager()->getStorage('component_content');
    if ($load && !$revisions) {
      $component_contents = $storage->loadMultiple($component_contents);
    }
    foreach ($component_contents as $component_content) {
      if ($load && $revisions) {
        $component_content = $storage->loadRevision($component_content);
      }
      _component_content_mass_update_helper($component_content, $updates, $langcode);
    }
    \Drupal::messenger()->addStatus(t('The update has been performed.'));
  }
}

/**
 * Updates individual component contents when fewer than 10 are queued.
 *
 * @param \Drupal\cohesion_elements\ComponentContentInterface $component_content
 *   A component content to update.
 * @param array $updates
 *   Associative array of updates.
 * @param string $langcode
 *   (optional) The language updates should be applied to. If none is specified
 *   all available languages are processed.
 *
 * @return \Drupal\cohesion_elements\ComponentContentInterface
 *   An updated component content object.
 *
 * @see component_content_mass_update()
 */
function _component_content_mass_update_helper(ComponentContentInterface $component_content, array $updates, $langcode = NULL) {
  $langcodes = isset($langcode) ? [$langcode] : array_keys($component_content->getTranslationLanguages());
  // For efficiency manually save the original component content before applying any changes.
  $component_content->original = clone $component_content;
  foreach ($langcodes as $langcode) {
    foreach ($updates as $name => $value) {
      $component_content->getTranslation($langcode)->$name = $value;
    }
  }
  $component_content->save();
  return $component_content;
}

/**
 * Implements callback_batch_operation().
 *
 * Executes a batch operation for component_content_mass_update().
 *
 * @param array $component_contents
 *   An array of component content IDs.
 * @param array $updates
 *   Associative array of updates.
 * @param string $langcode
 *   The language updates should be applied to. If none is specified all
 *   available languages are processed.
 * @param bool $load
 *   TRUE if $component_contents contains an array of component content IDs to be loaded, FALSE if it
 *   contains fully loaded component contents.
 * @param bool $revisions
 *   (optional) TRUE if $component_contents contains an array of revision IDs instead of
 *   component content  IDs. Defaults to FALSE; will be ignored if $load is FALSE.
 * @param array|\ArrayAccess $context
 *   An array of contextual key/values.
 */
function _component_content_mass_update_batch_process(array $component_contents, array $updates, $langcode, $load, $revisions, &$context) {
  if (!isset($context['sandbox']['progress'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['max'] = count($component_contents);
    $context['sandbox']['component_contents'] = $component_contents;
  }

  // Process component contents by groups of 5.
  /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
  $storage = \Drupal::entityTypeManager()->getStorage('component_content');
  $count = min(5, count($context['sandbox']['component_contents']));
  for ($i = 1; $i <= $count; $i++) {
    // For each id, load the component content, reset the values, and save it.
    $component_content = array_shift($context['sandbox']['component_content']);
    if ($load) {
      $component_content = $revisions ?
        $storage->loadRevision($component_content) : $storage->load($component_content);
    }
    $component_content = _component_content_mass_update_helper($component_content, $updates, $langcode);

    // Store result for post-processing in the finished callback.
    $context['results'][] = Link::fromTextAndUrl($component_content->label(), $component_content->toUrl())->toString();

    // Update our progress information.
    $context['sandbox']['progress']++;
  }

  // Inform the batch engine that we are not finished,
  // and provide an estimation of the completion level we reached.
  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}

/**
 * Implements callback_batch_finished().
 *
 * Reports the 'finished' status of batch operation for component_content_mass_update().
 *
 * @param bool $success
 *   A boolean indicating whether the batch mass update operation successfully
 *   concluded.
 * @param string[] $results
 *   An array of rendered links to component contents updated via the batch mode process.
 * @param array $operations
 *   An array of function calls (not used in this function).
 *
 * @see _component_content_mass_update_batch_process()
 */
function _component_content_mass_update_batch_finished($success, $results, $operations) {
  if ($success) {
    \Drupal::messenger()->addStatus(t('The update has been performed.'));
  }
  else {
    \Drupal::messenger()->addError(t('An error occurred and processing did not complete.'));
    $message = \Drupal::translation()->formatPlural(count($results), '1 item successfully processed:', '@count items successfully processed:');
    $item_list = [
      '#theme' => 'item_list',
      '#items' => $results,
    ];
    $message .= \Drupal::service('renderer')->render($item_list);
    \Drupal::messenger()->addStatus($message);
  }
}
