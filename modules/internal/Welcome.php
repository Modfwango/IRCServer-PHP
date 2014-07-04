<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent", "ConnectionCreatedEvent", "LUSERS",
      "MOTD", "UserRegistrationEvent");
    public $name = "Welcome";

    public function receiveConnectionCreated($name, $connection) {
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
      $connection->send(":".__SERVERDOMAIN__." 001 ".
        $connection->getOption("nick")." :Welcome to the ".__NETNAME__.
        " Internet Relay Chat Network ".$connection->getOption("nick"));
      $connection->send(":".__SERVERDOMAIN__." 002 ".
        $connection->getOption("nick")." :Your host is ".__SERVERDOMAIN__."[".
        $connection->getLocalIP()."/".$connection->getLocalPort.
        "], running version ".__PROJECTVERSION__);
      $connection->send(":".__SERVERDOMAIN__." 003 ".
        $connection->getOption("nick")." :This server was created ".date(
        "D M d Y", __STARTTIME__)." at ".date("H:i:s e", __STARTTIME__));
      $connection->send(":".__SERVERDOMAIN__." 004 ".
        $connection->getOption("nick")." ".__SERVERDOMAIN__." ".
        __PROJECTVERSION__." ".__USERMODES__." ".__CHANNELMODES__." ".
        __CHANNELMODESWITHPARAMS__);
      $connection->send(":".__SERVERDOMAIN__." 005 ".
        $connection->getOption("nick").
        " PREFIX=() CHANTYPES= :are supported by this server");

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
      EventHandling::registerForEvent("connectionCreatedEvent", $this,
        "receiveConnectionCreated");
      EventHandling::registerForEvent("userRegistrationEvent", $this,
        "receiveUserRegistration");
      return true;
    }
  }
?>
