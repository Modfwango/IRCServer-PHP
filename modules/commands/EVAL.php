<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Self");
    public $name = "EVAL";
    private $channel = null;
    private $client = null;
    private $modes = null;
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
        if ($connection->getOption("operator") == true) {
          $output = explode("\n", trim(@eval(implode(" ", $command))));
          $i = 0;
          foreach ($output as $line) {
            $i++;
            $base = ":".$this->self->getConfigFlag("serverdomain")." NOTICE ".
              $connection->getOption("nick")." :*** EVAL (".$i."):  ";
            $length = (510 - strlen($base));
            foreach (str_split($line, $length) as $outline) {
              if (trim($outline) != null) {
                $connection->send($base.$outline);
              }
            }
          }
        }
        else {
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 481 ".($connection->getOption("nick") ?
            $connection->getOption("nick") : "*")." :Permission Denied - ".
            "You're not an IRC operator");
        }
      }
      else {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 451 ".($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "eval");
      return true;
    }
  }
?>
