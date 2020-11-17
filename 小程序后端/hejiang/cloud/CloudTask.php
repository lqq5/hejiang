<?php
/**
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/24 10:22
 */


namespace app\hejiang\cloud;


class CloudTask
{
    /**
     * @param string $url 回调访问的url，注意不要加域名，授权系统自动加上
     * @param int $delay_seconds 任务执行延时，秒
     * @return mixed
     * @throws CloudException
     */
    public static function create($url, $delay_seconds)
    {
        $res = HttpClient::post(CloudApi::TASK_CREATE, [
            'url' => $url,
            'delay_seconds' => $delay_seconds,
        ]);
        return json_decode($res, true);
    }
}