<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "ChannelModeEvent", "ChannelOperator",
      "Client", "CommandEvent", "Modes", "Numeric", "Self", "UserModeEvent");
    public $name = "MODE";
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
          if (count($command) > 1) {
            if (count($command) > 2) {
              for ($i = 2; $i < count($command); $i++) {
                $command[1] .= " ".$command[$i];
              }
            }

            $channel = $this->channel->getChannelByName($command[0]);
            $client = $this->client->getClientByNick($command[0]);
            if ($channel != false) {
              $opped = false;
              $permitted = true;
              $modes = $this->modes->parseModes("0", $command[1]);
              $has = $this->channel->hasModes($channel["name"],
                array("ChannelOperator"));
              if (is_array($has)) {
                foreach ($has as $m) {
                  if ($m["param"] == $connection->getOption("id")) {
                    $opped = true;
                    break;
                  }
                }
              }
              if ($opped == false) {
                foreach ($modes as $mo) {
                  $mod = $this->modes->getModeByName($mo["name"]);
                  if ($mod[3] != 3 || (isset($mo["param"])
                      && trim($mo["param"]) != null)) {
                    $permitted = false;
                  }
                }
              }
              if ($opped == true || $permitted == true) {
                $event = EventHandling::getEventByName(
                  "channelModeEvent");
                if ($event != false) {
                  foreach ($event[2] as $id => $registration) {
                    // Trigger the channelModeEvent event for each
                    // registered module.
                    EventHandling::triggerEvent("channelModeEvent", $id,
                      array($connection, $channel, $modes));
                  }
                }
              }
              else {
                $connection->send($this->numeric->get("ERR_CHANOPRIVSNEEDED",
                  array(
                    $this->self->getConfigFlag("serverdomain"),
                    $connection->getOption("nick"),
                    $channel["name"]
                  )
                ));
              }
            }
            elseif ($client != false) {
              if ($client->getOption("nick")
                  == $connection->getOption("nick")) {
                $modes = $this->modes->parseModes("1", $command[1]);
                $event = EventHandling::getEventByName("userModeEvent");
                if ($event != false) {
                  foreach ($event[2] as $id => $registration) {
                    // Trigger the userModeEvent event for each registered
                    // module.
                    EventHandling::triggerEvent("userModeEvent", $id,
                      array($connection, $modes));
                  }
                }
              }
              else {
                $connection->send($this->numeric->get("ERR_USERSDONTMATCH",
                  array(
                    $this->self->getConfigFlag("serverdomain"),
                    $connection->getOption("nick")
                  )
                ));
              }
            }
            else {
              $connection->send($this->numeric->get("ERR_NOSUCHCHANNEL", array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $command[0]
              )));
            }
          }
          else {
            $channel = $this->channel->getChannelByName($command[0]);
            $client = $this->client->getClientByNick($command[0]);
            if ($channel != false) {
              $ms = $this->modes->getModeStringComponents($channel["modes"],
                true);
              $connection->send($this->numeric->get("RPL_CHANNELMODEIS", array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $channel["name"],
                trim("+".implode($ms[0])." ".implode(" ", $ms[1]))
              )));
              if (!isset($data[2])) {
                $connection->send($this->numeric->get("RPL_CREATIONTIME", array(
                  $this->self->getConfigFlag("serverdomain"),
                  $connection->getOption("nick"),
                  $channel["name"],
                  $channel["time"]
                )));
              }
            }
            elseif ($client != false) {
              if ($client->getOption("nick")
                  == $connection->getOption("nick")) {
                $ms = $this->modes->getModeStringComponents(
                  $connection->getOption("modes"), true);
                $connection->send($this->numeric->get("RPL_UMODEIS", array(
                  $this->self->getConfigFlag("serverdomain"),
                  $connection->getOption("nick"),
                  trim("+".implode($ms[0])." ".implode(" ", $ms[1]))
                )));
              }
              else {
                $connection->send($this->numeric->get("ERR_USERSDONTMATCH",
                  array(
                    $this->self->getConfigFlag("serverdomain"),
                    $connection->getOption("nick")
                  )
                ));
              }
            }
            else {
              $connection->send($this->numeric->get("ERR_NOSUCHCHANNEL", array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $command[0]
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
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("mode", false));
      return true;
    }
  }
?>
