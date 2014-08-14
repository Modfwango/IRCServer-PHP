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
                    Logger::info($mode["param"]);
                    Logger::info($connection->getOption("nick"));
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
                }
                else {
                  $connection->send(":".__SERVERDOMAIN__." 482 ".
                    $connection->getOption("nick")." ".$target.
                    " :You're not a channel operator");
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
      /*
       *  Invite command examples:
       *
       *  INVITE OperServ #test
       *  :oxygen.arinity.org 442 lol #test :You're not on that channel
       *
       *    JOIN #test
       *    :lol!lol@199.68.xkl.qkq JOIN #test
       *    :oxygen.arinity.org 353 lol = #test :lol @clayfreeman
       *    :oxygen.arinity.org 366 lol #test :End of /NAMES list.
       *
       *  INVITE OperServ #test
       *  :kelabs.arinity.org 482 lol #test :You're not a channel operator
       *
       *    :clayfreeman!clay@clayfreeman.com MODE #test +g
       *
       *  INVITE OperServ #test
       *  :kelabs.arinity.org 341 lol OperServ #test
       *
       *  INVITE bobby #test
       *  :kelabs.arinity.org 401 lol bobby :No such nick/channel
       */
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      EventHandling::createEvent(
        "lackOfChannelOperatorShouldPreventInvitationEvent", $this,
        "receiveBanShouldPreventAction");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "invite");
      return true;
    }
  }
?>
