<?php

class DmClient
{
    static $servers_file='servers.ini';

    /** @var $dm_server stdClass */
    static $dm_server = null;

    /** @var $dm_site stdClass */
    static $dm_site = null;

    static $chunkable_services = [
        '/coa/list/countries' => 50,
        '/coa/list/publications' => 10
    ];

    static function initCoaServers()
    {
        self::$dm_server = null;
        self::$dm_site = null;
        $servers = parse_ini_file(BASEPATH.'../application/config/'.self::$servers_file, true);

        foreach ($servers as $name => $server) {
            $serverObject = json_decode(json_encode($server));

            if ($serverObject->dm_server) {
                $roles = [];
                array_walk($serverObject->role_passwords, function ($role) use (&$roles) {
                    list($roleName, $rolePassword) = explode(':', $role);
                    $roles[$roleName] = $rolePassword;
                });
                $serverObject->role_passwords = $roles;
                self::$dm_server = $serverObject;
            } else {
                self::$dm_site = $serverObject;
            }
        }
    }

    /**
     * @param string $query
     * @return mixed|null
     */
    public static function get_query_results_from_dm_site($query) {
        $output = self::get_secured_page(self::$dm_site, 'sql.php?db=' . DmClient::$dm_site->db_name . '&req=' . urlencode($query));
        $unserialized = @unserialize($output);
        if (is_array($unserialized)) {
            list($champs,$resultats) = $unserialized;
            foreach($champs as $i_champ=>$nom_champ) {
                foreach($resultats as $i=>$resultat) {
                    $resultats[$i][$nom_champ]=$resultat[$nom_champ];
                }
            }
            return $resultats;
        }
        return [];
    }

    /**
     * @param string $query
     * @param $db
     * @return mixed|null
     * @internal param stdClass $server DmClient::$coa_server or DmClient::$dm_server
     */
    public static function get_query_results_from_dm_server($query, $db)
    {
        return self::get_service_results(self::$dm_server, 'POST', '/rawsql', [
            'query' => $query,
            'db' => $db
        ]);
    }

    private static function get_page($url) {
        $handle = @fopen($url, "r");

        if ($handle) {
            $buffer="";
            while (!feof($handle)) {
                $buffer.= fgets($handle, 4096);
            }
            fclose($handle);
            return $buffer;
        }
        else {
            return null;
        }
    }

    private static function get_secured_page(stdClass $dmServer, $url) {
        return self::get_page(implode('/', ['http://'.$dmServer->ip, $dmServer->web_root, $url.'&mdp='.sha1($dmServer->db_password)]));
    }

    /**
     * @param stdClass $server DmClient::$coa_server or DmClient::$dm_server
     * @param $method
     * @param $path
     * @param array $parameters
     * @return mixed|null
     */
    public static function get_service_results($server, $method, $path, $parameters = [], $do_not_chunk = false)
    {
        $role = 'rawsql';
        $ch = curl_init();
        $url = 'http://'.$server->ip . '/' . $server->web_root . $path;

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
                break;
            case 'GET':
                if (count($parameters) > 0) {
                    if (!$do_not_chunk && count($parameters) === 1 && isset(self::$chunkable_services[$path])) {
                        $parameterListChunks = array_chunk(explode(',', $parameters[count($parameters) -1]), self::$chunkable_services[$path]);
                        $results = [];
                        foreach ($parameterListChunks as $parameterListChunk) {
                            $results = array_merge(
                                $results,
                                self::get_service_results($server, $method, $path, [implode(',', $parameterListChunk)], true)
                            );
                        }
                        return $results;
                    }
                    $url .= '/' . implode('/', $parameters);
                }
                break;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $method === 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = [
            'Authorization: Basic ' . base64_encode(implode(':', [$role, $server->role_passwords[$role]])),
            'Content-Type: application/x-www-form-urlencoded',
            'Cache-Control: no-cache',
            'x-dm-version: 1.0',
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $buffer = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!empty($buffer) && $responseCode >= 200 && $responseCode < 300) {
            $results = json_decode($buffer, true);
            if (is_array($results)) {
                return $results;
            }
        }

        return [];
    }
}