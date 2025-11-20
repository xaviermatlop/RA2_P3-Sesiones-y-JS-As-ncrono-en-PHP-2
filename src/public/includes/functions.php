<?php
function load_json($path) {
    if (!file_exists($path)) return [];
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function save_json($path, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($path, $json) !== false;
}

function old_field(string $name, array $source = []): string {
    return isset($source[$name]) ? $source[$name] : "";
}

function field_error($name, $errors = []) {
    return isset($errors[$name]) ? '<p class="form-error" style="color:red;">' . $errors[$name] . '</p>' : '';
}
