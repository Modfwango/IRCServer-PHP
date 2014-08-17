<?php
  class @@CLASSNAME@@ {
    public $depend = array("Client", "CommandEvent", "ConnectionCreatedEvent",
      "LUSERS", "Modes", "MOTD", "Self", "USER");
    public $name = "Welcome";
    private $client = null;
    private $modes = null;
    private $self = null;

    public function receiveConnectionCreated($name, $connection) {
      $connection->send(":".$this->self->getConfigFlag("serverdomain").
        " NOTICE * :*** Looking up your hostname...");
      $ip = $connection->getIP();
      $host = $connection->getHost();
      if ($ip == $host) {
        $connection->send(":".$this->self->getConfigFlag("serverdomain").
          " NOTICE * :*** Couldn't look up your hostname");
        $connection->setOption("id", hash("sha256", rand().$ip));
      }
      else {
        $connection->send(":".$this->self->getConfigFlag("serverdomain").
          " NOTICE * :*** Found your hostname");
        $connection->setOption("id", hash("sha256", rand().$host));
      }
      $connection->setOption("signon", time());
      $connection->setOption("idle", time());
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

      $umodes = array();
      if ($this->modes->getModesByType("0") != false) {
        foreach ($this->modes->getModesByType("0") as $mode) {
          if ($mode[2] == "1") {
            $umodes[] = $mode[1];
          }
        }
      }

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

      $cmodespp = array_merge($cmodesp, $cmodesb);

      $connection->send(":".$this->self->getConfigFlag("serverdomain")." 001 ".
        $connection->getOption("nick")." :Welcome to the ".
        $this->self->getConfigFlag("netname")." Internet Relay Chat Network ".
        $connection->getOption("nick"));
      $connection->send(":".$this->self->getConfigFlag("serverdomain")." 002 ".
        $connection->getOption("nick")." :Your host is ".
        $this->self->getConfigFlag("serverdomain")."[".
        $connection->getLocalIP()."/".$connection->getPort().
        "], running version ".$this->self->getConfigFlag("version"));
      $connection->send(":".$this->self->getConfigFlag("serverdomain")." 003 ".
        $connection->getOption("nick")." :This server was created ".
        date("D M d Y", __STARTTIME__)." at ".date("H:i:s e", __STARTTIME__));
      $connection->send(":".$this->self->getConfigFlag("serverdomain")." 004 ".
        $connection->getOption("nick")." ".
        $this->self->getConfigFlag("serverdomain")." ".
        $this->self->getConfigFlag("version")." ".implode($umodes)." ".
        implode($cmodes)." ".implode($cmodespp));
      $connection->send(":".$this->self->getConfigFlag("serverdomain")." 005 ".
        $connection->getOption("nick")." CHANTYPES=# CHANMODES=".
        implode($cmodesb).",".implode($cmodesk).",".implode($cmodess).",".
        implode($cmodes)." PREFIX=(".implode($pmodes).")".
        implode($pprefixes)." NETWORK=".
        $this->self->getConfigFlag("netname")." STATUSMSG=".
        implode($pprefixes)." :are supported by this server");

      $event = EventHandling::getEventByName("commandEvent");
      if ($event != false) {
        foreach ($event[2] as $id => $registration) {
          // Trigger the commandEvent event for each registered module.
          if (in_array(strtolower(trim($registration[2])),
              array("lusers", "motd"))) {
            EventHandling::triggerEvent("commandEvent", $id, array($connection,
              array()));
          }
        }
      }
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("connectionCreatedEvent", $this,
        "receiveConnectionCreated");
      EventHandling::registerForEvent("userRegistrationEvent", $this,
        "receiveUserRegistration");
      return true;
    }
  }
?>
