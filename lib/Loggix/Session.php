<?php
/**
 * Class to handle browser sessions. Stores session data in a database using PDO.
 * 
 * Requires PHP 5.2 or later
 * 
 * @package   Loggix
 * @author    Walter Ebert (http://www.walterebert.com)
 * @author    Loggix Project
 * @copyright Copyright (c) 2008 Walter Ebert
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://www.walterebert.com/code/session-pdo.html
 * @version   9.7.27
 */
// Loggix_Session class is based on we_sessionPdo class ver 0.9 by Walber Ebert.

class Loggix_Session
{
    /**
     * Database connection
     * 
     * @access protected
     * @var    object
     */
    protected $db = null;

    /**
     * Use database transaction
     * 
     * @access protected
     * @var    boolean
     */
    protected $transaction = false;

    /**
     * Regenerate session ID
     * 
     * @access protected
     * @var    boolean
     */
    protected $regenerate_id = false;

    /**
     * Session Time
     *
     * @access protected
     * @var    integer
     */
    protected $maxlifetime = 21600; // 3600 * 6 = 6 hours
    
    /**
     * Constructor
     *
     * @access public
     * @param  object  $pdo         PDO database object
     * @param  boolean $transaction Use database transactions [optional]
     * @param  boolean $sessionName Session name [optional]
     * @return void
     */
    public function __construct($pdo, $transaction = false, $sessionName = 'PDOSESSID')
    {
        // Set database connection
        $this->db = $pdo;
        if ($transaction) {
            $this->transaction = true;
            $this->db->beginTransaction();
        }

        // Start session
        session_set_save_handler(array(__CLASS__, '_open'),
                                 array(__CLASS__, '_close'),
                                 array(__CLASS__, '_read'),
                                 array(__CLASS__, '_write'),
                                 array(__CLASS__, '_destroy'),
                                 array(__CLASS__, '_gc'));
        session_name($sessionName);
        session_start();
    }

    /**
     * Regenerate session ID after the session call.
     *
     * @access public
     * @return void
     */
    public function regenerate_id()
    {
        $this->regenerate_id = true;
    }

    /**
     * Destroy session, session data and session cookie.
     * 
     * @access public
     * @return void
     */
    public function destroy()
    {
        $_SESSION = array();

        if( isset($_COOKIE[session_name()]) ) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', 1, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    /**
     * Get raw session data from database.
     *
     * @access protected
     * @param  string $id Session ID
     * @return array or false
     */
    protected function _fetchSession($id)
    {
        
        // Set session time
        // $sessionTime = 3600 * 3; // 3 hours
        $sessionTime = $this->maxlifetime;
        
        $sql  = 'SELECT '
              .     'id, sess_var '
              . 'FROM '
              .     'loggix_session '
              . 'WHERE '
              .     'id = :id '
              . 'AND '
              .     'sess_date > :sess_date';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(
                   array(
                       ':id' => $id, 
                       ':sess_date' => (time() - (int)$sessionTime)
                   )
               );
        $sessions = $stmt->fetchAll();

        return empty($sessions) ? false : $sessions[0] ;
    }

    /**
     * Open session. Not relevant to this class.
     *
     * @access protected
     * @param  $savePath
     * @param  $sessionName
     * @return true
     */
    protected function _open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * Close session. Not relevant to this class.
     *
     * @access protected
     * @return true
     */
    protected function _close()
    {
        return true;
    }

    /**
     * Read session data.
     *
     * @access protected
     * @param  string $id Session ID
     * @return string or false
     */
    protected function _read($id)
    {
        $session = $this->_fetchSession($id);

        return ($session == false) ? false : $session['sess_var'] ;
    }

    /**
     * Write session data.
     *
     * @access protected
     * @param  string $id          Session ID
     * @param  string $sessionData Session data
     * @return void
     */
    protected function _write($id, $sessionData)
    {
        $session = $this->_fetchSession($id);
        if ($session == false) {
            $insertSql = 'INSERT INTO '
                       .     'loggix_session '
                       .     '(id, sess_var, sess_date) '
                       . 'VALUES '
                       .     '(:id, :data, :time)';
            $stmt = $this->db->prepare($insertSql);
        } else {
            $updateSql = 'UPDATE '
                       .    'loggix_session '
                       . 'SET '
                       .     'sess_var = :data, '
                       .     'sess_date = :time '
                       . 'WHERE '
                       .     'id = :id';
            $stmt = $this->db->prepare($updateSql);
        }
        $stmt->execute(
                   array(
                       ':id'   => $id, 
                       ':data' => $sessionData, 
                       ':time' => time()
                   )
               );
    }

    /**
     * Destroy session.
     *
     * @access protected
     * @param  string $id Session ID
     * @return void
     */
    protected function _destroy($id)
    {

        $stmt = $this->db->prepare('DELETE FROM loggix_session WHERE id = :id');
        $stmt->execute(
                   array(
                       ':id' => $id
                   )
               );
    }

    /**
     * Garbage collection.
     *
     * @access protected
     * @param  integer $maxlifetime Maximum session life time
     * @return void
     */
    protected function _gc($maxlifetime = 21600)
    {

        $stmt = $this->db->prepare('DELETE FROM loggix_session WHERE sess_date < :time');
        $stmt->execute(
                   array(
                       ':time' => (time() - (int)$maxlifetime)
                   )
               );
    }

    /**
     * Destructor
     *
     * @access public 
     * @return void
     */
    public function __destruct()
    {
        // Create new session ID
        if ($this->regenerate_id) {
            session_regenerate_id(true);
        }

        // Close session
        session_write_close();
        if ($this->transaction) {
            $this->db->commit();
        }
    }
}
