<?php

namespace Drupal\cohesion\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Master template' condition.
 *
 * @Condition(
 *   id = "cohesion_master_template",
 *   label = @Translation("Master template")
 * )
 */
class MasterTemplate extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['using_master_template'] = [
      '#title' => $this->t('Using master template'),
      '#type' => 'checkbox',
      '#default_value' => is_int($this->configuration['using_master_template']) ? $this->configuration['using_master_template'] : 0,
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['using_master_template'] = $form_state->getValue('using_master_template');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('The page is using Site Studio master Template');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (!$this->configuration['using_master_template'] && !$this->isNegated()) {
      return TRUE;
    }

    return _cohesion_templates_get_master_template();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['using_master_template' => []] + parent::defaultConfiguration();
  }

}
