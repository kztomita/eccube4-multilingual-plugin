<?php

namespace Plugin\MultiLingual\Common;

class ParseUrlHelper
{
    /**
     * parse_url()が返す$componentをURL文字列に戻す。
     *
     * @param array $component
     * @return string
     */
    public static function buildURL(array $component): string
    {
        $url = '';
        if (isset($component['scheme'])) {
            $url .= $component['scheme'].'://';
        }
        if (isset($component['user']) || isset($component['pass'])) {
            if (isset($component['user'])) {
                $url .= $component['user'];
            }
            if (isset($component['pass'])) {
                $url .= ':' . $component['pass'];
            }
            $url .= '@';
        }
        if (isset($component['host'])) {
            $url .= $component['host'];
        }
        if (isset($component['port'])) {
            $url .= ':' . $component['port'];
        }
        if (isset($component['path'])) {
            $url .= $component['path'];
        }
        if (isset($component['query'])) {
            $url .= '?' . $component['query'];
        }
        if (isset($component['fragment'])) {
            $url .= '#' . $component['fragment'];
        }

        return $url;
    }
}
