<?php

namespace SocioChat\Cron;

use SocioChat\DAO\MailQueueDAO;
use SocioChat\Utils\Mail;

class ServiceMailer implements CronService
{
    /**
     * @param array $options
     */
    public function setup(array $options)
    {

    }

    /**
     * @return boolean
     */
    public function canRun()
    {
        return true;
    }

    /**
     * @return string|null
     */
    public function getLockName()
    {
        return 'Mailer';
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return "Script to mail messages\n";
    }

    public function run()
    {
	    $mail = new Mail();

	    /** @var MailQueueDAO $message */
	    foreach (MailQueueDAO::create()->getAllList() as $message) {
		    echo "Sending message to {$message->getEmail()}";
	        $mail->send($message->getEmail(), $message->getTopic(), $message->getMessage());
		    $message->dropById($message->getId());
        }
    }
}
