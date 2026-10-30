<?php

namespace Drupal\cohesion_website_settings;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Link;

/**
 * Class WebsiteSettingsListBuilder.
 *
 * Provides a listing of Site Studio website settings entities.
 *
 * @package Drupal\cohesion_website_settings
 */
class WebsiteSettingsListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $types = [
      'base_unit_settings' => [
        'label' => $this->t('Base unit settings'),
        'description' => $this->t('Manage your @settings_label', ['@settings_label' => 'base unit settings']),
        'add_link' => Link::createFromRoute('Base unit settings', 'entity.cohesion_website_settings.edit_form', ['cohesion_website_settings' => 'base_unit_settings']),
      ],
      'color_palette' => [
        'label' => $this->t('Color palette'),
        'description' => $this->t('Manage your @settings_label', ['@settings_label' => 'color palette']),
        'add_link' => Link::createFromRoute('Color palette', 'cohesion_website_settings.color_palette_edit_form', []),
      ],
      'default_font_settings' => [
        'label' => $this->t('Default font settings'),
        'description' => $this->t('Manage your @settings_label', ['@settings_label' => 'default font settings']),
        'add_link' => Link::createFromRoute('Default font settings', 'entity.cohesion_website_settings.edit_form', ['cohesion_website_settings' => 'default_font_settings']),
      ],
      'font_libraries' => [
        'label' => $this->t('Font libraries'),
        'description' => $this->t('Manage your @settings_label', ['@settings_label' => 'font libraries']),
        'add_link' => Link::createFromRoute('Font libraries', 'cohesion_website_settings.font_libraries_edit_form', []),
      ],
      'icon_libraries' => [
        'label' => $this->t('Icon libraries'),
        'description' => $this->t('Manage your @settings_label', ['@settings_label' => 'icon libraries']),
        'add_link' => Link::createFromRoute('Icon libraries', 'cohesion_website_settings.icon_libraries_edit_form', []),
      ],
      'responsive_grid_settings' => [
        'label' => $this->t('Responsive grid settings'),
        'description' => $this->t('Manage your @settings_label', ['@settings_label' => 'responsive grid settings']),
        'add_link' => Link::createFromRoute('Responsive grid settings', 'entity.cohesion_website_settings.edit_form', ['cohesion_website_settings' => 'responsive_grid_settings']),
      ],
      'scss_variables' => [
        'label' => $this->t('SCSS variables'),
        'description' => $this->t('Manage your @settings_label', ['@settings_label' => 'SCSS variables']),
        'add_link' => Link::createFromRoute('SCSS variables', 'cohesion_website_settings.scss_variables_edit_form'),
      ],
    ];

    // Send this to entity-add-list.html.twig via system.module.
    $build = [
      '#theme' => 'entity_add_list',
      '#bundles' => $types,
      '#add_bundle_message' => $this->t('There are no available website settings. Go to the batch import page to import the list of website settings.'),
      '#cache' => [
        'tags' => $this->entityType->getListCacheTags(),
      ],
    ];

    return $build;
  }

}
