<?php

/**
* Quoted from class PrimedTNT
*/

declare(strict_types=1);

namespace MulqiGaming64\TNTExplodeTime\TNT;

use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\entity\ExplosionPrimeEvent;
use MulqiGaming64\TNTExplodeTime\TNT\TNTExplosion;
use MulqiGaming64\TNTExplodeTime\TNTExplodeTime;
use pocketmine\world\Position;

class TNTExplode extends PrimedTNT{
	
	/** @var int */
	public $newFuse = 80;
	
	public function setNewFuse(int $fuse) : void{
		if($fuse < 0 or $fuse > 32767){
			throw new \InvalidArgumentException("Fuse must be in the range 0-32767");
		}
		$this->newFuse = $fuse;
		$this->networkPropertiesDirty = true;
	}
	
	public function getNewFuse() : int{
		return $this->newFuse;
	}
	
	protected function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);
		
		if(!$this->isFlaggedForDespawn()){
			$this->newFuse -= $tickDiff;
			$this->setNameTag(str_replace("{time}", "" . ($this->fuse / 20), TNTExplodeTime::getInstance()->getFormat()));
			$this->networkPropertiesDirty = true;
			if($this->newFuse <= 0){
				$this->flagForDespawn();
				$this->explode();
			}
		}

		return $hasUpdate or $this->fuse >= 0;
	}

	public function explode() : void{
		$ev = new ExplosionPrimeEvent($this, 4);
		$ev->call();
		if(!$ev->isCancelled()){
			//TODO: deal with underwater TNT (underwater TNT treats water as if it has a blast resistance of 0)
			$explosion = new TNTExplosion(Position::fromObject($this->location->add(0, $this->size->getHeight() / 2, 0), $this->getWorld()), $ev->getForce(), $this);
			if($ev->isBlockBreaking()){
				$explosion->explodeA();
			}
			$explosion->explodeB();
		}
	}
}
