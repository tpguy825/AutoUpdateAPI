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
    public Main $instance;

    public function onLoad(): void {
        $this->instance = $this;
    }

    public function onEnable(): void {
		//if ($this->config->isCheckUpdate()) {
            $plugins = $this->getServer()->getPluginManager()->getPlugins();
            foreach($plugins as $plugin) {
                array_push($this->downloads, ["name" => $plugin->getName(), "version" => $plugin->getDescription()->getVersion()]);
            }
			try {
				$this->getScheduler()->scheduleTask(new UpdaterTask($this));
                $this->getLogger()->info("Checking for updates...");
			} catch (Exception $e) {
				$this->getLogger()->warning("Unable to start AutoPluginUpdater task: ".$e->getMessage());
			}
		//}
	}
}