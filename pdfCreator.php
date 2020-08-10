<?php
//Параметры
$apikey = '55aecbc2861a5686955156e7d7604e50';
$value = 'http://d1vine.online/temp/html/';

$result = file_get_contents("http://api.pdf4b.ru/pdf?apikey=" . urlencode($apikey) . "&value=" . urlencode($value) . "&PageSize=A1");

file_put_contents('file.pdf', $result);
header("Location: http://d1vine.online/temp/html/file.pdf");