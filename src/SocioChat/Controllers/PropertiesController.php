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
use SocioChat\DAO\OnlineDAO;
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

	    $onlineLimitRule = function ($val) {
		    $val = (int) $val;
	        return $val >=0 && $val <=50;
        };

        $aboutRule = function ($val) {
            $len = mb_strlen($val);
            return $len >=0 && $len <= 1024;
        };

        try {
            $form = (new Form())
                ->import($request)
                ->addRule(PropertiesDAO::NAME, Rules::namePattern(), $lang->getPhrase('InvalidNameFormat'))
                ->addRule(PropertiesDAO::ABOUT, $aboutRule, $lang->getPhrase('InvalidField'))
                ->addRule(PropertiesDAO::TIM, Rules::timPattern(), $lang->getPhrase('InvalidTIMFormat'))
                ->addRule(PropertiesDAO::SEX, Rules::sexPattern(), $lang->getPhrase('InvalidSexFormat'))
                ->addRule(PropertiesDAO::CITY, Rules::cityPattern(), $lang->getPhrase('InvalidCityFormat'))
                ->addRule(PropertiesDAO::BIRTH, Rules::birthYears(), $lang->getPhrase('InvalidYearFormat'))
                ->addRule(PropertiesDAO::CENSOR, Rules::notNull(), $lang->getPhrase('InvalidField'))
	            ->addRule(PropertiesDAO::NOTIFY_VISUAL, Rules::notNull(), $lang->getPhrase('InvalidField'))
	            ->addRule(PropertiesDAO::NOTIFY_SOUND, Rules::notNull(), $lang->getPhrase('InvalidField'))
	            ->addRule(PropertiesDAO::LINE_BREAK_TYPE, Rules::notNull(), $lang->getPhrase('InvalidField'))
	            ->addRule(PropertiesDAO::ONLINE_NOTIFICATION, $onlineLimitRule, $lang->getPhrase('InvalidField'))
	            ->addRule(PropertiesDAO::IS_SUBSCRIBED, Rules::notNull(), $lang->getPhrase('InvalidField'))
                ->addRule(PropertiesDAO::MESSAGE_ANIMATION_TYPE, Rules::msgAnimationType(), $lang->getPhrase('InvalidField'))
            ;
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

        $oldName = $user->getProperties()->getName();

        $this->importProperties($user, $request);
        $this->guestsUpdateResponse($user, $oldName);
        $this->propertiesResponse($user);
    }

	protected function addKarma(ChainContainer $chain)
	{
		$this->manageKarma($chain, 1);
	}

	protected function decreaseKarma(ChainContainer $chain)
	{
		$this->manageKarma($chain, -1);
	}

	private function manageKarma(ChainContainer $chain, $mark)
	{
		$operator = $chain->getFrom();
		$request = $chain->getRequest();
        $mark = (int) $mark;

		if (!isset($request['user_id'])) {
			RespondError::make($operator, ['user_id' => $operator->getLang()->getPhrase('RequiredPropertyNotSpecified')]);
			return;
		}

		if ($request['user_id'] == $operator->getId()) {
			RespondError::make($operator, ['user_id' => $operator->getLang()->getPhrase('CantDoToYourself')]);
			return;
		}

		if (!$operator->isRegistered()) {
			RespondError::make($operator, ['user_id' => $operator->getLang()->getPhrase('RegisteredOnly')]);
			return;
		}

        if ($operator->getProperties()->getOnlineCount() < 3600) {
            RespondError::make($operator, ['user_id' => $operator->getLang()->getPhrase('OnlineTimeTooLow')]);
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
			->setKarma($karma+$mark)
			->save();

		$mark = UserKarmaDAO::create()
			->setUserId($request['user_id'])
			->setEvaluator($operator)
			->setMark($mark)
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
        $this->handleNameChange($user, $request);

        $properties = $user->getProperties();

        $properties
            ->setUserId($user->getId())
            ->setName($request[PropertiesDAO::NAME])
            ->setAbout(strip_tags(trim($request[PropertiesDAO::ABOUT])))
            ->setTim(TimEnum::create($request[PropertiesDAO::TIM]))
            ->setSex(SexEnum::create($request[PropertiesDAO::SEX]))
            ->setCity($request[PropertiesDAO::CITY])
            ->setBirthday($request[PropertiesDAO::BIRTH])
	        ->setSubscription($request[PropertiesDAO::IS_SUBSCRIBED])
            ->setOptions(
                [
                    PropertiesDAO::CENSOR => $request[PropertiesDAO::CENSOR],
                    PropertiesDAO::LINE_BREAK_TYPE => $request[PropertiesDAO::LINE_BREAK_TYPE],
                    PropertiesDAO::NOTIFY_VISUAL => $request[PropertiesDAO::NOTIFY_VISUAL],
                    PropertiesDAO::NOTIFY_SOUND => $request[PropertiesDAO::NOTIFY_SOUND],
                    PropertiesDAO::ONLINE_NOTIFICATION => $request[PropertiesDAO::ONLINE_NOTIFICATION],
                    PropertiesDAO::ONLINE_NOTIFICATION_LAST => $properties->getOnlineNotificationLast(),
                    PropertiesDAO::MESSAGE_ANIMATION_TYPE => $request[PropertiesDAO::MESSAGE_ANIMATION_TYPE]
                ]
            );

        $properties->save(false);

	    $online = OnlineDAO::create();
	    $online->setOnlineList($user->getChannelId());
    }

    private function isExpired(NameChangeDAO $changeLog, $nameChangeFreq)
    {
        return ($changeLog->getDate() + $nameChangeFreq) < time();
    }

    private function handleNameChange(User $user, $request)
    {
        $name = $request[PropertiesDAO::NAME];
        $guestName = $user->getLang()->getPhrase('Guest');
        $nameChangeFreq = DI::get()->getConfig()->nameChangeFreq;
        $isNewbie = mb_strpos($name, $guestName) !== false;
        $changeLog = NameChangeDAO::create()->getLastByUser($user);
        $hasNameChanged = $name != $user->getProperties()->getName();

        if ($isNewbie) {
            $newname = str_replace($guestName, TimEnum::create($request[PropertiesDAO::TIM])->getShortName(), $name);
            $duplUser = PropertiesDAO::create()->getByUserName($newname);

            if (!($duplUser->getId() && $duplUser->getUserId() != $user->getId())) {
                $name = $newname;
            }
        } elseif ($changeLog && $hasNameChanged && !$this->isExpired($changeLog, $nameChangeFreq)) {
            RespondError::make(
                $user,
                $user->getLang()->getPhrase(
                    'NameChangePolicy',
                    date('Y-m-d H:i', $changeLog->getDate() + $nameChangeFreq)
                )
            );
            return;
        }

        if ($changeLog = NameChangeDAO::create()->getLastByName($name)) {
            if ($changeLog->getUserId() != $user->getId()) {
                if (!$this->isExpired($changeLog, $nameChangeFreq)) {
                    RespondError::make(
                        $user,
                        $user->getLang()->getPhrase(
                            'NameTakePolicy',
                            date('Y-m-d H:i', $changeLog->getDate() + $nameChangeFreq)
                        )
                    );
                    return;
                }
            }
        }

        if ($hasNameChanged && !$isNewbie) {
            NameChangeDAO::create()
                ->setUser($user)
                ->save();
        }
    }
}
