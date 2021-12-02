<?php

namespace MulqiGaming64\TNTExplodeTime;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\entity\Entity;
use pocketmine\entity\object\PrimedTNT;

class TNTExplodeTime extends PluginBase{
	
    public function onEnable(): void{
    	$this->saveDefaultConfig();
    	$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(
        	function(){
        		foreach ($this->getServer()->getWorldManager()->getWorlds() as $worlds) {
         		   foreach ($worlds->getEntities() as $entity) {
         			   if($entity instanceof PrimedTNT){
         					$time = "" . $entity->getFuse() / 10; // For Get Time To Explode, why divide by ten? because if not the time will be 80-5
         					$entity->setNameTag(str_replace(["{time}"], [$time], $this->getConfig()->get("time-text")));
                		}
                	}
                }
            }
        ), 20);
    }
}
