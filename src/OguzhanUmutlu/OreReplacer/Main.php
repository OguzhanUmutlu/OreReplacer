<?php

namespace OguzhanUmutlu\OreReplacer;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use OguzhanUmutlu\OreReplacer\EventListener;

class Main extends PluginBase {
  public function onEnable() {
    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    $this->saveDefaultConfig();
    $this->config = new Config($this->getDataFolder() . "config.yml");
    $this->data = new Config($this->getDataFolder() . "data.yml", Config::YAML, ["data" => []]);
    if($this->config->getNested("config-version") != 2) {
      $this->getLogger()->warning("Config update detected! Config is updating...");
      unlink($this->getDataFolder() . "config.yml");
      $this->saveDefaultConfig();
    }
  }
}
