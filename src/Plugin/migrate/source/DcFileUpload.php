<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlFile;
use Drupal\migrate\MigrateException;

/**
 * SQL-based source plugin for file uploads.
 *
 * @MigrateSource(
 *   id = "dc_file_upload",
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
class DcFileUpload extends DcSqlFile {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    parent::alterQuery($query);
    $bundle = $this->getConfig('bundle');
    if (empty($bundle)) {
      throw new MigrateException('You need to specify the bundle in the plugin definition or in the migration.');
    }

    $query->join('upload', 'u', 'f.fid = u.fid');
    $query->join('node', 'n', 'n.nid = u.nid AND n.vid = u.vid');

    $query->addField('u', 'nid');
    $query->addField('u', 'description');
    $query->addField('u', 'list');
    $query->addField('u', 'weight');

    $query->condition('n.type', $bundle);
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = []) {
    parent::alterFields($fields);
    $fields['upload_nid'] = $this->t('Node, the file is attached to');
    $fields['file_description'] = $this->t('File description');
    $fields['file_list'] = $this->t('List file');
    $fields['file_weight'] = $this->t('Weight of file in node');
  }

}
