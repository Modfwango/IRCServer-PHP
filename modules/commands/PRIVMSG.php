<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "ChannelMessageEvent", "Client",
      "CommandEvent", "Numeric", "PrivateMessageEvent", "Self");
    public $name = "PRIVMSG";
    private $channel = null;
    private $client = null;
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
        if (count($command) == 2) {
          $targets = array($command[0]);
          if (stristr($command[0], ",")) {
            $targets = explode(",", $command[0]);
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
                        array($connection, $c, $command[1]));
                  }
                }
              }
              else {
                $connection->send($this->numeric->get("ERR_NOSUCHNICK", array(
                  $this->self->getConfigFlag("serverdomain"),
                  $connection->getOption("nick"),
                  $target
                )));
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
                        array($connection, $c, $command[1]));
                  }
                }
              }
              else {
                $connection->send($this->numeric->get("ERR_NOSUCHNICK", array(
                  $this->self->getConfigFlag("serverdomain"),
                  $connection->getOption("nick"),
                  $target
                )));
              }
            }
          }
        }
        elseif (count($command) == 1) {
          $connection->send($this->numeric->get("ERR_NOTEXTTOSEND", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick"),
            $this->name
          )));
        }
        elseif (count($command) == 0) {
          $connection->send($this->numeric->get("ERR_NORECIPIENT", array(
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
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("privmsg", false));
      return true;
    }
  }
?>
