<?php
$date = new DateTime();
$Ago = $date->modify('-1 seconds')->format('Y-m-d H:i:s');

$date = new DateTime();
$Later = $date->modify('1 seconds')->format('Y-m-d H:i:s');

echo "$Ago<br>";
echo "$Later<br>";