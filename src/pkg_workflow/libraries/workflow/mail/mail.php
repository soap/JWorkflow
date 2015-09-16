<?php
defined('_JEXEC') or die;
/**
 * Email handler class extendedfrom JMail with support for replacements
 *
 * @package     Workflow
 * @since		1.5.0
 */
class WFMail extends JMail
{
	protected $replacements = array();
	
	private $replaced = false;

	public function __construct()
	{
		$config = JFactory::getConfig();

		$smtpauth 	= ($config->get('smtpauth') == 0) ? null : 1;
		$smtpuser 	= $config->get('smtpuser');
		$smtppass 	= $config->get('smtppass');
		$smtphost 	= $config->get('smtphost');
		$smtpsecure = $config->get('smtpsecure');
		$smtpport 	= $config->get('smtpport');
		$mailfrom 	= $config->get('mailfrom');
		$fromname 	= $config->get('fromname');
		$mailer 	= $config->get('mailer');

		parent::__construct();

		switch ($mailer)
		{
			case 'smtp':
				$this->useSMTP($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
				break;

			case 'sendmail':
				$this->isSendmail();
				break;

			default:
				$this->isMail();
				break;
		}
	}
	
	public static function getInstance($id = 'Workflow')
	{
		if (empty(self::$instances[$id]))
		{
			self::$instances[$id] = new WFMail();
		}
	
		return self::$instances[$id];
	}

	public function setReplacements($replacements)
	{
		$this->replacements = $replacements;
		$this->replaced = false;
	}

	public function setBody($content)
	{
		parent::setBody($content);
		// replacements is array of key and value paris
		if (!$this->replaced) {
			$this->Body = strtr($this->Body, $this->replacements);
			$this->replaced = true;
		}
	}
}