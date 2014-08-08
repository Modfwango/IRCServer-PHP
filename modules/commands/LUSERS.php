<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent");
    public $name = "LUSERS";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if ($connection->getOption("registered") == true) {
        $connection->send(":".__SERVERDOMAIN__." 251 ".
          $connection->getOption("nick")." :There are ".count(
          ConnectionManagement::getConnections()).
          " users and 0 invisible on 1 servers");
        $connection->send(":".__SERVERDOMAIN__." 252 ".
          $connection->getOption("nick")." 0 :IRC Operators online");
        $connection->send(":".__SERVERDOMAIN__." 254 ".
          $connection->getOption("nick")." 0 :channels formed");
        $connection->send(":".__SERVERDOMAIN__." 255 ".
          $connection->getOption("nick")." :I have ".count(
          ConnectionManagement::getConnections())." clients and 0 servers");
        $connection->send(":".__SERVERDOMAIN__." 265 ".
          $connection->getOption("nick")." ".count(
          ConnectionManagement::getConnections())." ".count(
          ConnectionManagement::getConnections())." :Current local users ".
          count(ConnectionManagement::getConnections()).", max ".count(
          ConnectionManagement::getConnections()));
        $connection->send(":".__SERVERDOMAIN__." 266 ".
          $connection->getOption("nick")." ".count(
          ConnectionManagement::getConnections())." ".count(
          ConnectionManagement::getConnections())." :Current global users ".
          count(ConnectionManagement::getConnections()).", max ".count(
          ConnectionManagement::getConnections()));
        $connection->send(":".__SERVERDOMAIN__." 250 ".
          $connection->getOption("nick")." :Highest connection count: ".count(
          ConnectionManagement::getConnections())." (".count(
          ConnectionManagement::getConnections())." clients) (".count(
          ConnectionManagement::getConnections())." connections received)");
      }
      else {
        $connection->send(":".__SERVERDOMAIN__." 451 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "lusers");
      return true;
    }
  }
?>
