<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Row;
use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * General SQL-based source plugin for managed files.
 *
 * @MigrateSource(
 *   id = "dc_file",
 *   table = {
 *     "name": "files",
 *     "alias": "f",
 *     "ids": {
 *       "fid": "integer"
 *     },
 *     "fields": {
 *       "fid": "File ID",
 *       "uid": "Owner ID",
 *       "uri": "File URI",
 *       "directory": "File directory",
 *       "filename": "File name",
 *       "filepath": "File path (incl. scheme)",
 *       "filemime": "Mime type",
 *       "filesize": "Size of file",
 *       "status": "Status of file (permanent/temporary)",
 *       "timestamp": "File creation date"
 *     }
 *   }
 * )
 */
class DcSqlFile extends SqlBase {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    // Duplicate filepath as "uri".
    $query->addField('f', 'filepath', 'uri');

    // Filter by mime type if necessary.
    $mime_type = $this->getConfig('mime_type');
    if (!empty($mime_type)) {
      $query->condition('f.filemime', '%' . $this->database->escapeLike($mime_type) . '%', 'LIKE');
    }

    $query->distinct();
    $query->orderBy('f.fid', 'ASC');
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
