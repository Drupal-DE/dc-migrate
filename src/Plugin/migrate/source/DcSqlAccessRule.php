<?php

namespace Drupal\dc_migrate\Plugin\migrate\source;

use Drupal\up_migrate\Plugin\migrate\source\SqlBase;

/**
 * SQL-based source plugin for user access rules.
 *
 * @MigrateSource(
 *   id = "dc_access_rules",
 *   table = {
 *     "name": "access",
 *     "alias": "a",
 *     "ids": {
 *       "aid": "integer"
 *     },
 *     "fields": {
 *       "aid": "Access rule ID",
 *       "mask": "Rule pattern",
 *       "type": "Rule type",
 *       "status": "Deny or allow"
 *     }
 *   }
 * )
 */
class DcSqlAccessRule extends SqlBase {

}
