<?php
  class __CLASSNAME__ {
    public $depend = array("Numeric", "Self", "WHOISResponseEvent");
    public $name = "IdentityInfoWHOISResponse";
    private $numeric = null;
    private $self = null;

    public function receiveWHOISResponse($name, $id, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];

      if ($target->getOption("loggedin") != false) {
        $weight = "16.5";
        if (!isset($response[$weight])) {
          $response[$weight] = array();
        }
        $response[$weight][] = $this->numeric->get("RPL_WHOISACCOUNT", array(
          $this->self->getConfigFlag("serverdomain"),
          $source->getOption("nick"),
          $target->getOption("nick"),
          $target->getOption("loggedin")
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
