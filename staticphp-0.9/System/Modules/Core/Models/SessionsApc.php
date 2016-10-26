<?php

/*
|--------------------------------------------------------------------------
| Apc session class
|
| Extends sessions class as an optional backup
|--------------------------------------------------------------------------
*/

namespace Core\Models;

class SessionsApc extends Sessions
{
    private $db_link = false;

    public function __construct(&$db_link = null)
    {
        $this->db_link = &$db_link;
        parent::__construct($this->db_link);
    }

    public function read($id)
    {
        $data = apc_fetch($this->prefix.$id);
        if (!empty($data)) {
            return $data;
        }

        return (!empty($this->db_link) ? parent::read($id) : null);
    }

    public function write($id, $data)
    {
        apc_store($this->prefix.$id, $data, $this->expire);
        if (!empty($this->db_link)) {
            parent::write($id, $data);
        }

        return true;
    }

    public function destroy($id)
    {
        apc_delete($this->prefix.$id);
        if (!empty($this->db_link)) {
            parent::destroy($id);
        }

        return true;
    }
}
