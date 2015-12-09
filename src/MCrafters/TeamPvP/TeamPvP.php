<?php

namespace MCrafters\TeamPvP;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as Color;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class TeamPvP extends PluginBase implements Listener
{

    // Teams
    public $darkside = [];
    public $jetti = [];
    public $yml;

    public function onEnable()
    {
        // Initializing config files
        $this->saveResource("config.yml");
        $yml = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->yml = $yml->getAll();

        $this->getLogger()->debug("Config files have been saved!");

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getLogger()->info(Color::BOLD . Color::GOLD . "M" . Color::AQUA . "TeamPvP " . Color::GREEN . "Enabled" . Color::darkside . "!");
    }

    public function isFriend($p1, $p2)
    {
        if ($this->getTeam($p1) === $this->getTeam($p2) && $this->getTeam($p1) !== false) {
            return true;
        } else {
            return false;
        }
    }

    // isFriend
    public function getTeam($p)
    {
        if (in_array($p, $this->darkside)) {
            return "darkside";
        } elseif (in_array($p, $this->jetti)) {
            return "jetti";
        } else {
            return false;
        }
    }

    public function setTeam($p, $team)
    {
        if (strtolower($team) === "darkside") {
            if ($this->getTeam($p) === "jetti") {
                unset($this->jetti[$p]);
            }
            array_push($this->darkside, $p => $p);
        } elseif (strtolower($team) === "jetti") {
            if ($this->getTeam($p) === "darkside") {
                unset($this->darkside[$p]);
            }
            array_push($this->jetti, $p => $p);
        }
    }

    public function onInteract(PlayerInteractEvent $event)
    {
        $teams = array("darkside", "jetti");
        $b = $event->getBlock();
        if ($b->getX() === $this->yml["sign_join_x"] && $b->getY() === $this->yml["sign_join_y"] && $b->getZ() === $this->yml["sign_join_z"]) {
            if (count($this->darkside < 5) && count($this->jetti < 5)) {
                $this->setTeam($event->getPlayer()->getName(), array_rand($teams, 1));
                $event->getPlayer()->inGame = true;
                $event->getPlayer()->teleport(new Vector3($this->yml["jetti_enter_x"], $this->yml["jetti_enter_y"], $this->yml["jetti_enter_z"]));
            } elseif (count($this->darkside < 5)) {
                $this->setTeam($event->getPlayer()->getName(), "darkside");
                $event->getPlayer()->inGame = true;
                $event->getPlayer()->teleport(new Vector3($this->yml["darkside_enter_x"], $this->yml["darkside_enter_y"], $this->yml["darkside_enter_z"]));
            } elseif (count($this->jetti) < 5) {
                $this->setTeam($event->getPlayer()->getName(), "jetti");
                $event->getPlayer()->inGame = true;
                $event->getPlayer()->teleport(new Vector3($this->yml["jetti_enter_x"], $this->yml["jetti_enter_y"], $this->yml["jetti_enter_z"]));
            } else {
                $event->getPlayer()->sendMessage("§bStarWars §4> §7Sides are full");
            }
        }
    }

    public function onEntityDamage(EntityDamageEvent $event)
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->getEntity() instanceof Player) {
                if ($this->isFriend($event->getDamager()->getName(), $event->getEntity()->getName())) {
                    $event->setCancelled(true);
                    $event->getDamager()->sendMessage("§bStarWars §4> §8" . $event->getEntity()->getName() . " §7is in your team!");
            }
        }
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args)
    {
        $teams = array("darkside", "jetti");
        switch ($cmd->getName()) {
            case "team": {
                switch (strtolower($args[0])) {
                    case "darkside": {
                        if ($sender instanceof Player) {
                            $this->setTeam($sender->getName(), "darkside");
                            $sender->inGame = true;
                            $sender->teleport(new Vector3($this->yml["darkside_enter_x"], $this->yml["darkside_enter_y"], $this->yml["darkside_enter_z"]));
                            return true;
                        } else
                            return false;
                    }
                    case "jetti": {
                        if ($sender instanceof Player) {
                            $this->setTeam($sender->getName(), "jetti");
                            $sender->inGame = true;
                            $sender->teleport(new Vector3($this->yml["jetti_enter_x"], $this->yml["jetti_enter_y"], $this->yml["jetti_enter_z"]));
                            return true;
                        } else
                            return false;
                    }
                    case "var": {
                        var_dump($this->darkside);
                        var_dump($this->jetti);
                        return true;
                    }
                    default: {
                        return false;
                    }
                }
            }
        }
    }
}//Class
