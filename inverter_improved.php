<?php
/**
 * SolArkMonitor – SolArk/SunSynk Cloud API Client
 * API base: https://api.solarkcloud.com
 *
 * Usage: Configure the credentials below, then run via CLI or a web request.
 * Returns the current energy flow data for your plant as a decoded PHP array.
 */

// ─── CONFIG ──────────────────────────────────────────────────────────────────
$energyApiUsername = 'your@email.com';       // Your SolArkCloud login email
$energyApiPassword = 'top_secret_password';  // Your SolArkCloud password
$energyApiPlantId  = 12345;                  // Your numeric plant ID
$timezone          = 'America/New_York';     // Adjust to your timezone
// ─────────────────────────────────────────────────────────────────────────────

$baseUrl = 'https://api.solarkcloud.com';

// ─── STEP 1: Authenticate and obtain Bearer token ────────────────────────────
function solark_get_token(string $baseUrl, string $username, string $password): string
{
    $url     = $baseUrl . '/oauth/token';
    $headers = [
        'Content-Type: application/json;charset=UTF-8',
        'origin: '  . $baseUrl . '/oauth/token',
        'referer: ' . $baseUrl . '/oauth/token',
    ];
    $payload = json_encode([
        'client_id'  => 'csp-web',
        'grant_type' => 'password',
        'username'   => $username,
        'password'   => $password,
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("cURL error during authentication: $err");
    }
    curl_close($ch);

    $body = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("Invalid JSON response from auth endpoint.");
    }

    if (empty($body['data']['access_token'])) {
        $msg = $body['msg'] ?? 'Unknown error';
        throw new RuntimeException("Authentication failed: $msg");
    }

    return $body['data']['access_token'];
}

// ─── STEP 2: Fetch energy flow data for today ────────────────────────────────
function solark_get_energy_flow(string $baseUrl, string $token, int $plantId, string $date): array
{
    $endpoint    = $baseUrl . '/api/v1/plant/energy/' . $plantId . '/flow';
    $queryString = http_build_query([
        'date' => $date,
        'id'   => $plantId,
        'lan'  => 'en',
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $endpoint . '?' . $queryString,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("cURL error fetching energy flow: $err");
    }
    curl_close($ch);

    $body = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("Invalid JSON response from energy flow endpoint.");
    }

    if (empty($body['data'])) {
        $msg = $body['msg'] ?? 'No data returned';
        throw new RuntimeException("Energy flow request failed: $msg");
    }

    return $body['data'];
}

// ─── STEP 3: Fetch daily energy totals ───────────────────────────────────────
function solark_get_energy_day(string $baseUrl, string $token, int $plantId, string $date): array
{
    $endpoint    = $baseUrl . '/api/v1/plant/energy/' . $plantId . '/day';
    $queryString = http_build_query([
        'date' => $date,
        'id'   => $plantId,
        'lan'  => 'en',
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $endpoint . '?' . $queryString,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("cURL error fetching daily energy: $err");
    }
    curl_close($ch);

    $body = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("Invalid JSON response from day energy endpoint.");
    }

    if (empty($body['data'])) {
        $msg = $body['msg'] ?? 'No data returned';
        throw new RuntimeException("Day energy request failed: $msg");
    }

    return $body['data'];
}

// ─── MAIN ─────────────────────────────────────────────────────────────────────
try {
    $currentDate = (new DateTime('now', new DateTimeZone($timezone)))->format('Y-m-d');

    // 1. Get auth token
    $token = solark_get_token($baseUrl, $energyApiUsername, $energyApiPassword);
    echo "Token obtained successfully.\n";

    // 2. Get real-time energy flow
    $flowData = solark_get_energy_flow($baseUrl, $token, $energyApiPlantId, $currentDate);
    echo "\n=== Energy Flow (real-time) ===\n";
    echo json_encode($flowData, JSON_PRETTY_PRINT) . "\n";

    // 3. Get daily totals
    $dayData = solark_get_energy_day($baseUrl, $token, $energyApiPlantId, $currentDate);
    echo "\n=== Daily Energy Totals ===\n";
    echo json_encode($dayData, JSON_PRETTY_PRINT) . "\n";

    // ── Example: automation decision based on solar watts ──
    $solarWatts = $flowData['pvPower'] ?? 0;   // key name may vary; inspect your output
    if ($solarWatts > 1000) {
        echo "\nSun is out ($solarWatts W) — safe to run high loads.\n";
    } else {
        echo "\nLow solar output ($solarWatts W) — conserve energy.\n";
    }

} catch (RuntimeException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
