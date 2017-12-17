<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlNode;

/**
 * SQL-based source plugin for forum nodes.
 *
 * @MigrateSource(
 *   id = "dc_node__forum",
 *   bundle = "forum",
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
 *       "body": "Node body value",
 *       "comment": "Comment status"
 *     }
 *   }
 * )
 */
class DcNodeForum extends DcSqlNode {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    parent::alterQuery($query);

    $query->innerJoin('forum', 'f', 'f.vid = n.vid');
    $query->leftJoin('term_node', 'tn', 'tn.vid = n.vid');
    $query->leftJoin('term_data', 'td', 'td.tid = tn.tid AND td.vid = :vid', [':vid' => 6]);
    $query->addField('f', 'tid', 'forum');
    $query->addField('td', 'tid', 'drupal_version');

    $query->groupBy('n.vid');
    $query->distinct();
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = []) {
    parent::alterFields($fields);

    $fields['forum'] = $this->t('Forum');
    $fields['drupal_version'] = $this->t('Drupal version');
  }

}
