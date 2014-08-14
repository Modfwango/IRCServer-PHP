<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "ChannelOperator", "Client",
      "CommandEvent");
    public $name = "INVITE";
    private $channel = null;
    private $client = null;

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
                if (!$this->channel->clientIsOnChannel(
                    $connection->getOption("id"), $target)) {
                  $canInvite = false;
                  $event = EventHandling::getEventByName(
                    "lackOfChannelOperatorShouldPreventInvitationEvent");
                  if ($event != false) {
                    foreach ($event[2] as $id => $registration) {
                      // Trigger the
                      // lackOfChannelOperatorShouldPreventInvitationEvent event
                      // for each registered module.
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
                      if ($mode["param"] == $connection->getOption("nick")) {
                        $canInvite = true;
                      }
                    }
                  }
                  if ($canInvite == true) {
                    $recipient->send(":".$connection->getOption("nick")."!".
                      $connection->getOption("ident")."@".
                      $connection->getOption("nick")." INVITE ".
                      $recipient->getOption("nick")." :".$target);
                    $connection->send(":".__SERVERDOMAIN__." 341 ".
                      $connection->getOption("nick")." ".
                      $recipient->getOption("nick")." ".$target);
                  }
                  else {
                    $connection->send(":".__SERVERDOMAIN__." 482 ".
                      $connection->getOption("nick")." ".$target.
                      " :You're not a channel operator");
                  }
                }
                else {
                  $connection->send(":".__SERVERDOMAIN__." 443 ".
                    $connection->getOption("nick")." ".
                    $recipient->getOption("nick")." ".$target.
                    " :is already on channel");
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
            $connection->send(":".__SERVERDOMAIN__." 401 ".
              $connection->getOption("nick")." ".$command[0].
              " :No such nick/channel");
          }
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 461 ".
            $connection->getOption("nick")." INVITE :Not enough parameters");
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
      $this->client = ModuleManagement::getModuleByName("Client");
      EventHandling::createEvent(
        "lackOfChannelOperatorShouldPreventInvitationEvent", $this);
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "invite");
      return true;
    }
  }
?>
