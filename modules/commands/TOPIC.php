<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "CommandEvent", "ChannelTopicEvent");
    public $name = "TOPIC";
    private $channel = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if ($connection->getOption("registered") == true) {
        if (count($command) > 0) {
          $targets = array($command[0]);
          if (stristr($command[0], ",")) {
            $targets = explode(",", $command[0]);
          }
          foreach ($targets as $target) {
            if (count($command) > 1) {
              $c = $this->channel->getChannelByName($target);
              if ($c != false) {
                if ($this->channel->clientIsOnChannel(
                    $connection->getOption("id"), $c["name"])) {
                  $event = EventHandling::getEventByName("channelTopicEvent");
                  if ($event != false) {
                    foreach ($event[2] as $id => $registration) {
                      // Trigger the channelTopicEvent event for each
                      // registered module.
                      EventHandling::triggerEvent("channelTopicEvent", $id,
                          array($connection, $c, $command[1]));
                    }
                  }
                }
                else {
                  $connection->send(":".__SERVERDOMAIN__." 442 ".
                    $connection->getOption("nick")." ".$c["name"].
                    " :You're not on that channel");
                }
              }
              else {
                $connection->send(":".__SERVERDOMAIN__." 403 ".
                  $connection->getOption("nick")." ".$target.
                  " :No such channel");
              }
            }
            else {
              $c = $this->channel->getChannelByName($target);
              if ($c != false) {
                if (!isset($c["topic"]) || $c["topic"]["text"] == null) {
                  if (!isset($data[2])) {
                    $connection->send(":".__SERVERDOMAIN__." 331 ".
                      $connection->getOption("nick")." ".$target.
                      " :No topic is set.");
                  }
                }
                else {
                  $base = ":".__SERVERDOMAIN__." 332 ".
                    $connection->getOption("nick")." ".$target." :";
                  $connection->send($base.substr($c["topic"]["text"], 0,
                    (510 - strlen($base))));
                  $connection->send(":".__SERVERDOMAIN__." 333 ".
                    $connection->getOption("nick")." ".$target." ".
                    $c["topic"]["author"]." ".$c["topic"]["timestamp"]);
                }
              }
            }
          }
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 461 ".
            $connection->getOption("nick")." TOPIC :Not enough parameters");
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
        "topic");
      return true;
    }
  }
?>
