<?php
  class __CLASSNAME__ {
    public $depend = array("Outgoing~juno", "Self"/*, "Server"*/);
    public $name = "SERVER~juno";
    private $juno = null;
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

      $c = $this->juno->getConnection($command[1]);
      if ($this->juno->getVersion() == $command[2] && $c != false) {
        if (/*$this->server->getServerBySID($command[0]) == false &&
            $this->server->getServerByHost($command[1]) == false &&*/
            $this->juno->getSID() != $command[0] &&
            strtolower($this->self->getConfigFlag("serverdomain")) !=
            strtolower($command[1])) {
          $connection->setOption("server", true);
          $connection->setOption("protocol", "juno");
          $connection->setOption("sid", $command[0]);
          $connection->setOption("servhost", $command[1]);
          $connection->setOption("sendpass", $c["sendpass"]);
          $connection->setOption("recvpass", $c["recvpass"]);

          if ($connection->getOption("acquainted") == true) {
            $this->juno->authenticate($connection);
          }
          else {
            $this->juno->acquaint($connection);
          }
        }
        else {
          $connection->disconnect();
        }
      }
    }

    public function isInstantiated() {
      $this->juno = ModuleManagement::getModuleByName("Outgoing~juno");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("server"));
      return true;
    }
  }
?>
