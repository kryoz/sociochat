<?php
namespace MyApp\Controllers;

use MyApp\Chain\ChainContainer;
use MyApp\Clients\PendingDuals;
use MyApp\Clients\User;
use MyApp\Clients\UserCollection;
use MyApp\DAO\PropertiesDAO;
use MyApp\Enum\SexEnum;
use MyApp\Enum\TimEnum;
use MyApp\Forms\Form;
use MyApp\Forms\Rules;
use MyApp\Forms\WrongRuleNameException;
use MyApp\OnOpenFilters\ResponseFilter;
use MyApp\Response\MessageResponse;
use MyApp\Response\UserPropetiesResponse;
use MyApp\Utils\Lang;

class PropertiesController extends ControllerBase
{
	private $actionsMap = [
		'info' => 'processInfo',
		'submit' => 'processSubmit'
	];

	public function handleRequest(ChainContainer $chain)
	{
		$action = $chain->getRequest()['action'];
			if (!isset($this->actionsMap[$action])) {
			$this->errorResponse($chain->getFrom());
			return;
		}

		$this->{$this->actionsMap[$action]}($chain);
	}

	protected function getFields()
	{
		return ['action'];
	}

	protected function processSubmit(ChainContainer $chain)
	{
		$request = $chain->getRequest();
		$user = $chain->getFrom();
		$lang = Lang::get();

		try {
			$form = (new Form())
				->import($request)
				->addRule(PropertiesDAO::NAME, Rules::namePattern(), $lang->getPhrase('InvalidNameFormat'))
				->addRule(PropertiesDAO::TIM, Rules::timPattern(), $lang->getPhrase('InvalidTIMFormat'))
				->addRule(PropertiesDAO::SEX, Rules::sexPattern(), $lang->getPhrase('InvalidSexFormat'));
				//->addRule(PropertiesDAO::NOTIFICATIONS, Rules::notNull(), 'Не заполнены настройки уведомлений');
		} catch (WrongRuleNameException $e) {
			$this->errorResponse($user, ['property' => $lang->getPhrase('InvalidProperty')]);
			return;
		}

		if (!$form->validate()) {
			$this->errorResponse($user, $form->getErrors());
			return;
		}

		$request[PropertiesDAO::NAME] = strip_tags(trim($request[PropertiesDAO::NAME]));

		$duplUser = PropertiesDAO::create()->getByUserName($request[PropertiesDAO::NAME]);

		if ($duplUser->getId() && $duplUser->getUserId() != $user->getId()) {
			$this->errorResponse($user, [PropertiesDAO::NAME => $lang->getPhrase('NameAlreadyRegistered', $request[PropertiesDAO::NAME])]);
			$this->propertiesResponse($user);
			return;
		}

		$oldName = $user->getProperties()->getName();


		$this->importProperties($user, $request);

		if ($user->isInPrivateChat() || PendingDuals::get()->getUserPosition($user)) {
			$this->forbiddenChangeInDualization($user);
			$this->propertiesResponse($user);
			return;
		}

		$this->guestsUpdateResponse($user, $oldName);
		$this->propertiesResponse($user);

		(new ResponseFilter())->notifyOnPendingDuals($user);
	}

	private function propertiesResponse(User $user)
	{
		$response = (new UserPropetiesResponse())
			->setUserProps($user)
			->setChatId($user->getChatId());

		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify();
	}

	private function guestsUpdateResponse(User $user, $oldName)
	{
		$response = (new MessageResponse())
			->setGuests(UserCollection::get()->getUsersByChatId($user->getChatId()))
			->setChatId($user->getChatId())
			->setTime(null);

		$props = $user->getProperties();

		if ($props->getName() != $oldName) {
			$female = $props->getSex()->getId() == SexEnum::FEMALE ? 'а': '';
			$response->setMsg(Lang::get()->getPhrase('UserChangedName', $oldName, $female, $props->getName()));
		}

		UserCollection::get()
			->setResponse($response)
			->notify();
	}

	private function forbiddenChangeInDualization(User $user)
	{
		$response = (new UserPropetiesResponse())
			->setUserProps($user)
			->setMsg(Lang::get()->getPhrase('ProfileChangeForbiddenInDualization'))
			->setChatId($user->getChatId());

		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify();
	}

	private function importProperties(User $user, $request)
	{
		$name = $request[PropertiesDAO::NAME];
		$tim = TimEnum::create($request[PropertiesDAO::TIM]);
		$guestName = Lang::get()->getPhrase('Guest');

		if (mb_strpos($name, $guestName) !== false) {
			$newname = str_replace($guestName, $tim->getShortName(), $name);
			$duplUser = PropertiesDAO::create()->getByUserName($newname);

			if (!($duplUser->getId() && $duplUser->getUserId() != $user->getId())) {
				$name = $newname;
			}
		}

		$properties = $user->getProperties();

		$properties
			->setUserId($user->getId())
			->setName($name)
			->setTim(TimEnum::create($request[PropertiesDAO::TIM]))
			->setSex(SexEnum::create($request[PropertiesDAO::SEX]));
			//->setNotifications($request[PropertiesDAO::NOTIFICATIONS]);

		$properties->save();
	}
}