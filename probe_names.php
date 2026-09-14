<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$outFile = sys_get_temp_dir() . '/portal_names.txt';
$f = fopen($outFile, 'w');

$router = app('router');
$routes = $router->getRoutes();
$all = $routes->getRoutesByName();
$names = array_keys($all);
sort($names);

fwrite($f, "=== route NAMES containing: student | parent | news | gallery | page | public | home ===\n");
foreach ($names as $n) {
    if (preg_match('/student|parent|news|gallery|page|public|home/', $n)) {
        fwrite($f, '   ' . $n . PHP_EOL);
    }
}

fwrite($f, "\n=== EXACT URI -> name -> route() check for the targets controllers/seeds use ===\n");
$checks = [
    'cms.student.dashboard' => 'cms.student.dashboard',
    'cms.parent.dashboard' => 'cms.parent.dashboard',
    'cms.parent.billing' => 'cms.parent.billing',
    'cms.public.news' => 'cms.public.news',
    'public.home' => 'public.home',
    'public.news' => 'public.news',
    'portals.student.dashboard' => 'portals.student.dashboard',
    'portals.parent.dashboard' => 'portals.parent.dashboard',
];
foreach ($checks as $label => $name) {
    $ok = $router->has($name) ? 'REGISTERED' : 'MISSING';
    $uri = '';
    if ($router->has($name)) {
        $route = $all[$name];
        $uri = $route->uri();
    }
    fwrite($f, sprintf('   %-32s %-10s %s' . PHP_EOL, $label, $ok, $uri));
}

fclose($fört);
echo 'WROTE ' . $outFile . ' | bytes=' . filesize($outFile) . PHP_EOL;
