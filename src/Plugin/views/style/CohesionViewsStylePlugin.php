<?php

namespace Drupal\cohesion\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Site Studio layout style plugin to render views using Site Studio layouts.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "cohesion_layout",
 *   title = @Translation("View template"),
 *   help = @Translation("Displays using view templates."),
 *   theme = "views_view_cohesion_layout",
 *   display_types = {"normal"}
 * )
 */
class CohesionViewsStylePlugin extends StylePluginBase {

  /**
   * Does the style plugin support grouping of rows.
   *
   * @var bool
   */
  protected $usesGrouping = FALSE;

  /**
   * Does the style plugin for itself support to add fields to it's output.
   *
   * @var bool
   */
  protected $usesFields = TRUE;

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = FALSE;

  /**
   * Set default options.
   *
   * @return array
   *   Array of options used by the style.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    // Send the array to the view options.
    $options['views_template'] = ['default' => NULL];
    $options['master_template'] = ['default' => NULL];

    return $options;
  }

  /**
   * Form for admin to select the template they want to use.
   *
   * @param mixed $form
   *   Form object passed by reference.
   * @param \Drupal\core\Form\FormStateInterface $form_state
   *   Form state including form values.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Load all view template config entities.
    $view_templates = \Drupal::entityTypeManager()->getStorage('cohesion_view_templates')->loadMultiple();

    // Build into a keyed array.
    $view_templates_list = [];

    foreach ($view_templates as $view_template) {
      if ($view_template->get('status')) {
        $view_templates_list[$view_template->id()] = $view_template->get('label');
      }
    }

    if (count($view_templates_list)) {
      $form['views_template'] = [
        '#title' => $this->t('Template'),
        '#description' => $this->t('View template to use for this view.'),
        '#type' => 'select',
        '#default_value' => $this->options['views_template'],
        '#options' => $view_templates_list,
      ];
    }
    else {
      $form['views_template'] = [
        '#markup' => t("No view templates have been created."),
      ];
    }

    // Master template selection.
    $master_template_ids = \Drupal::service('entity_type.manager')->getStorage('cohesion_master_templates')->getQuery()
      ->accessCheck(TRUE)
      ->execute();
    $master_template_storage = \Drupal::service('entity_type.manager')->getStorage('cohesion_master_templates');
    $master_templates = $master_template_storage->loadMultiple($master_template_ids);

    $form['master_template'] = [
      '#type' => 'select',
      '#title' => t('Master template'),
      '#options' => [
        '__none__' => t('No master template'),
      ],
      '#required' => FALSE,
      '#default_value' => $this->options['master_template'],
      '#access' => TRUE,
      '#weight' => 2,
    ];

    foreach ($master_templates as $key => $template) {
      // Show templates if they're enabled or being used.
      if ($template->status() && $template->isSelectable()) {
        $form['master_template']['#options'][$key] = $template->get('label');
      }
    }
  }

  /**
   * Provide a form in the views wizard if this style is selected.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $type
   *   The display type, either block or page.
   */
  public function wizardForm(&$form, FormStateInterface $form_state, $type) {
  }

  /**
   * Render the display in this style.
   */
  public function render() {
    if ($this->usesRowPlugin() && empty($this->view->rowPlugin)) {
      trigger_error('Drupal\views\Plugin\views\style\StylePluginBase: Missing row plugin', E_WARNING);
      return FALSE;
    }

    // Group the rows according to the grouping instructions, if specified.
    $sets = $this->renderGrouping($this->view->result, $this->options['grouping'], TRUE);

    $sets = $this->renderGroupingSets($sets);

    return $sets;
  }

  /**
   * Renders a group of rows of the grouped view.
   *
   * @param array $rows
   *   The result rows rendered in this group.
   *
   * @return array
   *   The render array containing the single group theme output.
   */
  protected function renderRowGroup(array $rows = []) {

    // Default.
    $theme = $this->themeFunctions();

    return [
      '#theme' => $theme,
      // If a view template is available, this is the theme suggestion for that
      // template.
      '#view' => $this->view,
      '#rows' => $rows,
    ];
  }

  /**
   * Imported from parent.
   *
   * @param $sets
   * @param int $level
   *
   * @return array
   */
  public function renderGroupingSets($sets, $level = 0) {
    $output = [];
    $theme_functions = $this->view->buildThemeFunctions($this->groupingTheme);
    foreach ($sets as $set) {
      $level = $set['level'] ?? 0;

      $row = reset($set['rows']);
      // Render as a grouping set.
      if (is_array($row) && isset($row['group'])) {
        $single_output = [
          '#theme' => $theme_functions,
          '#view' => $this->view,
          '#grouping' => $this->options['grouping'][$level],
          '#rows' => $set['rows'],
        ];

        $single_output['#grouping_level'] = $level;
        $single_output['#title'] = $set['group'];
        $output[] = $single_output;
      }
      // Render as a record set.
      else {
        foreach ($set['rows'] as $index => $row) {
          $this->view->row_index = $index;
          $single_output = $this->renderRowGroup([$this->view->rowPlugin->render($row)]);
          $single_output['#grouping_level'] = $index;
          $single_output['#title'] = 'cohesion_group_' . $index;
          $output[] = $single_output;
        }

      }

    }
    unset($this->view->row_index);
    return $output;
  }

}
