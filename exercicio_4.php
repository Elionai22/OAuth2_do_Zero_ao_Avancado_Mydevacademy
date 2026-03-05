<?php
header('Content-Type: application/json; charset=utf-8');

$logs = [
    ["event" => "login_request", "actor" => "user"],
    ["event" => "authorization_request", "actor" => "client_app"],
    ["event" => "issue_token", "actor" => "auth_server"],
    ["event" => "api_request", "actor" => "client_app"],
    ["event" => "serve_resource", "actor" => "api_server"]
];

$report = [
    "Client" => [],
    "Authorization Server" => [],
    "Resource Server" => [],
    "Resource Owner" => []
];

foreach ($logs as $log) {

    $actor = strtolower($log["actor"]);

    if (strpos($actor, "client") !== false || strpos($actor, "app") !== false) {
        $report["Client"][] = $log;
    }

    elseif (strpos($actor, "auth") !== false) {
        $report["Authorization Server"][] = $log;
    }

    elseif (strpos($actor, "api") !== false || strpos($actor, "resource") !== false) {
        $report["Resource Server"][] = $log;
    }

    elseif (strpos($actor, "user") !== false) {
        $report["Resource Owner"][] = $log;
    }
}

echo json_encode($report, JSON_PRETTY_PRINT);

?>