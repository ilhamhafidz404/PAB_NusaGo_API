<?php
function resError($message = 'Terjadi kesalahan', $error = "", $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'message' => $message,
        'error' => $error
    ]);
    exit;
}

function sendSuccess($data = [], $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
    exit;
}
