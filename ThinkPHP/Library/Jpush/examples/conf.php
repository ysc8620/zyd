<?php
require __DIR__ . '/../autoload.php';

use JPush\Client as JPush;

#$app_key = getenv('app_key');
#$master_secret = getenv('master_secret');
#$registration_id = getenv('registration_id');
$app_key = "30b1dce198d525524980af61";
$master_secret = "c1281a437204064c2190979f";
$registration_id = "1a1018970aa0c6a908c";
$client = new JPush($app_key, $master_secret);
