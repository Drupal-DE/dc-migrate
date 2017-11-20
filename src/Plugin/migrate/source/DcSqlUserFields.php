<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Row;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * SQL-based source plugin for user account fields.
 *
 * @MigrateSource(
 *   id = "dc_user__fields",
 *   table = {
 *     "name": "users",
 *     "alias": "u"
 *   },
 *   ids = {
 *     "uid": "integer"
 *   },
 *   fields = {
 *     "uid": "User ID",
 *     "signature": "Signature"
 *   }
 * )
 */
class DcSqlUserFields extends SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    $query->join('profile_values', 'pv', 'pv.uid = u.uid');
    $query->addExpression("GROUP_CONCAT(pv.value ORDER BY pv.fid SEPARATOR '§')", 'profile_values');
    $query->groupBy('u.uid');
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = []) {
    // Add profile fields.
    $fields['fullname'] = 'Full name';
    $fields['company'] = 'Company';
    $fields['location'] = 'User location';
    $fields['website'] = 'Website';
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }

    // Extract profile values.
    $profile_values = explode('§', $row->getSourceProperty('profile_values'));
    $row->setSourceProperty('fullname', isset($profile_values[0]) ? $profile_values[0] : NULL);
    $row->setSourceProperty('location', isset($profile_values[1]) ? $profile_values[1] : NULL);
    $row->setSourceProperty('company', isset($profile_values[4]) ? $profile_values[4] : NULL);

    $website = isset($profile_values[3]) ? $profile_values[3] : '';
    // Check if domain is valid (very simple check).
    if (empty($website) || !preg_match('#(http(?:s)?:\/\/)?(?:[\w-]+\.)*([\w-]{1,63})(?:\.(?:\w{3}|\w{2}))(?:$|\/)#i', $website)) {
      $row->setSourceProperty('website', NULL);
    }
    else {
      if (strpos($website, 'http://') === FALSE) {
        // Prepend website with protocol.
        $row->setSourceProperty('website', 'http://' . $website);
      }
    }
  }

}
