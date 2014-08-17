<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "ChannelNoticeEvent", "Client",
      "CommandEvent", "PrivateNoticeEvent", "Self");
    public $name = "NOTICE";
    private $channel = null;
    private $client = null;
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
                  "channelNoticeEvent");
                if ($event != false) {
                  foreach ($event[2] as $id => $registration) {
                    // Trigger the channelNoticeEvent event for each
                    // registered module.
                    EventHandling::triggerEvent("channelNoticeEvent", $id,
                        array($connection, $c, $command[1]));
                  }
                }
              }
              else {
                $connection->send(":".$this->self->getConfigFlag(
                "serverdomain")." 401 ".$connection->getOption("nick")." ".
                "NOTICE :No such nick/channel");
              }
            }
            elseif (preg_match("/^[[\\]a-zA-Z\\\\`_^{|}][[\\]a-zA-Z0-9\\\\`_".
                    "^{|}-]*$/", $target)) {
              $c = $this->client->getClientByNick($target);
              if ($c != false) {
                $found = true;
                $event = EventHandling::getEventByName(
                  "privateNoticeEvent");
                if ($event != false) {
                  foreach ($event[2] as $id => $registration) {
                    // Trigger the privateNoticeEvent event for each
                    // registered module.
                    EventHandling::triggerEvent("privateNoticeEvent", $id,
                        array($connection, $c, $command[1]));
                  }
                }
              }
              else {
                $connection->send(":".$this->self->getConfigFlag(
                  "serverdomain")." 401 ".$connection->getOption("nick")." ".
                  "NOTICE :No such nick/channel");
              }
            }
          }
        }
        elseif (count($command) == 1) {
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 412 ".$connection->getOption("nick")." :No text ".
            "to send");
        }
        elseif (count($command) == 0) {
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 411 ".$connection->getOption("nick")." :No ".
            "recipient given (NOTICE)");
        }
      }
      else {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 451 ".($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "notice");
      return true;
    }
  }
?>
