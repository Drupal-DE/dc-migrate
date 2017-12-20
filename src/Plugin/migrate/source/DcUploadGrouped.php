<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * SQL-based source plugin for uploads grouped by node ID.
 *
 * @MigrateSource(
 *   id = "dc_upload_grouped",
 *   table = {
 *     "name": "upload",
 *     "alias": "u",
 *     "ids": {
 *       "vid": "integer"
 *     },
 *     "fields": {
 *       "nid": "Node ID",
 *       "vid": "Node revision",
 *       "fids": "File IDs"
 *     }
 *   }
 * )
 */
class DcUploadGrouped extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $bundle = $this->getConfig('bundle');
    if (empty($bundle)) {
      throw new MigrateException('You need to specify the bundle in the plugin definition or in the migration.');
    }

    $query = $this->select($this->getTableName(), $this->getTableAlias());
    $query->join('node', 'n', 'n.nid = u.nid AND n.vid = u.vid');

    $query->fields($this->getTableAlias(), ['nid', 'vid']);
    $query->addExpression('GROUP_CONCAT(u.fid)', 'fids');

    // Now the tricky part: only select fids from latest revision.
    $subquery = $this->select($this->getTableName(), $this->getTableAlias() . '2');
    $subquery->addExpression('MAX(vid)');
    $subquery->where('nid = u.nid');
    $query->condition('u.vid', $subquery);

    $query->condition('n.type', $bundle);

    $query->groupBy('u.nid');
    $query->orderBy('u.vid');
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
