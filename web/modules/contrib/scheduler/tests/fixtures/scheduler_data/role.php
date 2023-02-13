<?php

/**
 * @file
 * A database agnostic dump for testing purposes.
 *
 * This file was generated by the Drupal 9.2.6 db-tools.php script.
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

$connection->schema()->createTable('role', [
  'fields' => [
    'rid' => [
      'type' => 'serial',
      'not null' => TRUE,
      'size' => 'normal',
      'unsigned' => TRUE,
    ],
    'name' => [
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '64',
      'default' => '',
    ],
    'weight' => [
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
    ],
  ],
  'primary key' => [
    'rid',
  ],
  'unique keys' => [
    'name' => [
      'name',
    ],
  ],
  'indexes' => [
    'name_weight' => [
      'name',
      'weight',
    ],
  ],
  'mysql_character_set' => 'utf8mb3',
]);