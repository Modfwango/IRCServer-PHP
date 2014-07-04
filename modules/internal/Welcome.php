<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent", "ConnectionCreatedEvent", "LUSERS",
      "UserRegistrationEvent");
    public $name = "Welcome";

    public function receiveConnectionCreatedEvent($name, $connection) {
      $connection->send(":".__SERVERDOMAIN__.
        " NOTICE * :*** Looking up your hostname...");
      $ip = $connection->getIP();
      $host = $connection->getHost();
      if ($ip == $host) {
        $connection->send(":".__SERVERDOMAIN__.
          " NOTICE * :*** Couldn't look up your hostname");
      }
      else {
        $connection->send(":".__SERVERDOMAIN__.
          " NOTICE * :*** Found your hostname");
      }
    }

    public function receiveUserRegistration($name, $connection) {
      $connection->send(":".__SERVERDOMAIN__." 001 ".(
        $connection->getOption("nick") ? $connection->getOption("nick") : "*").
        " :Welcome to the ".__NETNAME__." Internet Relay Chat Network ".(
        $connection->getOption("nick") ? $connection->getOption("nick") : "*"));
      $connection->send(":".__SERVERDOMAIN__." 002 ".(
        $connection->getOption("nick") ? $connection->getOption("nick") : "*").
        " :Your host is ".__SERVERDOMAIN__."[".$connection->getLocalIP()."/".
        $connection->getLocalPort."], running version ".__PROJECTVERSION__);
      $connection->send(":".__SERVERDOMAIN__." 003 ".(
        $connection->getOption("nick") ? $connection->getOption("nick") : "*").
        " :This server was created ".date("D M d Y", __STARTTIME__)." at ".
        date("H:i:s e", __STARTTIME__));
      $connection->send(":".__SERVERDOMAIN__." 004 ".(
        $connection->getOption("nick") ? $connection->getOption("nick") : "*").
        " ".__SERVERDOMAIN__." ".__PROJECTVERSION__." ".__USERMODES__." ".
        __CHANNELMODES__." ".__CHANNELMODESWITHPARAMS__);
      $connection->send(":".__SERVERDOMAIN__." 005 ".(
        $connection->getOption("nick") ? $connection->getOption("nick") : "*").
        " CHANTYPES= CHANMODES= CHANLIMIT=:30 PREFIX=() MAXLIST=:100 MODES=4 ".
        "NETWORK=".__NETNAME__." :are supported by this server");
      $connection->send(":".__SERVERDOMAIN__." 005 ".(
        $connection->getOption("nick") ? $connection->getOption("nick") : "*").
        " STATUSMSG= CASEMAPPING=rfc1459 NICKLEN=30 CHANNELLEN=50 TOPICLEN=390".
        " :are supported by this server");
      $connection->send(":".__SERVERDOMAIN__." 005 ".(
        $connection->getOption("nick") ? $connection->getOption("nick") : "*").
        " FNC TARGMAX=NAMES:1,LIST:1,KICK:1,WHOIS:1,PRIVMSG:5,NOTICE:5 :are ".
        "supported by this server");

      $event = EventHandling::getEventByName("commandEvent");
      if ($event != false) {
        foreach ($event[2] as $id => $registration) {
          // Trigger the commandEvent event for each registered module.
          EventHandling::triggerEvent("commandEvent", $id, array($connection,
            array("LUSERS")));
        }
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("connectionCreatedEvent", $this,
        "receiveConnectionCreated");
      EventHandling::registerForEvent("userRegistrationEvent", $this,
        "receiveUserRegistration");
      return true;
    }
  }
?>
