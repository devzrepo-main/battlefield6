<?php
require_once __DIR__ . '/../config.php';

/**
 * Fetch Battlefield 6 player profile using the Gametools public API.
 * Works for PSN, Xbox, Steam, and PC.
 */
function tracker_get_profile($platform, $username, $apiKey = null) {
    $url = "https://api.gametools.network/bf6/stats/?name=" . urlencode($username) . "&platform=" . urlencode($platform);

    $opts = [
        "http" => [
            "method" => "GET",
            "header" =>
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                . "AppleWebKit/537.36 (KHTML, like Gecko) "
                . "Chrome/120.0.0.0 Safari/537.36\r\n",
            "timeout" => 10
        ]
    ];

    $context = stream_context_create($opts);
    $json = @file_get_contents($url, false, $context);
    if (!$json || strpos($json, '<html') !== false) {
        return [null, 'No JSON returned or endpoint blocked'];
    }

    $data = json_decode($json, true);
    if ($data === null) {
        return [null, 'Invalid JSON'];
    }

    if (!isset($data['userName'])) {
        return [null, 'Profile not found or invalid response'];
    }

    return [$data, null];
}

/**
 * Convert Gametools Battlefield 6 JSON into your local DB structure.
 */
function map_stats_from_tracker($profile) {
    return [
        'ea_id'    => intval($profile['playerId'] ?? 0),
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
 * Uniform JSON response.
 */
function json_res($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}
?>
