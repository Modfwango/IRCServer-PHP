<?php
  class @@CLASSNAME@@ {
    public $depend = array("Client", "CommandEvent", "Self",
      "WHOISResponseEvent");
    public $name = "WHOIS";
    private $client = null;
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
        if (count($command) > 0) {
          // array_reverse(ksort($response, SORT_NUMERIC));
          // :kelabs.arinity.org 401 lol bobdhk :No such nick/channel
          // :kelabs.arinity.org 318 lol bobdhk :End of /WHOIS list.
          $client = $this->client->getClientByNick($command[0]);
          if ($client != false) {
            Logger::info(var_export($client, true));
            $event = EventHandling::getEventByName("WHOISResponseEvent");
            if ($event != false) {
              foreach ($event[2] as $id => $registration) {
                // Trigger the WHOISResponseEvent event for each registered
                // module.
                EventHandling::triggerEvent("WHOISResponseEvent", $id,
                  array($connection, $client, array()));
              }
            }
          }
          else {
            $connection->send(":".$this->self->getConfigFlag(
              "serverdomain")." 401 ".$connection->getOption("nick")." ".
              $command[0]." :No such nick/channel");
          }
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 318 ".$connection->getOption("nick")." ".
            $command[0]." :End of /WHOIS list.");
        }
        else {
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 461 ".$connection->getOption("nick")." WHOIS ".
            ":Not enough parameters");
        }
      }
      else {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 451 ".($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")." :You have not registered");
      }
    }

    public function receiveWHOISResponse($name, $data) {
      $source = $data[0];
      $target = $data[1];
      ksort($data[2], SORT_NATURAL);
      Logger::info(var_export($data[2], true));
      $response = array_reverse($data[2]);

      foreach ($response as $weight => $responses) {
        foreach ($responses as $r) {
          $source->send($r);
        }
      }
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "whois");
      EventHandling::registerForEvent("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
