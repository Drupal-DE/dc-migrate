<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\MigrateException;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * General SQL-based source plugin for nodes.
 *
 * @MigrateSource(
 *   id = "dc_node",
 *   table = {
 *     "name": "node",
 *     "alias": "n",
 *     "ids": {
 *       "nid": "integer"
 *     },
 *     "fields": {
 *       "nid": "Node ID",
 *       "vid": "Node revision",
 *       "language": "Language",
 *       "title": "Node title",
 *       "uid": "Node author",
 *       "status": "Node status",
 *       "created": "Creation date",
 *       "changed": "Update date",
 *       "promote": "Promoted to frontpage",
 *       "sticky": "Sticky at top of lists",
 *       "body": "Node body value"
 *     }
 *   }
 * )
 */
class DcSqlNode extends SqlBase {

  /**
   * List of fields to include in query.
   *
   * @var array
   */
  protected $field_definitions = [];

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    $bundle = $this->getConfig('bundle');
    if (empty($bundle)) {
      throw new MigrateException('You need to specify the bundle in the plugin definition or in the migration.');
    }
    // Filter by bundle.
    $query->condition('type', $bundle);

    // Join to {node_revisions} to include body field.
    $query->join('node_revisions', 'nr', 'n.vid = nr.vid');
    $query->addField('nr', 'body');

    // Add optional joins to field data tables.
    foreach ($this->getFieldDefinitions() as $key => $field) {
      $alias = $field['alias'];
      $table_alias = $field['table_alias'];
      $value_key = empty($field['value_key']) ? 'value' : $field['value_key'];
      $join_condition = isset($field['condition']) ? $field['condition'] : "{$table_alias}.entity_id = n.nid";
      $query->leftJoin("field_data_{$key}", $table_alias, $join_condition);
      $query->addField($table_alias, "{$key}_{$value_key}", $alias);
    }

    return $query->orderBy('n.nid', 'ASC');
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = array()) {
    foreach ($this->getFieldDefinitions() as $key => $field) {
      $fields[$field['alias']] = isset($field['description']) ? $field['description'] : $key;
    }
  }

  /**
   * Get list of field definitions to include in the query.
   *
   * @return array
   *   Associative list of field definitions.
   */
  public function getFieldDefinitions() {
    return [];
  }

}
