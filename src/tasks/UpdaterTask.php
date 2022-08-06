<?php

declare(strict_types=1);

namespace tpguy825\AutoPluginUpdater\tasks;

use tpguy825\AutoPluginUpdater\Main;
use pocketmine\scheduler\Task;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;
use Phar;
use Exception;
use function yaml_parse;

class UpdaterTask extends Task {
	private Main $main;
	
	/**
	 * @param Main $main
	 */
	public function __construct(Main $main) {
		$this->main = $main;
	}
	
	/**
	 * @throws Exception
	 */
	public function onRun(): void {
		$main = $this->main;
		/** @var array<string[]> $downloads Array of plugins to download and their versions */
		$downloads = $main->downloads;
		foreach($downloads as $download) {
			$result = Internet::getURL("https://poggit.pmmp.io/get/".$download["name"], 10, ["User-Agent: AutoPluginUpdater by tpguy825 v".$main->version.", php ".PHP_VERSION]);
			if($result instanceof InternetRequestResult) {
				if ($result->getCode() !== 200) {
					$main->getLogger()->warning("Could not download ".$download["name"].": ".$result->getCode());
					continue;
				}
				try {
					file_put_contents($main->getDataFolder()."/../../plugins/".$download["name"].".phar", $result->getBody());
					$pluginphar = new Phar($main->getDataFolder()."/../../plugins/".$download["name"].".phar");
					$pluginphar->extractTo($main->getDataFolder(), "plugin.yml");
					rename($main->getDataFolder()."plugin.yml", $main->getDataFolder().$download["name"].".plugin.yml");
					try {
						try {
							$pluginyml = file_get_contents($main->getDataFolder()."/plugin.yml");
							if($pluginyml === false) {
								$main->getLogger()->warning("Could not read plugin.yml.");
								$pluginyml = "version: \"Error, check console\"";
							}
						} catch (Exception $e) {
							$main->getLogger()->warning("Could not read plugin.yml: ".$e->getMessage());
							$pluginyml = "version: \"Error, check console\"";
						}
						unlink($main->getDataFolder().$download["name"].".plugin.yml");
						$pluginyml = yaml_parse($pluginyml);
						/**
						 * @var string[] $pluginyml 
						 * @var string $pluginversion
						*/
						$pluginversion = $pluginyml["version"];
						if ($pluginversion > $download["version"]) {
							$main->getLogger()->info("Plugin ".$download["name"]." is outdated! (".$pluginversion." > ".$download["version"].") Updating...");
						} else {
							$main->getLogger()->debug("Plugin ".$download["name"]." is up to date!");
						}
					} catch (Exception $e) {
						$main->getLogger()->warning("Could not parse plugin.yml for ".$download["name"].": ".$e->getMessage());
						$pluginversion = null;
					}
					$main->getLogger()->debug("Plugin ".$download["name"]." updated to version ".$pluginversion."!");
					$download = ["name" => $download, "version" => $pluginversion];
				} catch (Exception $e) {
					$main->getLogger()->warning("Could not write ".$download["name"].": ".$e->getMessage());
					$download = ["name" => $download, "error" => $e->getMessage()];
				}
			} else {
				$main->getLogger()->warning("Could not download ".$download["name"].": Internet::getURL() returned null and not InternetRequestResult");
				$download = ["name" => $download, "error" => "result is null"];
			}
		}
		$main->downloads = $downloads;
	}
}