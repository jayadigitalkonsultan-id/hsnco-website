<?php

// Use the application's existing health route, without a database write.
$context = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
$result = @file_get_contents('http://127.0.0.1:8080/up', false, $context);
$status = $http_response_header[0] ?? '';
exit($result !== false && preg_match('/\s200\s/', $status) ? 0 : 1);
