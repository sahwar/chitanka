<?php namespace App\Mail;

use App\Entity\User;

class UsernameRequestMailer extends Notifier {

	private $twig;

	public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig) {
		parent::__construct($mailer);
		$this->twig = $twig;
	}

	/**
	 * Send a new password to a given user
	 * @param User $user
	 * @param string $sender
	 */
	public function sendUsername(User $user, $sender) {
		$template = $this->twig->loadTemplate('App:Mail:request_username.txt.twig');
		$templateParams = array('user' => $user);
		$message = \Swift_Message::newInstance($template->renderBlock('subject', $templateParams))
			->setFrom($sender, 'Моята библиотека')
			->setTo($user->getEmail(), $user->getUsername())
			->setBody($template->renderBlock('body', $templateParams));

		$this->sendMessage($message);
	}

}