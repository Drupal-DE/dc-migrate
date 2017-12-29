<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlNode;
use Drupal\migrate\Row;

/**
 * SQL-based source plugin for showroom nodes.
 *
 * @MigrateSource(
 *   id = "dc_node__showroom",
 *   bundle = "showroom",
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
class DcNodeShowroom extends DcSqlNode {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    parent::alterQuery($query);

    $query->join('content_type_showroom', 'cs', 'cs.vid = n.vid');
    $query->addField('cs', 'field_screenshot_der_webseite_fid', 'screenshot_fid');

    $query->leftJoin('content_field_url_der_webseite', 'fu', 'fu.vid = n.vid');
    $query->addField('fu', 'field_url_der_webseite_url', 'url');

    $query->leftJoin('term_node', 'tn', 'tn.nid = n.nid');
    $query->leftJoin('term_data', 'td', 'td.tid = tn.tid AND td.vid = :vid', [':vid' => 10]);
    $query->addExpression('GROUP_CONCAT(td.tid)', 'categories');

    $query->groupBy('n.vid');
    $query->distinct();
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = []) {
    $fields['screenshot_fid'] = $this->t('ID of website screenshot');
    $fields['url'] = $this->t('Showroom website url');
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }
    $url = $row->getSourceProperty('url');
    if (strpos($url, 'http') === FALSE) {
      $row->setSourceProperty('url', 'http://' . $url);
    }

    $categories = explode(',', $row->getSourceProperty('categories'));
    array_walk($categories, function(&$value, $key) {
      $value = [
        'tid' => $value,
      ];
    });
    $row->setSourceProperty('categories', $categories);
  }

}
