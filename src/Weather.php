<?php


namespace ColdCGH\Weather;


use ColdCGH\Weather\Exceptions\HttpException;
use ColdCGH\Weather\Exceptions\InvalidArgumentException;
use Exception;
use GuzzleHttp\Client;
use function in_array;
use function json_decode;
use function strtolower;

class Weather
{
    protected $key;
    /**
     * @var array
     */
    protected $guzzleOptions=[];

    public function __construct($key='6d52c2d9163293726cf26728be328c04')
    {
        $this->key = $key;
    }

    # 用户可以自定义 guzzle 实例的参数
    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    # 创建一个方法用于返回 guzzle 实例
    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function getWeather($city, $type = 'base', $format = 'json')
    {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        if (!in_array(strtolower($format), ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: '.$format);
        }

        if (!in_array(strtolower($type), ['base', 'all'])) {
            throw new InvalidArgumentException('Invalid type value(base/all): '.$type);
        }

        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => strtolower($format),
            'extensions' =>  strtolower($type),
        ]);

        try {
            $response = $this->getHttpClient()->get($url, [
                'query' => $query,
            ])->getBody()->getContents();

            return 'json' === $format ? json_decode($response, true) : $response;
        } catch (Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }
}