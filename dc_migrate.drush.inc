<?php

use Drupal\Core\Database\Database;
use Psr\Log\LogLevel;

/**
 * Implements hook_drush_command().
 */
function dc_migrate_drush_command() {
  $commands = [];

  $commands['dc-fix-upload-weights'] = [
    'description' => 'Fix weights of uploaded files',
    'core' => ['8+'],
    'arguments' => [
      'type' => "The type of the upload (one of 'node', 'comment'). Defaults to 'node'.",
    ],
    'options' => [
      'limit' => \dt('The number of items to process. Set to -1 to process all items. Defaults to -1 (all).'),
      'batch-size' => \dt('The number of items to process per batch run. Defaults to 100.'),
    ],
  ];

  return $commands;
}

/**
 * Finish callback for batch operation.
 *
 * @param type $success
 * @param type $results
 * @param type $operations
 */
function _dc_migrate_batch_finished($success, $results, $operations) {
  if ($success) {
    $message = 'Items processed: ' . $results['processed'];
  }
  else {
    $message = t('Finished with an error.');
  }

  drush_print($message);
}

/**
 * Command callback for "dc-fix-upload-weights".
 *
 * This is necessary, because in some cases all uploaded files for a node have a
 * weight of "0" so it is impossible to migrate this into the new structure,
 * where the delta ("weight") is part of the unique key.
 *
 * @param string $type
 *   Type of upload ("node" or "comment").
 */
function drush_dc_migrate_dc_fix_upload_weights($type = 'node') {
  if (!in_array($type, ['node', 'comment'])) {
    $type = 'node';
  }
  $options = [
    'type' => $type,
    'limit' => drush_get_option('limit', -1),
    'batch-size' => drush_get_option('batch-size', 100),
  ];
  if ($options['batch-size'] < 1) {
    $options['batch-size'] = 100;
  }
  if (empty($options['limit'])) {
    drush_print('No processing necessary; exit.');
    return;
  }

  $batch_definition = [
    'init_message' => 'Fix upload weights',
    'operations' => [
      ['_dc_migrate_dc_fix_upload_weights_process', [$options]],
    ],
    'progress_message' => 'Processed @current out of @total.',
    'finished' => '_dc_migrate_batch_finished',
  ];
  batch_set($batch_definition);
  $batch = &batch_get();
  $batch['progressive'] = TRUE;
  drush_backend_batch_process();
}

/**
 * Batch operation to fix upload weights.
 *
 * @param array $options
 *   Additional options for the batch.
 * @param array $context
 *   Context information used for the batch.
 */
function _dc_migrate_dc_fix_upload_weights_process($options, &$context) {
  // Setup database connection to source database. Unfortunately this can't be
  // done in the batch setup, because the connection is not serializable.
  if (($databases = \Drupal::state()->get('up_migrate.databases')) === NULL) {
    drush_log('No source databases defined. Run `drush upm-database-add` to add at a database.', LogLevel::WARNING);
    $context['finished'] = 1;
    return;
  }
  foreach ($databases as $key => $info) {
    $config = array_filter($info) + [
      'host' => 'localhost',
      'port' => '3306',
      'username' => '',
      'password' => '',
      'driver' => 'mysql',
      'namespace' => 'Drupal\Core\Database\Driver\mysql',
      'init_commands' => [
        // Use custom sql_mode so we disable "ONLY_FULL_GROUP_BY".
        'sql_mode' => "SET sql_mode = 'ANSI,STRICT_TRANS_TABLES,STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER'",
      ],
    ];
    // No need to check if the connection has been added before, since
    // addConnectionInfo() does this.
    Database::addConnectionInfo($key, 'default', $config);
  }
  if (empty($databases['dc_migrate'])) {
    drush_log('Source database "dc_migrate" is not defined. Run `drush upm-database-add` to add a database.', LogLevel::WARNING);
    $context['finished'] = 1;
    return;
  }
  $database = Database::getConnection('default', 'dc_migrate');

  $table = ('node' === $options['type']) ? 'upload' : 'comment_upload';
  $key_column = ('node' === $options['type']) ? 'vid' : 'cid';

  // Select all rows where at least 2 files are uploaded to 1 revision.
  $query = $database->select($table, 'u');
  $query->fields('u', [$key_column, 'fid', 'weight']);
  $query->addExpression('COUNT(*)', 'cnt');
  $query->groupBy("u.{$key_column}");
  $query->having('cnt > :cnt', [':cnt' => 1]);
  $query->orderBy("u.{$key_column}");
  $query->orderBy('u.weight');
  $query->orderBy('u.description');

  if (empty($context['sandbox']['max'])) {
    $context['finished'] = 0;
    $context['sandbox']['max'] = ($options['limit'] > -1) ? $options['limit'] : $query->countQuery()->execute()->fetchField();
    $context['sandbox']['progress'] = 0;
    $context['message'] = 'Fix upload weights ...';
    $context['results']['updated'] = 0;
    $context['results']['failures'] = 0;
    $context['results']['processed'] = 0;
  }

  // Limit resultset.
  $limit = ($options['limit'] === -1 || ($options['limit'] > $options['batch-size'])) ? $options['batch-size'] : $options['limit'];
  $query->range($context['results']['processed'], $limit);

  $rows = $query->execute()
    ->fetchAll();

  foreach ($rows as $row) {
    // Fetch all uploads for the specific revision.
    $subquery = $database->select($table, 'u');
    $subquery->fields('u', [$key_column, 'fid', 'weight']);
    $subquery->condition("u.{$key_column}", $row->{$key_column});
    $subquery->orderBy('u.weight');
    $subquery->orderBy('u.description');
    $uploads = $subquery->execute()->fetchAll();
    $transaction = $database->startTransaction("upload-{$row->{$key_column}}");
    foreach ($uploads as $key => $value) {
      // Simply set the key as weight to keep the original order.
      $value->weight = $key;
      // Write updated values back to source database.
      $args = [
        ':weight' => $value->weight, ':fid' => $value->fid, ':key_column_value' => $value->{$key_column}
      ];
      $query_string = "UPDATE {$table} SET weight = :weight WHERE fid = :fid AND {$key_column} = :key_column_value";
      $database->query($query_string, $args);
    }
    // Commit transaction.
    unset($transaction);

    $context['sandbox']['progress'] ++;
    $context['results']['processed'] ++;
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
  $context['message'] = sprintf('Processed %d out of %d items', $context['results']['processed'], $context['sandbox']['max']);
}
