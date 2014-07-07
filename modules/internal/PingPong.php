<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent", "QUIT", "Timer", "USER");
    public $name = "PingPong";
    private $quit = null;
    private $responses = array();

    private function getPingResponse($server, $subject) {
      return ":".$server." PONG ".$server." :".$subject;
    }

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if (strtolower($command[0]) == "ping") {
        if (count($command) == 2) {
          $connection->send(
            $this->getPingResponse(__SERVERDOMAIN__, $command[1]));
        }
        elseif (count($command) > 2) {
          if (strtolower($command[2]) == strtolower(__SERVERDOMAIN__)) {
            $connection->send(
              $this->getPingResponse(__SERVERDOMAIN__, $command[1]));
          }
          else {
            $connection->send(":".__SERVERDOMAIN__." 402 ".
              $connection->getOption("nick")." ".$command[2].
              " :No such server");
          }
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 409 ".
            $connection->getOption("nick")." :No origin specified");
        }
        return true;
      }
      if (strtolower($command[0]) == "pong") {
        if (strtolower($command[1]) == strtolower(__SERVERDOMAIN__)) {
          $this->responses[$connection->getOption("id")] = true;
        }
        return true;
      }
      return false;
    }

    public function receiveUserRegistration($name, $connection) {
      $this->responses[$connection->getOption("id")] = true;
      ModuleManagement::getModuleByName("Timer")->newTimer(__PINGTIME__, $this,
        "sendPingRequest", $connection);
      return true;
    }

    public function sendPingRequest($connection) {
      if ($this->responses[$connection->getOption("id")] == true) {
        $this->responses[$connection->getOption("id")] = false;
        $connection->send("PING :".__SERVERDOMAIN__);
        ModuleManagement::getModuleByName("Timer")->newTimer(__PINGTIME__,
          $this, "sendPingRequest", $connection);
      }
      else {
        $message = "Ping timeout: ".__PINGTIME__." seconds";
        if ($connection->getOption("registered") == true) {
          $connection->send(":".$connection->getOption("nick")."!".
            $connection->getOption("ident")."@".$connection->getHost().
            " QUIT :".$message);
        }
        $connection->send("ERROR :Closing Link: ".$connection->getHost().
          " (".$message.")");
        $this->quit->notifyQuit(null, $connection, $message);
        $connection->setOption("registered", false);
        $connection->disconnect();
      }
    }

    public function isInstantiated() {
      $this->quit = ModuleManagement::getModuleByName("QUIT");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      EventHandling::registerForEvent("userRegistrationEvent", $this,
        "receiveUserRegistration");
      return true;
    }
  }
?>
