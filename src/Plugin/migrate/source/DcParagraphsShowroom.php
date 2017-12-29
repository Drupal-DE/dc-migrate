<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Row;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * SQL-based source plugin for showroom paragraphs.
 *
 * @MigrateSource(
 *   id = "dc_paragraphs__showroom",
 *   table = {
 *     "name": "content_type_showroom",
 *     "alias": "cs",
 *     "ids": {
 *       "nid": "integer"
 *     },
 *     "fields": {
 *       "nid": "Node ID",
 *     }
 *   }
 * )
 */
class DcParagraphsShowroom extends SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    // Add description of custom development.
    $query->addField('cs', 'field_erluterung_zu_eigenen_mod_value', 'custom_development');

    // Add node body.
    $query->join('node_revisions', 'nr', 'cs.vid = nr.vid');
    $query->addField('nr', 'body', 'description');

    // Add used core modules.
    $query->leftJoin('term_node', 'tn1', 'tn1.nid = cs.nid');
    $query->leftJoin('term_data', 'td1', 'td1.tid = tn1.tid AND td1.vid = :vid', [':vid' => 11]);
    $query->addExpression("GROUP_CONCAT(DISTINCT td1.name SEPARATOR ', ')", 'core_modules');

    // Add used contrib modules.
    $query->leftJoin('term_node', 'tn2', 'tn2.nid = cs.nid');
    $query->leftJoin('term_data', 'td2', 'td2.tid = tn2.tid AND td2.vid = :vid', [':vid' => 12]);
    $query->addExpression("GROUP_CONCAT(DISTINCT td2.name SEPARATOR ', ')", 'contrib_modules');

    $query->groupBy('cs.nid');
    $query->orderBy('cs.nid');
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = array()) {
    $fields['description'] = $this->t('Description of showroom entry');
    $fields['custom_development'] = $this->t('Description of custom development');
    $fields['core_modules'] = $this->t('List of used core module names');
    $fields['contrib_modules'] = $this->t('List of used contributed module names');
    $fields['content'] = $this->t('Internal property for field_content');
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }

    $nid = $row->getSourceProperty('nid');
    $content = [];
    for ($delta = 0; $delta < 4; $delta++) {
      $content[] = [
        'delta' => $delta,
        'id' => $nid,
      ];
    }
    $row->setSourceProperty('content', $content);

    return TRUE;
  }

}
