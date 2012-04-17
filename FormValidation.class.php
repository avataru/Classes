<?php
/**
 * Form validation class
 *
 * LICENSE: CC BY-NC-SA 3.0
 * http://creativecommons.org/licenses/by-nc-sa/3.0/
 *
 * @version 2.5
 * @author Mihai Zaharie <mihai@zaharie.ro>
 *
 *
 * Changelog:
 *
 * 2.5
 *
 * Added the getErrors() method that returns any unignored error.
 *
 * 2.4
 *
 * Added a new check for the date rule so European dates can be validated even
 * if they use American separators (slash) and the date is ambigous. Ex.:
 * 23/05/2012 is a valid dd/mm/yyyy date.
 *
 * The count rule now also works with strings not just arrays and the count will
 * be 1 or 0 (if the string is empty) in this case.
 *
 * Added examples for each rule and for the validate(), addRules() and
 * addRegexRule() methods.
 *
 * 2.3
 *
 * New method trimSpaces() that functions like htmlSanitize(). By default it
 * will be used for all fields when the object is created.
 *
 * The htmlSanitize() method doesn't rely on another custom recursive function
 * to go through the array.
 *
 * The countCheck() made more sense to be static.
 *
 * Added DocBlock comments for every variable and method.
 *
 * Added a new parameter to force debug() to execute regardless of the debug
 * status.
 *
 * The constructor will accept invalid or empty form data to prevent errors to
 * trigger when trying to auto-trim a non-aray of values.
 *
 * 2.2
 *
 * The "regex", "chars", "numeric", "email", "phone", "cnp", "base64", "date"
 * and "value" rules will now assume a valid value if the value they are
 * checking is an empty string. Use the "required" rule to validate against
 * empty string values.
 *
 * 2.1
 *
 * Fixed the "count" rule since it was special and needed to take the whole
 * array of field values instead of each value in turn.
 *
 */

class FormValidation
{
	/**
	 * Form data
	 *
	 * @var array
	 */
	public $form = array();

	/**
	 * Validation errors
	 *
	 * @var array
	 */
	public $errors = array();

	/**
	 * Charset to use where needed
	 *
	 * Supported values: http://www.php.net/manual/en/mbstring.supported-encodings.php
	 *
	 * @var string
	 */
	public $charset = 'UTF-8';

	/**
	 * Form defaults
	 *
	 * @var array
	 */
	protected $default = array();

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	protected $rules = array();

	/**
	 * Fields to reset
	 *
	 * @var array
	 */
	protected $reset = array();

	/**
	 * Multi-value (array) fields for which invalid values are ignored
	 *
	 * @var array
	 */
	protected $ignoreInvalid = array();

	/**
	 * Fields to ignore when validation fails
	 *
	 * @var array
	 */
	protected $ignoreErrors = array();

	/**
	 * Debugging state
	 *
	 * @var boolean
	 */
	protected $debug = false;

	/**
	 * Loads and prepares the form data for validation
	 *
	 * @param array $form Raw form data (usually it's $_POST or $_GET)
	 * @param string $charset Charset to use, optional
	 * @param boolean $trimSpaces When true, all field values will be trimmed when the instance is created, optional, defaults to true
	 * @return void
	 */
	public function __construct($form, $charset = null, $trimSpaces = true)
	{
		$this->form = (is_array($form) && !empty($form)) ? $form : array();

		if (!is_null($charset))
		{
			$this->charset = $charset;
		}

		if ($trimSpaces)
		{
			$this->trimSpaces();
		}
	}

	/**
	 * Turns debugging on and off
	 *
	 * @param boolean $state When true debugging is turned on, defaults to false
	 * @return void
	 */
	public function debugging($state = false)
	{
		$this->debug = ($state == true) ? true : false;
	}

	/**
	 * Adds validation rules for a field
	 *
	 * Usage:
	 *
	 * $instance->addRules(
	 * 	'fieldName',
	 * 	'rule1:option1:option2|rule2:option3:option4'
	 * );
	 *
	 * Example:
	 *
	 * To validate a required field, "username" of 6-25 characters length that
	 * can only contain letters, numbers, dahses and underscores we can use the
	 * following. Considering that the length must be more than 6 characters,
	 * the "required" rule can be omitted but it's a best practice not to.
	 *
	 * $instance->addRules(
	 * 	'username',
	 * 	'required|length:6-25|chars:alpha:digit:dash'
	 * );
	 *
	 * @param string $field Field name
	 * @param string $rules Rules expression
	 * @return boolean
	 */
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

			return true;
		}

		return false;
	}

	/**
	 * Adds a regex validation rule for a field
	 *
	 * The regex rule must be added separately because parsing the rules
	 * expression would otherwise be a nightmare since it can contain any
	 * character and it must take into account all kind of escapes.
	 *
	 * Example:
	 *
	 * To validate a password of 6-25 characters length that can only contain
	 * letters, numbers, dahses and underscores we can use the following regex.
	 *
	 * $instance->addRegexRule(
	 * 	'password',
	 * 	'/^[a-z0-9_-]$/i'
	 * );
	 *
	 * @param string $field Field name
	 * @param string $pattern Regex pattern
	 * @return boolean
	 */
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
			return true;
		}

		return false;
	}

	/**
	 * Validates the form fields against the rules
	 *
	 * @param boolean $resetInvalidValues When true invalid values will be reset
	 * @param mixed $defaultFormValues Array of form value defaults, optional, when null the values will reset to empty strings or arrays
	 * @return void
	 */
	public function validate($resetInvalidValues = true, $defaultFormValues = null)
	{
		if (is_array($defaultFormValues) && !empty($defaultFormValues))
		{
			$this->default = $defaultFormValues;
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

						if (is_array($this->form[$field]) && $rule != 'count')
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

		if ($resetInvalidValues === true)
		{
			$this->form = array_merge($this->form, $this->reset);
		}
	}

	/**
	 * Checks if a field failed to validate
	 *
	 * @param string $field Field name
	 * @return boolean
	 */
	public function hasError($field)
	{
		return (!empty($this->errors[$field])) ? true : false;
	}

	/**
	 * Checks if any field failed to validate except if an error should be
	 * ignored
	 *
	 * @return boolean
	 */
	public function hasErrors()
	{
		$unignoredErrors = array_diff_key($this->errors, $this->ignoreErrors);
		return (!empty($unignoredErrors)) ? true : false;
	}

	/**
	 * Adds an error for a field
	 *
	 * @param string $field Field name
	 * @param string $error Error text
	 * @return boolean true
	 */
	public function addError($field, $error)
	{
		$this->errors[$field] = $error;
		return true;
	}

	/**
	 * Returns all the validation errors, except for the ignored ones
	 *
	 * @return array
	 */
	public function getErrors()
	{
		$unignoredErrors = array_diff_key($this->errors, $this->ignoreErrors);
		return $unignoredErrors;
	}

	/**
	 * Sets a field value to its default
	 *
	 * @param string $field Field name
	 * @return boolean true
	 */
	public function setToDefault($field)
	{
		$this->form[$field] = (isset($this->default[$field])) ? $this->default[$field] : (is_array($this->form[$field]) ? array() : '');
		return true;
	}

	/**
	 * Sets the field(s) for which invalid values should be ignored
	 *
	 * This is only used for multi-value (array) fields
	 *
	 * @param mixed $fields Field name or an array of field names
	 * @return boolean true
	 */
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

	/**
	 * Sets the field(s) for which failed validations should be ignored
	 *
	 * This is not used when checking the error for a single field
	 *
	 * @param mixed $fields Field name or an array of field names
	 * @return boolean true
	 */
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

	/**
	 * HTML sanitize the field values
	 *
	 * @param mixed $fields Field name or an array of field names, optional, defaults to all fields
	 * @return boolean true
	 */
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

			$values = array_intersect_key($this->form, array_flip($fields));
		}
		else
		{
			$values = $this->form;
		}

		array_walk_recursive($values, create_function('&$val, $key', '$val = htmlspecialchars($val);'));
		$parsedForm = $values;

		$this->form = array_merge($this->form, $parsedForm);

		return true;
	}

	/**
	 * Trim the field values
	 *
	 * @param mixed $fields Field name or an array of field names, optional, defaults to all fields
	 * @return boolean true
	 */
	public function trimSpaces($fields = null, $characters = null)
	{
		$characters = (!empty($characters)) ? $characters : " \t\n\r\0\x0B";

		if (isset($fields))
		{
			if (!is_array($fields))
			{
				$field = $fields;
				$fields = array();
				$fields[] = $field;
			}

			$values = array_intersect_key($this->form, array_flip($fields));
		}
		else
		{
			$values = $this->form;
		}

		array_walk_recursive($values, create_function('&$val, $key, $chars', '$val = trim($val, $chars);'), $characters);
		$parsedForm = $values;

		$this->form = array_merge($this->form, $parsedForm);

		return true;
	}

	/**
	 * Checks if the value is not empty
	 *
	 * Example:
	 * $instance->addRules('email', 'required');
	 *
	 * @param string $value Value to check
	 * @param null $null Unused
	 * @return boolean
	 */
	protected function checkRuleRequired($value, $null = null)
	{
		if (!empty($value))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if the value matches the value of another field
	 *
	 * Example:
	 * $instance->addRules('password', 'match:passwordCheck');
	 *
	 * @param string $value Value to check
	 * @param string $otherField Other field name
	 * @return boolean
	 */
	protected function checkRuleMatch($value, $otherField)
	{
		if (isset($this->form[$otherField]) && $value == $this->form[$otherField])
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if the value is different than the value of another field
	 *
	 * Example:
	 * $instance->addRules('email', 'distinct:friendEmail');
	 *
	 * @param string $value Value to check
	 * @param string $otherField Other field name
	 * @return boolean
	 */
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

	/**
	 * Checks if the value matches a regex pattern
	 *
	 * Example:
	 * $instance->addRegexRule('id', '/^[1-9][0-9]*$/');
	 *
	 * @param string $value Value to check
	 * @param string $pattern Regex pattern
	 * @return boolean
	 */
	protected function checkRuleRegex($value, $pattern)
	{
		if (empty($value))
		{
			return true;
		}

		if (preg_match($pattern, $value))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks the length of the value
	 *
	 * For valid expressions, see the countCheck() method documentation.
	 *
	 * Example:
	 * $instance->addRules('username', 'length:>=5');
	 *
	 * @param string $value Value to check
	 * @param string $expression Length expression
	 * @return boolean
	 */
	protected function checkRuleLength($value, $expression)
	{
		$length = (function_exists('mb_strlen')) ? mb_strlen($value, $this->charset) : strlen($value);
		return self::countCheck($length, $expression);
	}

	/**
	 * Checks the characters in the value
	 *
	 * Options:
	 * 	space 	the space character
	 * 	dash 	dash and underscore
	 * 	digit 	digits
	 * 	symbol 	symbols
	 * 	alpha 	letters (lower and upper case)
	 *
	 * The options can be combined for more complex sets.
	 *
	 * Example:
	 * $instance->addRules('address', 'chars');
	 *
	 * @param string $value Value to check
	 * @param string $options Allowed character sets, optional, defaults to printable ASCII
	 * @return boolean
	 */
	protected function checkRuleChars($value, $options = '')
	{
		if (empty($value))
		{
			return true;
		}

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

	/**
	 * Checks if the value is numeric
	 *
	 * Options:
	 * 	integer or float
	 * 	positive or negative
	 * 	nonzero
	 *
	 * The options can be combined for more complex sets.
	 *
	 * Example:
	 * $instance->addRules('donation', 'numeric:positive:nonzero');
	 *
	 * @param string $value Value to check
	 * @param string $options Allowed numeric types, optional, defaults to any numeric value
	 * @return boolean
	 */
	protected function checkRuleNumeric($value, $options = '')
	{
		if (empty($value))
		{
			return true;
		}

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

	/**
	 * Checks if the value is valid email address
	 *
	 * This only checks the format, not if the email actually exists.
	 *
	 * The format is according to RFC 5322 except for the special characters and
	 * comments. WARNING: Some systems might consider an email address invalid
	 * if it contains some characters that should be normally allowed. Also,
	 * while not checked here, an email address must not be longer than 254
	 * characters.
	 *
	 * Example:
	 * $instance->addRules('emailAddress', 'email');
	 *
	 * @param string $value Value to check
	 * @param null $null Unused
	 * @return boolean
	 */
	protected function checkRuleEmail($value, $null = null)
	{
		if (empty($value))
		{
			return true;
		}

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

	/**
	 * Checks if the value is valid phone number
	 *
	 * Preset formats:
	 * 	ro 				Any Romanian phone number
	 * 	ro-landline 	Any Romanian landline phone number
	 * 	ro-mobile 		Any Romainan mobile phone number
	 *
	 * Flexible formats:
	 * 	0-9 			Fixed digit
	 * 	N 				Any digit
	 *
	 * 	Eg.: 900NNNNNNN would validate a phone number that starts with 900
	 *           and has 10 digits.
	 *
	 * Example:
	 * $instance->addRules('phone', '900NNNNNNN');
	 *
	 * @param string $value Value to check
	 * @param string $format Phone number format
	 * @return boolean
	 */
	protected function checkRulePhone($value, $format)
	{
		$formatPattern = '';

		if (empty($value))
		{
			return true;
		}

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

	/**
	 * Checks if the value is valid Romanian CNP (Personal Numerical Code)
	 *
	 * A valid CNP has the following format: GYYMMDDCCNNNX
	 *
	 * 	G 		Gender and birth century/residency
	 * 	YY 		Birth year
	 * 	MM 		Birth month
	 * 	DD 		Birth day
	 * 	CC 		Registration county
	 * 	NNN 	Registration code
	 * 	X 		Checksum digit
	 *
	 * Gender codes:
	 * 	1 - male born between 1 Jan 1900 and 31 Dec 1999
	 * 	2 - female born between 1 Jan 1900 and 31 Dec 1999
	 * 	3 - male born between 1 Jan 1800 and 31 Dec 1899
	 * 	4 - female born between 1 Jan 1800 and 31 Dec 1899
	 * 	5 - male born between 1 Jan 2000 and 31 Dec 2099
	 * 	6 - female born between 1 Jan 2000 and 31 Dec 2099
	 * 	7 - male foreigner with Romanian residency
	 * 	8 - female foreigner with Romanian residency
	 * 	9 - foreigner
	 *
	 * County codes:
	 * 	01 - Alba,					02 - Arad,					03 - Arges,
	 * 	04 - Bacau,				 	05 - Bihor,				 	06 - Bistrita-Nasaud,
	 * 	07 - Botosani,				08 - Brasov,				09 - Braila,
	 * 	10 - Buzau,				 	11 - Caras-Severin,		 	12 - Cluj,
	 * 	13 - Constanta,			 	14 - Covasna,				15 - Dambovita,
	 * 	16 - Dolj,					17 - Galati,				18 - Gorj,
	 * 	19 - Harghita,				20 - Hunedoara,			 	21 - Ialomita,
	 * 	22 - Iasi,					23 - Ilfov,				 	24 - Maramures,
	 * 	25 - Mehedinti,			 	26 - Mures,				 	27 - Neamt,
	 * 	28 - Olt,					29 - Prahova,				30 - Satu Mare,
	 * 	31 - Salaj,				 	32 - Sibiu,				 	33 - Suceava,
	 * 	34 - Teleorman,			 	35 - Timis,				 	36 - Tulcea,
	 * 	37 - Vaslui,				38 - Valcea,				39 - Vrancea,
	 * 	40 - Bucuresti,			 	41 - Bucuresti Sector 1,	42 - Bucuresti Sector 2,
	 * 	43 - Bucuresti Sector 3,	44 - Bucuresti Sector 4,	45 - Bucuresti Sector 5,
	 * 	46 - Bucuresti Sector 6,	51 - Calarasi,				52 - Giurgiu
	 *
	 * Example:
	 * $instance->addRules('code', 'cnp');
	 *
	 * @param string $value Value to check
	 * @param null $null Unused
	 * @return boolean
	 */
	protected function checkRuleCnp($value, $null = null)
	{
		if (empty($value))
		{
			return true;
		}

		$code = $value;

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

	/**
	 * Checks if the value is valid padded Base64 string
	 *
	 * Example:
	 * $instance->addRules('encodedData', 'base64');
	 *
	 * @param string $value Value to check
	 * @param null $null Unused
	 * @return boolean
	 */
	protected function checkRuleBase64($value, $null = null)
	{
		if (empty($value))
		{
			return true;
		}

		return $this->checkRuleRegex($value, '/^(?:[a-z0-9+\/]{4})*(?:[a-z0-9+\/]{2}==|[a-z0-9+\/]{3}=|[a-z0-9+\/]{4})$/i');
	}

	/**
	 * Checks if the value is valid date
	 *
	 * For the format, use the date() function formats.
	 *
	 * Example:
	 * $instance->addRules('birthDate', 'date:d.m.Y');
	 *
	 * @param string $value Value to check
	 * @param string $format Date format
	 * @return boolean
	 */
	protected function checkRuleDate($value, $format)
	{
		if (empty($value))
		{
			return true;
		}

		if (!empty($format))
		{
			$date = $value;

			// Try to catch European dates with American separators (slash
			// instead of dot or dash)

			if (preg_match('/d\/m/i', $format))
			{
				$date = str_replace('/', '.', $value);
			}

			$timestamp = strtotime($date);

			if (date($format, $timestamp) == $value)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the value equals another value or is in a set of values
	 *
	 * Example:
	 * $instance->addRules('gender', 'values:male,female');
	 *
	 * @param string $value Value to check
	 * @param mixed $values Valid value or an array of valid values
	 * @return boolean
	 */
	protected function checkRuleValue($value, $values)
	{
		if (empty($value))
		{
			return true;
		}

		$validValues = (strpos($values, ',') !== false) ? explode(',', $values) : $values;

		if ((is_array($validValues) && in_array($value, $validValues)) || ($value == $validValues))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks the number of values
	 *
	 * For valid expressions, see the countCheck() method documentation.
	 *
	 * Example:
	 * $instance->addRules('departments', 'count:3-5');
	 *
	 * @param array $values Array of values to check
	 * @param string $expression Count expression
	 * @return boolean
	 */
	protected function checkRuleCount($values, $expression)
	{
		$count = (is_array($values)) ? count($values) : ((!empty($values)) ? 1 : 0);
		return self::countCheck($count, $expression);
	}

	/**
	 * Checks a numeric value against an expression
	 *
	 * Expressions:
	 * 	number 		Examples: "1" or "15"
	 * 	range 		Examples: "0-100" or "1-255"
	 * 	comparison 	Examples: "<10", "<=50", ">2" or ">=4"
	 *
	 * @param string $value Numeric value to check
	 * @param string $expression Count expression
	 * @return boolean
	 */
	private static function countCheck($value, $expression)
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

	/**
	 * Prints a message if debugging is on
	 *
	 * @param string $message Debug message
	 * @param string $details Aditional debug information, optional
	 * @param boolean $forced When true forces the debug error trigger, optional
	 * @return void
	 */
	protected function debug($message, $details = null, $forced = false)
	{
		if ($forced || $this->debug)
		{
			error_log('FormValidation error: ' . $message . (($details != null) ? ' | ' . $details : ''));
		}
	}

	/**
	 * Returns the complete list of available validation rules
	 *
	 * @return array
	 */
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