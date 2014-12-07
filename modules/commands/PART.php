<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "CommandEvent", "ChannelPartEvent",
      "Numeric", "Self");
    public $name = "PART";
    private $channel = null;
    private $numeric = null;
    private $self = null;

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
              $connection->send($this->numeric->get("ERR_NOSUCHCHANNEL", array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $target
              )));
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
        array("part", false));
      return true;
    }
  }
?>
