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

    public function parseModes($type, $modeString) {
      $mex = array($modeString);
      if (stristr($mex[0], " ")) {
        $mex = explode(" ", $mex[0]);
      }

      $operation = "+";
      $modes = array();
      $ms = str_split(array_shift($mex));
      $mex = array_values($mex);
      foreach ($ms as $m) {
        if ($m == "+" || $m == "-") {
          $operation = $m;
        }
        else {
          $mode = $this->modes->getModeByChar($type, $m);
          if ($mode != false) {
            if ($operation == "+" && in_array($mode[3],
                array("1", "2", "3", "4"))) {
              if (isset($mex[0])) {
                $modes[] = array(
                  "operation" => $operation,
                  "name" => $mode[0],
                  "param" => array_shift($mex)
                );
                $mex = array_values($mex);
              }
            }
            elseif ($operation == "+" && in_array($mode[3],
                    array("0"))) {
              $modes[] = array(
                "operation" => $operation,
                "name" => $mode[0]
              );
            }
            elseif ($operation == "-" && in_array($mode[3],
                    array("1", "3", "4"))) {
              if (isset($mex[0])) {
                $modes[] = array(
                  "operation" => $operation,
                  "name" => $mode[0],
                  "param" => array_shift($mex)
                );
                $mex = array_values($mex);
              }
            }
            elseif ($operation == "-" && in_array($mode[3],
                    array("0", "2"))) {
              $modes[] = array(
                "operation" => $operation,
                "name" => $mode[0]
              );
            }
          }
        }
      }
      return $modes;
    }

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
              $has = $this->channel->hasModes($channel["name"],
                array("ChannelOperator"));
              if (is_array($has)) {
                foreach ($has as $m) {
                  if ($m["param"] == $connection->getOption("id")) {
                    $opped = true;
                    $modes = $this->parseModes("0", $command[1]);
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
                }
              }
              if ($opped == false) {
                return;
                // TODO: Move this logic to individual list mode modules.
                // $okay = false;
                // $ms = $this->modes->getModeNamesByType("3");
                // foreach (str_split($command[1]) as $mode) {
                //   $m = $this->modes->getModeByChar("0", $mode);
                //   if ($m != false && !in_array($m["name"], $ms)) {
                //     $okay = false;
                //   }
                // }
                // if ($okay == false) {
                //   $connection->send($this->numeric->get("ERR_CHANOPRIVSNEEDED",
                //     array(
                //       $this->self->getConfigFlag("serverdomain"),
                //       $connection->getOption("nick"),
                //       $channel["name"]
                //     )
                //   ));
                // }
                // else {
                //   // Use as a filter to list modes for this channel.
                //   $connection->send($this->numeric->get("RPL_ENDOFBANLIST",
                //     array(
                //       $this->self->getConfigFlag("serverdomain"),
                //       $connection->getOption("nick"),
                //       $channel["name"]
                //     )
                //   ));
                // }
              }
            }
            elseif ($client != false) {
              if ($client->getOption("nick")
                  == $connection->getOption("nick")) {
                $modes = $this->parseModes("1", $command[1]);
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
        "mode");
      return true;
    }
  }
?>
