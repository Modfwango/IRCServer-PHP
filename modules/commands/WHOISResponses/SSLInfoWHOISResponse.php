<?php
  class __CLASSNAME__ {
    public $depend = array("Numeric", "Self", "WHOISResponseEvent");
    public $name = "SSLInfoWHOISResponse";
    private $numeric = null;
    private $self = null;

    public function receiveWHOISResponse($name, $id, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];

      if ($target->getSSL() == true) {
        $weight = "41.5";
        if (!isset($response[$weight])) {
          $response[$weight] = array();
        }
        $response[$weight][] = $this->numeric->get("RPL_WHOISSECURE", array(
          $this->self->getConfigFlag("serverdomain"),
          $source->getOption("nick"),
          $target->getOption("nick")
        ));
        $data[2] = $response;
        return array(null, $data);
      }
      return array(true);
    }

    public function isInstantiated() {
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerAsEventPreprocessor("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
