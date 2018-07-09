<?php

/**
 */
final class Cfg
{
    public static $Crawler = [
        "allow_helpers" => ['phantom', 'http_r', 'curl'],

        "default_helper" => 'phantom',

        "phantom" => [
//            "file_js" => 'getproc/get_bgp.js',
            "file_js" => 'getproc/get_bgp',
            "options_array" => [
                "--load-images" => 'false',
                "--ignore-ssl-errors" => 'true',
                "--web-security" => 'no',
                "--cookies-file" => 'tmp/cookies_'
            ],

            "js_options_array" => [
                'TimeOut' => 40,
                'Delay' => 30
            ],

            "url_for_find" => [
                "request" => "http://bgp.he.net/search?",
                "params" => [
                    "search[search]" => "find str",
                    "commit" => "Search"
                ]
            ],

            "get_allowstatus" => [200, 300, 301, 302, 303]
        ],

        "http_r" => [
            "config" => [
                'follow_redirects' => true,
                'max_redirects' => 2,
                'ssl_verify_peer' => false,
                'ssl_verify_host' => false
            ]
        ]

    ];

    public static $Whoisdata = [
        "allow_helpers" => ['jwhois', 'net_whois'],
        "first_server" => 'whois.iana.org',
//        "default_helper" => 'jwhois',
        "default_helper" => 'net_whois',
        "jwhois_cmd" => 'jwhois'
    ];

    public static $ParserStepOne = [
        "selectors" => [
            "dom" => [
                "1" => "search",
                "2" => "table",
                "3" => "tr",
                "4" => "td"
            ],
            "simple" => [
                "1" => "div#search",
                "2" => "table",
                "3" => "tr",
                "4" => "td"
            ]
        ],
        "allow_helpers" => ['dom', 'simple'],
        "default_helper" => 'dom',
//        "default_helper"    => 'simple',
        "preg_match" => '/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}(0?|[1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\/([1-9]?[0-9])$/'
    ];
    public static $ParserStepTwo = [
        "selectors" => [
            "dom" => [
                "1" => "table",
                "2" => "data-onview",
                "3" => "tbody",
                "4" => "tr",
                "5" => "td"
            ],
            "simple" => [
                "0" => "body div table[data-onview]",
                "1" => "body div table[data-onview] tbody tr",
                "2" => "td"
            ]
        ],
        "allow_helpers" => ['dom', 'simple'],
        "default_helper" => 'dom',
//        "default_helper" => 'simple',
        "preg_matches" => [
            "1" => '/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}(0?|[1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/',
            "2" => '/^[\w_-]+\.[a-z]{2,3}$/i'
//            "2"      => '/^((([a-z\d_-]+)\.){1}([a-z]){2,3})$/i'
        ]
    ];

    public static $ParserStepThree = [
        "allow_helpers" => ['phpwhois', 'simple'],
        "default_helper" => 'simple'
    ];

    public static $DBProvider = [
        "provider" => "mysql",
        "mysql_params" => [
            "host" => "localhost",
            "port" => "",
            "dbname" => "domains",
            "domains_params" => [
                "user" => "root",
//                "passwd" => "JzpEk0659o",
                "passwd" => "",
                "connection_attr" => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ERRMODE_WARNING => true,
                    PDO::ATTR_ERRMODE => true
                ],
                "set_attr" => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ],
                "attributes" => [
//                    "global connect_timeout" => 5,
                    "interactive_timeout" => 60,
                    "wait_timeout" => 90
                ]
            ]
        ]
    ];

    public static $StepOne = [
        "getdata_select_all" => 'select b.`gap`, a.`search` from `t_search` as a left join `t_ipgap` as b on a.`id` = b.`search_id` where a.`search` like ?',
        "savedata_select_search" => 'select `id`, `search` from `t_search` where `search` = ?',
        "savedata_insert_search" => 'insert into `t_search`(`search`) values(?)',
        "savedata_select_gap" => 'select `id`, `gap` from `t_ipgap` where `gap` = ?',
        "savedata_update_gap" => 'update `t_ipgap` set `gap` = ?, `search_id` = ? where `id` = ?',
        "savedata_insert_gap" => 'insert into `t_ipgap`(`gap`, `search_id`) values (?, ?)'

    ];

    public static $StepTwo = [
        "getdata_select_domain" => 'select b.`domain`, b.`ip` from `t_ipgap` as a left join `t_ip` as b on a.`id` = b.`ipgap_id` where a.`gap` = ?',
        "savedata_select_domain" => 'select `id`, `domain` from `t_ip` where `domain` = ?',
        "savedata_select_gap" => 'select `id`, `gap` from `t_ipgap` where `gap` = ?',
        "savedata_update_ip" => 'update `t_ip` set `ip` = ?, `ipgap_id` = ? where `id` = ?',
        "savedata_insert_ip" => 'insert into `t_ip`(`domain`, `ip`, `ipgap_id`) values (?, ?, ?)',

        "net_iteration" => 0
    ];

    public static $StepThree = [
        "getdata_select_domain" => 'select `id`, `domain` from `t_ip` where `domain` = ?',
        "getdata_select_fields" => 'select id, name from `t_fields`',
        "get_data_select_whois" => 'select * from `t_whois` where `ip_id` = ?',
        "savedata_select_domain" => 'select `id`, `domain` from `t_ip` where `domain` = ?',
        "savedata_select_whois" => 'select `id` from `t_whois` where `ip_id` = ?',
        "savedata_select_fields" => 'select id, name from `t_fields`',
        "savedata_update_whois" => 'update `t_whois` set %s=? where `id` = ?',
        "savedata_insert_whois" => 'insert into `t_whois`( %s, `ip_id` ) values ( %s )',

        "whois_ident_fields" => [
            'administrative' => 'admin',
            'technical' => 'tech'
        ]
    ];

    public static $AutocompleteAjax = [
        "autocomplete_limit" => 15,
        "sql_select" => 'select a.`id`, a.`search` from `t_search` as a where a.`search` like ? limit 0,'
    ];

    public static $InfoView = [
        "rows_ipgap_tmpl" => [
            '                  <div id="row" class="nopadding nomargin col-md-12">',
            '	                    <div  class="col-md-4 fa-border d_table_row" >',
            '                           <button id="href_ajax" title="view subnet detail" %s ref="%s" class="d_xx_small ipgap_dtl"></button>',
            '                           <i class="underline view-subnet">%s</i>',
            '                       </div>',
            '	                    <div class="col-md-8 fa-border d_table_row">',
            '	                        <div class="col-md-12">',
            '                                %s',
            '                           </div>',
            '                        </div>',
            '                  </div>'
        ],

        "header_ip_tmpl" => [
            '                  <div id="row" class="nopadding nomargin  col-md-12">',
            '	                    <div class="col-md-4 text-center fa-border">',
            '	                        <b class="small">Domains</b>',
            '                       </div>',
            '	                    <div class="col-md-8 text-center fa-border">',
            '	                        <b class="small">IP</b>',
            '                        </div>',
            '                  </div>'
        ],

        "rows_ip_tmpl" => [
            '                  <div id="row" class="nopadding nomargin  col-md-12 ip-table">',
            '	                    <div class="small col-md-4 fa-border  ip-table-cell">',
            '                           <a id="whois_ajax" title="Whois detail" class="underline view-subnet" %s href="%s">%s</a>',
            '                       </div>',
            '	                    <div class="small col-md-8 fa-border ip-table-cell">',
            '	                        <div class="col-md-12 text-center">',
            '                                %s',
            '                           </div>',
            '                        </div>',
            '                  </div>'
        ],

        "datail_tmpl" => [
            '                  <div id="row" class="nopadding nomargin  col-md-12">',
            '	                    <div class="col-md-4 small fa-border">',
            '                                %s',
            '                       </div>',
            '	                    <div class="col-md-8 small fa-border">',
            '                                %s',
            '                        </div>',
            '                  </div>'
        ],

        "dialog_title" => 'Domain details',

        "ipgap_title_name" => [
            "fields" => ['Subnet', 'Registrar Name'],
            "table_title" => "Registrars Information"
        ],

        "ip_title_name" => [
            "fields" => ['IP', 'Domain'],
            "table_title" => "Domains Information"
        ],

        "no_result_warning" => [
            '                    <div class="ui-state-highlight nomargin row col-md-12 text-center">',
            '                      <h4>No results!!!</h4>',
            '                   </div>'
        ]
    ];

    public static $CronWalker = [
        "next_step_by_sockets_processing" => true,
        "next_walker_class_name" => 'CronStepOne',
        "select_limit" => 5,
        "token_dt_diff" => true,
        "dt_diff" => 0, //(diff one day and lather)
        "handledata_select_by_data" => 'select `id`, `search` from `t_search` where datediff(curdate(), `dt`) > %d limit %d,%d',
        "handledata_select_all" => 'select `id`, `search` from `t_search` limit %d,%d',
        "savedata_update_search" => 'update `t_search` set `dt` = now() where `id` = ?'
    ];

    public static $CronStepOne = [
        "next_step_by_sockets_processing" => true,
        "next_walker_class_name" => 'CronStepTwo',

        "fields" => ["id", "gap"],

        "handledata_delete" => 'delete from `t_ipgap` where `id` = %d',
        "handledata_select" => 'select `id`, `gap` from `t_ipgap` where `gap` = ?',
        "handledata_update" => 'update `t_ipgap` set `gap` = ?, `search_id` = ? where `id` = ?',
        "handledata_insert" => 'insert into `t_ipgap`(`gap`, `search_id`) values (?, ?)',
//        "getdata_select_all" => 'select b.`id`, b.`gap` from `t_ipgap` as b where b.`search_id` = ?'
        "getdata_select_all" => 'select b.`id`, b.`gap` from `t_ipgap` as b where b.`search_id` = ? order by b.`id` desc'
    ];

    public static $CronStepTwo = [
        "next_step_by_sockets_processing" => false,
        "next_walker_class_name" => 'CronStepThree',

        "fields" => ["id", "domain", "ip"],

        "net_iteration" => 3,

        "handledata_delete" => 'delete from `t_ip` where `id` = %d',
        "handledata_select" => 'select `id`, `domain`, `ip` from `t_ip` where `` = ?',
        "handledata_update" => 'update `t_ip` set `domain` = ?, `ip` = ?, `ipgap_id` where `id` = ?',
        "handledata_insert" => 'insert into `t_ip`(`domain`, `ip`, `ipgap_id`) values (?, ?, ?)',
        "getdata_select_all" => 'select a.`id`, a.`domain`, a.`ip` from `t_ip` as a where a.`ipgap_id` = ?'
    ];

    public static $CronStepThree = [
        "getdata_select_domain" => 'select `id`, `domain` from `t_ip` where `domain` = ?',
        "getdata_select_fields" => 'select id, name from `t_fields`',
        "get_data_select_whois" => 'select * from `t_whois` where `ip_id` = ?',
        "savedata_select_domain" => 'select `id`, `domain` from `t_ip` where `domain` = ?',
        "savedata_select_whois" => 'select `id` from `t_whois` where `ip_id` = ?',
        "savedata_select_fields" => 'select id, name from `t_fields`',
        "savedata_update_whois" => 'update `t_whois` set %s=? where `id` = ?',
        "savedata_insert_whois" => 'insert into `t_whois`( %s, `ip_id` ) values ( %s )',

        "whois_ident_fields" => [
            'administrative' => 'admin',
            'technical' => 'tech'
        ]

    ];

    public static $SocketCronRequest = [
        "allowed_addr" => '127.0.0.1',
        "url_addr" => '127.0.0.1',
        "url_port" => 80,
        "headers" => [
            "GET /domains/cron.php?params=%s HTTP/1.0\r\n",
//            "Host: 127.0.0.1\r\n",
            "Host: tickets\r\n",
//            "Cookie: PHPSESSID=8kac7gmuup7sa51qj3n4eq5mq2; XDEBUG_TRACE=1; XDEBUG_SESSION=PHPSTORM;\r\n",
            "User-Agent: Mozilla/5.0 (Windows NT 6.1) Chrome/45.0.2454.101\r\n",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n",
            "Accept-Language: ru,en-us;q=0.7,en;q=0.3\r\n",
            "Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7\r\n",
            "\r\n"
        ]
    ];
}