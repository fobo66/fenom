<?php
/*
 * This file is part of Cytro.
 *
 * (c) 2013 Ivan Shalganov
 *
 * For the full copyright and license information, please view the license.md
 * file that was distributed with this source code.
 */
namespace Cytro;
use Cytro;

/**
 * Primitive template
 */
class Render extends \ArrayObject {
    private static $_props = array(
        "name" => "runtime",
        "base_name" => "",
        "scm" => false,
        "time" => 0,
        "depends" => array()
    );
    /**
     * @var \Closure
     */
    protected $_code;
    /**
     * Template name
     * @var string
     */
    protected $_name = 'runtime';
    /**
     * Provider's schema
     * @var bool
     */
    protected $_scm = false;
    /**
     * Basic template name
     * @var string
     */
    protected $_base_name = 'runtime';
    /**
     * @var Cytro
     */
    protected $_cytro;
    /**
     * Timestamp of compilation
     * @var float
     */
    protected $_time = 0.0;

    /**
     * @var array depends list
     */
    protected $_depends = array();

    /**
     * @var int tempalte options (see Cytro options)
     */
    protected $_options = 0;

    /**
     * Template provider
     * @var ProviderInterface
     */
    protected $_provider;

    /**
     * @param Cytro $cytro
     * @param callable $code template body
     * @param array $props
     */
    public function __construct(Cytro $cytro, \Closure $code, $props = array()) {
        $this->_cytro = $cytro;
        $props += self::$_props;
        $this->_name = $props["name"];
        $this->_provider = $this->_cytro->getProvider($props["scm"]);
        $this->_scm = $props["scm"];
        $this->_time = $props["time"];
        $this->_depends = $props["depends"];
        $this->_code = $code;
    }

    /**
     * Get template storage
     * @return Cytro
     */
    public function getStorage() {
        return $this->_cytro;
    }

    public function getDepends() {
        return $this->_depends;
    }

    public function getScm() {
        return $this->_scm;
    }

    public function getProvider() {
        return $this->_provider;
    }

    public function getBaseName() {
        return $this->_base_name;
    }

    public function getOptions() {
        return $this->_options;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->_name;
    }

    /**
     * Get template name
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    public function getTime() {
        return $this->_time;
    }


    /**
     * Validate template
     * @return bool
     */
    public function isValid() {
        $provider = $this->_cytro->getProvider(strstr($this->_name, ":"), true);
        if($provider->getLastModified($this->_name) >= $this->_time) {
            return false;
        }
        foreach($this->_depends as $tpl => $time) {
            if($this->_cytro->getTemplate($tpl)->getTime() !== $time) {
                return false;
            }
        }
        return true;
    }

    /**
     * Execute template and write into output
     * @param array $values for template
     * @return Render
     */
    public function display(array $values) {
        $this->exchangeArray($values);
        $this->_code->__invoke($this);
        return $this;
    }

    /**
     * Execute template and return result as string
     * @param array $values for template
     * @return string
     * @throws \Exception
     */
    public function fetch(array $values) {
        ob_start();
        try {
            $this->display($values);
            return ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Stub
     * @param $method
     * @param $args
     * @throws \BadMethodCallException
     */
    public function __call($method, $args) {
        throw new \BadMethodCallException("Unknown method ".$method);
    }
}
