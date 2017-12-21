<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlFile;
use Drupal\migrate\MigrateException;

/**
 * SQL-based source plugin for file uploads.
 *
 * @MigrateSource(
 *   id = "dc_file_comment_upload",
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
class DcFileCommentUpload extends DcSqlFile {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    parent::alterQuery($query);
    $bundle = $this->getConfig('bundle');
    if (empty($bundle)) {
      throw new MigrateException('You need to specify the bundle in the plugin definition or in the migration.');
    }

    $query->join('comment_upload', 'u', 'f.fid = u.fid');
    $query->join('node', 'n', 'n.nid = u.nid');

    $query->addField('u', 'cid');
    $query->addField('u', 'nid');
    $query->addField('u', 'description', 'file_description');
    $query->addField('u', 'list', 'file_display');
    $query->addField('u', 'weight', 'file_weight');

    $query->condition('n.type', $bundle);
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = []) {
    parent::alterFields($fields);
    $fields['cid'] = $this->t('Comment, the file is attached to');
    $fields['nid'] = $this->t('Node, the comment is associated to');
    $fields['file_description'] = $this->t('File description');
    $fields['file_display'] = $this->t('Display file');
    $fields['file_weight'] = $this->t('Weight of file in node');
  }

}
