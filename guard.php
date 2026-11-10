<?php
define('RL_DIR', __DIR__ . '/rl');

function rl_ip() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $partes = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($partes[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function rl_white_lista() {
    $f = __DIR__ . '/rl_white.txt';
    if (!file_exists($f)) return [];
    $lineas = file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return $lineas ? array_map('trim', $lineas) : [];
}

function rl_ua_bot() {
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($ua === '') return false;
    $lista = ['googlebot', 'bingbot', 'bingpreview', 'facebookexternalhit', 'whatsapp',
              'twitterbot', 'telegrambot', 'linkedinbot', 'discordbot', 'slackbot', 'embedly'];
    foreach ($lista as $p) {
        if (strpos($ua, $p) !== false) return true;
    }
    return false;
}

function rl_check() {
    $ip = rl_ip();
    if (in_array($ip, rl_white_lista(), true)) return true;
    if (!is_dir(RL_DIR)) @mkdir(RL_DIR, 0775, true);
    $f = RL_DIR . '/' . md5($ip) . '.json';
    $ahora = time();
    $d = ['hits' => [], 'block' => 0];
    if (file_exists($f)) {
        $j = json_decode((string)@file_get_contents($f), true);
        if (is_array($j)) {
            if (isset($j['hits']) && is_array($j['hits'])) $d['hits'] = $j['hits'];
            if (isset($j['block'])) $d['block'] = (int)$j['block'];
        }
    }
    if (!empty($d['block']) && $ahora < $d['block']) return false;
    $hits = [];
    foreach ($d['hits'] as $t) { if ((int)$t > $ahora - 60) $hits[] = (int)$t; }
    $hits[] = $ahora;
    $d['hits'] = $hits;
    if (count($hits) > 12) {
        $d['hits'] = [];
        $d['block'] = $ahora + 90;
        @file_put_contents($f, json_encode($d));
        return false;
    }
    @file_put_contents($f, json_encode($d));
    return true;
}