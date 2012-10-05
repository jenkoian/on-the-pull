<?php
require_once __DIR__.'/../vendor/autoload.php';
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Github\Client;

$app = new Application();

$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../views'),
    'twig.options' => array('cache' => __DIR__.'/../cache'),
));

$app->register(new HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => __DIR__.'/../cache/',
));

$app->get('/', function () use ($app) {

    $client = new Github\Client();
    /**
     * Uncomment the following line, if you need to include private repositories.
     * There are a number of ways of configuring the github authentication, please see: https://github.com/KnpLabs/php-github-api/blob/master/doc/security.md
     */
    //$client->authenticate();

    $username = 'YOUR_USERNAME';

    // TODO: The following is super slow, speed this up.
    $repos = $client->api('organization')->repositories('boxuk');
    foreach ($repos as $repo) {
        $pullRequests[$repo['name']] = $client->api('pull_request')->all($username, $repo['name']);
    }

    $response = $app['twig']->render('index.twig', array(
        'pullRequests' => $pullRequests
    ));

    return new Response($response, 200, array(
        'Cache-Control' => 's-maxage=5'
    ));

});

$app->run();