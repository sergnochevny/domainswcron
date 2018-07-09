<?php
/**
 */

require_once('Autoload.php');

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

$cron = new CronWalker();
$cron->Walk();
exit;