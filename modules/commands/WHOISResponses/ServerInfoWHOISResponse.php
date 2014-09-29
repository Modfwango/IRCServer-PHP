<?php
  class __CLASSNAME__ {
    public $depend = array("Numeric", "Self", "WHOISResponseEvent");
    public $name = "ServerInfoWHOISResponse";
    private $numeric = null;
    private $self = null;

    public function receiveWHOISResponse($name, $id, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];

      $weight = "66.666";
      if (!isset($response[$weight])) {
        $response[$weight] = array();
      }
      $response[$weight][] = $this->numeric->get("RPL_WHOISSERVER", array(
        $this->self->getConfigFlag("serverdomain"),
        $source->getOption("nick"),
        $target->getOption("nick"),
        $this->self->getConfigFlag("serverdomain"),
        $this->self->getConfigFlag("serverdescription")
      ));
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
