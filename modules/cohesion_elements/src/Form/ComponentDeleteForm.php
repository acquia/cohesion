<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion\Form\CohesionDeleteForm;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Class ComponentDeleteForm.
 *
 * Builds the form to delete Site Studio custom styles entities.
 *
 * @package Drupal\cohesion_elements\Form
 */
class ComponentDeleteForm extends CohesionDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $build = [
      [
        '#markup' => $this->t('Deleting a <em>Component</em> will delete all instances where it is in use.
    All content that’s been added to instances of this <em>Component</em> will be removed including <em>Component content</em> and the configuration of your <em>Component</em>.
    This action cannot be undone.'),
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ],
    ];

    $rows = function ($result = []) {
      $rows_data = [];
      foreach ($result as $entity) {
        $rows_data[] = [
          [
            'data' => new FormattableMarkup('<a href=":link">@name</a>', [
              ':link' => $entity['url'],
              '@name' => $entity['name'],
            ]),
          ],
        ];
      }
      return $rows_data;
    };

    $in_use_entities = \Drupal::service('cohesion_usage.update_manager')->getFormattedInUseEntitiesList($this->entity);

    // Component has in-use.
    if (!empty($in_use_entities)) {
      // Overall warning.
      $build[] = [
        '#markup' => t('This <em>Component</em> is in-use on the following entities and may not be safe to delete.'),
      ];

      // Build the list of entity types.
      foreach ($in_use_entities as $type => $result) {
        $build[] = [
          '#type' => 'details',
          '#open' => TRUE,
          '#title' => $type,
          'table' => [
            '#type' => 'table',
            '#header' => [],
            '#rows' => $rows($result),
          ],
        ];
      }
    }
    // Component is not in use.
    else {
      $build[] = [
        '#markup' => t('This <em>Component</em> is not in use and may be safe to delete.'),
      ];

    }

    return \Drupal::service('renderer')->render($build);
    /*;
     */
  }

}
