<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * General SQL-based source plugin for comments.
 *
 * @MigrateSource(
 *   id = "dc_comment",
 *   table = {
 *     "name": "comments",
 *     "alias": "c",
 *     "ids": {
 *       "cid": "integer",
 *       "pid": "integer",
 *       "nid": "integer"
 *     },
 *     "fields": {
 *       "cid": "Comment ID",
 *       "pid": "Parent ID",
 *       "nid": "Node ID",
 *       "uid": "Comment author",
 *       "subject": "Comment subject",
 *       "comment": "Comment",
 *       "hostname": "Hostname",
 *       "timestamp": "Timestamp of comment",
 *       "status": "Comment status (0 = published, 1 = unpublished)",
 *       "thread": "Comment thread position",
 *       "name": "Author name",
 *       "mail": "Author mail",
 *       "homepage": "Author homepage"
 *     }
 *   }
 * )
 */
class DcSqlComment extends SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    $bundle = $this->getConfig('bundle');

    if (!empty($bundle)) {
      // Limit comments to specified node bundle.
      $query->join('node', 'n', 'n.nid = c.nid');
      if (is_array($bundle)) {
        $query->condition('n.type', $bundle, 'IN');
      }
      else {
        $query->condition('n.type', $bundle);
      }
    }

    $query->orderBy('c.cid', 'ASC');
  }

}
