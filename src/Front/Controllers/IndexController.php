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

class IndexController extends BaseController
{
    public function index(Request $request)
    {
        /** @var Config $config */
        $config = $this->app['config'];

        $httpAcceptLanguage = $request->getPreferredLanguage(['ru', 'en']);
        $lifetime = $config->session->lifetime;

        $cookies = $request->cookies;
        if (!$cookies->has('lang')) {
            $cookie = new Cookie('lang', $httpAcceptLanguage, time() + $lifetime, '/', '.' . $config->domain->web, 1);
        }

        $escapedFragment = null;
        if ($request->query->has('_escaped_fragment_')) {
            $fn = ROOT . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'chatlog.txt';
            $escapedFragment = file_get_contents($fn);
        }

        $content = $this->app['twig']->render('index.twig', [
            'escapedFragment' => $escapedFragment,
            'config' => $config,
            'title' => 'соционический чат без регистрации',
            'js' => '',
            'meta' => '<meta name="fragment" content="!">',
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

    public function faq(Request $request)
    {
        return $this->app['twig']->render('faq.twig', [
            'config' => $this->app['config'],
            'title' => 'частые вопросы',
        ]);
    }
}