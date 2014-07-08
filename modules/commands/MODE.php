<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "ChannelOperator", "Client",
      "CommandEvent", "Modes");
    public $name = "MODE";
    private $channel = null;
    private $client = null;
    private $modes = null;

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

      if (strtolower($command[0]) == "mode") {
        if ($connection->getOption("registered") == true) {
          if (count($command) > 1) {
            if (count($command) > 2) {
              if (count($command) > 3) {
                for ($i = 3; $i < count($command); $i++) {
                  $command[2] .= " ".$command[$i];
                }
              }

              $channel = $this->channel->getChannelByName($command[1]);
              $client = $this->client->getClientByNick($command[1]);
              if ($channel != false) {
                $opped = false;
                $has = $this->channel->hasModes($channel["name"],
                  array("ChannelOperator"));
                if (is_array($has)) {
                  foreach ($has as $m) {
                    if ($m["param"] == $connection->getOption("nick")) {
                      $opped = true;
                      $modes = $this->parseModes("0", $command[2]);
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
                  $connection->send(":".__SERVERDOMAIN__." 482 ".
                    $connection->getOption("nick")." ".$channel["name"].
                    " :You're not a channel operator");
                }
              }
              elseif ($client != false) {
                if ($client->getOption("nick")
                    == $connection->getOption("nick")) {
                  $modes = $this->parseModes("1", $command[2]);
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
                  $connection->send(":".__SERVERDOMAIN__." 502 ".
                    $connection->getOption("nick").
                    " :Can't change mode for other users");
                }
              }
              else {
                $connection->send(":".__SERVERDOMAIN__." 403 ".
                  $connection->getOption("nick")." ".$command[1].
                  " :No such channel");
              }
            }
            else {
              $channel = $this->channel->getChannelByName($command[1]);
              $client = $this->client->getClientByNick($command[1]);
              if ($channel != false) {
                $modes = array();
                $params = array();
                if (isset($channel["modes"]) && count($channel["modes"]) > 1) {
                  foreach ($channel["modes"] as $mode) {
                    $m = $this->modes->getModeByName($mode["name"]);
                    if ($m != false) {
                      if ($m == "0") {
                        $modes[] = $m[1];
                      }
                      if ($m == "1" || $m == "2") {
                        $modes[] = $m[1];
                        $params[] = $mode["param"];
                      }
                    }
                  }
                }
                $modes = "+".implode($modes);
                $params = implode(" ", $params);
                $modeString = $modes." ".$params;
                $connection->send(":".__SERVERDOMAIN__." 324 ".
                  $connection->getOption("nick")." ".$channel["name"]." ".
                  $modeString);
                if (isset($channel["modetime"])) {
                  $connection->send(":".__SERVERDOMAIN__." 329 ".
                    $connection->getOption("nick")." ".$channel["name"]." ".
                    $channel["modetime"]);
                }
                else {
                  $channel["modetime"] = $channel["created"];
                  $this->channel->setChannel($channel);
                }
              }
              elseif ($client != false) {
                if ($client->getOption("nick")
                    == $connection->getOption("nick")) {
                  $modes = array();
                  $params = array();
                  if (is_array($client->getOption("modes"))) {
                    foreach ($client->getOption("modes") as $mode) {
                      $m = $this->modes->getModeByName($mode["name"]);
                      if ($m != false) {
                        if ($m == "0") {
                          $modes[] = $m[1];
                        }
                        if ($m == "1" || $m == "2") {
                          $modes[] = $m[1];
                          $params[] = $mode["param"];
                        }
                      }
                    }
                  }
                  $modes = "+".implode($modes);
                  $params = implode(" ", $params);
                  $modeString = $modes." ".$params;
                  $connection->send(":".__SERVERDOMAIN__." 221 ".
                    $connection->getOption("nick")." ".$modeString);
                }
                else {
                  $connection->send(":".__SERVERDOMAIN__." 502 ".
                    $connection->getOption("nick").
                    " :Can't change mode for other users");
                }
              }
              else {
                $connection->send(":".__SERVERDOMAIN__." 403 ".
                  $connection->getOption("nick")." ".$command[1].
                  " :No such channel");
              }
            }
          }
          else {
            $connection->send(":".__SERVERDOMAIN__." 461 ".
              $connection->getOption("nick")." MODE :Not enough parameters");
          }
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 451 ".(
            $connection->getOption("nick") ? $connection->getOption("nick") :
            "*")." :You have not registered");
        }
        return true;
      }
      return false;
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
