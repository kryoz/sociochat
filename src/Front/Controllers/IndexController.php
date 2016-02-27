<?php

namespace Front\Controllers;

use Silex\Application;
use SocioChat\Enum\MsgAnimationEnum;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;
use SocioChat\Forms\Rules;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zend\Config\Config;

class IndexController
{
    public function index(Application $app, Request $request)
    {
        /** @var Config $config */
        $config = $app['config'];

        $httpAcceptLanguage = $request->getPreferredLanguage(['ru', 'en']);

        $lifetime = $config->session->lifetime;
        $hostURL = $config->domain->protocol . $config->domain->web;
        $maxMsgLength = $config->msgMaxLength;

        $cookies = $request->cookies;
        if (!$cookies->has('lang')) {
            $cookie = new Cookie('lang', $httpAcceptLanguage, time() + $lifetime, '/', '.' . $config->domain->web, 1);
        }

        $escapedFragment = null;
        if ($request->get('_escaped_fragment_', '')) {
            $fn = ROOT . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'chatlog.txt';
            $escapedFragment = file_get_contents($fn);
        }

        $content = $app['twig']->render('index.twig', [
            'hostUrl' => $hostURL,
            'maxMsgLength' => $maxMsgLength,
            'escapedFragment' => $escapedFragment,
            'config' => $config,
            'title' => 'соционический чат без регистрации',
            'js' => '',
            'meta' => '',
            'TimEnumList' => TimEnum::getList(),
            'SexEnumList' => SexEnum::getList(),
            'YearsRange' => Rules::getBirthYearsRange(),
            'MsgAnimationEnum' => MsgAnimationEnum::getList(),
        ]);

        $response = new Response($content);

        if (!empty($cookie)) {
            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}