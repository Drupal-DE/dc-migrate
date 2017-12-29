<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\dc_migrate\Plugin\migrate\source\DcSqlFile;
use Drupal\migrate\Row;

/**
 * SQL-based source plugin for showroom images.
 *
 * @MigrateSource(
 *   id = "dc_file__showroom_image",
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
class DcFileShowroomImage extends DcSqlFile {

  /**
   * {@inheritdoc}
   */
  protected function alterQuery(SelectInterface $query) {
    parent::alterQuery($query);
    $query->join('content_type_showroom', 's', 'f.fid = s.field_screenshot_der_webseite_fid');
    $query->addField('s', 'field_screenshot_der_webseite_data', 'data');
  }

  /**
   * {@inheritdoc}
   */
  protected function alterFields(array &$fields = array()) {
    $fields['image_alt'] = $this->t('Alternative text');
    $fields['image_title'] = $this->t('Image title');
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $data = unserialize($row->getSourceProperty('data'));
    if (isset($data['alt'])) {
      $row->setDestinationProperty('image_alt', $data['alt']);
    }
    if (isset($data['title'])) {
      $row->setDestinationProperty('image_title', $data['title']);
    }

    return parent::prepareRow($row);
  }

}
