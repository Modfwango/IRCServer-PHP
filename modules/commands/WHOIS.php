<?php
  class __CLASSNAME__ {
    public $depend = array("Client", "CommandEvent", "Numeric", "Self",
      "WHOISResponseEvent");
    public $name = "WHOIS";
    private $client = null;
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
        if (count($command) > 0) {
          $client = $this->client->getClientByNick($command[0]);
          if ($client != false) {
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
            $connection->send($this->numeric->get("ERR_NOSUCHNICK", array(
              $this->self->getConfigFlag("serverdomain"),
              $connection->getOption("nick"),
              $command[0]
            )));
          }
          $connection->send($this->numeric->get("RPL_ENDOFWHOIS", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick"),
            $command[0]
          )));
        }
        else {
          $connection->send(":".$this->numeric->get("ERR_NEEDMOREPARAMS", array(
            $this->self->getConfigFlag("serverdomain"),
            $connection->getOption("nick"),
            $this->name
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

    public function receiveWHOISResponse($name, $data) {
      $source = $data[0];
      $target = $data[1];
      ksort($data[2], SORT_NATURAL);
      $response = array_reverse($data[2]);

      foreach ($response as $weight => $responses) {
        foreach ($responses as $r) {
          $source->send($r);
        }
      }
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "whois");
      EventHandling::registerForEvent("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
