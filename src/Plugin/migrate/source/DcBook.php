<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * SQL-based source plugin for book nodes.
 *
 * @MigrateSource(
 *   id = "dc_book",
 *   table = {
 *     "name": "book",
 *     "alias": "b",
 *     "fields": {
 *       "nid": "Node ID",
 *       "bid": "Book ID",
 *       "mlid": "Menu link ID",
 *       "plid": "Parent link ID",
 *       "weight": "Weight",
 *       "p1": "The first mlid in the materialized path. If N = depth, then pN must equal the mlid. If depth > 1 then p(N-1) must equal the parent link mlid. All pX where X > depth must equal zero. The columns p1 .. p9 are also called the parents.",
 *       "p2": "The second mlid in the materialized path. See p1.",
 *       "p3": "The third mlid in the materialized path. See p1.",
 *       "p4": "The fourth mlid in the materialized path. See p1.",
 *       "p5": "The fifth mlid in the materialized path. See p1.",
 *       "p6": "The sixth mlid in the materialized path. See p1.",
 *       "p7": "The seventh mlid in the materialized path. See p1.",
 *       "p8": "The eighth mlid in the materialized path. See p1.",
 *       "p9": "The ninth mlid in the materialized path. See p1."
 *     }
 *   }
 * )
 */
class DcBook extends SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    $query->join('menu_links', 'ml', 'b.mlid = ml.mlid');
    $ml_fields = ['mlid', 'plid', 'weight', 'has_children', 'depth'];
    for ($i = 1; $i <= 9; $i++) {
      $field = "p{$i}";
      $ml_fields[] = $field;
      $query->orderBy('ml.' . $field);
    }
    $query->fields('ml', $ml_fields);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'mlid' => [
        'type' => 'integer',
        'alias' => 'ml',
      ],
    ];
  }

}
