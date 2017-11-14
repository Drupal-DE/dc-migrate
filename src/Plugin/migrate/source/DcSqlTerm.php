<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * General SQL-based source plugin for taxonomy terms.
 *
 * @MigrateSource(
 *   id = "dc_term",
 *   table = {
 *     "name": "term_data",
 *     "alias": "td",
 *     "ids": {
 *       "tid": "integer"
 *     },
 *     "fields": {
 *       "tid": "Term ID",
 *       "vid": "Term vocabulary",
 *       "name": "Term name",
 *       "description": "Term description",
 *       "weight": "Term weight",
 *       "parent": "Term parent"
 *     }
 *   }
 * )
 */
class DcSqlTerm extends SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    $options = $this->getConfig('options', []);

    if (empty($options['disable_hierarchy'])) {
      // Join to hierarchy table.
      $query->join('term_hierarchy', 'th', 'td.tid = th.tid');
      $query->addField('th', 'parent');
      $query->groupBy('td.tid');
    }

    // Filter by vocabulary ID.
    $vid = $this->getConfig('vid');
    if (isset($vid)) {
      $query->condition('td.vid', $vid);
    }

    // Filter by vocabulary name.
    $vocabulary = $this->getConfig('vocabulary', FALSE);
    if (!empty($vocabulary)) {
      // Add join to vocabulary table to allow filtering by machine_name.
      $query->join('vocabulary', 'tv', 'td.vid = tv.vid');
      $query->condition('tv.machine_name', $vocabulary);
    }

  }

}
