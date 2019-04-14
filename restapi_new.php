<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$userData = array("username" => "selvi", "password" => "selvi@123");
$ch = curl_init("https://2htl3jvdh82ivi2s.mojostratus.io/rest/V1/integration/admin/token");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));

$token = curl_exec($ch);
//var_dump(json_decode($token,true));
//exit;
$request_url='https://2htl3jvdh82ivi2s.mojostratus.io/rest/V1/customOption';
$data_json = [
    "customOption" => [
        "sku" => "EF005757BAW",
        "options" => "processing"
    ]
];
$data_string = json_encode($data_json);
$ch = curl_init($request_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_string),
    "Authorization: Bearer " . json_decode($token))
);
$response = curl_exec($ch);
$response =  json_decode($response);
print_r($response);
curl_close($ch);