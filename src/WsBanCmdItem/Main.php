<?php

namespace WsBanCmdItem;

use pocketmine\plugin\{Plugin,PluginBase};
use pocketmine\event\Listener;
use pocketmine\{Player,Server};
use pocketmine\utils\Config;
use pocketmine\command\{Command,CommandSender};
use pocketmine\event\player\{PlayerCommandPreprocessEvent,PlayerInteractEvent,PlayerItemConsumeEvent};
use pocketmine\event\block\{BlockPlaceEvent,BlockBreakEvent};
use pocketmine\event\entity\{EntityDamageEvent,EntityDamageByEntityEvent,EntityShootBowEvent};

class Main extends PluginBase implements Listener{

 public $prefix="§7[§6WsBanCmdItem§7]";

public function onEnable(){
		$this->getLogger()->info("§6WsBanCmdItem插件已加载.作者Wshape1");
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		@mkdir($this->getDataFolder(),0777,true);
		$this->BanC=new Config($this->getDataFolder()."BanCommands.yml",Config::YAML,[]);
		$this->BanI=new Config($this->getDataFolder()."BanItems.yml",Config::YAML,[]);
  $this->Admins=new Config($this->getDataFolder()."Admins.yml",Config::YAML,[]);
	}

  public function onCommand(CommandSender $sender, Command $command, $label, array $args){
 switch($command->getName()){
 
 case "wbana":
 if($sender instanceof Player) return $sender->sendMessage($this->prefix."§c请在后台使用");
 if(!isset($args[0])) return $sender->sendMessage($this->prefix."§b /wbana <Name>  --添加/删除一个管理员");
 $n=strtolower($args[0]);
 if(!$this->Admins->exists($n)){
 $sender->sendMessage($this->prefix."§b成功添加 {$args[0]} 为管理员");
 $this->Admins->set($n,$n);
 $this->Admins->save();
 }else{
 $sender->sendMessage($this->prefix."§b成功删除管理员 ".$args[0]);
 $this->Admins->remove($n);
 $this->Admins->save();
 }
 
 break;
 case "wbanc":
 if(!$sender->isOp()) return $sender->sendMessage("§4YOU ARE NOT OP!!!");
 if(!isset($args[0])) return $sender->sendMessage($this->prefix." /wbanc\n§1> §6add <指令> --添加禁用指令\n§1> §6del <指令> --删除禁用指令\n§1> §6list --被禁用指令列表");
 switch($args[0]){
 case "add":
 if(!isset($args[1])) return $sender->sendMessage($this->prefix." /wbanc add <指令>");
 if($this->BanC->exists($args[1])) return $sender->sendMessage($this->prefix." 已存在命令 ".$args[1]);
 
 $sender->sendMessage($this->prefix." 成功添加命令 ".$args[1]);
 $this->BanC->set($args[1],$args[1]);
 $this->BanC->save();
 break;
 case "del":
 if(!isset($args[1])) return $sender->sendMessage($this->prefix." /wbanc del <指令>");
 if(!$this->BanC->exists($args[1])) return $sender->sendMessage($this->prefix." 不存在命令 ".$args[1]);
 
 $sender->sendMessage($this->prefix." 成功删除命令 ".$args[1]);
 $this->BanC->remove($args[1]);
 $this->BanC->save();
 break;
 case "list":
 if($this->BanC->getAll() == null){
 $sender->sendMessage($this->prefix."Ban Commands List\n§b[什么都没有]");
 }else{
 $sender->sendMessage($this->prefix."Ban Commands List\n§6 - §b".implode("\n§6 - §b",$this->BanC->getAll()));
}
 }
 
 break;
 #---分割线---
 case "wbani":
  if(!$sender->isOp()) return $sender->sendMessage("§4YOU ARE NOT OP!!!");
 if(!isset($args[0])) return $sender->sendMessage($this->prefix." /wbani\n§1> §6add <ID:特殊值> --添加禁用物品\n§1> §6del <ID:特殊值> --删除禁用物品\n§1> §6list --被禁用物品列表");
 switch($args[0]){
 case "add":
 if(!isset($args[1])) return $sender->sendMessage($this->prefix." /wbani add <ID:特殊值>");
 if($this->BanI->exists($args[1])) return $sender->sendMessage($this->prefix." 已存在物品 ".$args[1]);
 if(count(explode(":",$args[1])) == 1){
 $arr=$args[1].":0";
 }else{
 $arr=$args[1];
 }
 $sender->sendMessage($this->prefix." 成功添加物品 ".$arr);
 $this->BanI->set($arr,$arr);
 $this->BanI->save();
 break;
 case "del":
 if(!isset($args[1])) return $sender->sendMessage($this->prefix." /wbani del <ID:特殊值>");
 if(!$this->BanI->exists($args[1])) return $sender->sendMessage($this->prefix." 不存在物品 ".$args[1]);
 if(count(explode(":",$args[1])) == 1){
 $arr=$args[1].":0";
 }else{
 $arr=$args[1];
 }
 $sender->sendMessage($this->prefix." 成功删除物品 ".$arr);
 $this->BanI->remove($arr);
 $this->BanI->save();
 break;
 case "list":
 if($this->BanI->getAll() == null){
 $sender->sendMessage($this->prefix."Ban Items List\n§b[什么都没有]");
 }else{
 $sender->sendMessage($this->prefix."Ban Items List\n§6 - §b".implode("\n§6 - §b",$this->BanI->getAll()));
}
 }
 
 break;
}
 }
 
 public function BanCmds(PlayerCommandPreprocessEvent $event){
 $player=$event->getPlayer();
 if(!$this->Admins->exists(strtolower($player->getName()))){
 $commandInfo=explode(" ", $event->getMessage()); $command=substr(array_shift($commandInfo), 1);
 if($this->BanC->exists($command)){
 $player->sendMessage("§c禁止使用禁用指令 ".$command);
 $event->setCancelled();
 }
 }
 }
 public function BanItems($event){
 foreach($this->BanI->getAll() as $i){
 $player=$event->getPlayer();
 if($this->Admins->exists(strtolower($player->getName()))) return;
 $ci=explode(":",$i);
 if($event->getItem()->getID() == $ci[0] and $event->getItem()->getDamage() == $ci[1]){
 $event->setCancelled();
 $player->sendMessage(" §c禁止使用禁用物品 {$ci[0]}:".$ci[1]);
 }
 }
 }
 
 public function ItemTouch(PlayerInteractEvent $event){
 $this->BanItems($event);
 }
 public function ItemPlace(BlockPlaceEvent $event){
 $this->BanItems($event);
 }
 public function ItemBreak(BlockBreakEvent $event){
 $this->BanItems($event);
 }
 public function ItemHurt(EntityDamageEvent $event) {
    if($event instanceof EntityDamageByEntityEvent and $event->getDamager() instanceof Player){
    $p=$event->getDamager();
    $item=$p->getInventory()->getItemInHand();
 foreach($this->BanI->getAll() as $i){
 if($this->Admins->exists(strtolower($p->getName()))) return;
 $ci=explode(":",$i);
 if($item->getID() == $ci[0] and $item->getDamage() == $ci[1]){
 $event->setCancelled();
 $p->sendMessage(" §c禁止使用禁用物品 {$ci[0]}:".$ci[1]);
 }
 }
 }
 }
 
 public function ItemEat(PlayerItemConsumeEvent $event){
 $this->BanItems($event);
 }
 public function onShoot(EntityShootBowEvent $event) {
foreach($this->BanI->getAll() as $i){
$player=$event->getEntity();
 if($player instanceof Player){
 if($this->Admins->exists(strtolower($player->getName()))) return;
 $ci=explode(":",$i);
 if($event->getBow()->getID() == $ci[0] and $event->getBow()->getDamage() == $ci[1]){
 $event->setCancelled();
 $player->sendMessage(" §c禁止使用禁用物品 {$ci[0]}:".$ci[1]);
 }
 
 }
 }
 }
 }