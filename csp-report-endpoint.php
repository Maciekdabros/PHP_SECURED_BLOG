<?php
$logFile = 'csp_reports.log';
$handle = fopen($logFile, 'a');

$data = file_get_contents("php://input");

fwrite($handle, $data . "\n");
fclose($handle);

header("HTTP/1.1 204 No Content");