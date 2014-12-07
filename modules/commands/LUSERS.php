<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Numeric", "Self");
    public $name = "LUSERS";
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
        $connection->send($this->numeric->get("RPL_LUSERCLIENT", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick"),
          count(ConnectionManagement::getConnections()),
          count(ConnectionManagement::getConnections()),
          "1"
        )));

        $connection->send($this->numeric->get("RPL_LUSEROP", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick"),
          "0"
        )));

        $connection->send($this->numeric->get("RPL_LUSERCHANNELS", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick"),
          "0"
        )));

        $connection->send($this->numeric->get("RPL_LUSERME", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick"),
          count(ConnectionManagement::getConnections()),
          "1"
        )));

        $connection->send($this->numeric->get("RPL_LOCALUSERS", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick"),
          count(ConnectionManagement::getConnections()),
          count(ConnectionManagement::getConnections()),
          count(ConnectionManagement::getConnections()),
          count(ConnectionManagement::getConnections())
        )));

        $connection->send($this->numeric->get("RPL_GLOBALUSERS", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick"),
          count(ConnectionManagement::getConnections()),
          count(ConnectionManagement::getConnections()),
          count(ConnectionManagement::getConnections()),
          count(ConnectionManagement::getConnections())
        )));

        $connection->send($this->numeric->get("RPL_STATSCONN", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick"),
          count(ConnectionManagement::getConnections()),
          count(ConnectionManagement::getConnections()),
          count(ConnectionManagement::getConnections())
        )));
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
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("lusers", false));
      return true;
    }
  }
?>
