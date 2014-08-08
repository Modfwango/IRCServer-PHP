<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "CommandEvent", "ChannelPartEvent");
    public $name = "PART";
    private $channel = null;

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
          $message = null;
          if (count($command) > 1) {
            $message = "Part: ".$command[1];
          }
          $targets = array($command[0]);
          if (stristr($command[0], ",")) {
            $targets = explode(",", $command[0]);
          }
          foreach ($targets as $target) {
            $c = $this->channel->getChannelByName($target);
            if ($c != false) {
              $event = EventHandling::getEventByName("channelPartEvent");
              if ($event != false) {
                foreach ($event[2] as $id => $registration) {
                  // Trigger the channelPartEvent event for each registered
                  // module.
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
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "part");
      return true;
    }
  }
?>
