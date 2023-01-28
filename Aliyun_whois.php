<?php

namespace Aliyun_whois;

/**
 * 阿里云 whois 查询
 * @author 欧阳鹏
 */
class Whois
{
    /** 域名 */
    public string $domain;
    /**
     * @param string $domain 域名
     */
    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }
    /** 获取 whois 信息 */
    public function get_whois(): string
    {
        $response = file_get_contents('https://whois.aliyun.com/domain/' . $this->domain);
        $cookie = $this->get_cookie($http_response_header);
        preg_match('/var umToken=\'(.*?)\'/', $response, $matches);
        $um_token = $matches[1];
        $curl_url = 'https://whois.aliyun.com/whois/api_whois_info?' . http_build_query([
            'domainName' => $this->domain,
            'umToken' => $um_token
        ]);
        $curl = curl_init($curl_url);
        curl_setopt($curl, CURLOPT_COOKIE, http_build_query($cookie, '', ';'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($curl);
    }
    /**
     * 获取响应头中的 Set-Cookie 值
     * @param array $response_header 响应头
     * @return array Cookie 关键数组
     */
    public function get_cookie(array $response_header): array
    {
        $result = [];
        foreach ($response_header as $header) {
            if (preg_match('/^Set-Cookie/i', $header)) {
                preg_match('/^Set-Cookie:\s*(\S*?)=(\S*?);/i', $header, $matches);
                $result[$matches[1]] = urldecode($matches[2]);
            }
        }
        return $result;
    }
}
$domain = $_GET['domain'] ?? $_POST['domain'] ?? '';
$domain = $domain ? $domain : 'apee.top';
$whois = new Whois($domain);
$data = $whois->get_whois();
header('Content-Type: application/json');
echo $data;
