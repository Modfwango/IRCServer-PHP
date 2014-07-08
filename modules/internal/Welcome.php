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

      $cmodes = array();
      foreach ($this->modes->getModesByType("0") as $mode) {
        $cmodes[] = $mode[1];
      }

      $cmodess = array();
      foreach ($this->modes->getModesByType("2") as $mode) {
        $cmodess[] = $mode[1];
      }

      $cmodesb = array();
      foreach ($this->modes->getModesByType("3") as $mode) {
        $cmodesb[] = $mode[1];
      }

      $cmodesk = array();
      foreach ($this->modes->getModesByType("5") as $mode) {
        $cmodesk[] = $mode[1];
      }

      $cmodesp = array();
      $cmodesp = array_merge($cmodesp, $cmodess);
      foreach ($this->modes->getModesByType("4") as $mode) {
        $cmodesp[] = $mode[1];
      }
      $cmodesp = array_merge($cmodesp, $cmodesk);

      $cmodespp = array();
      $cmodespp = array_merge($cmodespp, $cmodess);
      $cmodespp = array_merge($cmodespp, $cmodesb);
      foreach ($this->modes->getModesByType("4") as $mode) {
        $cmodespp[] = $mode[1];
      }
      $cmodespp = array_merge($cmodespp, $cmodesk);

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
        $connection->getOption("nick").
        " CHANTYPES=&# EXCEPTS INVEX CHANMODES=".implode($cmodesb).",".
        implode($cmodesk).",".implodes($cmodess).",".implode($cmodesp).
        " CHANLIMIT=&#:15". " PREFIX=(".implode(null, $pmodes).")".implode(
        $pprefixes)." MAXLIST=beI:25 MODES=4 NETWORK=".__NETNAME__." KNOCK".
        " STATUSMSG=@+ CALLERID=g :are supported by this server");
      $connection->send(":".__SERVERDOMAIN__." 005 ".
        $connection->getOption("nick").
        " SAFELIST ELIST=U CASEMAPPING=rfc1459 CHARSET=ascii NICKLEN=9".
        " CHANNELLEN=50 TOPICLEN=160 ETRACE CPRIVMSG CNOTICE DEAF=D".
        " MONITOR=100 :are supported by this server");
      $connection->send(":".__SERVERDOMAIN__." 005 ".
        $connection->getOption("nick").
        " FNC TARGMAX=NAMES:1,LIST:1,KICK:1,WHOIS:1,PRIVMSG:4,NOTICE:4,".
        "ACCEPT:,MONITOR: :are supported by this server :are supported by this".
        " server");

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
