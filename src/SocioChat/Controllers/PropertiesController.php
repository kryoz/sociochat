<?php
namespace SocioChat\Controllers;

use Core\Utils\DbQueryHelper;
use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\ChannelNotifier;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\NameChangeDAO;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\UserKarmaDAO;
use SocioChat\DI;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;
use Core\Form\Form;
use SocioChat\Forms\Rules;
use Core\Form\WrongRuleNameException;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;
use SocioChat\Response\UserPropetiesResponse;
use SocioChat\Utils\CharTranslator;
use Zend\Config\Config;

class PropertiesController extends ControllerBase
{
    private $actionsMap = [
        'uploadAvatar' => 'processUpload',
	    'removeAvatar' => 'processRemoveAvatar',
	    'addKarma' => 'addKarma',
	    'decreaseKarma' => 'decreaseKarma',
        'submit' => 'processSubmit'
    ];

    public function handleRequest(ChainContainer $chain)
    {
        $action = $chain->getRequest()['action'];
        if (!isset($this->actionsMap[$action])) {
            RespondError::make($chain->getFrom());
            return;
        }

        $this->{$this->actionsMap[$action]}($chain);
    }

    protected function getFields()
    {
        return ['action'];
    }

    protected function processUpload(ChainContainer $chain)
    {
        $image = isset($chain->getRequest()['image']) ? $chain->getRequest()['image'] : null;
        $user = $chain->getFrom();
        $lang = $user->getLang();
        /* @var $config Config */
        $config = DI::get()->getConfig();
        $dir = $config->uploads->avatars->dir . DIRECTORY_SEPARATOR;

        if (!$image || !file_exists($dir . $image . '.jpg')) {
            RespondError::make($user, ['image' => $lang->getPhrase('profile.IncorrectRequest')]);
            return;
        }

        $properties = $user->getProperties();

        $properties
            ->setAvatarImg($image)
            ->save();

        $chatId = $user->getChannelId();

        $this->propertiesResponse($chain->getFrom());

        $response = (new MessageResponse())
            ->setGuests(DI::get()->getUsers()->getUsersByChatId($chatId))
            ->setChannelId($chatId)
            ->setTime(null);

        DI::get()->getUsers()
            ->setResponse($response)
            ->notify();
    }

	protected function processRemoveAvatar(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$properties = $user->getProperties();

		$properties
			->setAvatarImg(null)
			->save();

		$chatId = $user->getChannelId();

		$this->propertiesResponse($chain->getFrom());

		$response = (new MessageResponse())
			->setGuests(DI::get()->getUsers()->getUsersByChatId($chatId))
			->setChannelId($chatId)
			->setTime(null);

		DI::get()->getUsers()
			->setResponse($response)
			->notify();
	}

    protected function processSubmit(ChainContainer $chain)
    {
        $request = $chain->getRequest();
        $user = $chain->getFrom();
        $lang = $user->getLang();

        try {
            $form = (new Form())
                ->import($request)
                ->addRule(PropertiesDAO::NAME, Rules::namePattern(), $lang->getPhrase('InvalidNameFormat'))
                ->addRule(PropertiesDAO::TIM, Rules::timPattern(), $lang->getPhrase('InvalidTIMFormat'))
                ->addRule(PropertiesDAO::SEX, Rules::sexPattern(), $lang->getPhrase('InvalidSexFormat'))
                ->addRule(PropertiesDAO::CITY, Rules::cityPattern(), $lang->getPhrase('InvalidCityFormat'))
                ->addRule(PropertiesDAO::BIRTH, Rules::birthYears(), $lang->getPhrase('InvalidYearFormat'))
                ->addRule(PropertiesDAO::CENSOR, Rules::notNull(), $lang->getPhrase('InvalidField'));
        } catch (WrongRuleNameException $e) {
            RespondError::make($user, ['property' => $lang->getPhrase('InvalidProperty') . ' ' . $e->getMessage()]);
            return;
        }

        if (!$form->validate()) {
            RespondError::make($user, $form->getErrors());
            return;
        }

        $userName = $request[PropertiesDAO::NAME] = strip_tags(trim($request[PropertiesDAO::NAME]));

        if (!$this->checkIfAlreadyRegisteredName(CharTranslator::toEnglish($userName), $user)) {
            return;
        }

        if (!$this->checkIfAlreadyRegisteredName(CharTranslator::toRussian($userName), $user)) {
            return;
        }

        if ($user->isInPrivateChat() || PendingDuals::get()->getUserPosition($user)) {
            $this->forbiddenChangeInDualization($user);
            $this->propertiesResponse($user);
            return;
        }

        $oldName = $user->getProperties()->getName();

        $this->importProperties($user, $request);
        $this->guestsUpdateResponse($user, $oldName);
        $this->propertiesResponse($user);

        ChannelNotifier::notifyOnPendingDuals($user);
    }

	protected function addKarma(ChainContainer $chain)
	{
		$operator = $chain->getFrom();
		$request = $chain->getRequest();

		if (!isset($request['user_id'])) {
			RespondError::make($operator, ['user_id' => 'No user specified']);
			return;
		}

		if ($request['user_id'] == $operator->getId()) {
			RespondError::make($operator, ['user_id' => 'Cannot change karma for yourself']);
			return;
		}

		$users = DI::get()->getUsers();
		$user = $users->getClientById($request['user_id']);

		if (!$user) {
			$properties = PropertiesDAO::create()->getByUserId($request['user_id']);
		} else {
			$properties = $user->getProperties();
		}

		$lastMark = UserKarmaDAO::create()->getLastMarkByEvaluatorId($request['user_id'], $operator->getId());

		if ($lastMark) {
			if ((time() - strtotime($lastMark->getDateRegister()) < DI::get()->getConfig()->karmaTimeOut)) {
				RespondError::make($operator, ['user_id' => $operator->getLang()->getPhrase('profile.KarmaTimeOut')]);
				return;
			}

		}

		$karma = UserKarmaDAO::create()->getKarmaByUserId($request['user_id']);

		$properties
			->setKarma($karma+1)
			->save();

		$mark = UserKarmaDAO::create()
			->setUserId($request['user_id'])
			->setEvaluator($operator)
			->setMark(1)
			->setDateRegister(DbQueryHelper::timestamp2date());
		$mark->save();

		$chatId = $operator->getChannelId();

		$response = (new MessageResponse())
			->setGuests($users->getUsersByChatId($chatId))
			->setChannelId($chatId)
			->setTime(null);

		DI::get()->getUsers()
			->setResponse($response)
			->notify();
	}

	protected function decreaseKarma(ChainContainer $chain)
	{
		$operator = $chain->getFrom();
		$request = $chain->getRequest();

		if (!isset($request['user_id'])) {
			RespondError::make($operator, ['user_id' => 'No user specified']);
			return;
		}

		if ($request['user_id'] == $operator->getId()) {
			RespondError::make($operator, ['user_id' => 'Cannot change karma for yourself']);
			return;
		}

		$users = DI::get()->getUsers();
		$user = $users->getClientById($request['user_id']);

		if (!$user) {
			$properties = PropertiesDAO::create()->getByUserId($request['user_id']);
		} else {
			$properties = $user->getProperties();
		}

		$lastMark = UserKarmaDAO::create()->getLastMarkByEvaluatorId($request['user_id'], $operator->getId());

		if ($lastMark) {
			if ((time() - strtotime($lastMark->getDateRegister()) < DI::get()->getConfig()->karmaTimeOut)) {
				RespondError::make($operator, ['user_id' => $operator->getLang()->getPhrase('profile.KarmaTimeOut')]);
				return;
			}

		}

		$karma = UserKarmaDAO::create()->getKarmaByUserId($request['user_id']);

		$properties
			->setKarma($karma-1)
			->save();

		$mark = UserKarmaDAO::create()
			->setUserId($request['user_id'])
			->setEvaluator($operator)
			->setMark(-1)
			->setDateRegister(DbQueryHelper::timestamp2date());
		$mark->save();

		$chatId = $operator->getChannelId();

		$response = (new MessageResponse())
			->setGuests($users->getUsersByChatId($chatId))
			->setChannelId($chatId)
			->setTime(null);

		DI::get()->getUsers()
			->setResponse($response)
			->notify();
	}

    private function checkIfAlreadyRegisteredName($userName, User $user)
    {
        $duplUser = PropertiesDAO::create()->getByUserName($userName);

        if ($duplUser->getId() && $duplUser->getUserId() != $user->getId()) {
            RespondError::make(
                $user,
                [
                    PropertiesDAO::NAME => $user->getLang()->getPhrase('NameAlreadyRegistered', $userName)
                ]
            );
            $this->propertiesResponse($user);
            return;
        }

        return true;
    }

    private function propertiesResponse(User $user)
    {
        $response = (new UserPropetiesResponse())
            ->setUserProps($user)
            ->setChannelId($user->getChannelId());

        (new UserCollection())
            ->attach($user)
            ->setResponse($response)
            ->notify();
    }

    private function guestsUpdateResponse(User $user, $oldName)
    {
        $response = (new MessageResponse())
            ->setGuests(DI::get()->getUsers()->getUsersByChatId($user->getChannelId()))
            ->setChannelId($user->getChannelId())
            ->setTime(null);

        $props = $user->getProperties();

        if ($props->getName() != $oldName) {
            $response->setMsg(MsgToken::create('UserChangedName', $oldName, $props->getName()));
        }

        DI::get()->getUsers()
            ->setResponse($response)
            ->notify();
    }

    private function forbiddenChangeInDualization(User $user)
    {
        $response = (new UserPropetiesResponse())
            ->setUserProps($user)
            ->setMsg(MsgToken::create('ProfileChangeForbiddenInDualization'))
            ->setChannelId($user->getChannelId());

        (new UserCollection())
            ->attach($user)
            ->setResponse($response)
            ->notify();
    }

    private function importProperties(User $user, $request)
    {
        $config = DI::get()->getConfig();
        $name = $request[PropertiesDAO::NAME];
        $tim = TimEnum::create($request[PropertiesDAO::TIM]);
        $guestName = $user->getLang()->getPhrase('Guest');
        $isNewbie = mb_strpos($name, $guestName) !== false;

        $changeLog = NameChangeDAO::create()->getLastByUser($user);
        $hasNameChanged = $name != $user->getProperties()->getName();

        if ($isNewbie) {
            $newname = str_replace($guestName, $tim->getShortName(), $name);
            $duplUser = PropertiesDAO::create()->getByUserName($newname);

            if (!($duplUser->getId() && $duplUser->getUserId() != $user->getId())) {
                $name = $newname;
            }
        } elseif ($changeLog && $hasNameChanged && !$this->isExpired($changeLog, $config)) {
            RespondError::make(
                $user,
                $user->getLang()->getPhrase(
                    'NameChangePolicy',
                    date('Y-m-d H:i', $changeLog->getDate() + $config->nameChangeFreq)
                )
            );
            return;
        }

        if ($changeLog = NameChangeDAO::create()->getLastByName($name)) {
            if ($changeLog->getUserId() != $user->getId()) {
                if (!$this->isExpired($changeLog, $config)) {
                    RespondError::make(
                        $user,
                        $user->getLang()->getPhrase(
                            'NameTakePolicy',
                            date('Y-m-d H:i', $changeLog->getDate() + $config->nameChangeFreq)
                        )
                    );
                    return;
                }
            }
        }

        $properties = $user->getProperties();

        if ($hasNameChanged && !$isNewbie) {
            NameChangeDAO::create()
                ->setUser($user)
                ->save();
        }

        $properties
            ->setUserId($user->getId())
            ->setName($name)
            ->setTim(TimEnum::create($request[PropertiesDAO::TIM]))
            ->setSex(SexEnum::create($request[PropertiesDAO::SEX]))
            ->setCity($request[PropertiesDAO::CITY])
            ->setBirthday($request[PropertiesDAO::BIRTH])
            ->setOptions([PropertiesDAO::CENSOR => $request[PropertiesDAO::CENSOR]]);

        $properties->save();
    }

    private function isExpired(NameChangeDAO $changeLog, Config $config)
    {
        return ($changeLog->getDate() + $config->nameChangeFreq) < time();
    }
}
