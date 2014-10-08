<?php
  class __CLASSNAME__ {
    public $depend = array("Numeric", "Self", "WHOISResponseEvent");
    public $name = "UserInfoWHOISResponse";
    private $numeric = null;
    private $self = null;

    public function receiveWHOISResponse($name, $id, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];

      $weight = "100";
      if (!isset($response[$weight])) {
        $response[$weight] = array();
      }
      $response[$weight][] = $this->numeric->get("RPL_WHOISUSER", array(
        $this->self->getConfigFlag("serverdomain"),
        $source->getOption("nick"),
        $target->getOption("nick"),
        $target->getOption("ident"),
        $target->getHost(),
        $target->getOption("realname")
      ));
      $data[2] = $response;
      return array(null, $data);
    }

    public function isInstantiated() {
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerAsEventPreprocessor("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
