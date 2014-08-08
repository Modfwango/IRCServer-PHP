<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "ChannelMessageEvent", "Client",
      "CommandEvent", "PrivateMessageEvent");
    public $name = "PRIVMSG";
    private $channel = null;
    private $client = null;

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
        if (count($command) == 2) {
          $targets = array($command[0]);
          if (stristr($command[0], ",")) {
            $targets = explode(",", $command[0]);
          }
          foreach ($targets as $target) {
            if (preg_match("/^[#][\x21-\x2B\x2D-\x7E]*$/", $target)) {
              $c = $this->channel->getChannelByName($target);
              if ($c != false) {
                $found = true;
                $event = EventHandling::getEventByName(
                  "channelMessageEvent");
                if ($event != false) {
                  foreach ($event[2] as $id => $registration) {
                    // Trigger the channelMessageEvent event for each
                    // registered module.
                    EventHandling::triggerEvent("channelMessageEvent", $id,
                        array($connection, $c, $command[1]));
                  }
                }
              }
              else {
                $connection->send(":".__SERVERDOMAIN__." 401 ".
                  $connection->getOption("nick")." PRIVMSG :No such".
                  " nick/channel");
              }
            }
            elseif (preg_match("/^[[\\]a-zA-Z\\\\`_^{|}][[\\]a-zA-Z0-9\\\\`_".
                    "^{|}-]*$/", $target)) {
              $c = $this->client->getClientByNick($target);
              if ($c != false) {
                $found = true;
                $event = EventHandling::getEventByName(
                  "privateMessageEvent");
                if ($event != false) {
                  foreach ($event[2] as $id => $registration) {
                    // Trigger the privateMessageEvent event for each
                    // registered module.
                    EventHandling::triggerEvent("privateMessageEvent", $id,
                        array($connection, $c, $command[1]));
                  }
                }
              }
              else {
                $connection->send(":".__SERVERDOMAIN__." 401 ".
                  $connection->getOption("nick")." PRIVMSG :No such".
                  " nick/channel");
              }
            }
          }
        }
        elseif (count($command) == 1) {
          $connection->send(":".__SERVERDOMAIN__." 412 ".
            $connection->getOption("nick")." :No text to send");
        }
        elseif (count($command) == 0) {
          $connection->send(":".__SERVERDOMAIN__." 411 ".
            $connection->getOption("nick")." :No recipient given (PRIVMSG)");
        }
      }
      else {
        $connection->send(":".__SERVERDOMAIN__." 451 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "privmsg");
      return true;
    }
  }
?>
