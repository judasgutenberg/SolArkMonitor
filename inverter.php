$energyApiUsername = 'youremail@domain.com';
$energyApiPassword = 'password';
$energyApiPlantId = xxxx;


$plantId = $energyApiPlantId;
$url = 'https://pv.inteless.com/oauth/token';
$headers = [
        'Content-Type: application/json;charset=UTF-8', // Set Content-Type header to application/json
 
 
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
//var_dump($response);
echo "<HR>";
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
        'date' => $currentDate,
        'id' => 16588,
        'lan' => 'en'         
];

// Make GET request to user endpoint
$queryString = http_build_query($userParams);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $actionUrl . "?" . $queryString);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . $access_token, // Set the Authorization header with your token
        "Accept: application/json",
    ));
$dataResponse = curl_exec($ch);
var_dump($dataResponse);
curl_close($ch);

$dataBody = json_decode($dataResponse, true);

var_dump($dataBody["data"]);
 
