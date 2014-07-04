<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent");
    public $name = "LUSERS";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if (strtolower($command[0]) == "lusers") {
        $connection->send(":".__SERVERDOMAIN__." 251 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." :There are ".count(ConnectionManager::getConnections()).
          " users and 0 invisible on 1 servers");
        $connection->send(":".__SERVERDOMAIN__." 252 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." 0 :IRC Operators online");
        $connection->send(":".__SERVERDOMAIN__." 254 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." 0 :channels formed");
        $connection->send(":".__SERVERDOMAIN__." 255 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." :I have ".count(ConnectionManager::getConnections()).
          " clients and 0 servers");
        $connection->send(":".__SERVERDOMAIN__." 265 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." ".count(ConnectionManager::getConnections())." ".count(
          ConnectionManager::getConnections())." :Current local users ".count(
          ConnectionManager::getConnections()).", max ".count(
          ConnectionManager::getConnections()));
        $connection->send(":".__SERVERDOMAIN__." 266 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." ".count(ConnectionManager::getConnections())." ".count(
          ConnectionManager::getConnections())." :Current global users ".count(
          ConnectionManager::getConnections()).", max ".count(
          ConnectionManager::getConnections()));
        $connection->send(":".__SERVERDOMAIN__." 250 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." :Highest connection count: ".count(
          ConnectionManager::getConnections())." (".count(
          ConnectionManager::getConnections())." clients) (".count(
          ConnectionManager::getConnections())." connections received)");
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
