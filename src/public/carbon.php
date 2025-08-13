<?php
require '../vendor/autoload.php';

use Carbon\Carbon;

$dt = Carbon::now();
$year = $dt->addyear()->year;
$month = $dt->addmonth()->month;
$day = $dt->addday()->day;
$hour = $dt->addhour()->hour;
$minute = $dt->addminute()->minute;
$second = $dt->addsecond()->second;

echo "$year/";
echo "$month/";
echo "$day/";
echo "$hour:";
echo "$minute:";
echo "$second<br>";

$dt = Carbon::now();
$year = $dt->subyear()->year;
$month = $dt->submonth()->month;
$day = $dt->subday()->day;
$hour = $dt->subhour()->hour;
$minute = $dt->subminute()->minute;
$second = $dt->subsecond()->second;

echo $dt->format('Y/m/d H:i:s') . "<br>";
