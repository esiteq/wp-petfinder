<?php
// Petfinder API v2
/*
 * Created on: 2019-10-27
 * Author: Alex Raven
 * Description: Petfinder.com API v2 implementation with caching to reduce API calls
 */

namespace WP_Petfinder;

class Petfinder_API
{
    protected $token = '';
    protected $token_expires = 0;
    public    $response;
    public    $api_key, $api_secret;
    /**
     * Petfinder_API::__construct()
     *
     * @param string $api_key
     * @param string $api_secret
     * @return void
     */
    public function __construct($api_key, $api_secret)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }
    /**
     * Petfinder_API::get_description()
     * Get Full animal description
     * @param string $url
     * @return string
     */
    public function get_description($url)
    {
        $result = wp_remote_get($url);
        if (!is_wp_error($result) && $result['response']['code'] == 200)
        {
            $html = $result['body'];
            $tmp = explode('data-test="Pet_Story_Section">', $html);
            if (is_array($tmp) && count($tmp) == 2)
            {
                $html = $tmp[1];
                $tmp = explode('<div class="u-vr4x">', $html);
                if (is_array($tmp))
                {
                    $html = $tmp[1];
                    $tmp = explode('</div>', $html);
                    if (is_array($tmp))
                    {
                        $html = trim($tmp[0]);
                        $html = str_replace('<br />', "\n", $html);
                        $html = str_replace("\n\n", "\n", $html);
                        return $html;
                    }
                }
            }
        }
        return '';
    }
    /**
     * Petfinder_API::remote_get()
     *
     * @param mixed $url
     * @param mixed $args
     * @return
     */
    private function remote_get($url, $args = [])
    {
        $headers = [
            'Authorization' => 'Bearer '. $this->get_token()
        ];
        $param = [
            'headers' => $headers
        ];
        if (is_array($args))
        {
            $param = array_merge($param, $args);
        }
        $response = wp_remote_get($url, $param);
        if (is_wp_error($response))
        {
            return false;
        }
        $this->response = $response;
        if ($response['response']['code'] == 200)
        {
            return json_decode($response['body'], JSON_OBJECT_AS_ARRAY);
        }
        else
        {
            return false;
        }
        return $result;
    }
    /**
     * Petfinder_API::get_animal_breeds()
     *
     * @param string $type
     * @return
     */
    function get_animal_breeds($type = 'dog')
    {
        // pull from the API
        $response = $this->remote_get('https://api.petfinder.com/v2/types/'. esc_attr($type). '/breeds');
        if (isset($response['breeds']))
        {
            $tmp = $response['breeds'];
            $breeds = [];
            if (is_array($tmp))
            {
                foreach ($tmp as $breed)
                {
                    $breeds[] = $breed['name'];
                }
            }
        }
        return $breeds;
    }
    /**
     * Petfinder_API::get_organizations()
     *
     * @param mixed $args
     * @return
     */
    public function get_organizations($args = [])
    {
        // pull from the API
        $param = http_build_query($args);
        $params = !empty($args) ? '?'. $param : '';
        $response = $this->remote_get('https://api.petfinder.com/v2/organizations'. $params);
        return $response;
    }
    /**
     * Petfinder_API::get_animals()
     *
     * @param array $args
     * @return array
     */
    public function get_animals($args = [])
    {
        // pull from the API
        $param = http_build_query($args);
        $params = !empty($args) ? '?'. $param : '';
        $response = $this->remote_get('https://api.petfinder.com/v2/animals'. $params);
        return $response;
    }
    /**
     * Petfinder_API::get_organization()
     * Get organization by id
     * @param string $id
     * @return array
     */
    public function get_organization($id)
    {
        $response = $this->remote_get('https://api.petfinder.com/v2/organizations/'. $id);
        $organization = false;
        if (is_array($response['organization']))
        {
            $organization = $response['organization'];
        }
        return $organization;
    }
    /**
     * Petfinder_API::get_animal()
     * Returns Animal details by id
     * @param integer $id
     * @return array
     */
    public function get_animal($id)
    {
        $response = $this->remote_get('https://api.petfinder.com/v2/animals/'. $id);
        $animal = false;
        if (is_array($response['animal']))
        {
            $animal = $response['animal'];
        }
        return $animal;
    }
    /**
     * Petfinder_API::get_animal_types()
     * Returns animal types from Petfinder
     * @return array
     */
    public function get_animal_types()
    {
        $types = $this->remote_get('https://api.petfinder.com/v2/types');
        if (isset($types['types']))
        {
            $types = $types['types'];
        }
        else
        {
            $types = [];
        }
        return $types;
    }
    /**
     * Petfinder_API::get_token()
     * Returns Petfinder OAuth token
     * @return
     */
    private function get_token()
    {
        if (!empty($this->token))
        {
            return $this->token;
        }
        $this->token = get_transient('pf_access_token');
        $expires = 3600;
        //unset($this->token);
        if (!$this->token)
        {
            $args = [
                'body' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->api_key,
                    'client_secret' => $this->api_secret
                ]
            ];
            $response = wp_remote_post('https://api.petfinder.com/v2/oauth2/token', $args);
            if ($response['response']['code'] == 200)
            {
                $json = json_decode($response['body'], JSON_OBJECT_AS_ARRAY);
                if (is_array($json))
                {
                    $this->token = $json['access_token'];
                    $expires = $json['expires_in'];
                }
            }
            else
            {
                $this->token = '';
            }
            set_transient('pf_access_token', $this->token, $expires);
        }
        return $this->token;
    }
}
?>