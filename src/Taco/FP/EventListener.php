<?php namespace Taco\FP;

/*
  _____               _
 |_   _|_ _  ___ ___ | |
   | |/ _` |/ __/ _ \| |
   | | (_| | (_| (_) |_|
   |_|\__,_|\___\___/(_)
Copyright (C) 2021  Taco!#1305
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class EventListener implements Listener {

    /**
     * @param PlayerPreLoginEvent $event
     */
    public function onPreLogin(PlayerPreLoginEvent $event) : void {
        $player = $event->getPlayer();
        Loader::getInstance()->cps[$player->getLowerCaseName()] = [0, 0];
        Loader::getInstance()->combo[$player->getLowerCaseName()] = 0;
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event) : void {
        $player = $event->getPlayer();
        if (isset(Loader::getInstance()->cps[$player->getLowerCaseName()])) unset(Loader::getInstance()->cps[$player->getLowerCaseName()]);
        if (isset(Loader::getInstance()->combo[$player->getLowerCaseName()])) unset(Loader::getInstance()->combo[$player->getLowerCaseName()]);
    }

    /**
     * @param EntityDamageByEntityEvent $event
     */
    public function onDamage(EntityDamageByEntityEvent $event) : void {
        $config = Loader::getInstance()->config;
        $entity = $event->getEntity();
        if (in_array($entity->getLevel()->getName(), $config["restricted-worlds"])) return;
        $attacker = $event->getDamager();
        if ($attacker instanceof Player) {
            $pos1 = $entity->getPosition();
            $pos2 = $attacker->getPosition();
            $x = max($pos2->getX() - $pos1->getX(), max(0, $pos1->getX() - $pos2->getX()));
            $z = max($pos2->getZ() - $pos1->getZ(), max(0, $pos1->getZ() - $pos2->getZ()));
            $attacker->sendPopup(str_replace([
                "{name}",
                "{reach}",
                "{cps}",
                "{health-attacked}",
                "{health-attacker}",
                "{combo}"
            ],
            [
                $entity->getNameTag(),
                round(sqrt($x * $x + $z * $z), 2) + ($attacker->getPing() * 0.002),
                Loader::getInstance()->getCPS($attacker),
                round($entity->getHealth(), 2),
                round($attacker->getHealth(), 2),
                Loader::getInstance()->combo[$attacker->getLowerCaseName()]
            ],
                $config["format"]
            ));
            Loader::getInstance()->combo[$attacker->getLowerCaseName()]++;
            if ($entity instanceof Player) Loader::getInstance()->combo[$entity->getLowerCaseName()] = 0;
        }
    }

    public function onDataPacket(DataPacketReceiveEvent $event) : void {
        $player = $event->getPlayer();
        $p = $event->getPacket();
        if ($p instanceof LevelSoundEventPacket and $p->sound == LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE or $p instanceof InventoryTransactionPacket and $p->trData instanceof UseItemOnEntityTransactionData) {
            Loader::getInstance()->cps($player);
        }
    }

}