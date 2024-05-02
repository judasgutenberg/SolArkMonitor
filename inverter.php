<?php
//now with zero dependencies (unless you count CURL).

$energyApiUsername = 'your_email@wherever.com';
$energyApiPassword = 'your_password';
$energyApiPlantId = your_plant_id;
 

 
$plantId = $energyApiPlantId;
$url = 'https://pv.inteless.com/oauth/token';
$headers = [
        'Content-Type: application/json;charset=UTF-8', // Set Content-Type header to application/json
        'accept: application/json',
        'Sec-Fetch-Mode: cors',
        'Origin: https://pv.inteless.com',
        'Accept: application/json',
        'Accept-Encoding:   ',
        'Accept-Language: en-US,en;q=0.9',
        'Sec-Ch-Ua: "Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
        'Sec-Ch-Ua-Platform: Windows',
        'Sec-Ch-Ua-Mobile: ?0',
        'Priority: u=1, i',
        'Referer: https://pv.inteless.com/login',
        'Content-Length: 127',
        'Content-Type: application/json;charset=UTF-8',
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Site: same-origin',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
];
$params = [
        'client_id' => 'csp-web',
        'grant_type' => 'password',
        'password' => $energyApiPassword,
        'username' => $energyApiUsername,
        'source' => 'elinter',
];

$ch = curl_init();
// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Set custom headers

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo 'Curl error: ' . curl_error($ch);
}

curl_close($ch);

 
$bodyData = json_decode($response, true);
$data = $bodyData["data"];
$access_token = $data['access_token'];
$currentDateTime = new DateTime('now', new DateTimeZone('America/New_York')); 
//echo $currentDateTime->format('Y-m-d h:i:s');
$currentDate =  $currentDateTime->format('Y-m-d');
$actionUrl = 'https://pv.inteless.com/api/v1/plant/energy/' . $plantId  . '/day?date=' . $currentDate . "&id=" . $plantId . "&lan=en";
$actionUrl = 'https://pv.inteless.com/api/v1/plant/energy/' . $plantId  . '/flow?date=' . $currentDate . "&id=" . $plantId . "&lan=en"; 
$actionUrl = 'https://pv.inteless.com/api/v1/plant/energy/' . $plantId  . '/flow';
$userParams =   [
        'access_token' => $access_token,
        'date' => $currentDate,
        'id' => 16588,
        'lan' => 'en'         
];

// Make GET request to user endpoint
$queryString = http_build_query($userParams);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $actionUrl . "?" . $queryString);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$dataResponse = curl_exec($ch);
curl_close($ch);

$dataBody = json_decode($dataResponse, true);

var_dump($dataBody["data"]);
 
