<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * SQL-based source plugin for comment uploads grouped by node ID.
 *
 * @MigrateSource(
 *   id = "dc_comment_upload_grouped",
 *   table = {
 *     "name": "comment_upload",
 *     "alias": "u",
 *     "ids": {
 *       "cid": "integer"
 *     },
 *     "fields": {
 *       "cid": "Comment ID",
 *       "nid": "Node ID",
 *       "fids": "File IDs"
 *     }
 *   }
 * )
 */
class DcCommentUploadGrouped extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $bundle = $this->getConfig('bundle');
    if (empty($bundle)) {
      throw new MigrateException('You need to specify the bundle in the plugin definition or in the migration.');
    }

    $query = $this->select($this->getTableName(), $this->getTableAlias());
    $query->join('node', 'n', 'n.nid = u.nid');

    $query->fields($this->getTableAlias(), ['nid', 'cid']);
    $query->addExpression('GROUP_CONCAT(u.fid)', 'fids');

    $query->condition('n.type', $bundle);

    $query->groupBy('u.cid');
    $query->orderBy('u.cid');
    $query->orderBy('u.weight');
    $query->orderBy('u.description');

    // Extending classes should alter the query.
    $this->alterQuery($query);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }
    $fids = explode(',', $row->getSourceProperty('fids'));
    array_walk($fids, function(&$value, $key) {
      $value = [
        'fid' => $value,
      ];
    });
    $row->setSourceProperty('fids', $fids);
  }

}
