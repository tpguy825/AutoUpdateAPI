<?php

declare(strict_types=1);

namespace tpguy825\AutoPluginUpdater\tasks;

use Exception;
use tpguy825\AutoPluginUpdater\Main;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use Phar;
use pocketmine\utils\InternetRequestResult;
use function yaml_parse;

class UpdaterTask extends AsyncTask {
	private Main $main;

	/**
	 * @throws Exception
	 */
	public function onRun(): void {
		$this->main = Main::$instance;
		/** @var array<string[]> $downloads Array of plugins to download and their versions */
		$downloads = $this->main->downloads;
		for($x = 0; $x <= count($downloads); $x++) {
			$result = Internet::getURL("https://poggit.pmmp.io/get/".$downloads[$x]["name"], 10, ["User-Agent: AutoPluginUpdater by tpguy825 v".$this->main->version.", php ".PHP_VERSION]);
			if($result instanceof InternetRequestResult) {
				if ($result->getCode() !== 200) {
					$this->main->getLogger()->warning("Could not download ".$downloads[$x]["name"].": ".$result->getCode());
					continue;
				}
				try {
					file_put_contents($this->main->getDataFolder()."/../../plugins/".$downloads[$x]["name"].".phar", $result->getBody());
					$pluginphar = new Phar($this->main->getDataFolder()."/../../plugins/".$downloads[$x]["name"].".phar");
					$pluginphar->extractTo($this->main->getDataFolder(), "plugin.yml");
					try {
						try {
							$pluginyml = file_get_contents($this->main->getDataFolder()."/plugin.yml");
							if($pluginyml === false) {
								$this->main->getLogger()->warning("Could not read plugin.yml.");
								$pluginyml = "version: \"Error, check console\"";
							}
						} catch (Exception $e) {
							$this->main->getLogger()->warning("Could not read plugin.yml: ".$e->getMessage());
							$pluginyml = "version: \"Error, check console\"";
						}
						$pluginyml = yaml_parse($pluginyml);
						/**
						 * @var string[] $pluginyml 
						 * @var string $pluginversion
						*/
						$pluginversion = $pluginyml["version"];
						if ($pluginversion > $downloads[$x]["version"]) {
							$this->main->getLogger()->warning("Plugin ".$downloads[$x]["name"]." is outdated! (".$pluginversion." > ".$downloads[$x]["version"].") Updating...");
						} else {
							$this->main->getLogger()->debug("Plugin ".$downloads[$x]["name"]." is up to date!");
						}
					} catch (Exception $e) {
						$this->main->getLogger()->warning("Could not parse plugin.yml for ".$downloads[$x]["name"].": ".$e->getMessage());
						$pluginversion = null;
					}
					$this->main->getLogger()->debug("Plugin ".$downloads[$x]["name"]." updated to version ".$pluginversion."!");
					$downloads[$x] = ["name" => $downloads[$x], "version" => $pluginversion];
				} catch (Exception $e) {
					$this->main->getLogger()->warning("Could not write ".$downloads[$x]["name"].": ".$e->getMessage());
					$downloads[$x] = ["name" => $downloads[$x], "error" => $e->getMessage()];
				}
			} else {
				$this->main->getLogger()->warning("Could not download ".$downloads[$x]["name"].": Internet::getURL() returned null and not InternetRequestResult");
				$downloads[$x] = ["name" => $downloads[$x], "error" => "result is null"];
			}
		}
		$this->setResult($downloads);
	}

	public function onCompletion(): void {
		$this->main->getLogger()->info("Successfully updated plugins!");
	}
}