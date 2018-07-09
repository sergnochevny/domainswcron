<?php

/**
 */

class ParserStepThree extends AbstractParser
{
    const WHOISINFO_EOL = "\n";
    const WHOISINFO_FIELDDLM = ": ";

    private $result_array = [];

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->helper = Cfg::${get_called_class()}["default_helper"];
    }

    /**
     * @param $data
     * @return array
     */
    public function getParseResult($data){
        $this->result_array = [];
        if( in_array($this->helper, Cfg::${get_called_class()}["allow_helpers"]) ){
            switch ( $this->helper ){
                case Cfg::${get_called_class()}["allow_helpers"][0]:
                    $this->result_array = str_split($data);
                    break;

                case Cfg::${get_called_class()}["allow_helpers"][1]:
                    $str_array = explode(self::WHOISINFO_EOL, explode(">>>", $data)[0]);
                    foreach ( $str_array as $row ){
                        $row_array = explode(self::WHOISINFO_FIELDDLM, $row);
                        if (!empty($row_array) && (count($row_array) > 1) && (strlen(trim($row_array[0])) > 0) && (strlen(trim($row_array[1])) > 0)){
                            if (array_key_exists( trim($row_array[0]), $this->result_array )){
                                if ( is_array( $this->result_array[trim($row_array[0])] )){
                                    array_push( $this->result_array[trim($row_array[0])],
                                        trim(strip_tags($row_array[1])) );
                                }
                                else {
                                    if ( !empty($this->result_array[trim($row_array[0])]{0})) {
                                        $this->result_array[trim($row_array[0])] =
                                            [$this->result_array[trim($row_array[0])], trim(strip_tags($row_array[1]))];
                                    }
                                    else $this->result_array[trim($row_array[0])] = trim(strip_tags($row_array[1]));
                                }
                            }
                            else  $this->result_array[trim($row_array[0])] = trim(strip_tags($row_array[1]));
                        }
                    }
                    break;
            }
        }

//        $this->result_array["WHOIS DATA"] = trim(explode( '>>>', $data )[0] );
        return $this->result_array;
    }
}
