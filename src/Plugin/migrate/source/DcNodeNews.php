<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlNode;

/**
 * SQL-based source plugin for news nodes.
 *
 * @MigrateSource(
 *   id = "dc_node__news",
 *   bundle = "news",
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
class DcNodeNews extends DcSqlNode {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    parent::alterQuery($query);

    $query->leftJoin('term_node', 'tn', 'tn.nid = n.nid');
    $query->leftJoin('term_data', 'td', 'td.tid = tn.tid');
    $query->addField('td', 'tid', 'category');

    $query->distinct();
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = []) {
    parent::alterFields($fields);

    $fields['category'] = $this->t('News category');
  }

}
