<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * SQL-based source plugin for user pictures.
 *
 * @MigrateSource(
 *   id = "dc_file__user_picture",
 *   table = {
 *     "name": "users",
 *     "alias": "u",
 *     "ids": {
 *       "uid": "integer"
 *     },
 *     "fields": {
 *       "uid": "Owner ID",
 *       "uri": "File URI",
 *       "directory": "File directory",
 *       "filename": "File name",
 *       "filepath": "File path (incl. scheme)",
 *     }
 *   }
 * )
 */
class DcFileUserPicture extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Rewrite query.
    $query = $this->select('users', 'u');
    $query->addField('u', 'uid');
    $query->addField('u', 'picture', 'uri');
    $query->condition('u.picture', '', '<>');
    $query->isNotNull('u.picture');

    return $query->distinct()->orderBy('u.uid');
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Compute the filepath property, which is a physical representation of the
    // URI relative to the configured source path.
    $path = str_replace(['public:/', 'private:/', 'temporary:/'], ['public', 'private', 'temporary'], $row->getSourceProperty('uri'));
    $row->setSourceProperty('filepath', $path);
    // Extract directory.
    $row->setSourceProperty('directory', substr($path, 0, strrpos($path, '/') + 1));
    $row->setSourceProperty('filename', substr($path, strrpos($path, '/') + 1));

    return parent::prepareRow($row);
  }

}
