<?php

namespace Detain\MyAdminPiwik;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Plugin
 *
 * @package Detain\MyAdminPiwik
 */
class Plugin
{
	public static $name = 'Piwik Plugin';
	public static $description = 'Allows handling of Piwik Analytics';
	public static $help = '';
	public static $type = 'plugin';

	/**
	 * Plugin constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * @return array
	 */
	public static function getHooks()
	{
		return [
			//'system.settings' => [__CLASS__, 'getSettings'],
			//'ui.menu' => [__CLASS__, 'getMenu'],
		];
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getMenu(GenericEvent $event)
	{
		$menu = $event->getSubject();
		if ($GLOBALS['tf']->ima == 'admin') {
			function_requirements('has_acl');
            if (has_acl('client_billing')) {
            }
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getRequirements(GenericEvent $event)
	{
        /**
         * @var \MyAdmin\Plugins\Loader $this->loader
         */
        $loader = $event->getSubject();
		$loader->add_requirement('class.Piwik', '/../vendor/detain/myadmin-piwik-analytics/src/Piwik.php');
		$loader->add_requirement('deactivate_kcare', '/../vendor/detain/myadmin-piwik-analytics/src/abuse.inc.php');
		$loader->add_requirement('deactivate_abuse', '/../vendor/detain/myadmin-piwik-analytics/src/abuse.inc.php');
		$loader->add_requirement('get_abuse_licenses', '/../vendor/detain/myadmin-piwik-analytics/src/abuse.inc.php');
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
    public static function getSettings(GenericEvent $event)
    {
        /**
         * @var \MyAdmin\Settings $settings
         **/
        $settings = $event->getSubject();
    }
}
