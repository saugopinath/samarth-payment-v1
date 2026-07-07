<?php
$ch = curl_init('http://samarth-v1.test/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
echo "LOGIN REDIRECTS TO:\n";
echo $response;

$ch2 = curl_init('http://samarth-v1.test/Admin/dashboard');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HEADER, true);
curl_setopt($ch2, CURLOPT_NOBODY, true);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, false);
$response2 = curl_exec($ch2);
echo "\nDASHBOARD REDIRECTS TO:\n";
echo $response2;
