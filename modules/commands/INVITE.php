<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "ChannelOperator", "Client",
      "ChannelInviteEvent", "CommandEvent",
      "LackOfChannelOperatorShouldPreventInvitationEvent", "Self");
    public $name = "INVITE";
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
        if (count($command) > 1) {
          $recipient = $this->client->getClientByNick($command[0]);
          if ($recipient != false) {
            $targets = array($command[1]);
            if (stristr($command[1], ",")) {
              $targets = explode(",", $command[1]);
            }
            foreach ($targets as $target) {
              $c = $this->channel->getChannelByName($target);
              if ($c != false) {
                if ($this->channel->clientIsOnChannel(
                    $connection->getOption("id"), $target)) {
                  if (!$this->channel->clientIsOnChannel(
                      $recipient->getOption("id"), $target)) {
                    $canInvite = false;
                    $event = EventHandling::getEventByName(
                      "lackOfChannelOperatorShouldPreventInvitationEvent");
                    if ($event != false) {
                      foreach ($event[2] as $id => $registration) {
                        // Trigger the
                        // lackOfChannelOperatorShouldPreventInvitationEvent
                        // event for each registered module.
                        if (!EventHandling::triggerEvent(
                            "lackOfChannelOperatorShouldPreventInvitationEvent",
                            $id, array($connection, $recipient, $c))) {
                          $canInvite = true;
                          break;
                        }
                      }
                    }
                    if ($canInvite == false) {
                      $has = $this->channel->hasModes($target,
                        array("ChannelOperator"));
                      foreach ($has as $mode) {
                        if ($mode["param"] == $connection->getOption("id")) {
                          $canInvite = true;
                        }
                      }
                    }
                    if ($canInvite == true) {
                      $event = EventHandling::getEventByName(
                        "channelInviteEvent");
                      if ($event != false) {
                        foreach ($event[2] as $id => $registration) {
                          // Trigger the channelInviteEvent event for each
                          // registered module.
                          EventHandling::triggerEvent("channelInviteEvent", $id,
                            array($connection, $recipient, $target));
                        }
                      }
                    }
                    else {
                      $connection->send(":".$this->self->getConfigFlag(
                        "serverdomain")." 482 ".$connection->getOption(
                        "nick")." ".$target." :You're not a channel operator");
                    }
                  }
                  else {
                    $connection->send(":".$this->self->getConfigFlag(
                      "serverdomain")." 443 ".$connection->getOption(
                      "nick")." ".$target." :is already on channel");
                  }
                }
                else {
                  $connection->send(":".$this->self->getConfigFlag(
                    "serverdomain")." 442 ".$connection->getOption("nick")." ".
                    $recipient->getOption("nick")." ".$target." :You're not ".
                    "on that channel");
                }
              }
              else {
                $connection->send(":".$this->self->getConfigFlag(
                  "serverdomain")." 403 ".$connection->getOption("nick")." ".
                  $target." :No such channel");
              }
            }
          }
          else {
            $connection->send(":".$this->self->getConfigFlag(
              "serverdomain")." 401 ".$connection->getOption("nick")." ".
              $command[0]." :No such nick/channel");
          }
        }
        else {
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 461 ".$connection->getOption("nick")." INVITE ".
            ":Not enough parameters");
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
        "invite");
      return true;
    }
  }
?>
