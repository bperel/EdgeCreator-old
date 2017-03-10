<?php

class DmClient
{
    static $servers_file='servers.ini';

    /** @var $coa_server stdClass */
    static $coa_server = null;

    /** @var $dm_server stdClass */
    static $dm_server = null;

    static function initCoaServers()
    {
        self::$coa_server = null;
        self::$dm_server = null;
        $servers = parse_ini_file(BASEPATH.'../application/config/'.self::$servers_file, true);
        foreach ($servers as $name => $server) {
            $serverObject = json_decode(json_encode($server));
            if (isset($serverObject->role_passwords)) {
                $roles = [];
                array_walk($serverObject->role_passwords, function ($role) use (&$roles) {
                    list($roleName, $rolePassword) = explode(':', $role);
                    $roles[$roleName] = $rolePassword;
                });
                $serverObject->role_passwords = $roles;
            }
            if ($serverObject->complete_coa_tables) {
                self::$coa_server = $serverObject;
            } else {
                self::$dm_server = $serverObject;
            }
        }
    }

    /**
     * @param stdClass $server DmClient::$coa_server or DmClient::$dm_server
     * @param string $query
     * @return mixed|null
     */
    public static function get_query_results_from_remote($server, $query)
    {
        $db = $server === DmClient::$dm_server ? 'db301759616' : 'edgecreator';

        if ($server === DmClient::$dm_server) {
            $output = self::get_secured_page(self::$dm_server, 'sql.php?db=' . $db . '&req=' . urlencode($query));
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
        }
        else {
            return self::get_service_results(self::$coa_server, 'POST', '/rawsql', [
                'query' => $query,
                'db' => $db
            ]);
        }
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
    public static function get_service_results($server, $method, $path, $parameters = [])
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