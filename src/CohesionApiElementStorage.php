<?php

namespace Drupal\cohesion;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Database\Database;

/**
 * Service to handle storage of cohesion elements.
 *
 * @package Drupal\cohesion
 */
class CohesionApiElementStorage {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * CohesionApiElementStorage constructor.
   */
  public function __construct() {
    $this->connection = Database::getConnection();
  }

  /**
   * @param $element_id
   * @param $element_group
   *
   * @return bool
   */
  public function cohElementExists($element_id, $element_group) {
    $count = 0;
    $select = $this->connection->select('coh_element_schema_info', 'si');

    if ($element_id) {
      $select->condition('si.element_id', $element_id);
      $select->condition('si.element_group', $element_group);
      $select->addExpression('COUNT(*)');
      $count = $select->execute()->fetchField();
    }

    if ($count > 0) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   *
   */
  public function get($element_id) {
    $entries = [];

    $select = $this->connection->select('coh_element_schema_info', 'si');
    $select->addField('si', 'id');
    $select->addField('si', 'element_id');
    $select->addField('si', 'element_label');
    $select->addField('si', 'element_group');
    $select->addField('si', 'element_weight');
    $select->addField('si', 'element_element');
    $select->addField('si', 'feature_id');

    if ($element_id) {
      $select->condition('si.element_id', $element_id);
      $select->orderBy('element_weight', 'ASC');
      $entries = $select->execute()->fetch(\PDO::FETCH_ASSOC);
    }

    return $entries;
  }

  /**
   *
   */
  public function getAll($limit = NULL) {
    $select = $this->connection->select('coh_element_schema_info', 'si');
    $select->addField('si', 'id');
    $select->addField('si', 'element_id');
    $select->addField('si', 'element_label');
    $select->addField('si', 'element_group');
    $select->addField('si', 'element_weight');
    $select->addField('si', 'element_element');
    $select->addField('si', 'feature_id');

    $select->orderBy('element_weight', 'ASC');

    if (isset($limit) && is_numeric($limit)) {
      $select->range(0, (int) $limit);
    }

    $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

    return $entries;
  }

  /**
   * Return a list of elements group a particular element group.
   *
   * @param $groupId
   *
   * @return mixed
   */
  public function getByGroup($groupId) {
    $select = $this->connection->select('coh_element_schema_info', 'si');
    $select->addField('si', 'id');
    $select->addField('si', 'element_id');
    $select->addField('si', 'element_label');
    $select->addField('si', 'element_group');
    $select->addField('si', 'element_weight');
    $select->addField('si', 'element_element');
    $select->addField('si', 'feature_id');
    $select->orderBy('element_weight', 'ASC');
    $select->condition('element_group', $groupId, '=');

    $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

    return $entries;
  }

  /**
   * @param array $params
   *
   * @return bool
   * @throws \Exception
   */
  public function cohInsert($params = []) {

    $query = $this->connection->insert('coh_element_schema_info')
      ->fields([
        'id',
        'element_id',
        'element_label',
        'element_group',
        'element_weight',
        'element_element',
        'feature_id',
      ]);

    if ($params) {
      foreach ($params as $key => $result) {
        $query->values($result);
      }
    }

    try {
      $query->execute();
      // Success message here.
      return TRUE;
    }
    catch (\PDOException $e) {
      \Drupal::logger('cohesionapi_element_update_error')
        ->error($e->getMessage());
      return FALSE;
    }
  }

  /**
   * @param $params
   */
  public function cohUpdate($params) {
    try {
      $this->connection->update('coh_element_schema_info')
        ->fields($params)
        ->condition('element_id', $params['element_id'], '=')
        ->condition('element_group', $params['element_group'], '=')
        ->execute();
      // Success message here.
    }
    catch (\PDOException $e) {
      // Query failed; recover based on $e->getMessage()
      \Drupal::logger('cohesionapi_element_update_error')
        ->error($e->getMessage());
    }
  }

  /**
   * @param array $params
   *
   * @throws \Exception
   */
  public function cohUpsert($params = []) {

    $element_id = $params['element_id'] ?? NULL;
    $element_group_id = $params['element_group'] ?? NULL;
    if ($element_id && $element_group_id) {
      if ($this->cohElementExists($element_id, $element_group_id)) {
        // Update.
        $this->cohUpdate($params);
      }
      else {

        // Insert.
        if (isset($params[0])) {
          // Handle multiple inserts.
          $insert_data = [];
          foreach ($params as $key => $value) {
            $value['id'] = NULL;
            $insert_data[] = $value;
          }
          $this->cohInsert($insert_data);
        }
        else {
          $params['id'] = NULL;
          $this->cohInsert([$params]);
        }
      }
    }
  }

  /**
   * @param $element_id
   * @param $element_group
   * @param bool $bulk
   */
  public function cohDelete($element_id, $element_group, $bulk = FALSE) {
    try {
      if ($bulk && is_array($element_id)) {
        $this->connection->delete('coh_element_schema_info')
          ->condition('element_id', $element_id, 'IN')
          ->condition('element_group', $element_group, '=')
          ->execute();
      }
      else {
        $this->connection->delete('coh_element_schema_info')
          ->condition('element_id', $element_id)
          ->condition('element_group', $element_group, '=')
          ->execute();
      }

      // Success message here.
    }
    catch (\PDOException $e) {
      \Drupal::logger('cohesionapi_element_update_error')
        ->error($e->getMessage());
    }
  }

  /**
   * @param $name
   * @param bool $bulk
   */
  public function cohDeleteKeyValue($name, $bulk = FALSE) {
    try {
      if ($bulk && is_array($name)) {
        $this->connection->delete('key_value')
          ->condition('name', $name, 'IN')
          ->execute();
      }
      else {
        $this->connection->delete('key_value')
          ->condition('name', $name)
          ->execute();
      }

      // Success message here.
    }
    catch (\PDOException $e) {
      \Drupal::logger('cohesionapi_element_update_error')
        ->error($e->getMessage());
    }
  }

  /**
   * @param array $params
   *
   * @return bool|void
   */
  public function cohUpdateAssetLibrary($params = []) {
    if ($params) {
      $storage = \Drupal::keyValue('coh_asset_library');
      if (isset($params['element_id']) && isset($params['content'])) {
        $storage->set($params['element_id'], Json::encode($params['content']));
      }
      return TRUE;
    }
  }

}
