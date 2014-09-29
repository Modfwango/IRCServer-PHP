<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "Client", "CommandEvent", "Modes",
      "Numeric", "Self");
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
          $modenames = array();
          $prefixes = array();
          foreach ($this->modes->getModesAndWeight() as $weight => $modes) {
            foreach ($modes as $mode) {
              $modenames[] = $mode[0];
              $prefixes[$mode[0]] = array($mode[4], $mode[5]);
            }
          }
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
              $p = array();
              $has = $this->channel->hasModes($channel["name"], $modenames);
              if ($has != false) {
                foreach ($has as $m) {
                  if ($m["param"] == $c->getOption("id")
                      && isset($prefixes[$m["name"]])) {
                    if (!isset($p[$prefixes[$m["name"]][1]])) {
                      $p[$prefixes[$m["name"]][1]] = array();
                    }
                    $p[$prefixes[$m["name"]][1]][] =
                      $prefixes[$m["name"]][0];
                  }
                }
              }
              ksort($p);
              end($p);
              $weight = key($p);
              $p = array_pop($p);
              if (is_array($p)) {
                $p = array_shift($p);
              }
              $users[] = array($c, $p);
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
