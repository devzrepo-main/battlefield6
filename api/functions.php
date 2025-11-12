<?php
require_once __DIR__ . '/../config.php';

/**
 * Fetch Battlefield 6 player profile using the Gametools public API.
 * Works for PSN, Xbox, Steam, and PC platforms.
 */
function tracker_get_profile($platform, $username, $apiKey = null) {
    $url = "https://api.gametools.network/bf6/stats/?name=" . urlencode($username) . "&platform=" . urlencode($platform);

    // --- Use cURL for reliability ---
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/122.0.0.0 Safari/537.36",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Referer: https://www.google.com/"
        ]
    ]);

    $json = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($json === false || $httpCode >= 400 || strpos($json, '<html') !== false) {
        return [null, "HTTP $httpCode - cURL error: $err"];
    }

    $data = json_decode($json, true);
    if ($data === null) {
        return [null, 'Invalid JSON'];
    }

    // Ensure it contains at least a name or ID
    if (!isset($data['userName'])) {
        return [null, 'Profile not found or invalid response'];
    }

    return [$data, null];
}

/**
 * Map Gametools Battlefield 6 JSON into your local DB structure.
 */
function map_stats_from_tracker($profile) {
    // Detect any possible user ID key
    $ea_id = 0;
    if (isset($profile['userId'])) {
        $ea_id = intval($profile['userId']);
    } elseif (isset($profile['playerId'])) {
        $ea_id = intval($profile['playerId']);
    } elseif (isset($profile['id'])) {
        $ea_id = intval($profile['id']);
    } elseif (isset($profile['personaId'])) {
        $ea_id = intval($profile['personaId']);
    }

    return [
        'ea_id'    => $ea_id,
        'handle'   => $profile['userName'] ?? '',
        'platform' => strtolower($profile['platform'] ?? 'unknown'),
        'kills'    => intval($profile['kills'] ?? 0),
        'deaths'   => intval($profile['deaths'] ?? 0),
        'wins'     => intval($profile['wins'] ?? ($profile['matches'] ?? 0)),
        'losses'   => intval($profile['loses'] ?? 0),
        'score'    => intval($profile['XP'][0]['total'] ?? 0)
    ];
}

/**
 * Send JSON response.
 */
function json_res($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}
?>
