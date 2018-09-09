<?php
/**
 * @author: zmh
 * @date: 2018-08-26
 */
namespace Framework\Validator;

/**
 * 参数校验
 * 数据验证器
 * 用于对来自非安全来源的数据（比如用户输入）进行验证
 * @example
 *  $rules = [
 * 'fdEmail' => ['required', 'email'], // 必须，邮箱验证规则
 * 'fdGender' => ['in' => [1, 2]], // 限定只能在1,2之间取值
 * 'fdAge' => ['int', 'min' => 10, 'max' => 120], // 使用整形验证规则，最小值10，最大值120
 * 'fdAddress' => ['minLength' => 10, 'maxLength' => 20], // 最小长度10，最大长度20
 * 'fdIdCard' => ['length' => 18],  // 18位
 * 'fdHomepage' => ['url'], // 使用url验证规则
 * 'fdIp' => ['ip'], // 使用ip验证规则
 * ];
 * @package Framework\Validator
 */
class Validator
{
    private $_rule = [
        "int" => [
            "method" => "checkInt",
            "message" => "字段[:keyword0:]类型不是合法的Int 格式"
        ],
        "bool" => [
            "method" => "checkBool",
            "message" => "字段[:keyword0:]类型不是合法的Bool 格式"
        ],
        "boolean" => [
            "method" => "checkBoolean",
            "message" => "字段[:keyword0:]类型不是合法的Boolean 格式"
        ],
        "ip" => [
            "method" => "checkIp",
            "message" => "字段[:keyword0:]类型不是合法的Ip 格式"
        ],
        "url" => [
            "method" => "checkUrl",
            "message" => "字段[:keyword0:]类型不是合法的Url 格式"
        ],
        "required" => [
            "method" => "checkRequired",
            "message" => "字段[:keyword0:] 为必填属性"
        ],
        "email" => [
            "method" => "checkEmail",
            "message" => "字段[:keyword0:]类型不是合法的Email 格式"
        ],
        "string" => [
            "method" => "checkString",
            "message" => "字段[:keyword0:]类型不是合法的String 格式"
        ],
        "min" => [
            "method" => "checkMin",
            "message" => "字段[:keyword0:]的值不能小于[:keyword1:]"
        ],
        "max" => [
            "method" => "checkMax",
            "message" => "字段[:keyword0:]的值不能大于[:keyword1:]"
        ],
        "in" => [
            "method" => "checkIn",
            "message" => "字段[:keyword0:]的值要在 :keyword1: 中"
        ],
        "length" => [
            "method" => "checkLength",
            "message" => "字段[:keyword0:]的值长度必须为 :keyword1:"
        ],
        "minLength" => [
            "method" => "checkLength",
            "message" => "字段[:keyword0:]的值长度必须大于 :keyword1:"
        ],
        "maxLength" => [
            "method" => "checkLength",
            "message" => "字段[:keyword0:]的值长度必须小于 :keyword1:"
        ]
    ];
    // 用户自定义规则
    private $_userRule = [];
    private $_data;

    /**
     * 检查规则
     *
     * @param       $data
     * @param array $rules
     *
     * @throws \Exception
     */
    public static function check($data, $rules = [])
    {
        $validator = new static();
        $validator->checkData($data, $rules);
    }

    public function checkData($data, $rules = [])
    {
        try {
            $this->_data = $data;
            foreach ($rules as $param => $rule) {
                foreach ($rule as $row => $extend) {
                    if (is_numeric($row)) {
                        $row = $extend;
                        $extend = null;
                    }
                    $tag = true;
                    if (!array_key_exists($row, $this->getRule())) {
                        throw new \Exception("未找到规则".$row);
                    }
                    $rule = $this->getRule();
                    $method = $rule[$row];
                    // 用户自定义规则
                    if ($method instanceof \Closure) {
                        $extend = is_array($extend) ? $extend : [$extend];
                        call_user_func_array($method, $extend);
                    } else {
                        // 系统规则
                        $method = 'Check'.Ucfirst($row);
                        method_exists(__CLASS__, $method) && $this->$method($param, $extend);
                    }
                }
            }
        } catch(\Exception $e) {
            throw $e;
        }
        return $this->_data;
    }

    // 添加规则
    public function addRule($ruleName, $rule)
    {
        $this->_rule = array_merge($this->_rule, [$ruleName => $rule]);
    }

    /**
     * 获取规则
     * @return array
     */
    protected function getRule()
    {
        return $this->_rule;
    }

    /**
     * 获取用户自定义规则
     * @return array
     */
    protected function getUserRule()
    {
        return $this->_userRule;
    }

    /**
     * 获取传入参数中的值
     * 并将入参中的值进行xss 和简单的安全过滤
     *
     * @param $param
     *
     * @return bool|mixed
     */
    protected function GetParam($param)
    {
        if (is_object($this->_data)) {
            $value = isset($this->_data->$param) ? $this->_data->$param : false;
            if ($value) {
                $value = self::filterTrojan(self::filterXSS($value));
                $this->_data->$param = $value;
            }
        } else {
            $value = isset($this->_data[$param]) ? $this->_data[$param] : false;
            if ($value) {
                $value = self::filterTrojan(self::filterXSS($value));
                $this->_data[$param] = $value;
            }
        }
        return $value;
    }

    /**
     * 检查int 类型
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckInt($param)
    {
        $value = $this->GetParam($param);
        if (!is_array($value) && !is_object($value) && strlen($value)) {
            if (false === filter_var($value, FILTER_VALIDATE_INT)) {
                throw new \Exception($this->parseMessage("int", $param));
            }
            return true;
        }
        return false;
    }

    /**
     * 检查必填
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckRequired($param)
    {
        $value = $this->GetParam($param);
        if ($value) {
            return true;
        }
        throw new \Exception($this->parseMessage("required", $param));
    }

    /**
     * 检查邮箱
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckEmail($param)
    {
        $value = $this->GetParam($param);
        if (!is_array($value) && !is_object($value) && strlen($value)) {
            if (false !== filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return true;
            }
        }
        throw new \Exception($this->parseMessage("email", $param));
    }

    /**
     * 检查bool类型
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckBoolean($param)
    {
        $value = $this->GetParam($param);
        if (!is_array($value) && !is_object($value) && strlen($value)) {
            if (false !== filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                return true;
            }
        }
        throw new \Exception($this->parseMessage("boolean", $param));
    }

    /**
     * 检查bool类型
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckBool($param)
    {
        $value = $this->GetParam($param);
        if (!is_array($value) && !is_object($value) && strlen($value)) {
            if (false !== filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                return true;
            }
        }
        throw new \Exception($this->parseMessage("bool", $param));
    }

    /**
     * 检查字符串
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckString($param)
    {
        $value = $this->GetParam($param);
        if (!is_array($value) && !is_object($value) && strlen($value)) {
            if (is_string($value)) {
                return true;
            }
        }
        throw new \Exception($this->parseMessage("string", $param));
    }

    /**
     * 检查最小值
     *
     * @param $param
     * @param $extend
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckMin($param, $extend)
    {
        $value = $this->GetParam($param);
        if (!is_array($value) && !is_object($value) && strlen($value)) {
            if ($value >= $extend) {
                return true;
            }
        }
        throw new \Exception($this->parseMessage("min", [
            $param,
            $extend
        ]));
    }

    /**
     * 检查最大值
     *
     * @param $param
     * @param $extend
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckMax($param, $extend)
    {
        $value = $this->GetParam($param);
        if (!is_array($value) && !is_object($value) && strlen($value)) {
            if ($value <= $extend) {
                return true;
            }
        }
        throw new \Exception($this->parseMessage("max", [
            $param,
            $extend
        ]));
    }

    /**
     * 检查范围
     *
     * @param $param
     * @param $extend
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckIn($param, $extend)
    {
        $value = $this->GetParam($param);
        if (!is_array($value) && !is_object($value) && strlen($value) && is_array($extend)) {
            if (in_array($value, $extend)) {
                return true;
            }
        }
        throw new \Exception($this->parseMessage("in", [
            $param,
            json_encode($extend, JSON_UNESCAPED_UNICODE)
        ]));
    }

    /**
     * 检查字符串长度
     * 中文占3个字符
     *
     * @param $param
     * @param $extend
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckLength($param, $extend)
    {
        $value = $this->GetParam($param);
        if (is_array($value) && is_numeric($extend)) {
            if (count($value) != $extend) {
                throw new \Exception($this->parseMessage("length", [
                    $param,
                    $extend
                ]));
            }
        } else if (is_string($value) && strlen($value)) {
            if (is_numeric($extend)) {
                if (strlen($value) != $extend) {
                    throw new \Exception($this->parseMessage("length", [
                        $param,
                        $extend
                    ]));
                }
            } else if (is_array($extend)) {
                list($min, $max) = $extend;
                if (!($value > $min && $value < $extend)) {
                    throw new \Exception($this->parseMessage("length", [
                        $param,
                        $extend
                    ]));
                }
            }
        }
        return true;
    }

    /**
     * 检查最小值
     *
     * @param $param
     * @param $extend
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckMinLength($param, $extend)
    {
        $value = $this->GetParam($param);
        if (is_array($value) && is_numeric($extend)) {
            if (count($value) < $extend) {
                throw new \Exception($this->parseMessage("minLength", [
                    $param,
                    $extend
                ]));
            }
        } else if (is_string($value) && strlen($value)) {
            if (is_numeric($extend)) {
                if (strlen($value) < $extend) {
                    throw new \Exception($this->parseMessage("minLength", [
                        $param,
                        $extend
                    ]));
                }
            }
        }
        return true;
    }

    /**
     * 检查最大值
     *
     * @param $param
     * @param $extend
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckMaxLength($param, $extend)
    {
        $value = $this->GetParam($param);
        if (is_array($value) && is_numeric($extend)) {
            if (count($value) > $extend) {
                throw new \Exception($this->parseMessage("minLength", [
                    $param,
                    $extend
                ]));
            }
        } else if (is_string($value) && strlen($value)) {
            if (is_numeric($extend)) {
                if (strlen($value) > $extend) {
                    throw new \Exception($this->parseMessage("minLength", [
                        $param,
                        $extend
                    ]));
                }
            }
        }
        return true;
    }

    /**
     * 检查ip
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckIp($param)
    {
        $value = $this->GetParam($param);
        if (!is_array($value) && !is_object($value) && strlen($value)) {
            if (true === filter_var($value, FILTER_VALIDATE_IP)) {
                return true;
            }
        }
        throw new \Exception($this->parseMessage("ip", $param));
    }

    /**
     * 检查url
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function CheckUrl($param)
    {
        $value = $this->GetParam($param);
        if (!is_array($value) && !is_object($value) && strlen($value)) {
            if (true === filter_var($value, FILTER_VALIDATE_URL)) {
                return true;
            }
        }
        throw new \Exception($this->parseMessage("ip", $param));
    }

    /**
     * 过滤木马代码。
     * Filter trojan codes.
     *
     * @param  string $var
     *
     * @access public
     * @return string
     */
    public static function filterTrojan($var)
    {

        if (strpos(htmlspecialchars_decode($var), '<?') === false) {
            return $var;
        }
        $var = (string) $var;
        $evils = [
            'eval',
            'exec',
            'passthru',
            'proc_open',
            'shell_exec',
            'system',
            '$$',
            'include',
            'require',
            'assert'
        ];
        $replaces = [
            'e v a l',
            'e x e c',
            ' p a s s t h r u',
            ' p r o c _ o p e n',
            's h e l l _ e x e c',
            's y s t e m',
            '$ $',
            'i n c l u d e',
            'r e q u i r e',
            'a s s e r t'
        ];
        $var = str_ireplace($evils, $replaces, $var);
        return $var;
    }

    /**
     * 过滤 XSS代码。
     * Filter XSS codes.
     *
     * @param  string $var
     *
     * @access public
     * @return string
     */
    public static function filterXSS($var)
    {
        if (stripos($var, '<script') !== false) {
            $var = (string) $var;
            $evils = [
                'appendchild(',
                'createElement(',
                'xss.re',
                'onfocus',
                'onclick',
                'innerHTML',
                'replaceChild(',
                'html(',
                'append(',
                'appendTo(',
                'prepend(',
                'prependTo(',
                'after(',
                'before(',
                'replaceWith('
            ];
            $replaces = [
                'a p p e n d c h i l d (',
                'c r e a t e E l e  m e n t (',
                'x s s . r e',
                'o n f o c u s',
                'o n c l i c k',
                'i n n e r H T M L',
                'r e p l a c e C h i l d (',
                'h t m l (',
                'a p p e n d (',
                'a p p e n d T o (',
                'p r e p e n d (',
                'p r e p e n d T o (',
                'a f t e r (',
                'b e f o r e (',
                'r e p l a c e W i t h ('
            ];
            $var = str_ireplace($evils, $replaces, $var);
        }
        /* Process like 'javascript:' */
        $var = preg_replace('/j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:/Ui', 'j a v a s c r i p t :', $var);
        return $var;
    }
    //
    //    protected function CheckRegexp($param, $extend)
    //    {
    //        $value = $this->GetParam($param);
    //        if (!is_array($value) && !is_object($value) && strlen($value)) {
    //            if (false == filter_var($value, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $extend]])) {
    //                throw new \Exception('Param of '.$param.'\'s format is invalid');
    //            }
    //        }
    //    }
    //
    //    protected function CheckExcept($param, $extend)
    //    {
    //        $value = $this->GetParam($param);
    //        if (!is_array($value) && !is_object($value) && strlen($value) && is_array($extend)) {
    //            if (in_array($value, $extend)) {
    //                throw new \Exception('Param of '.$param.' is invalid');
    //            }
    //        }
    //    }
    /**
     * 解析异常消息
     */
    protected function parseMessage($rule, $param)
    {
        $search = ":keyword0:";
        $replace = $param;
        if (is_array($param)) {
            $search = [];
            $replace = [];
            foreach ($param as $k => $v) {
                $search[] = ":keyword{$k}:";
                $replace[] = $v;
            }
        }
        return str_replace($search, $replace, $this->_rule[$rule]["message"]);
    }
}