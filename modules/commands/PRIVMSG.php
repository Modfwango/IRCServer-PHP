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

      if (strtolower($command[0]) == "privmsg") {
        if ($connection->getOption("registered") == true) {
          if (count($command) == 3) {
            $targets = array($command[1]);
            if (stristr($command[1], ",")) {
              $targets = explode(",", $command[1]);
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
                          array($connection, $c, $command[2]));
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
                          array($connection, $c, $command[2]));
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
          elseif (count($command) == 2) {
            $connection->send(":".__SERVERDOMAIN__." 412 ".
              $connection->getOption("nick")." :No text to send");
          }
          elseif (count($command) == 1) {
            $connection->send(":".__SERVERDOMAIN__." 411 ".
              $connection->getOption("nick")." :No recipient given (PRIVMSG)");
          }
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 451 ".(
            $connection->getOption("nick") ? $connection->getOption("nick") :
            "*")." :You have not registered");
        }
        return true;
      }
      return false;
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
