<?php
/**
 * Form validation class
 *
 * @version 2.0
 * @author Mihai Zaharie <mihai@zaharie.ro>
 */

/*
    Inspiration: http://codeigniter.com/user_guide/libraries/form_validation.html#rulereference
*/

class FormValidation
{
    public $form = array();
    public $errors = array();

    protected $default = array();
    protected $rules = array();
    protected $reset = array();
    protected $ignoreInvalid = array();
    protected $ignoreErrors = array();
    protected $charset = 'UTF-8'; // http://www.php.net/manual/en/mbstring.supported-encodings.php
    protected $debug = false;

    public function __construct($form, $charset = 'UTF-8')
    {
        $this->form = $form;
        $this->charset = $charset;
    }

    public function debugging($state = false)
    {
        $this->debug = ($state == true) ? true : false;
    }

    public function addRules($field, $rules)
    {
        $inputError = false;

        if (!isset($field) || empty($field))
        {
            $inputError = true;
            $this->debug('ERROR_MISSING_FIELD');
        }

        if (!isset($rules) || empty($rules))
        {
            $inputError = true;
            $this->debug('ERROR_MISSING_RULES');
        }

        if (!$inputError)
        {
            $rulesData = explode('|', $rules);
            foreach ($rulesData as $ruleData)
            {
                $separatorPosition = (strpos($ruleData, ':') === false) ? 0 : strpos($ruleData, ':');
                $rule = ($separatorPosition > 0) ? substr($ruleData, 0, $separatorPosition) : $ruleData;
                $options = ($separatorPosition > 0) ? substr($ruleData, $separatorPosition + 1) : '';

                if ($rule != 'regex')
                {
                    if (method_exists($this, 'checkRule' . ucfirst($rule)))
                    {
                        $this->rules[$field][$rule] = $options;
                    }
                    else
                    {
                        $this->debug('ERROR_UNKNOWN_RULE', $rule);
                    }
                }
                else
                {
                    $this->debug('ERROR_ADD_RULE_SEPARATELY', $rule);
                }
            }
        }

        return false;
    }

    public function addRegexRule($field, $pattern)
    {
        $inputError = false;

        if (!isset($field) || empty($field))
        {
            $inputError = true;
            $this->debug('ERROR_MISSING_FIELD');
        }

        if (!isset($pattern) || empty($pattern))
        {
            $inputError = true;
            $this->debug('ERROR_MISSING_PATTERN');
        }

        if (!$inputError)
        {
            $this->rules[$field]['regex'] = $pattern;
        }

        return false;
    }

    public function validate($resetInvalid = true, $default = null)
    {
        if (is_array($default) && !empty($default))
        {
            $this->default = $default;
        }

        foreach ($this->rules as $field => $fieldRules)
        {
            foreach ($fieldRules as $rule => $options)
            {
                if (!isset($this->errors[$field]))
                {
                    if ($rule == 'required' && !isset($this->form[$field]))
                    {
                        $this->form[$field] = '';
                    }

                    if (isset($this->form[$field]))
                    {
                        $ruleMethod = 'checkRule' . ucfirst($rule);

                        if (is_array($this->form[$field]))
                        {
                            if (empty($this->form[$field]))
                            {
                                $this->form[$field][] = '';
                            }

                            foreach ($this->form[$field] as $key => $value)
                            {
                                if (!$this->$ruleMethod($value, $options))
                                {
                                    if (array_key_exists($field, $this->ignoreInvalid))
                                    {
                                        unset($this->form[$field][$key]);
                                    }
                                    else
                                    {
                                        $this->errors[$field] = $rule;
                                        $this->reset[$field] = (isset($this->default[$field])) ? $this->default[$field] : (is_array($this->form[$field]) ? array() : '');
                                        break;
                                    }
                                }
                            }

                            if (in_array($field, $this->ignoreInvalid) && empty($this->form[$field]))
                            {
                                $this->errors[$field] = $rule;
                                $this->reset[$field] = (isset($this->default[$field])) ? $this->default[$field] : (is_array($this->form[$field]) ? array() : '');
                            }
                        }
                        else
                        {
                            if (!$this->$ruleMethod($this->form[$field], $options))
                            {
                                $this->errors[$field] = $rule;
                                $this->reset[$field] = (isset($this->default[$field])) ? $this->default[$field] : (is_array($this->form[$field]) ? array() : '');
                            }
                        }
                    }
                }
            }
        }

        if ($resetInvalid === true)
        {
            $this->form = array_merge($this->form, $this->reset);
        }
    }

    public function hasError($field)
    {
        return (!empty($this->errors[$field])) ? true : false;
    }

    public function hasErrors()
    {
        $unignoredErrors = array_diff_key($this->errors, $this->ignoreErrors);
        return (!empty($unignoredErrors)) ? true : false;
    }

    public function addError($field, $error)
    {
        $this->errors[$field] = $error;
        return true;
    }

    public function setToDefault($field)
    {
        $this->form[$field] = (isset($this->default[$field])) ? $this->default[$field] : (is_array($this->form[$field]) ? array() : '');
        return true;
    }

    public function ignoreInvalid($fields)
    {
        if (!is_array($fields))
        {
            $field = $fields;
            $fields = array();
            $fields[] = $field;
        }
        foreach ($fields as $field)
        {
            if (isset($this->form[$field]) && is_array($this->form[$field]))
            {
                $this->ignoreInvalid[$field] = true;
            }
        }
        return true;
    }

    public function ignoreErrors($fields)
    {
        if (!is_array($fields))
        {
            $field = $fields;
            $fields = array();
            $fields[] = $field;
        }
        foreach ($fields as $field)
        {
            $this->ignoreErrors[$field] = true;
        }
        return true;
    }

    public function htmlSanitize($fields = null)
    {
        if (isset($fields))
        {
            if (!is_array($fields))
            {
                $field = $fields;
                $fields = array();
                $fields[] = $field;
            }

            foreach ($fields as $field)
            {
                $this->form[$field] = htmlspecialchars($this->form[$field]);
            }
        }
        else
        {
            $this->form = array_map(array($this, 'recursiveHTMLSpecialChars'), $this->form);
        }

        return true;
    }

    protected function checkRuleRequired($value, $null = null)
    {
        if (!empty($value))
        {
            return true;
        }

        return false;
    }

    protected function checkRuleMatch($value, $otherField)
    {
        if (isset($this->form[$otherField]) && $value == $this->form[$otherField])
        {
            return true;
        }

        return false;
    }

    protected function checkRuleDistinct($value, $otherField)
    {
        if (strpos($otherField, ',') !== false)
        {
            $foundMatch = false;
            $otherFields = explode(',', $otherField);
            foreach ($otherFields as $singleField)
            {
                if (!$foundMatch && $singleField != '')
                {
                    $foundMatch = $this->checkRuleMatch($value, $singleField);
                }
            }
            return !$foundMatch;
        }
        else
        {
            return !$this->checkRuleMatch($value, $otherField);
        }

        return false;
    }

    protected function checkRuleRegex($value, $pattern)
    {
        if (preg_match($pattern, $value))
        {
            return true;
        }

        return false;
    }

    protected function checkRuleLength($value, $expression)
    {
        $length = (function_exists('mb_strlen')) ? mb_strlen($value, $this->charset) : strlen($value);
        return $this->countCheck($length, $expression);
    }

    protected function checkRuleChars($value, $options = '')
    {
        $hexValues['space'] = '\x20'; // normal space character
        $hexValues['dash'] = '\x2D\x5F'; // dash and underscore
        $hexValues['digit'] = '\x30-\x39'; // [:digit:]
        $hexValues['symbol'] = '\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7E';
        $hexValues['alpha'] = '\x41-\x5A\x61-\x7A'; // [:alpha:]

        if (!empty($options))
        {
            $sets = explode(':', $options);

            $characters = '';
            foreach ($sets as $set)
            {
                if (array_key_exists($set, $hexValues))
                {
                    $characters .= $hexValues[$set];
                }
            }
            return $this->checkRuleRegex($value, '/^[' . $characters . ']+$/');
        }
        else
        {
            return $this->checkRuleRegex($value, '/^[\x20-\x7E]+$/');
        }

        return false;
    }

    protected function checkRuleNumeric($value, $options = '')
    {
        if (is_numeric($value))
        {
            $type = 'any';
            $sign = 'any';
            $canBeZero = true;

            $typeCheckPassed = false;
            $signCheckPassed = false;
            $zeroCheckPassed = true;

            if (!empty($options))
            {
                $settings = explode(':', $options);

                foreach ($settings as $setting)
                {
                    if (in_array($setting, array('integer', 'float')))
                    {
                        $type = $setting;
                    }
                    elseif (in_array($setting, array('positive', 'negative')))
                    {
                        $sign = $setting;
                    }
                    elseif ($setting == 'nonzero')
                    {
                        $canBeZero = false;
                    }
                }
            }

            if ($type == 'integer' && preg_match('/^-?[0-9]+$/', $value))
            {
                $typeCheckPassed = true;
            }
            elseif ($type == 'float' && preg_match('/^-?[0-9]*\.[0-9]+$/', $value))
            {
                $typeCheckPassed = true;
            }
            elseif ($type == 'any')
            {
                $typeCheckPassed = true;
            }

            if ($sign == 'positive' && $value >= 0)
            {
                $signCheckPassed = true;
            }
            elseif ($sign == 'negative' && $value <= 0)
            {
                $signCheckPassed = true;
            }
            elseif ($sign == 'any')
            {
                $signCheckPassed = true;
            }

            if (!$canBeZero && $value == 0)
            {
                $zeroCheckPassed = false;
            }

            return ($typeCheckPassed && $signCheckPassed && $zeroCheckPassed) ? true : false;
        }

        return false;
    }

    protected function checkRuleEmail($value, $null = null)
    {
        if (preg_match('/^[^@]{1,64}@[^@]{4,253}$/', $value))
        {
            $invalidData = false;
            list($localPart, $domainPart) = explode('@', $value);

            if (!preg_match('/^((?:[a-zA-Z0-9-!#$%&\'*+\/=?^_`{|}~]\.?)*[a-zA-Z0-9-!#$%&\'*+\/=?^_`{|}~])$/', $localPart))
            {
                $invalidData = true;
            }

            if (!$invalidData && !preg_match('/^.+\..{2,}$/', $domainPart))
            {
                $invalidData = true;
            }
            if (!$invalidData && !preg_match('/^(?:\[(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])]|(?:(?:[a-zA-Z0-9]|[a-zA-Z0-9](?:[a-zA-Z0-9-])*[a-zA-Z0-9])\.?)*(?:[a-zA-Z0-9]|[a-zA-Z0-9](?:[a-zA-Z0-9-])*[a-zA-Z0-9]))$/', $domainPart))
            {
                $invalidData = true;
            }

            if (!$invalidData)
            {
                return true;
            }
        }

        return false;
    }

    protected function checkRulePhone($value, $format)
    {
        $formatPattern = '';

        switch($format)
        {
            case 'ro':
                $formatPattern = '0[237][0-9]{8}';
                break;
            case 'ro-landline':
                $formatPattern = '0[23][0-9]{8}';
                break;
            case 'ro-mobile':
                $formatPattern = '07[0-9]{8}';
                break;
            default:
        }

        if ($formatPattern == '' && preg_match('/^[0-9N]+$/i', $format))
        {
            $length = strlen($format);
            $numbers = 0;

            for ($i = 0; $i < $length; $i++)
            {
                if (strtoupper(substr($format, $i, 1)) == 'N')
                {
                    $numbers++;
                }
                else
                {
                    if ($numbers > 0)
                    {
                        $formatPattern .= '[0-9]{' . $numbers . '}';
                        $numbers = 0;
                    }
                    $formatPattern .= substr($format, $i, 1);
                }
            }

            if ($numbers > 0)
            {
                $formatPattern .= '[0-9]{' . $numbers . '}';
                $numbers = 0;
            }
        }

        if ($formatPattern != '')
        {
            return $this->checkRuleRegex($value, '/^' . $formatPattern . '$/');
        }
        else
        {
            return (strlen($value) >= 3 && $this->checkRuleNumeric($value, 'integer:positive')) ? true : false;
        }

        return false;
    }

    protected function checkRuleCnp($value, $null = null)
    {
        $code = $value;
        /*
            Gender codes:
            1 - male born between 1 Jan 1900 and 31 Dec 1999
            2 - female born between 1 Jan 1900 and 31 Dec 1999
            3 - male born between 1 Jan 1800 and 31 Dec 1899
            4 - female born between 1 Jan 1800 and 31 Dec 1899
            5 - male born between 1 Jan 2000 and 31 Dec 2099
            6 - female born between 1 Jan 2000 and 31 Dec 2099
            7 - male foreigner with Romanian residency
            8 - female foreigner with Romanian residency
            9 - foreigner

            County codes:
            01 - Alba,               02 - Arad,               03 - Arges,
            04 - Bacau,              05 - Bihor,              06 - Bistrita-Nasaud,
            07 - Botosani,           08 - Brasov,             09 - Braila,
            10 - Buzau,              11 - Caras-Severin,      12 - Cluj,
            13 - Constanta,          14 - Covasna,            15 - Dambovita,
            16 - Dolj,               17 - Galati,             18 - Gorj,
            19 - Harghita,           20 - Hunedoara,          21 - Ialomita,
            22 - Iasi,               23 - Ilfov,              24 - Maramures,
            25 - Mehedinti,          26 - Mures,              27 - Neamt,
            28 - Olt,                29 - Prahova,            30 - Satu Mare,
            31 - Salaj,              32 - Sibiu,              33 - Suceava,
            34 - Teleorman,          35 - Timis,              36 - Tulcea,
            37 - Vaslui,             38 - Valcea,             39 - Vrancea,
            40 - Bucuresti,          41 - Bucuresti Sector 1, 42 - Bucuresti Sector 2,
            43 - Bucuresti Sector 3, 44 - Bucuresti Sector 4, 45 - Bucuresti Sector 5,
            46 - Bucuresti Sector 6, 51 - Calarasi,           52 - Giurgiu
        */

        if (preg_match('/^([1-9])([0-9]{2}(?:0[1-9]|1[012])(?:0[1-9]|[12][0-9]|3[01]))(0[1-9]|[123][0-9]|4[0-6]|5[12])([0-9]{3})([0-9])$/', $code)
            && checkdate(substr($code, 3, 2), substr($code, 5, 2), substr($code, 1, 2)))
        {
            $controlSum = $code[0] * 2 + $code[1] * 7 + $code[2] * 9 + $code[3] * 1 +
                          $code[4] * 4 + $code[5] * 6 + $code[6] * 3 + $code[7] * 5 +
                          $code[8] * 8 + $code[9] * 2 + $code[10] * 7 + $code[11] * 9;
            $controlMod = $controlSum % 11;

            if (($controlMod < 10 && $controlMod == $code[12]) || ($controlMod == 10 && $code[12] == 1))
            {
                return true;
            }
        }

        return false;
    }

    protected function checkRuleBase64($value, $null = null)
    {
        return $this->checkRuleRegex($value, '/^(?:[a-z0-9+\/]{4})*(?:[a-z0-9+\/]{2}==|[a-z0-9+\/]{3}=|[a-z0-9+\/]{4})$/i');
    }

    protected function checkRuleDate($value, $format = '')
    {
        $timestamp = strtotime($value);

        if (!empty($format) && date($format, $timestamp) == $value)
        {
            return true;
        }

        return false;
    }

    protected function checkRuleValue($value, $values)
    {
        $validValues = (strpos($values, ',') !== false) ? explode(',', $values) : $values;

        if ((is_array($validValues) && in_array($value, $validValues)) || ($value == $validValues))
        {
            return true;
        }

        return false;
    }

    protected function checkRuleCount($value, $expression)
    {
        return $this->countCheck(count($value), $expression);
    }

    private function countCheck($value, $expression)
    {
        if (preg_match('/^[0-9]+$/', $expression) > 0)
        {
            if ($value == $expression)
            {
                return true;
            }
        }
        elseif (preg_match('/^(>=?|<=?)([0-9]+)$/', $expression, $matches) > 0)
        {
            switch ($matches[1])
            {
                case '<':
                    if ($value < (int) $matches[2])
                    {
                        return true;
                    }
                    break;
                case '<=':
                    if ($value <= (int) $matches[2])
                    {
                        return true;
                    }
                    break;
                case '>':
                    if ($value > (int) $matches[2])
                    {
                        return true;
                    }
                    break;
                case '>=':
                    if ($value >= (int) $matches[2])
                    {
                        return true;
                    }
                    break;
            }
        }
        elseif (preg_match('/^([0-9]+)-([0-9]+)$/', $expression, $matches) > 0)
        {
            if ($value >= (int) $matches[1] && $value <= (int) $matches[2])
            {
                return true;
            }
        }

        return false;
    }

    private function recursiveHTMLSpecialChars($input)
    {
        if (is_array($input))
        {
            if (!empty($input))
            {
                foreach ($input as $key => $value)
                {
                    $parsed[$key] = $this->recursiveHTMLSpecialChars($value);
                }
            }
            else
            {
                $parsed = array();
            }
        }
        else
        {
            $parsed = htmlspecialchars($input);
        }
        return $parsed;
    }

    protected function debug($message, $parameters = null)
    {
        if ($this->debug)
        {
            $parameters = ($parameters != null) ? $parameters : '';
            echo $message . ' : ' . $parameters . "<br />\n";
        }
    }

    public static function validRules()
    {
        $methods = array();
        foreach (get_class_methods(__CLASS__) as $method)
        {
            if (preg_match('/^checkRule([A-Z0-9][a-zA-Z0-9]*)$/', $method, $rule))
            {
                if (strtolower($rule[1]) != 'regex')
                {
                    $methods[] = strtolower($rule[1]);
                }
            }
        }
        sort($methods);
        return $methods;
    }
}