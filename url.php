<?php

use SocioChat\DI;
use SocioChat\DIBuilder;

require_once 'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');


$bitly = new \Hpatoio\Bitly\Client("9fad08233ce69edadef14b6be23f8f8dd48a0ab1");
$response = $bitly->shorten(['longUrl' => 'https://sociochat.me/ref.php?u=12', 'domain' => 'j.mp']);

echo $response['url'];
