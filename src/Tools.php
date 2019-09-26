<?php

namespace Yjtec\Lintools;

/**
 * Description of Tool
 *
 * @author Administrator
 */
class Tools {

    /**
     * 根据PHP各种类型变量生成唯一标识号
     * @param mixed $mix 变量
     * @return string
     */
    public static function toGuidString($mix) {
        if (is_object($mix)) {
            return spl_object_hash($mix);
        } elseif (is_resource($mix)) {
            $mix = get_resource_type($mix) . strval($mix);
        } else {
            $mix = serialize($mix);
        }
        return md5($mix);
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @return string
     */
    public static function parseName($name, $type = 0) {
        if ($type) {
            return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match) {
                        return strtoupper($match[1]);
                    }, $name));
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     */
    public static function dataReturn($data, $type = 'JSON', $jsonOption = 0, $jsonpHeader = 'yjtec') {
        switch (strtoupper($type)) {
            case 'XML' :// 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(self::xmlEncode($data));
            case 'JSONP':// 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit($jsonpHeader . '(' . json_encode($data, $jsonOption) . ');');
            case 'EVAL' :// 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            case 'JSON' :
            default :
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data, $jsonOption));
        }
    }

    /**
     * XML编码，待验证
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    public static function xmlEncode($data, $root = 'Yjtec', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8') {
        if (is_array($attr)) {
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml .= "<{$root}{$attr}>";
        $xml .= self::data2xml($data, $item, $id);
        $xml .= "</{$root}>";
        return $xml;
    }

    /**
     * 数据XML编码，待验证
     * @param mixed  $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id   数字索引key转换为的属性名
     * @return string
     */
    public static function data2xml($data, $item = 'item', $id = 'id') {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= (is_array($val) || is_object($val)) ? self::data2xml($val, $item, $id) : $val;
            $xml .= "</{$key}>";
        }
        return $xml;
    }

}
