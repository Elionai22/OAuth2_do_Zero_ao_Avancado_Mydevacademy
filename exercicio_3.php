<?php
header('Content-Type: application/json; charset=utf-8');

$json = '{
  "nodes": [
    {"id": "user", "label": "User"},
    {"id": "client_app", "label": "Client App"},
    {"id": "auth_server", "label": "Authorization Server"},
    {"id": "api_server", "label": "API Server"}
  ],
  "connections": [
    {"from": "user", "to": "client_app"},
    {"from": "client_app", "to": "auth_server"},
    {"from": "auth_server", "to": "client_app"},
    {"from": "client_app", "to": "api_server"}
  ]
}';

$data = json_decode($json, true);

$roles = [
    "Authorization Server" => [],
    "Resource Server" => [],
    "Client" => [],
    "Resource Owner" => []
];

foreach ($data['nodes'] as $node) {

    $label = strtolower($node['label']);

    if (strpos($label, 'auth') !== false) {
        $roles["Authorization Server"][] = $node['id'];
    } 
    elseif (strpos($label, 'api') !== false || strpos($label, 'resource') !== false) {
        $roles["Resource Server"][] = $node['id'];
    } 
    elseif (strpos($label, 'client') !== false || strpos($label, 'app') !== false) {
        $roles["Client"][] = $node['id'];
    } 
    elseif (strpos($label, 'user') !== false || strpos($label, 'owner') !== false) {
        $roles["Resource Owner"][] = $node['id'];
    }
}

$result = [
    "mapped_roles" => $roles
];

echo json_encode($result, JSON_PRETTY_PRINT);

?>