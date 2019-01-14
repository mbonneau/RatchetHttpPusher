<?php

$category = 'kittensCategory';
$entryData = array(
    'category' => $category
, 'title'    => 'The Kitten Title'
, 'article'  => 'The Kitten Article'
, 'when'     => time()
);

$context = stream_context_create([
    'http' => [
        'method' => "POST",
        'header' => "Content-type: application/json\r\n",
        'content' => json_encode($entryData)
    ]
]);

file_get_contents('http://127.0.0.1:8081/', null, $context);
