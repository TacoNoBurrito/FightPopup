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

use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase {

    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var array
     */
    public $cps = [];

    /**
     * @var array
     */
    public $config = [];

    /**
     * @var array
     */
    public $combo = [];

    public function onEnable() : void {
        self::$instance = $this;
        $this->saveConfig();
        $this->config = (array)$this->getConfig()->getAll();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    public static function getInstance() : self {
        return self::$instance;
    }

    public function cps(Player $player) : void {
        $cps = $this->cps[$player->getLowerCaseName()];
        $time = $cps[1];
        $clicks = $cps[0];
        if ($time == 0) {
            $time = time();
            $clicks++;
        }
        else {
            if (time() - $time > 0) {
                $time = time();
                $clicks = 0;
            } else $clicks++;
        }
        $this->cps[$player->getLowerCaseName()] = [$clicks, $time];
    }

    public function getCPS(Player $player) : int {
        return $this->cps[$player->getLowerCaseName()][0];
    }

}





























