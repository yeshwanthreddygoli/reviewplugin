<?php

namespace xrstf\Composer52;

use Composer\Repository\CompositeRepository;
use Composer\Script\Event;

class Generator {
	public static function onPostInstallCmd(Event $event) {
		$composer            = $event->getComposer();
		$installationManager = $composer->getInstallationManager();
		$repoManager         = $composer->getRepositoryManager();
		$localRepo           = $repoManager->getLocalRepository();
		$package             = $composer->getPackage();
		$config              = $composer->getConfig();

		
		$args     = $_SERVER['argv'];
		$optimize = in_array('-o', $args) || in_array('--optimize-autoloader', $args) || in_array('--optimize', $args);

		$suffix   = $config->get('autoloader-suffix');

		$generator = new AutoloadGenerator();
		$generator->dump($config, $localRepo, $package, $installationManager, 'composer', $optimize, $suffix);
	}
}
