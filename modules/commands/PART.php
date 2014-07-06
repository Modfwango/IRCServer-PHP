<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "CommandEvent", "ChannelPartEvent");
    public $name = "PART";
    private $channel = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if (strtolower($command[0]) == "part") {
        if ($connection->getOption("registered") == true) {
          if (count($command) > 1) {
            $message = null;
            if (count($command) > 2) {
              $message = "Part: ".$command[2];
            }
            $targets = array($command[1]);
            if (stristr($command[1], ",")) {
              $targets = explode(",", $command[1]);
            }
            foreach ($targets as $target) {
              $c = $this->channel->getChannelByName($target);
              if ($c != false) {
                $event = EventHandling::getEventByName("channelPartEvent");
                if ($event != false) {
                  foreach ($event[2] as $id => $registration) {
                    // Trigger the channelMessageEvent event for each
                    // registered module.
                    EventHandling::triggerEvent("channelPartEvent", $id,
                        array($connection, $c, $message));
                  }
                }
              }
              else {
                $connection->send(":".__SERVERDOMAIN__." 403 ".
                  $connection->getOption("nick")." ".$target.
                  " :No such channel");
              }
            }
          }
          else {
            $connection->send(":".__SERVERDOMAIN__." 461 ".
              $connection->getOption("nick")." PART :Not enough parameters");
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
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
