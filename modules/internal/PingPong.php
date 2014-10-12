<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "QUIT", "Numeric", "Self", "Timer",
      "UserRegistrationEvent");
    public $name = "PingPong";
    private $numeric = null;
    private $responses = array();
    private $self = null;

    private function getPingResponse($server, $subject) {
      return ":".$server." PONG ".$server." :".$subject;
    }

    public function receivePingCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if (count($command) == 1) {
        $connection->send(
          $this->getPingResponse($this->self->getConfigFlag("serverdomain"),
          $command[0]));
      }
      elseif (count($command) > 1) {
        if (strtolower($command[1]) == strtolower($this->self->getConfigFlag(
            "serverdomain"))) {
          $connection->send(
            $this->getPingResponse($this->self->getConfigFlag("serverdomain"),
            $command[0]));
        }
        else {
          $connection->send($this->numeric->get("ERR_NOSUCHSERVER", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick"),
            $command[1]
          )));
        }
      }
      else {
        $connection->send($this->numeric->get("ERR_NOORIGIN", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick")
        )));
      }
    }

    public function receivePongCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      if (strtolower($command[0]) == strtolower($this->self->getConfigFlag(
          "serverdomain"))) {
        $this->responses[$connection->getOption("id")] = true;
      }
    }

    public function receiveUserRegistration($name, $connection) {
      if ($connection->getType() != "2") {
        $this->responses[$connection->getOption("id")] = true;
        ModuleManagement::getModuleByName("Timer")->newTimer(
          $this->self->getConfigFlag("pingtime"), $this, "sendPingRequest",
          $connection);
      }
      return true;
    }

    public function sendPingRequest($connection) {
      if ($this->responses[$connection->getOption("id")] == true
          && time() - $connection->getOption("idle") >=
          $this->self->getConfigFlag("pingtime")) {
        $this->responses[$connection->getOption("id")] = false;
        $connection->send("PING :".$this->self->getConfigFlag("serverdomain"));
        ModuleManagement::getModuleByName("Timer")->newTimer(
          $this->self->getConfigFlag("pingtime"), $this, "sendPingRequest",
          $connection);
      }
      else {
        $message = "Ping timeout: ".$this->self->getConfigFlag(
          "pingtime")." seconds";
        if ($connection->getOption("registered") == true) {
          $connection->send(":".$connection->getOption("nick")."!".
            $connection->getOption("ident")."@".$connection->getHost().
            " QUIT :".$message);
        }
        $connection->send("ERROR :Closing Link: ".$connection->getHost().
          " (".$message.")");
        ModuleManagement::getModuleByName("QUIT")->notifyQuit(null, $connection,
          $message);
        $connection->setOption("registered", false);
        $connection->disconnect();
      }
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this,
        "receivePingCommand", "ping");
      EventHandling::registerForEvent("commandEvent", $this,
        "receivePongCommand", "pong");
      EventHandling::registerForEvent("userRegistrationEvent", $this,
        "receiveUserRegistration");
      return true;
    }
  }
?>
