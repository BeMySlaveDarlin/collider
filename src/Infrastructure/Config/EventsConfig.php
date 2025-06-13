<?php

declare(strict_types=1);

namespace App\Infrastructure\Config;

class EventsConfig
{
    public const int USERS_MAX_COUNT = 1_000;
    public const int EVENTS_COUNT = 10_000_000;
    public const int BATCH_SIZE = 25_000;
    private const array EVENT_TYPES = [
        'usr_reg' => ['id' => 1, 'page' => '/reg'],
        'usr_login' => ['id' => 2, 'page' => '/login'],
        'usr_logout' => ['id' => 3, 'page' => '/logout'],
        'usr_upd' => ['id' => 4, 'page' => '/profile'],

        'ord_new' => ['id' => 5, 'page' => '/order'],
        'ord_paid' => ['id' => 6, 'page' => '/pay'],
        'ord_ship' => ['id' => 7, 'page' => '/ship'],
        'ord_dlv' => ['id' => 8, 'page' => '/track'],

        'pay_ok' => ['id' => 9, 'page' => '/pay/ok'],
        'pay_fail' => ['id' => 10, 'page' => '/pay/fail'],
        'pay_refund' => ['id' => 11, 'page' => '/refund'],

        'prod_view' => ['id' => 12, 'page' => '/prod'],
        'cart_add' => ['id' => 13, 'page' => '/cart/add'],
        'cart_rm' => ['id' => 14, 'page' => '/cart/rm'],

        'mail_sent' => ['id' => 15, 'page' => '/mail/sent'],
        'mail_open' => ['id' => 16, 'page' => '/mail/open'],
        'mail_click' => ['id' => 17, 'page' => '/mail/click'],

        'ntf_sent' => ['id' => 18, 'page' => '/ntf/sent'],
        'ntf_read' => ['id' => 19, 'page' => '/ntf/read'],

        'api_req' => ['id' => 20, 'page' => '/api/req'],
        'api_resp' => ['id' => 21, 'page' => '/api/resp'],
        'api_err' => ['id' => 22, 'page' => '/api/err'],

        'pwd_reset' => ['id' => 23, 'page' => '/pwd/reset'],
        'pwd_chg' => ['id' => 24, 'page' => '/pwd/chg'],
        '2fa_on' => ['id' => 25, 'page' => '/2fa/on'],
        '2fa_off' => ['id' => 26, 'page' => '/2fa/off'],
        'usr_del' => ['id' => 27, 'page' => '/usr/del'],
        'usr_ban' => ['id' => 28, 'page' => '/usr/ban'],
        'usr_act' => ['id' => 29, 'page' => '/usr/act'],
        'sub_start' => ['id' => 30, 'page' => '/sub/start'],
        'sub_end' => ['id' => 31, 'page' => '/sub/end'],
        'sub_renew' => ['id' => 32, 'page' => '/sub/renew'],
        'inv_send' => ['id' => 33, 'page' => '/inv/send'],
        'inv_acc' => ['id' => 34, 'page' => '/inv/acc'],
        'feedback' => ['id' => 35, 'page' => '/feedback'],
        'avatar' => ['id' => 36, 'page' => '/avatar'],
        'prefs' => ['id' => 37, 'page' => '/prefs'],
        'mail_ver' => ['id' => 38, 'page' => '/verify'],
        'login_fail' => ['id' => 39, 'page' => '/login/fail'],
        'prof_view' => ['id' => 40, 'page' => '/prof/view'],
        'ntf_prefs' => ['id' => 41, 'page' => '/ntf/prefs'],
        'news_sub' => ['id' => 42, 'page' => '/news/sub'],

        'ord_cancel' => ['id' => 43, 'page' => '/ord/cancel'],
        'ret_req' => ['id' => 44, 'page' => '/ret/req'],
        'ret_ok' => ['id' => 45, 'page' => '/ret/ok'],
        'ret_no' => ['id' => 46, 'page' => '/ret/no'],
        'review' => ['id' => 47, 'page' => '/review'],
        'invoice' => ['id' => 48, 'page' => '/invoice'],

        'pay_pend' => ['id' => 49, 'page' => '/pay/pend'],
        'pay_disp' => ['id' => 50, 'page' => '/pay/disp'],
        'pay_settle' => ['id' => 51, 'page' => '/pay/settle'],

        'cart_view' => ['id' => 52, 'page' => '/cart'],
        'cart_upd' => ['id' => 53, 'page' => '/cart/upd'],
        'cart_clear' => ['id' => 54, 'page' => '/cart/clear'],
        'chk_start' => ['id' => 55, 'page' => '/chk/start'],
        'chk_done' => ['id' => 56, 'page' => '/chk/done'],

        'prod_rev' => ['id' => 57, 'page' => '/prod/rev'],
        'wish_add' => ['id' => 58, 'page' => '/wish/add'],
        'wish_rm' => ['id' => 59, 'page' => '/wish/rm'],
        'compare' => ['id' => 60, 'page' => '/compare'],
        'share' => ['id' => 61, 'page' => '/share'],
        'restock' => ['id' => 62, 'page' => '/restock'],
        'stock_low' => ['id' => 63, 'page' => '/stock/low'],

        'mail_bounce' => ['id' => 64, 'page' => '/mail/bounce'],
        'unsub' => ['id' => 65, 'page' => '/unsub'],

        'ntf_dismiss' => ['id' => 66, 'page' => '/ntf/dismiss'],
        'ntf_fail' => ['id' => 67, 'page' => '/ntf/fail'],

        'sess_start' => ['id' => 68, 'page' => '/sess/start'],
        'sess_exp' => ['id' => 69, 'page' => '/sess/exp'],
        'sess_end' => ['id' => 70, 'page' => '/sess/end'],

        'adm_login' => ['id' => 71, 'page' => '/adm/login'],
        'adm_logout' => ['id' => 72, 'page' => '/adm/logout'],
        'adm_usr_upd' => ['id' => 73, 'page' => '/adm/usr/upd'],
        'adm_usr_del' => ['id' => 74, 'page' => '/adm/usr/del'],
        'adm_report' => ['id' => 75, 'page' => '/adm/report'],
        'adm_cfg' => ['id' => 76, 'page' => '/adm/cfg'],

        'file_up' => ['id' => 77, 'page' => '/file/up'],
        'file_del' => ['id' => 78, 'page' => '/file/del'],
        'file_dl' => ['id' => 79, 'page' => '/file/dl'],
        'file_prev' => ['id' => 80, 'page' => '/file/prev'],

        'sup_new' => ['id' => 81, 'page' => '/sup/new'],
        'sup_close' => ['id' => 82, 'page' => '/sup/close'],
        'sup_reopen' => ['id' => 83, 'page' => '/sup/reopen'],
        'sup_msg' => ['id' => 84, 'page' => '/sup/msg'],
        'sup_rate' => ['id' => 85, 'page' => '/sup/rate'],

        'search' => ['id' => 86, 'page' => '/search'],
        'filter' => ['id' => 87, 'page' => '/filter'],
        'sort' => ['id' => 88, 'page' => '/sort'],

        'cfg_upd' => ['id' => 89, 'page' => '/cfg'],
        'lang' => ['id' => 90, 'page' => '/lang'],
        'tz' => ['id' => 91, 'page' => '/tz'],

        'token_gen' => ['id' => 92, 'page' => '/token/gen'],
        'token_rev' => ['id' => 93, 'page' => '/token/rev'],
        'rate_limit' => ['id' => 94, 'page' => '/rate'],

        'cron_start' => ['id' => 95, 'page' => '/cron/start'],
        'cron_done' => ['id' => 96, 'page' => '/cron/done'],
        'cron_fail' => ['id' => 97, 'page' => '/cron/fail'],

        'hook_in' => ['id' => 98, 'page' => '/hook/in'],
        'hook_ok' => ['id' => 99, 'page' => '/hook/ok'],
        'hook_fail' => ['id' => 100, 'page' => '/hook/fail'],
    ];

    public function getUsersMaxCount(): int
    {
        return self::USERS_MAX_COUNT;
    }

    public function getEventsCount(): int
    {
        return self::EVENTS_COUNT;
    }

    public function getBatchSize(): int
    {
        return self::BATCH_SIZE;
    }

    public function getEventTypes(): array
    {
        return self::EVENT_TYPES;
    }

    public function getEventTypeId(string $type): ?int
    {
        return isset(self::EVENT_TYPES[$type]) ? self::EVENT_TYPES[$type]['id'] : null;
    }
}
