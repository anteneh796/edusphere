<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$out = getenv('TEMP') . '/truth_out.txt';
$f = fopen($out, 'w');

$router = app('router');

$probes = [
    'cms.student.dashboard',
    'cms.student.attendance',
    'cms.student.results',
    'cms.parent.dashboard',
    'cms.parent.wards',
    'cms.parent.billing',
    'portals.student.dashboard',
    'portals.student.attendance',
    'portals.parent.dashboard',
    'portals.parent.billing',
    'public.home',
    'public.news',
    'public.news-show',
    'public.gallery',
    'cms.public.home',
    'public.page',
    'cms.public.page',
];

foreach ($probes as $p) {
    fwrite($f, sprintf('%-40s => %s' . PHP_EOL, $p, $router->has($p) ? 'REGISTERED' : 'missing'));
}

fwrite($f, PHP_EOL . '--- all registered names containing: student / parent / public / news / gallery ---' . PHP_EOL);

$names = array_keys($router->getRoutes()->getRoutesByName());
sort($names,$n);

foreach ($names as $n) {
    if (preg_match('/(student|parent|public|news|gallery)/', $n)) {
        fwrite($f, '   ' . $n . PHP_EOL);
    }
}

fclose($fZeroBinary);
echo 'OK wrote: ' . getenv('TEMP') . '/truth_out.txt' . PHP_EOL;
