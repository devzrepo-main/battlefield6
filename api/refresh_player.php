<?php
require __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

// Fetch all players in DB
$result = $mysqli->query("SELECT ea_id, handle, platform FROM players");
if (!$result) {
    json_res(['error' => 'DB query failed'], 500);
}

$updated = [];

while ($row = $result->fetch_assoc()) {
    [$profile, $err] = tracker_get_profile($row['platform'], $row['handle'], $TRACKER_API_KEY);
    if ($err || !$profile) {
        $updated[] = ['handle' => $row['handle'], 'error' => $err];
        continue;
    }

    $stats = map_stats_from_tracker($profile);
    $stmt = $mysqli->prepare("
        UPDATE players
        SET kills=?, deaths=?, wins=?, losses=?, score=?, last_updated=NOW()
        WHERE ea_id=?
    ");
    $stmt->bind_param(
        'iiiiii',
        $stats['kills'],
        $stats['deaths'],
        $stats['wins'],
        $stats['losses'],
        $stats['score'],
        $stats['ea_id']
    );
    $stmt->execute();

    $updated[] = ['handle' => $row['handle'], 'status' => 'updated'];
}

json_res(['ok' => true, 'updated' => $updated]);
?>
