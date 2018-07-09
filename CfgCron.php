<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 09.11.2015
 * Time: 15:19
 */
class CfgCron
{

    public static $CronStepOne = [
        "next_walker_class_name" => 'CronStepTwo',

        "handledata_delete_gap" => 'delete * from `t_ipgap` where `gap` = ?',
        "handledata_select_gap" => 'select `id`, `gap` from `t_ipgap` where `gap` = ?',
        "handledata_update_gap" => 'update `t_ipgap` set `gap` = ?, `search_id` = ? where `id` = ?',
        "handledata_insert_gap" => 'insert into `t_ipgap`(`gap`, `search_id`) values (?, ?)',
        "getdata_select_idall" => 'select b.`id`, b.`gap` from `t_search` as a left join `t_ipgap` as b on a.`id` = b.`search_id` where a.`search` = ?',
        "getdata_select_all" => 'select b.`gap`, a.`search` from `t_search` as a left join `t_ipgap` as b on a.`id` = b.`search_id` where a.`search` like ?',
    ];


}