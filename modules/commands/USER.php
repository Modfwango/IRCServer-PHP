<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent", "UserRegistrationEvent");
    public $name = "USER";

    public function preprocessUserRegistration($name, $id, $connection) {
      if (!preg_match("/^[a-zA-Z][a-zA-Z0-9]*$/", $connection->getOption(
          "ident"))) {
        $connection->send(":".__SERVERDOMAIN__." NOTICE ".
          $connection->getOption("nick")." :*** Your username is invalid. ".
          "Please make sure that your username contains only alphanumeric ".
          "characters.");
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

      if (strtolower($command[0]) == "user") {
        if (count($command) >= 5) {
          if (count($command) > 5) {
            for ($i = (count($command) - 1); $i > 5; $i--) {
              $command[$i - 1] = $command[$i - 1]." ".$command[$i];
            }
          }
          if ($connection->getOption("ident") == false) {
            $connection->setOption("ident", $command[1]);
            $connection->setOption("realname", $command[4]);
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
            $connection->send(":".__SERVERDOMAIN__." 462 ".
              $connection->getOption("nick")." :You may not reregister");
          }
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 461 ".(
            $connection->getOption("nick") ? $connection->getOption("nick") :
            "*")." USER :Not enough parameters");
        }
        return true;
      }
      return false;
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      EventHandling::registerAsEventPreprocessor("userRegistrationEvent", $this,
        "preprocessUserRegistration");
      return true;
    }
  }
?>
