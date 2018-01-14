<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * General SQL-based source plugin for subscriptions.
 *
 * @MigrateSource(
 *   id = "dc_subscriptions",
 *   table = {
 *     "name": "subscriptions",
 *     "alias": "s",
 *     "ids": {
 *       "sid": "integer"
 *     },
 *     "fields": {
 *       "sid": "Subscription ID",
 *       "module": "Module",
 *       "field": "Field",
 *       "value": "Field value (i.e. node ID)",
 *       "recipient_uid": "Recipient",
 *       "send_interval": "Send interval",
 *       "author_uid": "Author uid",
 *       "send_updates": "Send notification on updates",
 *       "send_comments": "Send notification on new comments"
 *     }
 *   }
 * )
 */
class DcSqlSubscriptions extends SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    if (($field = $this->getConfig('field')) === NULL) {
      return $query;
    }
    // Filter by field name.
    $query->condition('s.field', $field);

    if (($bundle = $this->getConfig('bundle')) === NULL) {
      return $query;
    }
    if ('nid' !== $field) {
      return $query;
    }
    // Filter by bundle.
    $query->join('node', 'n', 'n.nid = s.nid');
    $query->condition('n.type', $bundle);

    return $query;
  }

}
