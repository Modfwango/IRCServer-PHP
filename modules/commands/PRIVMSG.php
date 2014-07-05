<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "ChannelMessageEvent", "CommandEvent",
      "PrivateMessageEvent");
    public $name = "PRIVMSG";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if ($connection->getOption("registered") == true
           && strtolower($command[0]) == "privmsg") {
        if (count($command) == 3) {
          $targets = array($command[1])
          if (stristr($command[1], ",")) {
            $targets = explode(",", $command[1]);
          }
          foreach ($targets as $target) {
            $found = false;
            if (preg_match("/^[#][\x21-\x2B\x2D-\x7E]*$/", $target)) {
              $channels = ModuleManagement::getModuleByName("Channel")->
                getOption("channels");
              if ($channels == false) {
                $channels = array();
              }
              foreach ($channels as $c) {
                $name = $c["name"];
                if (strtolower($name) == strtolower($target)) {
                  $found = true;
                  $event = EventHandling::getEventByName("channelMessageEvent");
                  if ($event != false) {
                    foreach ($event[2] as $id => $registration) {
                      // Trigger the channelMessageEvent event for each
                      // registered module.
                      EventHandling::triggerEvent("channelMessageEvent", $id,
                          array($connection, $c, $command[2]));
                    }
                  }
                }
              }
            }
            elseif (preg_match("/^[[\\]a-zA-Z\\\\`_^{|}][[\\]a-zA-Z0-9\\\\`_^{".
                    "|}-]*$/", $target)) {
              foreach (ConnectionManagement::getConnections() as $c) {
                $nick = $c->getOption("nick");
                if ($nick != false && strtolower($nick)
                    == strtolower($target)) {
                  $found = true;
                  $event = EventHandling::getEventByName("privateMessageEvent");
                  if ($event != false) {
                    foreach ($event[2] as $id => $registration) {
                      // Trigger the privateMessageEvent event for each
                      // registered module.
                      EventHandling::triggerEvent("privateMessageEvent", $id,
                          array($connection, $c, $command[2]));
                    }
                  }
                }
              }
            }
            if ($found == false) {
              $connection->send(":".__SERVERDOMAIN__." 401 ".
                $connection->getOption("nick")." ".$command[1].
                " :No such nick/channel");
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
        return true;
      }
      return false;
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
