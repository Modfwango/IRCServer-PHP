<?php
  class @@CLASSNAME@@ {
    public $depend = array("Client", "CommandEvent", "ConnectionCreatedEvent",
      "LUSERS", "Modes", "MOTD", "USER");
    public $name = "Welcome";
    private $client = null;
    private $modes = null;

    public function receiveConnectionCreated($name, $connection) {
      $connection->send(":".__SERVERDOMAIN__.
        " NOTICE * :*** Looking up your hostname...");
      $ip = $connection->getIP();
      $host = $connection->getHost();
      if ($ip == $host) {
        $connection->send(":".__SERVERDOMAIN__.
          " NOTICE * :*** Couldn't look up your hostname");
        $connection->setOption("id", hash("sha256", rand().$ip));
      }
      else {
        $connection->send(":".__SERVERDOMAIN__.
          " NOTICE * :*** Found your hostname");
        $connection->setOption("id", hash("sha256", rand().$host));
      }
      $this->client->setClient($connection);
      return true;
    }

    public function receiveUserRegistration($name, $connection) {
      $pmodes = array();
      $pprefixes = array();
      foreach ($this->modes->getModesAndWeight() as $modes) {
        foreach ($modes as $mode) {
          $pmodes[] = $mode[1];
          $pprefixes[] = $mode[4];
        }
      }
      $pmodes = array_reverse($pmodes);
      $pprefixes = array_reverse($pprefixes);

      $cmodes = array();
      if ($this->modes->getModesByType("0") != false) {
        foreach ($this->modes->getModesByType("0") as $mode) {
          if ($mode[2] == "0") {
            $cmodes[] = $mode[1];
          }
        }
      }

      $cmodess = array();
      if ($this->modes->getModesByType("2") != false) {
        foreach ($this->modes->getModesByType("2") as $mode) {
          if ($mode[2] == "0") {
            $cmodess[] = $mode[1];
          }
        }
      }

      $cmodesb = array();
      if ($this->modes->getModesByType("3") != false) {
        foreach ($this->modes->getModesByType("3") as $mode) {
          $cmodesb[] = $mode[1];
        }
      }

      $cmodesk = array();
      if ($this->modes->getModesByType("5") != false) {
        foreach ($this->modes->getModesByType("5") as $mode) {
          $cmodesk[] = $mode[1];
        }
      }

      $cmodesp = array();
      $cmodesp = array_merge($cmodesp, $cmodess);
      if ($this->modes->getModesByType("4") != false) {
        foreach ($this->modes->getModesByType("4") as $mode) {
          $cmodesp[] = $mode[1];
        }
      }
      $cmodesp = array_merge($cmodesp, $cmodesk);

      $cmodespp = array();
      $cmodespp = array_merge($cmodesp, $cmodesb);

      $connection->send(":".__SERVERDOMAIN__." 001 ".
        $connection->getOption("nick")." :Welcome to the ".__NETNAME__.
        " Internet Relay Chat Network ".$connection->getOption("nick"));
      $connection->send(":".__SERVERDOMAIN__." 002 ".
        $connection->getOption("nick")." :Your host is ".__SERVERDOMAIN__."[".
        $connection->getLocalIP()."/".$connection->getPort().
        "], running version ".__PROJECTVERSION__);
      $connection->send(":".__SERVERDOMAIN__." 003 ".
        $connection->getOption("nick")." :This server was created ".date(
        "D M d Y", __STARTTIME__)." at ".date("H:i:s e", __STARTTIME__));
      $connection->send(":".__SERVERDOMAIN__." 004 ".
        $connection->getOption("nick")." ".__SERVERDOMAIN__." ".
        __PROJECTVERSION__." oiwszcrkfydnxbauglZCD ".implode($cmodes).
        " ".implode($cmodespp));
      $connection->send(":".__SERVERDOMAIN__." 005 ".
        $connection->getOption("nick")." CHANTYPES=# CHANMODES=".
        implode($cmodesb).",".implode($cmodesk).",".implode($cmodess).",".
        implode($cmodes)." PREFIX=(".implode($pmodes).")".implode($pprefixes).
        " NETWORK=".__NETNAME__." STATUSMSG=".implode($pprefixes).
        " :are supported by this server");

      $event = EventHandling::getEventByName("commandEvent");
      if ($event != false) {
        foreach ($event[2] as $id => $registration) {
          // Trigger the commandEvent event for each registered module.
          EventHandling::triggerEvent("commandEvent", $id, array($connection,
            array("LUSERS")));
          EventHandling::triggerEvent("commandEvent", $id, array($connection,
            array("MOTD")));
        }
      }
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerForEvent("connectionCreatedEvent", $this,
        "receiveConnectionCreated");
      EventHandling::registerForEvent("userRegistrationEvent", $this,
        "receiveUserRegistration");
      return true;
    }
  }
?>
