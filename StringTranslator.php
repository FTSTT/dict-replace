<?php
/**
 * StringTranslator Class
 *
 * 这个类用于根据键值对字典进行字符串翻译。
 * 支持添加、删除翻译对，贪婪匹配翻译，以及生成随机字符串。
 *
 * 用法示例：
 * $translator = new StringTranslator();
 * $translator->addTranslation("hello", "你好");
 * echo $translator->translate("hello world");
 *
 * @version 1.0
 * @author FTSTT
 */

class StringTranslator {
    private $dictionary = [];
    private $protectedParts = [];
    // 添加键值对
    public function addTranslation($key, $val) {
        $this->dictionary[$key] = $val;
    }

    // 根据键或值删除对应数据
    public function removeTranslation($value, $byValue = true) {
        if ($byValue) {
            $key = array_search($value, $this->dictionary);
            if ($key !== false) {
                unset($this->dictionary[$key]);
            }
        } else {
            unset($this->dictionary[$value]);
        }
    }

    // 翻译字符串
    public function translate($string) {
        // 按长度从长到短排序
        uksort($this->dictionary, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        // 使用数组保护已翻译的部分
        $protectedParts = [];

        foreach ($this->dictionary as $key => $val) {
            // 查找当前字符串中的每个关键字
            $string = preg_replace_callback("/$key/", function($matches) use ($val, &$protectedParts) {
                $pIndex=$this->generateRandomString();
                $protectedParts[$pIndex] = $val; // 添加到保护数组
                return  $pIndex ; // 用占位符替换
            }, $string);
        }

        // 还原已保护的部分
        foreach ($protectedParts as $index => $val) {
            $string = str_replace( $index , $val, $string);
        }

        return $string;
    }

    // 反向翻译
    public function reverseTranslate($string) {
        $reversedDict = array_flip($this->dictionary);
        uksort($reversedDict, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($reversedDict as $key => $val) {
            $string = preg_replace("/$key/", $val, $string);
        }

        return $string;
    }
    // 随机字符串生成函数
    public function generateRandomString($length=22, $charList="01234567") {//谁碰撞到了就再来一次 还中抓紧买彩票吧
        $protectedKeys = array_keys($this->protectedParts);
        $attempts = 0;
        $maxAttempts = 100;

        while (true) {
            $attempts++;

            // 超过最大尝试次数，扩大字符范围
            if ($attempts >= $maxAttempts) {
                $charList = implode(array_map('chr', range(32, 126))); // 使用可打印字符集
                $attempts = 0; // 重置尝试次数
            }

            $randomString = '';

            // 从字符列表中随机生成字符串
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $charList[random_int(0, strlen($charList) - 1)];
            }

            // 检查是否与 protectedParts 的键相同
            if (in_array($randomString, $protectedKeys)) {
                continue;
            }

            // 检查是否包含 dictionary 的任意键
            $containsKey = false;
            foreach ($this->dictionary as $key => $val) {
                if (strpos($randomString, $key) !== false) {
                    $containsKey = true; // 标记为包含
                    break;
                }
            }

            // 如果没有包含任何字典键，返回生成的随机字符串
            if (!$containsKey) {
                return "ξ".$randomString;
            }
        }
    }
}
