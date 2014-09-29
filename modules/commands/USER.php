<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Self", "UserRegistrationEvent");
    public $name = "USER";
    private $numeric = null;
    private $self = null;

    public function preprocessUserRegistration($name, $id, $connection) {
      if (!preg_match("/^[a-zA-Z][a-zA-Z0-9]*$/", $connection->getOption(
          "ident"))) {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." NOTICE ".$connection->getOption("nick")." :*** ".
          "Your username is invalid. Please make sure that your username ".
          "contains only alphanumeric characters.");
        $connection->send("ERROR :Closing Link: ".$connection->getIP().
          " (Invalid username [".$connection->getOption("ident")."])");
        $connection->disconnect();
        return array(false);
      }
      return array(true);
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

      if (count($command) >= 4) {
        if (count($command) > 4) {
          for ($i = (count($command) - 1); $i > 4; $i--) {
            $command[$i - 1] = $command[$i - 1]." ".$command[$i];
          }
        }
        if ($connection->getOption("ident") == false) {
          $connection->setOption("ident", substr($command[0], 0, 10));
          $connection->setOption("realname", substr($command[3], 0, 50));
          if ($connection->getOption("nick")) {
            $connection->setOption("registered", true);
            $event = EventHandling::getEventByName("userRegistrationEvent");
            if ($event != false) {
              foreach ($event[2] as $id => $registration) {
                // Trigger the userRegistrationEvent event for each registered
                // module.
                EventHandling::triggerEvent("userRegistrationEvent", $id,
                    $connection);
              }
            }
          }
        }
        elseif ($connection->getOption("nick") != false) {
          $connection->send($this->numeric->get("ERR_ALREADYREGISTERED", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick")
          )));
        }
      }
      else {
        $connection->send($this->numeric->get("ERR_NEEDMOREPARAMS", array(
          $this->self->getConfigFlag("serverdomain"),
          ($connection->getOption("nick") ?
          $connection->getOption("nick") : "*"),
          $this->name
        )));
      }
    }

    public function isInstantiated() {
      $this->Numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "user");
      EventHandling::registerAsEventPreprocessor("userRegistrationEvent", $this,
        "preprocessUserRegistration");
      return true;
    }
  }
?>
