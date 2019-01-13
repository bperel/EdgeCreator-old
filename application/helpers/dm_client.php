<?php

class DmClient
{
    static $servers_file='servers.ini';

    /** @var $dm_server stdClass */
    static $dm_server;

    static $chunkable_services = [
        '/coa/list/countries' => 50,
        '/coa/list/publications' => 10
    ];

    static $userData;

    static function init($userdata)
    {
        self::$userData = $userdata;
        self::$dm_server = null;
        $servers = parse_ini_file(APPPATH.'config/'.self::$servers_file, true);

        foreach ($servers as $name => $server) {
            $serverObject = json_decode(json_encode($server));

            $roles = [];
            array_walk($serverObject->role_passwords, function ($role) use (&$roles) {
                list($roleName, $rolePassword) = explode(':', $role);
                $roles[$roleName] = $rolePassword;
            });
            $serverObject->role_passwords = $roles;
            self::$dm_server = $serverObject;
        }
    }

    /**
     * @param string $query
     * @param        $db
     * @return mixed|null
     * @throws Exception
     */
    public static function get_query_results_from_dm_server($query, $db)
    {
        return self::get_service_results(self::$dm_server, 'POST', '/rawsql', [
            'query' => $query,
            'db' => $db
        ], 'rawsql');
    }

    /**
     * @param stdClass $server
     * @param string   $method
     * @param string   $path
     * @param array    $parameters
     * @return array|null|stdClass
     * @throws Exception
     */
    public static function get_service_results_ec($server, $method, $path, $parameters = []) {
        return self::get_service_results($server, $method, $path, $parameters, 'edgecreator');
    }

    /**
     * @param stdClass $server
     * @param string $method
     * @param string $path
     * @param array $parameters
     * @param string $role
     * @param bool $do_not_chunk
     * @return array|null|stdClass
     * @throws Exception
     */
    private static function get_service_results($server, $method, $path, $parameters = [], $role = 'rawsql', $do_not_chunk = false)
    {
        $ch = curl_init();
        $url = 'http://'.$server->ip . $server->web_root . $path;

        if ($method === 'GET') {
            if (count($parameters) > 0) {
                if (!$do_not_chunk && count($parameters) === 1 && isset(self::$chunkable_services[$path])) {
                    return self::get_chunkable_service_results($server, $method, $path, $parameters, $role);
                }
                $url .= '/' . implode('/', $parameters);
            }
        }
        else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = [
            'Authorization: Basic ' . base64_encode(implode(':', [$role, $server->role_passwords[$role]])),
            'Content-Type: application/x-www-form-urlencoded',
            'Cache-Control: no-cache',
            'x-dm-version: 1.0',
        ];
        if (array_key_exists('user', self::$userData)) {
            $headers[] = 'x-dm-user: ' . self::$userData['user'];
            $headers[] = 'x-dm-pass: ' . self::$userData['pass'];
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $buffer = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($buffer === 'null' || (empty($buffer) && $responseCode === 204)) {
            return null;
        }

        if (!empty($buffer) && $responseCode >= 200 && $responseCode < 300) {
            $results = json_decode($buffer);
            if (is_array($results) || is_object($results)) {
                return $results;
            }
        }

        ErrorHandler::error_log_and_exception('Call to service '.$method.' '.$server->web_root . $path. ' failed', "Response code = $responseCode, response buffer = $buffer");
        return null;
    }

    /**
     * @param stdClass $server
     * @param string   $method
     * @param string   $path
     * @param array    $parameters
     * @param string   $role
     * @return array|null|stdClass
     * @throws Exception
     */
    private static function get_chunkable_service_results($server, $method, $path, $parameters, $role)
    {
        $parameterListChunks = array_chunk(explode(',', $parameters[count($parameters) - 1]), self::$chunkable_services[$path]);
        $results = null;
        foreach ($parameterListChunks as $parameterListChunk) {
            $result = self::get_service_results($server, $method, $path, [implode(',', $parameterListChunk)], $role, true);
            if (is_object($result)) {
                if (is_null($results)) {
                    $results = $result;
                } else {
                    $results = (object)array_merge_recursive((array)$results, (array)$result);
                }
            } else if (is_array($result)) {
                if (is_null($results)) {
                    $results = array();
                } else {
                    $results = array_merge($results, $result);
                }
            }
        }
        return $results;
    }
}
