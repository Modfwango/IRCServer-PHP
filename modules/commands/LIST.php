<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "CommandEvent", "Numeric", "Self",
      "ShouldExposeChannelToUserEvent");
    public $name = "LIST";
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
        $connection->send($this->numeric->get("RPL_LISTSTART", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick")
        )));
        if (count($command) > 0) {
          $c = $this->channel->getChannelByName($command[0]);
          if ($c != false) {
            $show = true;
            $event = EventHandling::getEventByName(
              "shouldExposeChannelToUserEvent");
            if ($event != false) {
              foreach ($event[2] as $id => $registration) {
                // Trigger the shouldExposeChannelToUserEvent event for each
                // registered module.
                if (!EventHandling::triggerEvent(
                    "shouldExposeChannelToUserEvent", $id,
                    array($connection, $c["name"]))) {
                  $show = false;
                }
              }
            }
            if ($show == true) {
              $connection->send($this->numeric->get("RPL_LIST", array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $c["name"],
                count($c["members"]),
                (isset($c["topic"]["text"]) ? $c["topic"]["text"] : null)
              )));
            }
          }
          else {
            $connection->send($this->numeric->get("ERR_NOSUCHNICK", array(
              $this->self->getConfigFlag("serverdomain"),
              $connection->getOption("nick"),
              $command[0]
            )));
          }
        }
        else {
          foreach ($this->channel->getChannels() as $c) {
            $event = EventHandling::getEventByName(
              "shouldExposeChannelToUserEvent");
            if ($event != false) {
              foreach ($event[2] as $id => $registration) {
                // Trigger the shouldExposeChannelToUserEvent event for each
                // registered module.
                if (!EventHandling::triggerEvent(
                    "shouldExposeChannelToUserEvent", $id,
                    array($connection, $c["name"]))) {
                  continue 2;
                }
              }
            }
            $connection->send($this->numeric->get("RPL_LIST", array(
              $this->self->getConfigFlag("serverdomain"),
              $connection->getOption("nick"),
              $c["name"],
              count($c["members"]),
              (isset($c["topic"]["text"]) ? $c["topic"]["text"] : null)
            )));
          }
        }
        $connection->send($this->numeric->get("RPL_LISTEND", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick")
        )));
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
        "list");
      return true;
    }
  }
?>
