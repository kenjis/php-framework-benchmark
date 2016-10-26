<?php

/*
|--------------------------------------------------------------------------
| MongoDB session handler
|--------------------------------------------------------------------------
*/

namespace Core\Models;

class SessionsMongoDb
{
    private $st = null; // session table
    private $data = null;
    private $salt = null;

    public function __construct(&$mdb)
    {
        // Secure our sessions a little bit more
        session_name('SSSSS');
        ini_set('session.use_only_cookies', true);

        ini_set('session.entropy_file', '/dev/urandom');

        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);

        ini_set('session.gc_maxlifetime', 432000*4);
        ini_set('session.cookie_lifetime', 432000*4);

        ini_set('session.hash_function', 'sha512');
        ini_set('session.hash_bits_per_character', 5);

        // Set some variables
        $this->st = $mdb->sessions;
        $this->salt = md5($_SERVER['HTTP_USER_AGENT']);

        $this->prefix = session_name();
        $this->expire = session_cache_expire() * 60;

        // Register session handler
        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );
        session_start();
    }

    public function open()
    {
        // Do nothing
    }

    public function close()
    {
        // Do nothing
    }

    public function read($id)
    {
        $this->data = $this->st->find(['id' => $id, 'check' => $this->salt])->fields(['data' => true])->getNext();

        return (empty($this->data) ? null : $this->data['data']);
    }

    public function write($id, $data)
    {
        $this->data['id'] = $id;
        $this->data['data'] = $data;
        $this->data['check'] = $this->salt;
        $this->data['expires'] = time();
        $this->st->save($this->data);

        return true;
    }

    public function destroy($id)
    {
        $this->st->remove(['id' => $id]);
        // Also delete the cookie
        if (headers_sent() == false) {
            setcookie($this->prefix, '', time() - 1, '/');
        }

        return true;
    }

    public function gc($max)
    {
        $this->st->remove(['expires' => ['$lt' => (time() - $max)]]);

        return true;
    }
}
