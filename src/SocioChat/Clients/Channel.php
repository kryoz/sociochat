<?php

namespace SocioChat\Clients;

use SocioChat\DI;
use Core\Form\Form;
use SocioChat\Message\MsgRaw;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;

class Channel
{
    const BUFFER_LENGTH = 100;
    const TO_NAME = 'toName';
    const FROM_NAME = 'fromName';
    const TIM = 'tim';
    const SEX = 'sex';
    const AVATAR_THUMB = 'avatarThumb';
    const AVATAR_IMG = 'avatarImg';
    const TIME = 'time';
    const MSG = 'msg';
    const USER_INFO = 'userInfo';
    const FROM_USER_ID = 'fromUserId';

	protected $id;
    protected $history = [];
    protected $lastMsgId = 1;

	protected $name;
	protected $isPrivate = true;
	protected $ownerId = 1;
	protected $onJoinRule;

    public function __construct($id, $name = null, $isPrivate = true)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isPrivate = $isPrivate;
        $this->onJoinRule = function (Form $form, User $user) {
            if ($this->isPrivate() || $this->getId() == 1) {
                return true;
            }

            if (!$user->isRegistered()) {
                $form->markWrong('channelId', 'Вход разрешён только зарегистрированным участникам');
            }

            return $user->isRegistered();
        };
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

	/**
	 * @return int
	 */
	public function getLastMsgId()
	{
		return $this->lastMsgId;
	}

	public function setLastMsgId($id)
	{
		$this->lastMsgId = (int) $id;
		return $this;
	}

    /**
     * @param MessageResponse $response
     * @return int
     */
    public function pushResponse(MessageResponse $response)
    {
        if ($this->filterMessages($response) === false) {
            return;
        }

        $this->history[$this->lastMsgId] = $this->makeRecord($response);
        $keys = array_keys($this->history);

        if (count($this->history) > self::BUFFER_LENGTH) {
            unset($this->history[$keys[0]]);
        }

        $id = $this->lastMsgId;

        $this->lastMsgId++;

        return $id;
    }

	public function pushRawResponse(array $response)
	{
		$msg = $response[self::MSG];
		if (mb_strpos($msg, '|')) {
			$msg = call_user_func_array([MsgToken::class, 'create'], explode('|', $msg));
		} else {
			$msg = MsgRaw::create($msg);
		}
		$response[self::MSG] = $msg;
		$this->history[$this->lastMsgId] = $response;
		$this->lastMsgId++;
	}

    /**
     * @param int $lastMsgId
     * @return MessageResponse[]
     */
    public function getHistory($lastMsgId)
    {
        $history = $this->history;

        if ($lastMsgId > 0 && $lastMsgId <= count($history)) {
            $history = array_slice($this->history, $lastMsgId, null, true);
        }

        return $history;
    }

    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isPrivate()
    {
        return $this->isPrivate;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name ?: $this->getId();
    }

    // for the future
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
        return $this;
    }

    // for the future
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    public function setOnJoinRule(callable $rule)
    {
        $this->onJoinRule = $rule;
        return $this;
    }

    public function verifyOnJoinRule()
    {
        return $this->onJoinRule;
    }

    private function filterMessages(MessageResponse $response)
    {
        $response->setGuests(null);

        if (!$response->getFilteredMsg() && !$response->getMsg()) {
            return false;
        }
    }

    /**
     * @param MessageResponse $response
     * @return array
     */
    private function makeRecord(MessageResponse $response)
    {
        $record = [
            self::FROM_USER_ID => null,
            self::FROM_NAME => $response->getFromName(),
            self::TIME => $response->getTime(),
            self::MSG => $response->getFilteredMsg() ?: $response->getMsg(),
        ];

        if ($from = $response->getFrom()) {
            $dir = DI::get()->getConfig()->uploads->avatars->wwwfolder . DIRECTORY_SEPARATOR;
            $info = [
                self::TIM => $from->getProperties()->getTim()->getName(), //@TODO wrong lang
                self::SEX => $from->getProperties()->getSex()->getName(),
            ];


            if ($from->getProperties()->getAvatarThumb()) {
                $info += [
                    self::AVATAR_THUMB => $dir . $from->getProperties()->getAvatarThumb(),
                    self::AVATAR_IMG => $dir . $from->getProperties()->getAvatarImg(),
                ];
            }

            $record += [
                self::USER_INFO => $info
            ];
            $record[self::FROM_USER_ID] = $response->getFrom()->getId();
        }

        if ($response->getToUserName()) {
            $record += [self::TO_NAME => $response->getToUserName()];
            return $record;
        }
        return $record;
    }
}
