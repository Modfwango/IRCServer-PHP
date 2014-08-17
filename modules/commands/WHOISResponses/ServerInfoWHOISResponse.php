<?php
  class @@CLASSNAME@@ {
    public $depend = array("Self", "WHOISResponseEvent");
    public $name = "ServerInfoWHOISResponse";
    private $self = null;

    public function receiveWHOISResponse($name, $id, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];

      $weight = "66.666";
      if (!isset($response[$weight])) {
        $response[$weight] = array();
      }
      $response[$weight][] = ":".$this->self->getConfigFlag(
        "serverdomain")." 312 ".$source->getOption("nick")." ".
        $target->getOption("nick")." ".$this->self->getConfigFlag(
        "serverdomain")." :".$this->self->getConfigFlag("serverdescription");
      $data[2] = $response;
      return array(null, $data);
    }

    public function isInstantiated() {
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerAsEventPreprocessor("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
