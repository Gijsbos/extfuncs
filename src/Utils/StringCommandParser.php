<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use DateTime;
use Exception;
use Ramsey\Uuid\Uuid;
use ReflectionClass;

/**
 * StringCommandParser
 */
abstract class StringCommandParser
{
    /**
     * isCommandSyntax
     */
    public static function isCommandSyntax(string $input)
    {
        return str_starts_ends_with($input, "<", ">");
    }

    /**
     * parse
     */
    public static function parse(string $command)
    {
        $input = unwrap($command, "<", ">");

        // Get parameters
        $arguments = explode(";", $input);
        $input = array_shift($arguments);

        // Handle command
        switch($input)
        {
            case "string":
                $string = array_shift($arguments);
                return is_wrapped_in_quotes($string) ? unwrap_quotes($string) : $string;
            case "int":
                return intval(array_shift($arguments));
            case "float":
                return floatval(array_shift($arguments));
            case "bool":
            case "boolean":
                return boolval(array_shift($arguments));
            case "null":
                return null;
            case "constant":
                $input = strval(array_shift($arguments));
                if(strpos($input, "::") !== false)
                {
                    $explode = explode("::", $input);
                    $className = resolve_class($explode[0]);
                    $constant = $explode[1];
                    return (new ReflectionClass($className))->getConstant($constant);
                }
                else
                {
                    return constant($input);
                }
            case "random":
                $type = array_shift($arguments);
                switch($type)
                {
                    case "int":
                        $start = count($arguments) ? intval(array_shift($arguments)) : null;
                        $end = count($arguments) ? intval(array_shift($arguments)) : null;
                        return random_int($start, $end);
                    case "float":
                        $start = count($arguments) ? floatval(array_shift($arguments)) : null;
                        $end = count($arguments) ? floatval(array_shift($arguments)) : null;
                        return random_float($start, $end);
                    case "array":
                        $arrayString = array_shift($arguments);
                        return random_array_item(parse_array_string($arrayString));
                    case "date":
                        $from = array_shift($arguments) !== null ? $arguments : new DateTime();
                        $to = array_shift($arguments) !== null ? $arguments : new DateTime();
                        $opt = array_shift($arguments);
                        return random_date($from, $to, $opt);
                    case "ip":
                        $version = array_shift($arguments);
                        return random_ip($version);
                    default:
                        throw new Exception("Unknown command using '$input;$type'");
                }
            case "token":
                $length = count($arguments) ? intval(array_shift($arguments)) : 32;
                return random_token($length);
            case "defuse-crypto-key":
                return (string) \Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString();
            case "uuid4":
                if(class_exists("\Rhumsaa\Uuid\Uuid"))
                    return Uuid::uuid4()->toString();
                else
                    return "<uuid4>";
            case "date":
                $date = new DateTime();

                if(count($arguments))
                    $date->modify(array_shift($arguments));

                return count($arguments) ? $date->format(array_shift($arguments)) : $date->format("Y-m-d H:i:s");
            case "method":
            case "function":
                $classMethod = array_shift($arguments);
                $methodArgs = count($arguments) ? parse_array_string(array_shift($arguments)) : [];
                return Functions::execute($classMethod, $methodArgs);
            default:
                return $command;
        }
    }
}