<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "ChannelOperator", "Client",
      "ChannelInviteEvent", "CommandEvent",
      "LackOfChannelOperatorShouldPreventInvitationEvent", "Numeric", "Self");
    public $name = "INVITE";
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
                      $connection->send($this->numeric->get(
                        "ERR_CHANOPRIVSNEEDED",
                        array(
                          $this->self->getConfigFlag("serverdomain"),
                          $connection->getOption("nick"),
                          $target
                        )
                      ));
                    }
                  }
                  else {
                    $connection->send($this->numeric->get("ERR_USERONCHANNEL",
                      array(
                        $this->self->getConfigFlag("serverdomain"),
                        $connection->getOption("nick"),
                        $target
                      )
                    ));
                  }
                }
                else {
                  $connection->send($this->numeric->get("ERR_NOTONCHANNEL",
                    array(
                      $this->self->getConfigFlag("serverdomain"),
                      $connection->getOption("nick"),
                      $target
                    )
                  ));
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
          else {
            $connection->send($this->numeric->get("ERR_NOSUCHNICK", array(
              $this->self->getConfigFlag("serverdomain"),
              $connection->getOption("nick"),
              $command[0]
            )));
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
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("invite", false));
      return true;
    }
  }
?>
