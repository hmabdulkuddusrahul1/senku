<?php 

namespace Mateodioev\Senku\Models;

use Mateodioev\Utils\Exceptions\{KeyArrayException, RequestException};
use RuntimeException, Exception, stdClass;

use function header, in_array, array_keys, file_get_contents, json_decode, http_response_code, is_array, json_encode;


/**
 * Get and send response data
 */
class Response
{
  public string $default_content_type = 'application/json';

  /**
   * Get headers from request
   * @throws RuntimeException|KeyArrayException
   */
  public static function getHeader(string $key=null, $default = null)
  {
    $headers = getallheaders();
    $header = $headers[$key] ?? $default;

    if (empty($headers)) {
      throw new RuntimeException('Empty Headers', 400);
    } elseif ($header === null) {
      throw new KeyArrayException("Key $key not found", 404);
    } else {
      return $header;
    }
  }

  /**
   * Return user-agent and ip
   */
  public static function getResquestDatas(): array
  {
    $user_agent = self::getHeader('User-Agent', 'Unknow');
    $ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['HTTP_X_FORWARDED'] ?? $_SERVER['HTTP_X_CLUSTER_CLIENT_IP']
    ?? $_SERVER['HTTP_FORWARDED_FOR'] ?? $_SERVER['HTTP_FORWARDED'] ?? 'Unknow';

    return compact('user_agent', 'ip');
  }

  /**
   * Send http headers
   */
  public static function sendHeader(string $key, string $value, bool $replace = false): void
  {
    if (!headers_sent()) {
      header($key . ':' . $value, $replace);
    }
  }

  /**
   * Get input params from `$_GET`, `$_POST` y `$_REQUEST`, return null of default value if the key not found
   * @throws KeyArrayException
   */
  public static function getKey(string $key, $default=null, array $alloweds=null): string
  {
    $value = $_GET[$key] ?? $_POST[$key] ?? $_REQUEST[$key] ?? $default;

    if ($alloweds !== null && !in_array($value, $alloweds)) {
      throw new KeyArrayException("Invalid value for {$key}", 400);
    }
    return $value;
  }

  /**
   * Get json postbody string
   * @throws RequestException
   */
  public static function getJsonPost(bool $toArray = false): array|stdClass
  {
    $payload = array_keys($_POST)[0] ?? file_get_contents('php://input') ?? '';

    if (empty($payload)) {
      throw new RequestException('Empty json body', 400);
    } else {
      $json = json_decode($payload, $toArray);
      if ($json === null) {
        throw new Exception('Json-decode fail: ' . json_last_error_msg(), 500);
      } else {
        return $json;
      }
    }
  }

  public function Send(array|string $content, int $http_code): never
  {
    self::sendHeader('Content-Type', $this->default_content_type, true);
    http_response_code($http_code);
    
    if (is_array($content)) {
      $content = json_encode($content, JSON_PRETTY_PRINT);
    }
    echo $content;
    die;
  }

  /**
   * Send 200(OK) response
   */
  public function sendSuccess(array $data)
  {
    $this->Send([
      'ok' => true,
      'data' => $data
    ], 200);
  }

  /**
   * Send preformatted error response
   */
  public function sendError(string $error, int $error_code = 400)
  {
    $this->Send([
      'ok' => false,
      'error' => [
        'code' => $error_code,
        'message' => $error
      ]
    ], $error_code);
  }

}
