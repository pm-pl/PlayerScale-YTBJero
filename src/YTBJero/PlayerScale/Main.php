<?php

declare(strict_types=1);

namespace YTBJero\PlayerScale;
use pocketmine\plugin\{
    PluginBase, Plugin
};
use pocketmine\command\{
    Command, CommandSender
};
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use pocketmine\entity\Entity;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;

class Main extends PluginBase implements Listener{
    
    /** @var $size */
    public $size = array();
    
    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML);
    }
    
    public function onPlayerRespawn(PlayerRespawnEvent $ev): void
    {
        $player = $ev->getPlayer();
        if(!empty($this->size[$player->getName()])){
            $size = $this->size[$player->getName()];
            $player->setScale((float)$size);
         }
     }

     public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool 
     {
        if($cmd->getName() == "scale")
        {
            if(!$sender instanceof Player){
                $sender->sendMessage($this->config->get("use-in-game"));
                return false;
            }

            if(!$sender->hasPermission("scale.command"))
            {
                $sender->sendMessage($this->config->get("permission"));
                return false;
            }

            if(!isset($args[0])) 
            {
                $sender->sendMessage($this->config->get("usage"));
                return false;
            }

            if(is_numeric($args[0])) {
                if($args[0] > $this->config->get("max-size")) {
                    $sender->sendMessage(str_replace("{max}", (string) $this->config->get("max-size"), $this->config->get("max-message")));
                    return true;
                }elseif($args[0] <= $this->config->get("min-size")) {
                    $sender->sendMessage(str_replace("{min}", (string) $this->config->get("min-size"), $this->config->get("min-message")));
                    return true;
                }
                $this->size[$sender->getName()] = $args[0];
                $sender->setScale((float)$args[0]);
                $sender->sendMessage(str_replace("{size}", (string) $args[0], $this->config->get("change-size-success")));
            }elseif($args[0] == "reset") {
                if(!empty($this->size[$sender->getName()])) {
                    unset($this->size[$sender->getName()]);
                    $sender->setScale(1);
                    $sender->sendMessage($this->config->get("reset-success"));
                }else{
                    $sender->sendMessage($this->config->get("reset-success"));
                }
            }
        }
        return true;
    }
}