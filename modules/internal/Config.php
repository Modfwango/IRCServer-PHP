<?php
  class @@CLASSNAME@@ {
    public $name = "Config";
    private $configs = array();

    public function getConfig($name) {
      if (isset($this->configs[$name])) {
        return $this->configs[$name];
      }
      return false;
    }

    public function getConfigFlag($name, $key) {
      if (isset($this->configs[$name][$key])) {
        return $this->configs[$name][$key];
      }
      return false;
    }

    public function loadConfig($name, $config = null) {
      $name .= ".json";
      if (!isset($this->configs[$name])) {
        $contents = StorageHandling::loadFile($this, $name);
        if ($contents == false) {
          if (is_array($config)) {
            $contents = json_encode($config, JSON_PRETTY_PRINT);
            if (StorageHandling::saveFile($this, $name, $contents)) {
              $this->configs[$name] = json_decode($contents, true);
              return true;
            }
          }
        }
        else {
          $this->configs[$name] = json_decode($contents, true);
          return true;
        }
      }
      return false;
    }

    public function reloadConfig($name) {
      if ($this->unloadConfig($name) && $this->loadConfig($name)) {
        return true;
      }
      return false;
    }

    public function reloadConfigs() {
      foreach ($this->configs as $name => $config) {
        $this->unloadConfig($name);
        $this->loadConfig($name);
      }
    }

    public function unloadConfig($name) {
      if (isset($this->configs[$name])) {
        unset($this->configs[$name]);
        return true;
      }
      return false;
    }

    public function isInstantiated() {
      return true;
    }
  }
?>
