<?php

namespace OguzhanUmutlu\OreReplacer;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\math\Vector3;
use pocketmine\block\Block;

class EventListener implements Listener {
  public function __construct(Main $plugin) {
    $this->plugin = $plugin;
  }
  public function onBlockBreak(BlockBreakEvent $event) {
    $block = $event->getBlock();
    $plugin = $this->plugin;
    $config = $plugin->config;
    $data = $plugin->data->getNested("data");
    $whitelist = explode(",", $config->getNested("whitelist"));
    $blacklist = explode(",", $config->getNested("blacklist"));
    if(in_array($block->getLevel()->getName(), $blacklist)) return;
    if(count($whitelist) > 0 && !in_array($block->getLevel()->getName(), $whitelist)) return;
    for($x=$block->getX()-$config->getNested("check-radius-from")-$config->getNested("check-radius-to");$x<$block->getX()+$config->getNested("check-radius-from")+$config->getNested("check-radius-to")+1;$x++) {
      for($y=$block->getY()-$config->getNested("check-radius-from")-$config->getNested("check-radius-to");$y<$block->getY()+$config->getNested("check-radius-from")+$config->getNested("check-radius-to")+1;$y++) {
        for($z=$block->getZ()-$config->getNested("check-radius-from")-$config->getNested("check-radius-to");$z<$block->getZ()+$config->getNested("check-radius-from")+$config->getNested("check-radius-to")+1;$z++) {
          $now = $block->getLevel()->getBlock(new Vector3($x, $y, $z));
          $nowA = $now->getId().":".$now->getDamage();
          if(in_array($nowA, $config->getNested("replace-blocks")) && $now->distance($block->asVector3()) > $config->getNested("check-radius-from") && $now->distance($block->asVector3()) < ($config->getNested("check-radius-from")+$config->getNested("check-radius-to"))) {

            # Air detect
            $isthereair = false;
            for($airA=0;$airA<6;$airA++) {
              for($airB=1;$airB<$config->getNested("check-radius-from")+1;$airB++) {
                if(in_array($now->getSide($airA,$airB)->getId(), [0,8,9,10,11])) {
                  $isthereair = true;
                }
              }
            }
            if($isthereair == false && in_array($now->getId().":".$now->getDamage(), $config->getNested("replace-blocks"))) {
              $select = $config->getNested("ores")[rand(0,count($config->getNested("ores"))-1)];
              $data[explode(":", $select)[0].":". (isset(explode(":", $select)[1]) ? explode(":", $select)[1] : 0).":".$x.":". $y.":". $z.":".$now->getLevel()->getName()] = $now->getId().":".$now->getDamage();
              $block->getLevel()->setBlock($now->asVector3(), Block::get(explode(":", $select)[0], isset(explode(":", $select)[1]) ? explode(":", $select)[1] : 0));
            }
          }
        }
      }
    }
    # Re replace action
    for($x=$block->getX()-$config->getNested("check-radius-from");$x<$block->getX()+$config->getNested("check-radius-from");$x++) {
      for($y=$block->getY()-$config->getNested("check-radius-from");$y<$block->getY()+$config->getNested("check-radius-from");$y++) {
        for($z=$block->getZ()-$config->getNested("check-radius-from");$z<$block->getZ()+$config->getNested("check-radius-from");$z++) {
          if(isset($data[$block->getLevel()->getBlock(new Vector3($x, $y, $z))->getId().":".$block->getLevel()->getBlock(new Vector3($x, $y, $z))->getDamage().":".$x.":".$y.":".$z.":".$block->getLevel()->getName()])) {
            $blockA = explode(":", $data[$block->getLevel()->getBlock(new Vector3($x, $y, $z))->getId().":".$block->getLevel()->getBlock(new Vector3($x, $y, $z))->getDamage().":".$x.":".$y.":".$z.":".$block->getLevel()->getName()]);
            $block->getLevel()->setBlock(new Vector3($x,$y,$z), Block::get($blockA[0], $blockA[1]));
            unset($data[$block->getLevel()->getBlock(new Vector3($x, $y, $z))->getId().":".$block->getLevel()->getBlock(new Vector3($x, $y, $z))->getDamage().":".$x.":".$y.":".$z.":".$block->getLevel()->getName()]);
          }
        }
      }
    }
    $plugin->data->setNested("data", $data);
    $plugin->data->save();
    $plugin->data->reload();
  }
}
