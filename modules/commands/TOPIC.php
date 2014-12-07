<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "CommandEvent", "ChannelTopicEvent",
      "Numeric", "Self");
    public $name = "TOPIC";
    private $channel = null;
    private $numeric = null;
    private $self = null;

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
                  $connection->send($this->numeric->get("ERR_NOTONCHANNEL",
                    array(
                      $this->self->getConfigFlag("serverdomain"),
                      $connection->getOption("nick"),
                      $c["name"]
                    )
                  ));
                }
              }
              else {
                $connection->send($this->numeric->get("ERR_NOSUCHCHANNEL",
                  array(
                    $this->self->getConfigFlag("serverdomain"),
                    $connection->getOption("nick"),
                    $target
                  )
                ));
              }
            }
            else {
              $c = $this->channel->getChannelByName($target);
              if ($c != false) {
                if (!isset($c["topic"]) || $c["topic"]["text"] == null) {
                  if (!isset($data[2])) {
                    $connection->send($this->numeric->get("RPL_NOTOPIC", array(
                      $this->self->getConfigFlag("serverdomain"),
                      $connection->getOption("nick"),
                      $target
                    )));
                  }
                }
                else {
                  $base = $this->numeric->get("RPL_TOPIC", array(
                    $this->self->getConfigFlag("serverdomain"),
                    $connection->getOption("nick"),
                    $target,
                    null
                  ));
                  $connection->send($base.substr($c["topic"]["text"], 0,
                    (510 - strlen($base))));
                  $connection->send($this->numeric->get("RPL_TOPICWHOTIME",
                    array(
                      $this->self->getConfigFlag("serverdomain"),
                      $connection->getOption("nick"),
                      $target,
                      $c["topic"]["author"],
                      $c["topic"]["timestamp"]
                    )
                  ));
                }
              }
            }
          }
        }
        else {
          $connection->send(":".$this->numeric->get("ERR_NEEDMOREPARAMS", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick"),
            $this->name
          )));
        }
      }
      else {
        $connection->send($this->numeric->get("ERR_NOTREGISTERED", array(
          $this->self->getConfigFlag("serverdomain"),
          ($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")
        )));
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("topic", false));
      return true;
    }
  }
?>
