<?php
/**
 * Functions class
 *
 * This class contains misc useful functions.
 *
 * @version 1.5
 * @author Mihai Zaharie <mihai@zaharie.ro>
 * @date 1 November 2011
 */

class Functions
{
    /**
     * Escape for SQL
     *
     * Returns a sanitized string that can be used in SQL queries
     *
     * @return string
     */
    public static function escapeForSQL($mysqli, $query)
    {
        if (get_magic_quotes_gpc())
        {
            $query = stripslashes($query);
        }
        $query = $mysqli->real_escape_string($query);
        return $query;
    }

    /**
     * Get IP function
     *
     * Returns the user's IP address
     *
     * @return string
     */
    public static function getIPAddress()
    {
        if (isset($_SERVER['HTTP_X_FORWARD_FOR']))
        {
            return $_SERVER['HTTP_X_FORWARD_FOR'];
        }
        else
        {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * Validate the string against a regex
     *
     * Returns true if the regex matches
     *
     * @return bool
     */
    public static function matchesRegex($string, $regex)
    {
        if (preg_match($regex, $string) > 0)
        {
            return true;
        }

        return false;
    }

    /**
     * Truncate a string at an exact length respecting word boundaries
     *
     * Removes any special characters from a string and returns a truncated
     * version with an optional ellipsis at the end
     *
     * Note: a length value of 0 means the description will not be actually
     * truncated
     *
     * @return string
     */
    public static function truncate($string, $length, $ellipsis = '')
    {
        $length = ($length > 0) ? $length : strlen($string);

        $string = str_replace("\n", '', $string);
        $string = strip_tags($string);
        $data = (array) explode('\n\n', wordwrap($string, $length, '\n\n'));
        //$output = $this->stripExtendedChars($data[0]); // can break romanian characters
        $output = $data[0];
        return $output . ((strlen($data[0]) < strlen($string)) ? $ellipsis : '');
    }

    /**
     * Strip all the characters that can't be found on a normal keyboard
     *
     * Removes any special characters from a string and returns the remaining
     * string
     *
     * @return string
     */
    public static function stripExtendedChars($string, $transliterate = false)
    {
        $output = preg_replace('/([^\s\w`~!@#$%^&*()-_=+{}[\]<>|\\;:\'",.?]+)/i', '', $string);
        if ($transliterate)
        {
            $output = iconv('UTF-8', 'ISO-8859-1//IGNORE', $output);
        }
        return $output;
    }

    /**
     * Removes escape slashes and converts any HTML special characters to their
     * HTML entities
     *
     * Returns the string without escape slashes and with HTML entities
     *
     * @return string
     */
    public static function htmlSafe($string)
    {
        if (get_magic_quotes_gpc())
        {
            $string = stripslashes($string);
        }
        return htmlspecialchars($string);
    }

    /**
     * Converts any HTML special characters to their actual characters
     *
     * Returns the string with HTML entities decoded
     *
     * @return string
     */
    public static function realHTML($string)
    {
        return htmlspecialchars_decode($string);
    }

    /**
     * Make a string url safe
     *
     * Returns the string without any special charaters
     *
     * @return string
     */
    public static function makeURLString($string)
    {
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);   // Transliterate UTF-8 characters to ASCII
        $string = trim($string);                                // Remove trailing spaces
        $string = preg_replace('/[^a-z0-9\s]/i', '', $string);  // Remove non alphanum characters
        $string = str_replace(' ', '-', $string);               // Replace spaces with dashes
        $string = strtolower($string);                          // Convert all letters to lowercase
        return $string;
    }

    /**
     * Format bytes value based on size
     *
     * Returns the size and measurement unit
     *
     * @return string
     */
    public static function formatBytes($size)
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++)
        {
            $size /= 1024;
        }
        return round($size, 2) . $units[$i];
    }

    /**
     * Check if a variable is empty
     *
     * Returns true if the variable is empty
     *
     * @return bool
     */
    public static function isEmpty($var, $allow_false = false, $allow_whitespace = false)
    {
        if (!isset($var) ||
            is_null($var) || (
                !is_array($var)
                && $allow_whitespace == false
                && trim($var) == ''
                && !is_bool($var)
            ) || (
                $allow_false === false
                && is_bool($var)
                && $var === false
            ) || (
                is_array($var)
                && empty($var)
            )
        )
        {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Hash a string to be used as a password
     *
     * Returns the hash of the input string
     *
     * @return string
     */
    public static function hashPassword($password)
    {
        $password = sha1(substr($password, -2, 2) . $password);
        return $password;
    }

    /**
     * Returns string with newline formatting converted into HTML paragraphs.
     *
     * @param string $string String to be formatted
     * @param boolean $lineBreaks When true, single-line line-breaks will be converted to HTML break tags
     * @param boolean $xml When true, an XML self-closing tag will be applied to break tags (<br />)
     * @return string
     */
    public static function nl2p($string, $lineBreaks = true, $xml = true)
    {
        // Remove existing HTML formatting to avoid double-wrapping things
        $string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);

        // It is conceivable that people might still want single line-breaks
        // without breaking into a new paragraph.
        if ($lineBreaks == true)
        {
            $output = '<p>' . preg_replace('/(\n{2,}|\r{2,}|(?:\r\n){2,})/i', "</p>\n<p>", trim($string)) . '</p>';
            $output = preg_replace('/(?<!>)(\n|\r|\r\n)(?!>)/i', '<br' . ($xml == true ? ' /' : '') . '>', $output);
            return str_replace('<br /><br />', '<br />', $output);
        }
        else
        {
            return '<p>' . preg_replace('/((?:\n|\r|\r\n)+)/i', "</p>\n<p>", trim($string)) . '</p>';
        }
    }

    /**
     * Checks if a form appears to have been submitted
     *
     * @param string $method The method of the form (get or post)
     * @param string $submitName The name of the submit input
     * @return boolean
     */
    public static function gotFormData($method, $submitName = 'submit')
    {
        if ($method == 'get') { $data =& $_GET; } else { $data =& $_POST; }
        return (isset($data) && !empty($data) && isset($data[$submitName]) && !empty($data[$submitName]));
    }

    /**
     * Swaps two elements of an array
     *
     * @param array $array The array
     * @param integer $first The first element
     * @param second $first The second element
     * @return boolean true
     */
    public static function arraySwap(&$array, $first, $second)
    {
        $temp = $array[$first];
        $array[$first] = $array[$second];
        $array[$second] = $temp;
        return true;
    }
}