<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Row;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * General SQL-based source plugin for user accounts.
 *
 * @MigrateSource(
 *   id = "dc_user"
 * )
 */
class DcSqlUser extends SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    // Do not migrate the anonymous user.
    $query->condition('u.uid', 0, '>');
    $query->orderBy('u.uid', 'ASC');
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }
    $roles = $this->select('users_roles', 'ur')
      ->fields('ur', ['rid'])
      ->condition('ur.uid', $row->getSourceProperty('uid'))
      ->execute()
      ->fetchCol();

    $row->setSourceProperty('roles', $roles);

    if ($data = $row->getSourceProperty('data')) {
      $row->setSourceProperty('data', unserialize($data));
    }
    return TRUE;
  }

}
