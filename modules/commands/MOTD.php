<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Numeric", "Self");
    public $name = "MOTD";
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
        if (is_string($this->self->getConfigFlag("motd"))) {
          $connection->send($this->numeric->get("RPL_MOTDSTART", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick"),
            $this->self->getConfigFlag("serverdomain")
          )));
          if (stristr($this->self->getConfigFlag("motd"), "\n")) {
            foreach (explode("\n", $this->self->getConfigFlag("motd"))
                as $line) {
              $line = str_split($line, 80);
              foreach ($line as $l) {
                $connection->send($this->numeric->get("RPL_MOTD", array(
                  $this->self->getConfigFlag("serverdomain"),
                  $connection->getOption("nick"),
                  $l
                )));
              }
            }
          }
          else {
            $line = str_split($this->self->getConfigFlag("motd"), 80);
            foreach ($line as $l) {
              $connection->send($this->numeric->get("RPL_MOTD", array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $l
              )));
            }
          }
          $connection->send($this->numeric->get("RPL_ENDOFMOTD", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick")
          )));
        }
        else {
          $connection->send($this->numeric->get("ERR_NOMOTD", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick")
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
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        array("motd", false));
      return true;
    }
  }
?>
