<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Self");
    public $name = "LUSERS";
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
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 251 ".$connection->getOption("nick")." :There are ".
          count(ConnectionManagement::getConnections())." users and 0 ".
          "invisible on 1 servers");
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 252 ".$connection->getOption("nick")." 0 :IRC ".
          "Operators online");
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 254 ".$connection->getOption("nick")." 0 ".
          ":channels formed");
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 255 ".$connection->getOption("nick")." :I have ".
          count(ConnectionManagement::getConnections())." clients and 0 ".
          "servers");
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 265 ".$connection->getOption("nick")." ".
          count(ConnectionManagement::getConnections())." ".count(
          ConnectionManagement::getConnections())." :Current local users ".
          count(ConnectionManagement::getConnections()).", max ".count(
          ConnectionManagement::getConnections()));
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 266 ".$connection->getOption("nick")." ".count(
          ConnectionManagement::getConnections())." ".count(
          ConnectionManagement::getConnections())." :Current global users ".
          count(ConnectionManagement::getConnections()).", max ".count(
          ConnectionManagement::getConnections()));
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 250 ".$connection->getOption("nick")." :Highest ".
          "connection count: ".
          count(ConnectionManagement::getConnections())." (".count(
          ConnectionManagement::getConnections())." clients) (".count(
          ConnectionManagement::getConnections())." connections received)");
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
        "lusers");
      return true;
    }
  }
?>
