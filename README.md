# extfuncs

A collection of PHP helper functions and utility classes: array manipulation, random/test data generation, request helpers, string value parsing, encryption, hashing and `.env` handling.

## Requirements

- PHP 8.3 or higher
- Composer

## Installation

```bash
composer require gijsbos/extfuncs
```

The global functions in `src/library.php` are loaded automatically by Composer's autoloader. Every function is wrapped in `function_exists()`, so a function you define yourself with the same name takes precedence.

```php
require "vendor/autoload.php";

$token = random_token(32);
```

## Configuration

Some functions read environment variables. They can be set in the server environment or in a `.env` file loaded with `DotEnv::parse()` (see [Utility classes](#utility-classes)).

| Variable | Used by | Description |
| --- | --- | --- |
| `ALLOWED_HOSTS` | `get_host`, `get_base_uri`, `get_uri`, `get_uri_part`, `useHTTPS` | Comma separated list of hosts, e.g. `example.com,www.example.com`. Requests for other hosts fall back to the first entry. Strongly recommended in production, see [Security notes](#security-notes). |
| `ENC_KEY` | `Encrypter` | Encryption key, generate one with `Encrypter::generateKey()` or `vendor/bin/generate-defuse-key`. |
| `HASH_KEY` | `Hasher` | HMAC key used for hashing. |
| `ENVIRONMENT` / `ENV` | `Environment`, `App` | Values starting with `dev`, `prod` or `test` select the environment. `ENVIRONMENT` takes precedence over `ENV`. In development `App` displays all errors. |

## Functions

### Arrays

| Function | Description |
| --- | --- |
| `array_option($key, $array, $default = false, $throws = null)` | Returns `$array[$key]`, or `$default` when missing. `$throws` may be an exception class name or instance. |
| `array_has_option($key, $array, $default = false)` | Checks whether an option is set, either as a key (`['debug' => true]`) or a value (`['debug']`). |
| `array_get_key_value($path, $array, $delimiter = ".")` | Reads a nested value by path, e.g. `"user.address.city"`. |
| `array_map_assoc($callback, $array)` | Maps keys and values; the callback returns `[$newKey, $newValue]`. |
| `array_is_assoc($array)` | **Deprecated**, use `!array_is_list($array)`. |
| `is_array_of_arrays($array)` | `true` for a non empty list whose items are all arrays. |
| `array_in_array($haystack, $needle, $delimiter = ",")` | `true` when every needle item (array or delimited string) is in the haystack. |
| `array_get_keys($array, $asList = false)` | Returns the (nested) key structure of an array. |
| `array_keys_exist($keys, $data, $asList = false)` | Checks that a (nested) key structure exists in `$data`. |
| `array_has_keys($array, $keys, $asList, $strict, $throws)` | Like `array_keys_exist`; `$strict` also requires no extra keys. |
| `array_filter_keys($array, $keys, $asList, $include = true)` | Keeps (or removes, with `$include = false`) the given keys. |
| `array_filter_keys_recursive(...)` | Same as `array_filter_keys`, applied to nested key structures. |
| `array_diff_keys($array1, $array2, $asList = false)` | Returns the (nested) entries of `$array1` whose keys are missing in `$array2`. |
| `array_equals($array1, $array2)` | Compares arrays regardless of key and value order. |
| `array_sort_keys($array)` | Sorts keys and renumbers integer keys. |
| `array_shift_assoc(&$array)` / `array_pop_assoc(&$array)` | Removes the first/last element and returns it as `[$key => $value]`. |
| `keys_array_to_assoc($keys)` | Turns `['a', 'b' => [...]]` into `['a' => 'a', 'b' => [...]]`. |
| `filter_vars($vars, $filter, $include = true)` | Filters keys by an array or comma separated string. |
| `implode_key_value_array($array, $kvDelimiter = "=", $itemDelimiter = ",")` | Implodes an (nested) array into `key=value` pairs. |
| `sort_list_array($array, $descending = true)` / `sort_list_string($string, $delimiter, $descending = true)` | Sorts items by length. |
| `is_subset_of($array1, $array2)` | `true` when all values of `$array1` are in `$array2`. |
| `select_objects_from_list($objects, $params)` | Returns objects whose properties strictly match `$params`, including nested ones. |

```php
array_get_key_value("user.name", ["user" => ["name" => "Ann"]]); // "Ann"

array_map_assoc(fn($k, $v) => [strtoupper($k), $v * 2], ["a" => 1]); // ["A" => 2]

array_diff_keys(["a" => 1, "b" => 2], ["a" => 1]); // ["b" => 2]
```

### Random and test data

| Function | Description |
| --- | --- |
| `random_token($length)` | Cryptographically secure hex string. |
| `random_password($length = 8)` | Cryptographically secure password with at least one letter, digit and symbol. |
| `random_string($length, $pool = "a-zA-Z")` | Cryptographically secure string from a character pool. |
| `generate_bytes($length)` | **Deprecated**, use `random_bytes($length)`. |
| `uuid4()` / `is_uuid4($input)` | Generates or validates a version 4 UUID. |
| `random_float($min, $max)` | Cryptographically secure float between `$min` and `$max` (inclusive). |
| `random_array_item($array)` | Random array item. **Not** secure, use for test data only. |
| `random_date($from = now, $to = null, $format = "Y-m-d H:i:s")` | Random date between two dates or relative times (`"+1 day"`). |
| `random_ip($version = "v4")`, `random_name()`, `random_firstname()`, `random_lastname()`, `random_email()` | Fake data for tests and fixtures. |

```php
random_token(16);                          // "9f86d081884c7d65"
random_date("-1 year", "now", "Y-m-d");   // "2026-03-14"
```

### Request helpers

| Function | Description |
| --- | --- |
| `get_host()` | Validated request host, see [Security notes](#security-notes). |
| `get_base_uri($path = true, $useHTTPS = null)` | Current URL, optionally without path. |
| `get_uri($useHTTPS = null)` | Current URL including path and query. |
| `get_uri_part($key)` | A `parse_url()` part of the current URL, e.g. `"path"` or `"query"`. |
| `isHTTPS()` | Whether the request uses HTTPS. |
| `useHTTPS()` | Redirects (301) to the HTTPS version of the current URL. |
| `get_client_ip($reliable = true)` | Client IP from `REMOTE_ADDR`; with `false` it also reads forwarding headers. |
| `get_host_ip()` | **Deprecated**, use `gethostbyname(gethostname())`. |
| `get_referer($includeQuery = true)` | Referer header, optionally without query string. |
| `get_user_agent()` | User agent header. |

### Parsing

| Function | Description |
| --- | --- |
| `parse_string_value($string)` | Parses a PHP-like literal: numbers, booleans, `null`, quoted strings, constants, `Class::CONSTANT` and arrays. |
| `parse_array_string($args)` | Parses a comma separated argument string, e.g. `"'a', 1, 'k' => true"`. |
| `constant_parse($input)` | Evaluates constant expressions with `&`, `\|` and parentheses, e.g. `"E_ERROR \| E_WARNING"`. |
| `is_json($input)` | **Deprecated**, use `json_validate($input)`. Unlike `json_validate`, returns `false` for `"null"`. |
| `json_decode_preserve_empty_objects($json)` | Decodes JSON to arrays but keeps `{}` as an object, so re-encoding gives the same JSON. |
| `is_binary($input)` | `true` when the string contains non printable characters. |
| `filename($path)` | **Deprecated**, use `pathinfo($path, PATHINFO_FILENAME)`. Unlike `pathinfo`, returns the full path when there is no extension. |

```php
parse_string_value("['a', 'b']");    // ["a", "b"]
parse_array_string("'k' => true");   // ["k" => true]

json_encode(json_decode_preserve_empty_objects('{"a":{},"b":[]}')); // '{"a":{},"b":[]}'
```

### Files, processes and misc

| Function | Description |
| --- | --- |
| `include_recursive($path, $extension = ".php")` | Includes all files with the extension in a directory tree. |
| `rmdir_recursive($dir)` | Deletes a directory and its contents; symlinks are removed, not followed. |
| `exec_stdout($cmd, $lineFormat = null)` | Runs a command, streams its output live and returns the lines (stdout, then stderr). CLI only. |
| `env($key, $throws = null)` | Reads an environment variable from `getenv()` or `$_ENV`. |
| `resolve_class($name, $namespace = null, $throws = true)` | Resolves a class name, optionally within a namespace. |
| `resolve_callable($callable, $class = null, $namespace = null, $throws = true)` | Resolves `"function"`, `"Class::method"` or `"Class->method"` to a callable. |
| `flag_id($domain = 0)` | Returns the next bit flag (1, 2, 4, ...) for a domain. |
| `bench_start()` / `bench_end($start, $name, $print = true)` | Simple timing. |

## Deprecated functions

Functions marked **Deprecated** have a built-in PHP equivalent. They still work and will be kept until the next major version; switch to the built-in when convenient.

## Utility classes

All classes live in the `gijsbos\ExtFuncs\Utils` namespace.

| Class | Description |
| --- | --- |
| `DotEnv` | Reads and writes the `.env` file in the working directory. `DotEnv::parse()` registers all values as environment variables. |
| `Environment` | `isDevelopment()`, `isProduction()`, `isTest()` based on `ENVIRONMENT`/`ENV`. |
| `Encrypter` | `encrypt()` / `decrypt()` with [defuse/php-encryption](https://github.com/defuse/php-encryption) and `ENC_KEY`. |
| `Hasher` | HMAC hashing (`sha256`, `sha512`) with `HASH_KEY`. |
| `FileHasher` | Hashes files or lists of files. |
| `SessionManager` / `CookieManager` | Small wrappers around `$_SESSION` and cookies. |
| `App` | Application bootstrap: timezone, error reporting and session settings. |
| `Functions` | Checks and executes callables from strings, e.g. `Functions::execute("strtoupper", ["a"])`. |
| `DocCommentParser` / `DocPropertyParser` | Parses doc comment annotations into arrays. |
| `TextParser`, `StringValueParser`, `StringValueCaster`, `StringCommandParser` | String parsing helpers used by the parsing functions. |
| `StringCompareJaroWinkler`, `SmithWatermanGotoh` | String similarity algorithms. |

```php
use gijsbos\ExtFuncs\Utils\DotEnv;
use gijsbos\ExtFuncs\Utils\Encrypter;

DotEnv::parse(); // loads .env

$secret = Encrypter::encrypt("hello");
Encrypter::decrypt($secret); // "hello"
```

## Security notes

- **Host header.** The `Host` header is sent by the client. `get_host()` rejects malformed hosts, but without `ALLOWED_HOSTS` any well formed host is accepted. Set `ALLOWED_HOSTS` in production when you build absolute URLs, such as password reset links, or when responses are cached.
- **Forwarding headers.** `isHTTPS()` and `get_client_ip(false)` trust `X-Forwarded-*` and `Client-IP` headers, which clients can set. Only rely on them behind a proxy you control.
- **Randomness.** Use `random_token`, `random_password`, `random_string` or `random_float` for secrets. `random_array_item`, `random_date` and `random_ip` are not cryptographically secure.
- **Untrusted input.** Don't pass user input to `parse_string_value`, `parse_array_string` or `constant_parse`: they resolve arbitrary constants and class properties. `StringCommandParser` is even more powerful: `<function;name;args>` calls any PHP function. Escape arguments passed to `exec_stdout` with `escapeshellarg()`.

## Running the tests

```bash
composer install
vendor/bin/phpunit
```

The test bootstrap creates a `.env` file with a generated `ENC_KEY` on first run.

## License

MIT
