<?php

namespace MulqiGaming64\TNTExplodeTime;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\world\World;

use pocketmine\math\Vector3;
use pocketmine\entity\Location;
use pocketmine\world\Position;
use pocketmine\utils\Random;

use pocketmine\entity\projectile\Arrow;

use pocketmine\block\TNT;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;

use pocketmine\item\Durable;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\FlintSteel;
use pocketmine\item\Item;
use pocketmine\block\BlockFactory;

use MulqiGaming64\TNTExplodeTime\TNT\TNTExplode;

use function cos;
use function sin;
use const M_PI;

class TNTExplodeTime extends PluginBase implements Listener{
	
	/** @var TNTExplodeTime $instance */
    private static $instance;
    
    public function onEnable(): void{
    	$this->saveDefaultConfig();
    		
   	 // Register Entity
    	EntityFactory::getInstance()->register(TNTExplode::class, function(World $world, CompoundTag $nbt) : TNTExplode{
			return new TNTExplode(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ['TNTExplode']);
		
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
    	self::$instance = $this;
    }
    
    public static function getInstance(): TNTExplodeTime {
        return self::$instance;
    }
    
    public function isDamage(): bool{
    	return (bool) $this->getConfig()->get("damage");
    }
    
    public function isKnockback(): bool{
    	return (bool) $this->getConfig()->get("knockback");
    }
    
    public function isExplode(): bool{
    	return (bool) $this->getConfig()->get("explode");
    }
    
    public function isPlaced(): bool{
    	return (bool) $this->getConfig()->get("placed");
    }
    
    public function getTime(): int{
    	return (int) $this->getConfig()->get("time");
    }
    
    public function getFormat(): string{
    	return (string) $this->getConfig()->get("time-text");
    }
    
    public function spawnTNT(Location $location, int $explodeTime = 2): bool{
    	$mot = (new Random())->nextSignedFloat() * M_PI * 2;
    	/** @var TNTExplode $entity */
        $entity = new TNTExplode($location);
        $entity->setFuse($explodeTime * 20);
        $entity->setWorksUnderwater(false);
		$entity->setMotion(new Vector3(-sin($mot) * 0.02, 0.2, -cos($mot) * 0.02));
        $entity->setNameTagAlwaysVisible();
        $entity->setNameTagVisible();
        $entity->spawnToAll();
        return true;
    }
    
    public function onHitBlock(ProjectileHitBlockEvent $event){
    	$entity = $event->getEntity();
    	$block = $event->getBlockHit();
    	$pos = $block->getPosition();
    	if($block instanceof TNT){
    		if($entity instanceof Arrow and $entity->isOnFire()){
    			$pos->getWorld()->setBlock($pos, BlockFactory::getInstance()->get(0, 0));
    			$location = Location::fromObject($pos->add(0.5, 0, 0.5), $pos->getWorld());
    			$this->spawnTNT($location, $this->getTime());
    		}
    	}
    }
    
    public function onInteract(PlayerInteractEvent $event){
    	$item = $event->getItem();
    	$block = $event->getBlock();
    	$pos = $block->getPosition();
    	if($block instanceof TNT){
    		if($item instanceof FlintSteel or $item->hasEnchantment(VanillaEnchantments::FIRE_ASPECT())){
    			$event->cancel();
				if($item instanceof Durable){
					$item->applyDamage(1);
				}
				$pos->getWorld()->setBlock($pos, BlockFactory::getInstance()->get(0, 0));
				$location = Location::fromObject($pos->add(0.5, 0, 0.5), $pos->getWorld());
    			$this->spawnTNT($location, $this->getTime());
    		}
    	}
    }
    
    public function getPopItem(Item $item): Item{
    	$count = $item->getCount();
    	$count -= 1;
    	return $item->setCount($count);
    }
    
    public function onPlace(BlockPlaceEvent $event){
    	$block = $event->getBlock();
    	$pos = $block->getPosition();
    	$player = $event->getPlayer();
    	if($block instanceof TNT){
    		if($this->isPlaced()){
    			$event->cancel();
    			$location = Location::fromObject($pos->add(0.5, 0, 0.5), $pos->getWorld());
    			$this->spawnTNT($location, $this->getTime());
    			$item = $this->getPopItem($player->getInventory()->getItemInHand());
    			$player->getInventory()->setItemInHand($item);
    		}
    	}
    }
}
