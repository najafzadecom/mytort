<?php
namespace MailPoetVendor\Doctrine\DBAL\Logging;
if (!defined('ABSPATH')) exit;
use MailPoetVendor\Doctrine\Deprecations\Deprecation;
use function var_dump;
use const PHP_EOL;
class EchoSQLLogger implements SQLLogger
{
 public function __construct()
 {
 Deprecation::trigger('doctrine/dbal', 'https://github.com/doctrine/dbal/pull/3935', 'EchoSQLLogger is deprecated without replacement, move the code into your project if you rely on it.');
 }
 public function startQuery($sql, ?array $params = null, ?array $types = null)
 {
 echo $sql . PHP_EOL;
 if ($params) {
 var_dump($params);
 }
 if (!$types) {
 return;
 }
 var_dump($types);
 }
 public function stopQuery()
 {
 }
}
