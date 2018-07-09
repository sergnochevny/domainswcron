<?php
/**
 */

/**
 * Class Crawler
 */
class Crawler extends BaseAbstractClass
{

    /**
     * @var
     */

    protected $wi;
    protected $htr;
    protected $curl;
    protected $crawl_url = '';
    protected $find_str = '';
    protected $helper;

    /**
     * @param array|null $httpRequest_array
     */
    public function __construct(array $httpRequest_array = null)
    {
        parent::__construct();

        $this->helper = Cfg::$Crawler["default_helper"];
        $this->setUrl($httpRequest_array);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getData();
    }

    /**
     * @param $value
     * @throws HTTP_Request2_LogicException
     */
    protected function setUrl($value)
    {
        if (!is_null($value) && is_array($value)) {
            $httpRequestStr = $value["request"];
            $httpRequestStrParam = $value["params"];
            if (is_string($httpRequestStr) && !empty($httpRequestStr{0})) {
//                $httpRequestStr = urlencode($httpRequestStr);
                if (is_array($httpRequestStrParam) && !empty($httpRequestStrParam)) {
                    $httpRequestStr = $httpRequestStr . http_build_query($httpRequestStrParam);
                }
                $this->crawl_url = $httpRequestStr;

                if ($this->helper === Cfg::${get_called_class()}["allow_helpers"][1]) {
                    $this->htr = new HTTP_Request2($this->crawl_url);
                    $this->htr->setCookieJar();
                    $this->htr->setConfig(Cfg::${get_called_class()}[$this->helper]["config"]);
                }
            }
        }

    }

    /**
     * @param $value
     */
    protected function setHlpr($value)
    {
        if (in_array($value, Cfg::${get_called_class()}["allow_helpers"])) $this->helper = $value;
    }

    /**
     * @param $value
     */
    protected function setFind($value)
    {
        if (is_string($value) && !empty($value{0})) {
            $this->find_str = $value;
            $url_for_find = unserialize(serialize(Cfg::${get_called_class()}["phantom"]["url_for_find"])); //clone array
            $url_for_find["params"]["search[search]"] = '"' . $this->find_str . '"';
            $this->setUrl($url_for_find);
        }
    }

    /**
     * @return bool|mixed|string
     * @throws ProcedureFailedException
     */
    public function getData()
    {
        $page = '';
        if (is_string($this->crawl_url) && !empty($this->crawl_url{0})) {
            switch ($this->helper) {
                case Cfg::${get_called_class()}["allow_helpers"][0]:
                    if (is_string($this->find_str) && !empty($this->find_str{0})) {
                        $file_js = Cfg::${get_called_class()}[$this->helper]["file_js"];
                        $phantom_options_array = unserialize(serialize(Cfg::${get_called_class()}[$this->helper]["options_array"]));
                        $phantom_options_array['--cookies-file'] .= uniqid();
                        $js_options_array = unserialize(serialize(Cfg::${get_called_class()}[$this->helper]["js_options_array"]));

                        $phantom_options = '';
                        if (count($phantom_options_array)>0) {
                            foreach ($phantom_options_array as $key => $value) {
                                $phantom_options .= ' ' . $key . '=' . $value;
                            }
                        }
                        $phantom_options = trim($phantom_options);

                        $js_options = urlencode($this->find_str);
                        if (count($js_options_array)>0) {
                            foreach ($js_options_array as $key => $value) {
                                $js_options .= ' ' . $value;
                            }
                        }
                        $js_options = trim($js_options);

                        $exec_str = escapeshellcmd('phantomjs'
                            . ' ' . $phantom_options
                            . ' ' . $file_js
                            . ' ' . $js_options);

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
                        $response_array = json_decode($result, true);
                        if (in_array($response_array['status'], Cfg::${get_called_class()}[$this->helper]["get_allowstatus"])) {
                            $page = $response_array['content'];
                        }
                    }
                    break;

                case Cfg::${get_called_class()}["allow_helpers"][1]:
                    $page = $this->htr->send()->getBody();
                    break;

                case Cfg::${get_called_class()}["allow_helpers"][2]:
                    $this->curl = curl_init($this->crawl_url);
                    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($this->curl, CURLOPT_HTTPGET, true);
                    curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($this->curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) Chrome/45.0.2454.101");
                    curl_setopt($this->curl, CURLOPT_COOKIESESSION, true);
                    $page = curl_exec($this->curl);
                    curl_close($this->curl);
                    break;

            }
            if (function_exists('tidy_repair_string')) {
                $page = tidy_repair_string($page, ['indent' => true, 'output-xhtml' => true]);
            }

            return $page;
        }
        return false;
    }
}
