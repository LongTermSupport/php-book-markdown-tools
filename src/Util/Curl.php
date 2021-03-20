<?php

declare(strict_types=1);

namespace LTS\MarkdownTools\Util;

final class Curl
{
    private const USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36';

    private ?\CurlHandle $handle = null;

    public function fetchUrl(string $url): string
    {
        try {
            $curl = $this->getHandle();
            \Safe\curl_setopt($curl, CURLOPT_URL, $url);

            return \Safe\curl_exec($curl);
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Failed getting URL: ' . $url, $exception->getCode(), $exception);
        }
    }

    private function getHandle(): \CurlHandle
    {
        if ($this->handle instanceof \CurlHandle) {
            return $this->handle;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, "My User Agent Name");
        $this->handle = $curl;

        return $this->handle;
    }


}