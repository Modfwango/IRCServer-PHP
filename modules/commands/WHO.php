<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "Client", "CommandEvent", "Modes",
      "Numeric", "Self", "ShouldExposeChannelToUserEvent");
    public $name = "WHO";
    private $channel = null;
    private $client = null;
    private $modes = null;
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
          $match = "*";
          $users = array();
          $show = true;
          $channel = $this->channel->getChannelByName($command[0]);
          if ($channel != false) {
            $event = EventHandling::getEventByName(
              "shouldExposeChannelToUserEvent");
            if ($event != false) {
              foreach ($event[2] as $id => $registration) {
                // Trigger the shouldExposeChannelToUserEvent event for each
                // registered module.
                if (!EventHandling::triggerEvent(
                    "shouldExposeChannelToUserEvent", $id,
                    array($connection, $channel["name"]))) {
                  $show = false;
                }
              }
            }

            $match = $command[0];
            foreach ($channel["members"] as $member) {
              $c = $this->client->getClientByID($member);
              $users[] = array($c, $this->channel->getChannelMemberPrefixByID(
                $channel["name"], $c->getOption("id")));
            }
          }
          else {
            if ($command[0] == "0" || $command[0] == "*") {
              $users = ConnectionManagement::getConnections();
            }
            else {
              foreach ($this->client->getClientsByMatchingHost($command[0])
                        as $client) {
                $users[] = $client;
              }
              foreach ($this->client->getClientsByMatchingIdent($command[0])
                        as $client) {
                $users[] = $client;
              }
              foreach ($this->client->getClientsByMatchingNick($command[0])
                        as $client) {
                $users[] = $client;
              }
              foreach ($this->client->getClientsByMatchingRealname(
                        $command[0]) as $client) {
                $users[] = $client;
              }
            }
          }
          if ($show == true) {
            foreach ($users as $user) {
              $prefix = null;
              if (is_array($user)) {
                $prefix = $user[1];
                $user = $user[0];
              }
              $connection->send($this->numeric->get("RPL_WHOREPLY", array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $match,
                $user->getOption("ident"),
                $user->getHost(),
                $this->self->getConfigFlag("serverdomain"),
                $user->getOption("nick"),
                "H".$prefix,
                "0",
                $user->getOption("realname")
              )));
            }
          }
          $connection->send($this->numeric->get("RPL_ENDOFWHO", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick"),
            $command[0]
          )));
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
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "who");
      return true;
    }
  }
?>
