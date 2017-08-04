namespace SierraHydrog;

class Aquarius {
    use ContainerTrait;

    AquariusSoap soap;
    AquariusRest rest;
 
    function __construct() {
        $this->soap = AquariusSoap::getInstance();
        $this->rest = AquariusRest::getInstance();
    }

    function __call($name, $args) {

        $res = null;
        if (mehod_exists($this->soap, $name)) {
            $res = $soap->$name($args);
        } else if (method_exists($this->rest, $name)) {
            $res = $soap->$name($args);
        }
        print_r($res);
        return $res;
    }
}