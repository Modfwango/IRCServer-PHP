<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "Client", "CommandEvent", "Modes");
    public $name = "MODE";
    private $channel = null;
    private $client = null;
    private $modes = null;

    public function parseModes($type, $modeString) {
      /*
        MODE hi +i
        :hi MODE hi :+i

        JOIN #bobby
        :kelabs.arinity.org MODE #bobby +nt

        MODE #bobby -n
        :hi!lol@199.68.xkl.qkq MODE #bobby -n
      */
      $mex = array($modeString);
      if (stristr(" ", $mex[0])) {
        $mex = explode(" ", $mex[0]);
      }

      $operation = "+";
      $modes = array();
      $ms = str_split(array_shift($mex));
      $mex = array_values($mex);
      Logger::info(var_export($ms, true));
      Logger::info(var_export($mex, true));
      foreach ($ms as $m) {
        if ($m == "+" || $m == "-") {
          $operation = $m;
        }
        else {
          $mode = $this->modes->getModeByChar($type, $m);
          Logger::info(var_export($mode, true));
          if ($mode != false) {
            if ($operation == "+" && in_array($mode[3],
                array("1", "2", "3", "4"))) {
              if (isset($mex[0])) {
                $modes[] = array(
                  "operation" => $operation,
                  "name" => $mode[0],
                  "param" => array_shift($mex)
                );
                Logger::info("Added mode to stack:  ".var_export($modes));
                $mex = array_values($mex);
              }
            }
            elseif ($operation == "+" && in_array($mode[3],
                    array("0"))) {
              $modes[] = array(
                "operation" => $operation,
                "name" => $mode[0]
              );
              Logger::info("Added mode to stack:  ".var_export($modes));
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
                Logger::info("Added mode to stack:  ".var_export($modes));
              }
            }
            elseif ($operation == "-" && in_array($mode[3],
                    array("0", "2"))) {
              $modes[] = array(
                "operation" => $operation,
                "name" => $mode[0]
              );
              Logger::info("Added mode to stack:  ".var_export($modes));
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
                $modes = $this->parseModes("0", $command[2]);
                $event = EventHandling::getEventByName("channelModeEvent");
                if ($event != false) {
                  foreach ($event[2] as $id => $registration) {
                    // Trigger the channelModeEvent event for each registered
                    // module.
                    EventHandling::triggerEvent("channelModeEvent", $id,
                        array($connection, $channel, $modes));
                  }
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
              /*
                MODE hi
                :kelabs.arinity.org 221 hi +ix

                MODE #chat
                :kelabs.arinity.org 324 hi #chat +nt
                :kelabs.arinity.org 329 hi #chat 1401152496
              */
              $channel = $this->channel->getChannelByName($command[1]);
              $client = $this->client->getClientByNick($command[1]);
              if ($channel != false) {
                // Show channel modes.
              }
              elseif ($client != false) {
                if ($client->getOption("nick")
                    == $connection->getOption("nick")) {
                  // Show client modes.
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
