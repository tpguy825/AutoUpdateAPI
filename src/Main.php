<?php
declare(strict_types=1);

namespace tpguy825\AutoPluginUpdater;

use pocketmine\plugin\PluginBase;
use Exception;
use tpguy825\AutoPluginUpdater\tasks\UpdaterTask;

class Main extends PluginBase {
    /** @var array<string[]> $downloads Array of plugins to download */
    public array $downloads = [];
    public string $version = "0.0.1";
    public static Main $instance;

    public function onEnable(): void {
        self::$instance = $this;
		//if ($this->config->isCheckUpdate()) {
            $plugins = $this->getServer()->getPluginManager()->getPlugins();
            for($x = 0; $x <= count($plugins); $x++) {
                $this->downloads[$x] = ["name" => $plugins[$x]->getName(), "version" => $plugins[$x]->getDescription()->getVersion()];
            }
			try {
				$this->getServer()->getAsyncPool()->submitTask(new UpdaterTask);
                $this->getLogger()->info("Checking for updates...");
			} catch (Exception $e) {
				$this->getLogger()->warning("Unable to start AutoPluginUpdater task: ".$e->getMessage());
			}
		//}
	}
}