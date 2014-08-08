<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "ChannelJoinEvent", "CommandEvent");
    public $name = "JOIN";

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
        if (count($command) > 0) {
          $channels = array($command[0]);
          if (stristr($command[0], ",")) {
            $channels = explode(",", $command[0]);
          }
          foreach ($channels as $channel) {
            if (strlen($channel) < 51) {
              if (preg_match("/^[#][\x21-\x2B\x2D-\x7E]*$/", $channel)) {
                $event = EventHandling::getEventByName("channelJoinEvent");
                if ($event != false) {
                  foreach ($event[2] as $id => $registration) {
                    // Trigger the channelJoinEvent event for each registered
                    // module.
                    EventHandling::triggerEvent("channelJoinEvent", $id,
                        array($connection, $channel));
                  }
                }
              }
              else {
                $connection->send(":".__SERVERDOMAIN__." 479 ".
                  $connection->getOption("nick")." ".$channel.
                  " :Illegal channel name");
              }
            }
            else {
              $connection->send(":".__SERVERDOMAIN__." 479 ".
                $connection->getOption("nick")." ".$channel.
                " :Illegal channel name");
            }
          }
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 461 ".
            $connection->getOption("nick")." JOIN :Not enough parameters");
        }
      }
      else {
        $connection->send(":".__SERVERDOMAIN__." 451 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "join");
      return true;
    }
  }
?>
