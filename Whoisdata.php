<?php

/**
 */
class Whoisdata extends BaseAbstractClass
{
    /**
     * @var
     */
    protected $helper;
    protected $jwhois_cmd;

    protected $last_query_res;
    protected $last_domain;

    /**
     * @param null $domain
     */
    public function __construct($domain = null)
    {
        parent::__construct();
        $this->helper = Cfg::${get_called_class()}["default_helper"];

        if (!is_null($domain) && is_string($domain) && !empty($domain{0})) {
            $this->last_domain = $domain;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getWhois();
    }

    /**
     * @param $value
     */
    protected function setHlpr($value)
    {
        if (in_array($value, Cfg::${get_called_class()}["allow_helpers"])) {
            $this->helper = $value;
        }
    }

    /**
     * @param null $domain
     */
    protected function setSearchDomain($domain = null)
    {
        if (!is_null($domain) && is_string($domain) && !empty($domain{0})) {
            $this->last_domain = $domain;
        }
    }

    /**
     * @return bool|mixed|string
     * @throws ProcedureFailedException
     */
    protected function getData()
    {
        if (!is_null($this->last_domain) && is_string($this->last_domain) && !empty($this->last_domain{0})) {

            switch ($this->helper) {

                case Cfg::${get_called_class()}["allow_helpers"][0]:
                    $this->jwhois_cmd = Cfg::${get_called_class()}["jwhois_cmd"];
                    $exec_str = escapeshellcmd($this->jwhois_cmd . ' ' . $this->last_domain);
                    $descriptorspec = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
                    $process = proc_open($exec_str, $descriptorspec, $pipes, null, null);

                    if (!is_resource($process)) {
                        throw new ProcedureFailedException('proc_open() did not return a resource');
                    }

                    $result = stream_get_contents($pipes[1]);

                    fclose($pipes[0]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($process);

                    $this->last_query_res = $result;

                    break;

                case Cfg::${get_called_class()}["allow_helpers"][1]:
                    $server = Cfg::${get_called_class()}["first_server"];
                    $whois_provider = new Net_Whois();
                    $result = '';
                    while (true) {
                        $result = $whois_provider->query($this->last_domain, $server);
                        $matches = [];
                        if (preg_match('/^whois:\s+(.+)$/m', $result, $matches)) {
                            $server = trim($matches[1]);
                        } else {
                            if (preg_match('/^Whois Server:\s+(.+)$/m', $result, $matches)) {
                                $server = trim($matches[1]);
                            } else break;
                        }
                    }
                    break;
            }
            return ($this->last_query_res = $result);
        }

        return false;
    }

}
