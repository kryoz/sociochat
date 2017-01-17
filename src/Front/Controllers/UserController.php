<?php

namespace Front\Controllers;

use Core\BaseException;
use DateTime;
use Imagick;
use Silex\Application;
use SocioChat\DAO\NameChangeDAO;
use SocioChat\DAO\SessionDAO;
use SocioChat\DAO\UserDAO;
use SocioChat\DAO\UserKarmaDAO;
use SocioChat\Permissions\UserActions;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Zend\Config\Config;

class UserController extends BaseController
{
    public function upload(Request $request)
    {
        $app = $this->app;
        /** @var Config $config */
        $config = $app['config'];
        $avatarsConfig = $config->uploads->avatars;

        $token = $request->request->get('token');
        /** @var UploadedFile $img */
        $img = $request->files->get('img');
        $dim = $request->request->get('dim');

        if (!$img || $img->getError() != UPLOAD_ERR_OK || !$token || !$dim) {
            return $this->imgResponse(403, $app->trans('profile.IncorrectRequest'));
        }
        $uploadDir = ROOT . DIRECTORY_SEPARATOR . $avatarsConfig->dir . DIRECTORY_SEPARATOR;
        $uploadedName = sha1(time() . basename($img->getClientOriginalName()));
        $uploadedFile = $uploadDir . $uploadedName;
        $allowedMIME = ['image/gif', 'image/png', 'image/jpeg'];

        $dim = json_decode($dim, true);

        $token = SessionDAO::create()->getBySessionId($token);
        if (!$token->getId() || $dim === null) {
            return $this->imgResponse(403, $app->trans('profile.IncorrectRequest'));
        }

        if (!in_array($img->getMimeType(), $allowedMIME, 1)) {
            return $this->imgResponse(403, $app->trans('profile.IncorrectFileType'));
        }

        if ($img->getSize() > $avatarsConfig->maxsize) {
            return $this->imgResponse(403, $app->trans('profile.FileExceedsMaxSize'. ' ' . $avatarsConfig->maxsize));
        }

        if ($img->getError() != UPLOAD_ERR_OK || !$img->move($uploadDir, $uploadedName)) {
            return $this->imgResponse(403, $app->trans('profile.ErrorUploadingFile'));
        }

        try {
            $this->makeImage($uploadedFile, $avatarsConfig->thumbdim, 'png', '_t.png', $dim);
            $this->makeImage($uploadedFile, $avatarsConfig->thumbdim * 2, 'png', '_t@2x.png', $dim);
            $this->makeImage($uploadedFile, $avatarsConfig->maxdim, 'jpeg', '.jpg', $dim);
            $this->makeImage($uploadedFile, $avatarsConfig->maxdim * 2, 'jpeg', '@2x.jpg', $dim);
        } catch (BaseException $e) {
            $message = $app->trans('profile.ErrorProcessingImage') . ': ' . $e->getMessage();
            return $this->imgResponse(500, $message);
        }

        unlink($uploadedFile);

        return $this->imgResponse(200, 'OK', $uploadedName);
    }

    public function info($userId, Request $request)
    {
        $app = $this->app;
        /** @var SessionDAO $session */
        $session = SessionDAO::create()->getBySessionId($request->cookies->get('token'));
        if (!$session->getUserId()) {
            return new JsonResponse(['error' => 'Unauthorized'], 400);
        }
        
        $subject = UserDAO::create()->getById($userId);
        if (!$subject->getId()) {
            return new JsonResponse(['error' => 'No user found'], 400);
        }
        
        $owner = UserDAO::create()->getById($session->getUserId());
        $props = $subject->getPropeties();
        $avatarDir = $app['config']->uploads->avatars->wwwfolder . DIRECTORY_SEPARATOR;
        $note = $owner->getUserNotes()->getNote($subject->getId());
        $total = $props->getTotal();

        $dtF = new DateTime("@0");
        $dtT = new DateTime("@".$props->getOnlineCount());

        $names = [];
        foreach (NameChangeDAO::create()->getHistoryByUserId($subject->getId()) as $name) {
            /** @var NameChangeDAO $name */
            $names[] = $name->getName();
        }

        $response = [
            'id'    => $subject->getId(),
            'name' => $props->getName(),
            'about' => nl2br($props->getAbout()),
            'avatar' => $props->getAvatarImg() ? $avatarDir.$props->getAvatarImg() : null,
            'tim' => $props->getTim()->getName(),
            'sex' => $props->getSex()->getName(),
            'city' => $props->getCity(),
            'birth' => $props->getAge() ?: $app->trans('NotSpecified'),
            'note' => $note ?: '',
            'karma' => $props->getKarma(),
            'dateRegister' => $subject->getDateRegister(),
            'onlineTime' => $dtF->diff($dtT)->format('%a дней %h часов %i минут'),
            'wordRating' => $props->getWordRating() ? $props->getWordRating() . '-й из '. $total : $app->trans('NotSpecified'),
            'rudeRating' => $props->getRudeRating() ? $props->getRudeRating() . '-й из '. $total : $app->trans('NotSpecified'),
            'musicRating' => $props->getMusicRating() ? $props->getMusicRating() . '-й из '. $total : $app->trans('NotSpecified'),
            'names' => implode(', ', $names),
        ];

        $userActions = new UserActions($owner);
        $response['allowed'] = $userActions->getAllowed($subject);

        return new JsonResponse($response, 200);
    }

    public function karmaDetails($userId, Request $request)
    {
        /** @var SessionDAO $session */
        $session = SessionDAO::create()->getBySessionId($request->cookies->get('token'));
        if (!$session->getUserId()) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        $user = UserDAO::create()->getById($session->getUserId());
        $subject = UserDAO::create()->getById($userId);
        
        if (!$subject->getId()) {
            return new JsonResponse(['error' => 'No user found'], 400);
        }

        if ($subject->getId() !== $user->getId() && !$user->getRole()->isAdmin()) {
            return new JsonResponse(['error' => 'Allowed only to owner'], 403);
        }
        
        $response = UserKarmaDAO::create()->getMarksList($subject->getId(), 3);

        return new JsonResponse($response, 200);
    }

    private function imgResponse($code, $message, $image = null)
    {
        return new JsonResponse(
            [
                'success' => $code === 200,
                'response' => $message,
                'image' => $image
            ],
            $code
        );
    }

    private function makeImage($uploadedFile, $dim, $format, $extension, array $coords)
    {
        $imagick = new Imagick();
        $imagick->readImage($uploadedFile);
        $imgWidth = $imagick->getImageWidth();
        $imgHeight = $imagick->getImageHeight();

        if (
            $coords['w'] > $imgWidth ||
            $coords['h'] > $imgHeight ||
            $coords['x'] > $imgWidth ||
            $coords['y'] > $imgHeight ||
            $coords['portW'] > $imgWidth ||
            $coords['portH'] > $imgHeight
        )
        {
            throw new BaseException('Invalid crop data');
        }

        $xFactor = $imgWidth / $coords['portW'];
        $yFactor = $imgHeight / $coords['portH'];

        $imagick->cropImage(
            $xFactor * $coords['w'],
            $yFactor * $coords['h'],
            $xFactor * $coords['x'],
            $yFactor * $coords['y']
        );

        $imgWidth = $imagick->getImageWidth();
        $imgHeight = $imagick->getImageHeight();

        if ($imgHeight > $dim || $imgWidth > $dim) {
            if ($imgHeight > $imgWidth) {
                $imagick->resizeImage(0, $dim, Imagick::FILTER_CATROM, 1);
            } else {
                $imagick->resizeImage($dim, 0, Imagick::FILTER_CATROM, 1);
            }
        }

        $imagick->setImageFormat($format);

        $image = $uploadedFile . $extension;
        if (file_exists($image)) {
            unlink($image);
        }
        $imagick->writeImage($image);
    }
}