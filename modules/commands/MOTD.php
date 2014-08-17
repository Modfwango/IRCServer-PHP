<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent", "Self");
    public $name = "MOTD";
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
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 375 ".$connection->getOption("nick")." :- ".
            $this->self->getConfigFlag("serverdomain")." Message of the Day -");
          if (stristr($this->self->getConfigFlag("motd"), "\n")) {
            foreach (explode("\n", $this->self->getConfigFlag("motd"))
                as $line) {
              $line = str_split($line, 80);
              foreach ($line as $l) {
                $connection->send(":".$this->self->getConfigFlag(
                  "serverdomain")." 372 ".$connection->getOption("nick")." :- ".
                  $l);
              }
            }
          }
          else {
            $line = str_split($this->self->getConfigFlag("motd"), 80);
            foreach ($line as $l) {
              $connection->send(":".$this->self->getConfigFlag(
                "serverdomain")." 372 ".$connection->getOption("nick")." :- ".
                $l);
            }
          }
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 376 ".$connection->getOption("nick")." :End of ".
            "/MOTD command.");
        }
        else {
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 422 ".$connection->getOption("nick")." :MOTD ".
            "file is missing");
        }
      }
      else {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 451 ".($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "motd");
      return true;
    }
  }
?>
