<?php

/**
 */
class InfoView extends BaseAbstractClass
{

    private $findstr = '';

    /**
     * @return string
     */
    protected function getView()
    {
        $title = '';
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if (strlen($_POST['find']) > 0) {
                    $this->findstr = $_POST['find'];
                    $provider = StepOne::getInstance();
                    $provider->find = $this->findstr;
                    $result_array = $provider->data;
                    $result_rows = '';
                    if (count($result_array)>0) {
                        foreach ($result_array as $row_key => $row_value) {
                            try {
                                $result_rows .= sprintf(join('', Cfg::${get_called_class()}["rows_ipgap_tmpl"]),
                                    'option="ipgap"',
                                    str_replace('/', '-', $row_key),
                                    $row_key,
                                    htmlentities($row_value)
                                );
                            } catch (Exception $e) {
                            }
                        }
                    } else $result_rows = join(' ', Cfg::${get_called_class()}["no_result_warning"]);
                    $title = Cfg::${get_called_class()}["ipgap_title_name"];
                }
                break;

            case 'GET':
                if (array_key_exists('option', $_GET) && strlen($_GET['option']) > 0) {
                    switch ($_GET['option']) {
                        case 'ipgap':
                            if (strlen($_GET['value']) > 0) {
                                $ipgapstr = $_GET['value'];
                                $this->findstr = $ipgapstr;
                                $provider = StepTwo::getInstance();
                                $provider->find = $this->findstr;
                                $result_array = $provider->data;
                                $result_rows = join('', Cfg::${get_called_class()}["header_ip_tmpl"]);
                                if (count($result_array)>0) {
                                    foreach ($result_array as $row_key => $row_value) {
                                        try {
                                            $result_rows .= sprintf(join('', Cfg::${get_called_class()}["rows_ip_tmpl"]),
                                                'option="whois"',
                                                htmlentities($row_key),
                                                $row_key,
                                                htmlentities($row_value)
                                            );
                                        } catch (Exception $e) {
                                        }
                                    }
                                } else $result_rows = join(' ', Cfg::${get_called_class()}["no_result_warning"]);
                                $title = Cfg::${get_called_class()}["ip_title_name"];
                            }
                            break;

                        case 'whois':
                            if (strlen($_GET['value']) > 0) {
                                $domain = $_GET['value'];
                                $this->findstr = $domain;
                                $provider = StepThree::getInstance();
                                $provider->find = $this->findstr;
                                $result_array = $provider->data;
                                $result_rows = '';
                                if (count($result_array)>0) {
                                    foreach ($result_array as $row_key => $row_value) {
                                        try {
                                            if (is_array($row_value)) {
                                                $row_val = htmlentities(implode(', ', $row_value));
                                            } else $row_val = htmlentities($row_value);
                                            $result_rows .= sprintf(join('', Cfg::${get_called_class()}["datail_tmpl"]),
                                                htmlentities($row_key),
                                                $row_val
                                            );
                                        } catch (Exception $e) {
                                        }
                                    }
                                } else $result_rows = join(' ', Cfg::${get_called_class()}["no_result_warning"]);
                                $title = Cfg::${get_called_class()}["dialog_title"];
                            }
                            break;
                    }
                }
                break;
        }
        $result_json = [
            "htmldata" => $result_rows,
            "title" => $title
        ];
        return (json_encode($result_json));
    }
}