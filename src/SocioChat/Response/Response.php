<?php
namespace SocioChat\Response;

use SocioChat\Clients\User;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DI;

abstract class Response
{
    protected $guests;
    protected $fromName;
    /**
     * @var User
     */
    protected $from;
    /**
     * @var User
     */
    protected $recipient;
    protected $chatId;
    protected $privateProperties = ['privateProperties', 'chatId', 'from', 'recipient'];

    public function setChannelId($chatId)
    {
        $this->chatId = $chatId;
        return $this;
    }

    public function getChannelId()
    {
        return $this->chatId;
    }

    /**
     * @param array $guests
     * @return $this
     */
    public function setGuests(array $guests = null)
    {
        if ($guests === null) {
            $this->guests = null;
            return $this;
        }

	    $avatarDir = DI::get()->getConfig()->uploads->avatars->wwwfolder . DIRECTORY_SEPARATOR;

        foreach ($guests as $user) {
            /* @var $user User */
	        $props = $user->getProperties();
            $this->guests[] = [
		        PropertiesDAO::USER_ID => $props->getUserId(),
	            PropertiesDAO::NAME => $props->getName(),
	            PropertiesDAO::TIM => $props->getTim()->getName(),
	            PropertiesDAO::SEX => $props->getSex()->getName(),
	            PropertiesDAO::AVATAR . 'Thumb' => $props->getAvatarThumb() ? $avatarDir . $props->getAvatarThumb() : null,
	            PropertiesDAO::CITY => $props->getCity(),
	            PropertiesDAO::BIRTH => $props->getAge(),
	            PropertiesDAO::KARMA => $props->getKarma(),
	        ];
        }

        return $this;
    }

    public function setGuestsRaw($guests)
    {
        $this->guests = $guests;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getGuests()
    {
        return $this->guests;
    }

    public function getFromName()
    {
        return $this->fromName;
    }

    public function setFrom(User $user)
    {
        $this->from = $user;
        $this->fromName = $user->getProperties()->getName();

        return $this;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setRecipient(User $user)
    {
        $this->recipient = $user;
        return $this;
    }

    public function getRecipient()
    {
        return $this->recipient;
    }

    public function toString()
    {
        $arr = [];

        $reflection = new \ReflectionClass(new static);

        foreach ($reflection->getProperties() as $property) {
            $pName = $property->getName();
            $val = $this->{$pName};

            if ($val === null) {
                continue;
            }
            if (!in_array($pName, $this->privateProperties)) {
                $arr += [$pName => $this->{$pName}];
            }
        }

        return json_encode($arr);
    }
}
