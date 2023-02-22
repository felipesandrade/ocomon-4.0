<?php
require __DIR__ . "/assets/config.php";
require __DIR__ . "/" . "../src/OcomonApi.php";
require __DIR__ . "/" . "../src/Tickets.php";

use ocomon_api_access\OcomonApi\Tickets;

$tickets = new Tickets(
    "localhost/ocomon/ocomon_api/",
    "admin",
    "asnarasfalconpoqw"
);

/**
 * index
 */
echo "<h1>INDEX</h1>";
$index = $tickets->index(null);
// $index = $tickets->index([
//     "number" => 200, 
// ]);

if ($index->error()) {
    var_dump($index->error());
} else {
    var_dump($index->response());
}

/**
 * create
 */
echo "<h1>CREATE</h1>";

$create = $tickets->create([
    'description' => 'Chamado aberto em teste de utilização da API',
]);

if ($create->error()) {
    echo "<p class='error'>{$create->error()->message}</p>";
} else {
    var_dump($create->response());
}

/**
 * READ
 */
echo "<h1>READ</h1>";

$read = $tickets->read(24);

if ($read->error()) {
    echo "<p class='error'>{$read->error()->message}</p>";
} else {
    var_dump($read->response());
}

/**
 * UPDATE
 */
echo "<h1>UPDATE</h1>";

$update = $tickets->update(29, ["wallet" => "Updated By PHP API"]);

if ($update->error()) {
    echo "<p class='error'>{$update->error()->message}</p>";
} else {
    var_dump($update->response());
}

