<?php

namespace Front\Controllers;

use Core\Form\Form;
use Core\Utils\PasswordUtils;
use Silex\Application;
use SocioChat\DAO\ActivationsDAO;
use SocioChat\DAO\UserDAO;
use SocioChat\Forms\Rules;
use Symfony\Component\HttpFoundation\Request;

class ActivationController
{
    /**
     * @var Application
     */
    private $app;
    private $config;

    public function activation(Request $request, Application $app)
    {
        $this->app = $app;
        $config = $app['config'];
        $this->config = $config;

        $password = $request->get('password', '');
        $passwordRepeat = $request->get('password-repeat', '');
        $email = $request->get('email', '');
        $code = $request->get('code', '');

        $form = new Form();
        $form->import(['email' => $email]);
        $form
            ->addRule(ActivationsDAO::EMAIL, Rules::email(), '', 'emailPattern')
            ->addRule(ActivationsDAO::EMAIL, function ($val) {
                $user = UserDAO::create()->getByEmail($val);
                return (bool)$user->getId();
            }, '', 'userSearch');

        $validation = $form->validate();

        if (!$validation) {
            return $this->errorResponse();
        }

        $activation = ActivationsDAO::create();
        if (!$result = $activation->getActivation($email, $code)) {
            return $this->errorResponse();
        }
        $activation = $result[0];
        /* @var $activation ActivationsDAO */

        if (!$activation->getId() || $activation->getIsUsed()) {
            return $this->errorResponse();
        }

        if ($activation->getCode() !== $code) {
            return $this->errorResponse();
        }

        if (strtotime($activation->getTimestamp()) + $config->activationTTL < time()) {
            $activation->setIsUsed(true);
            $activation->save();
            return $this->errorResponse();
        }

        if (!$password) {
            return $this->prepareResponse(new Form(), false, $email, $code);
        }

        $form = new Form();
        $form->import($request->request->all());
        $form
            ->addRule('password', Rules::password(), $app->trans('Activation.PasswordComplexity'))
            ->addRule('password-repeat', Rules::password(), $app->trans('Activation.PasswordComplexity'));

        $validation = $form->validate();

        if (!$validation) {
            return $this->prepareResponse($form, $validation, $email, $code);
        }

        if ($password !== $passwordRepeat) {
            $validation = false;
            $form->markWrong('password', $app->trans('Activation.PasswordsNotEqual'));
            return $this->prepareResponse($form, $validation, $email, $code);
        }

        $user = UserDAO::create()->getByEmail($email);
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $user->save();

        $activation->setIsUsed(true);
        $activation->save();

        return $app['twig']->render('common_page.twig', [
                'title' => $app->trans('Success'),
                'heading' => $app->trans('Success'),
                'message' => $this->app->trans('Activation.PasswordHasChanged'),
                'config' => $config,
            ]
        );

    }

    public function recovery(Application $app, Request $request)
    {
        $this->app = $app;
        $config = $app['config'];
        $this->config = $config;

        $app['session']->start();
        $email = $request->get('email', '');
        $token = $request->get('token', '');

        $sessionToken = $request->getSession()->get('token');

        if (!$email || !$token) {
            return $this->showFirst($email);
        }

        $form = new Form();
        $form->import($request->request->all());
        $form
            ->addRule(ActivationsDAO::EMAIL, Rules::email(), 'email в таком формате не может существовать.', 'emailPattern')
            ->addRule(
                ActivationsDAO::EMAIL,
                function ($val) {
                    $user = UserDAO::create()->getByEmail($val);
                    return (bool)$user->getId();
                }, 'Такой email не найден в системе.', 'userSearch');

        $validation = $form->validate();

        if (!$validation || $sessionToken != $token) {
            return $this->showFirst($email, $validation, $form);
        }

        $activation = ActivationsDAO::create();
        $activation->getByEmail($email);

        if ($activation->getId() && !$activation->getIsUsed()) {
            $activation->setIsUsed(true);
            $activation->save();
        }

        $activation = ActivationsDAO::create();
        $activation->fillParams(
            [
                ActivationsDAO::EMAIL => $email,
                ActivationsDAO::CODE => substr(base64_encode(PasswordUtils::get(64)), 0, 64),
                ActivationsDAO::TIMESTAMP => date('Y-m-d H:i:s'),
                ActivationsDAO::USED => false
            ]
        );
        $activation->save();

        $msg = "<h2>Восстановление пароля в СоциоЧате</h2>
<p>Была произведена процедура восстановления пароля с использованием вашего email.</p>
<p>Для подтверждения сброса пароля перейдите по <a href=\"" . $config->domain->protocol . $config->domain->web . "/activation?email=$email&code=".$activation->getCode() . "\">ссылке</a></p>
<p>Данная ссылка действительна до " . date('Y-m-d H:i', time() + $config->activationTTL) . "</p>";

        $mailer = \SocioChat\DAO\MailQueueDAO::create();
        $mailer
            ->setEmail($email)
            ->setTopic('Sociochat.me - Восстановление пароля')
            ->setMessage($msg);
        $mailer->save();

        return $this->app['twig']->render(
            'recovery/recovery2.twig',
            [
                'title' => $this->app->trans('Activation.PasswordRecovery'),
                'heading' => $this->app->trans('Activation.PasswordRecovery'),
                'hasError' => '',
                'email' => $email,
                'config' => $config,
            ]
        );
    }

    private function showFirst($email, $validation = null, Form $form = null)
    {
        $token = PasswordUtils::get(20);
        $this->app['session']->set('token', $token);

        return $this->app['twig']->render(
            'recovery/recovery1.twig',
            [
                'title' => $this->app->trans('Activation.PasswordRecovery'),
                'heading' => $this->app->trans('Activation.PasswordRecovery'),
                'hasError' => $validation === false ?  : '',
                'email' => $email,
                'errors' => $form->getErrors(),
                'config' => $this->config,
                'token' => $token
            ]
        );
    }

    private function prepareResponse(Form $form, $validation, $email, $code)
    {
        return $this->app['twig']->render(
            'activation/prepare.twig',
            [
                'title' => $this->app->trans('Activation.PasswordRecovery'),
                'heading' => $this->app->trans('Activation.PasswordRecovery'),
                'hasError' => $validation === false ?  : '',
                'message' => $this->app->trans('Activation.ErrorText'),
                'errors' => $form->getErrors(),
                'config' => $this->config,
                'email' => $email,
                'code' => $code,
            ]
        );
    }

    private function errorResponse()
    {
        return $this->app['twig']->render(
            'common_page.twig',
            [
                'title' => $this->app->trans('Error'),
                'heading' => $this->app->trans('Error'),
                'message' => $this->app->trans('Activation.ErrorText'),
                'config' => $this->app['config'],
            ]
        );
    }
}