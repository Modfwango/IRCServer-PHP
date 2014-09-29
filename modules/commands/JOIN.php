<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "ChannelJoinEvent", "CommandEvent",
      "Numeric", "Self");
    public $name = "JOIN";
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
                $connection->send($this->numeric->get("ERR_BADCHANNAME", array(
                  $this->self->getConfigFlag("serverdomain"),
                  $connection->getOption("nick"),
                  $channel
                )));
              }
            }
            else {
              $connection->send($this->numeric->get("ERR_BADCHANNAME", array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $channel
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
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "join");
      return true;
    }
  }
?>
