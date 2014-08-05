<?php
  class @@CLASSNAME@@ {
    public $depend = array("Client", "PseudoConnection", "Quit",
      "UserRegistrationEvent");
    public $name = "NSClient";
    private $pclient = null;

    public function isInstantiated() {
      // Kill any pre-existing connection with "NickServ" as its nickname.
      $client = ModuleManagement::getModuleByName("Client");
      $cc = $client->getClientByNick("NickServ");
      if ($cc != false) {
        $quit = ModuleManagement::getModuleByName("Quit");
        $quit->notifyQuit(null, $cc,
          "Killed:  Ownership of nickname taken by services.");
        $cc->disconnect();
      }

      // Create a pseudo-connection to serve as the NickServ client.
      $this->pclient = new PseudoConnection();
      ConnectionManagement::newConnection($this->pclient);
      $this->pclient->setOption("nick", "NickServ");
      $this->pclient->setOption("ident", "services");
      $this->pclient->setOption("realname", "Nickname Services Client");

      // Mark the pseudo-connection as registered and trigger event.
      $this->pclient->setOption("registered", true);
      $event = EventHandling::getEventByName("userRegistrationEvent");
      if ($event != false) {
        foreach ($event[2] as $id => $registration) {
          // Trigger the userRegistrationEvent event for each registered
          // module.
          EventHandling::triggerEvent("userRegistrationEvent", $id,
              $this->pclient);
        }
      }
      return true;
    }
  }
?>
