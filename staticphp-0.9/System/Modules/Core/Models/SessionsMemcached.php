<?php

/*
|--------------------------------------------------------------------------
| Memcached session class
|
| Extends sessions class as an optional backup
|--------------------------------------------------------------------------
*/

namespace Core\Models;

class SessionsMemcached extends Sessions
{
    private $memcached = null;
    private $db_link = false;

    public function __construct(&$memcached, &$db_link = null)
    {
        $this->memcached = $memcached;
        $this->db_link = &$db_link;
        parent::__construct($this->db_link);
    }

    public function read($id)
    {
        $data = $this->memcached->get($this->prefix.$id);
        if (!empty($data)) {
            return $data;
        }

        return (!empty($this->db_link) ? parent::read($id) : null);
    }

    public function write($id, $data)
    {
        $this->memcached->set($this->prefix.$id, $data, $this->expire);
        if (!empty($this->db_link)) {
            parent::write($id, $data);
        }

        return true;
    }

    public function destroy($id)
    {
        $this->memcached->delete($this->prefix.$id);
        if (!empty($this->db_link)) {
            parent::destroy($id);
        }

        return true;
    }
}
