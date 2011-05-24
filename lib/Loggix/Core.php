<?php
/**
 * Loggix_Core - Core class for the log creation.
 *
 * PHP version 5
 *
 * @package   Loggix
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.16
 * @version   11.5.24
*/

/**
 * Include Config, Constants, and View Class
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'View.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Utility.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Plugin.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Session.php';

/*{{{ Database Tables Definitions */
define('CONFIG_TABLE',            'loggix_config');
define('USER_TABLE',              'loggix_user');
define('LOG_TABLE',               'loggix_log');
define('LOG_TAG_TABLE',           'loggix_log_tag');
define('LOG_TAG_MAP_TABLE',       'loggix_log_tag_map');
define('DOWNLOADS_META_TABLE',    'loggix_downloads_meta');
define('DOWNLOADS_DATA_TABLE',    'loggix_downloads_data');
define('DOWNLOADS_TAG_TABLE',     'loggix_downloads_tag');
define('DOWNLOADS_TAG_MAP_TABLE', 'loggix_downloads_tag_map');
define('SESSION_TABLE',           'loggix_session');
define('ACCESSLOG_TABLE',         'loggix_access');
define('TRACKBACK_TABLE',         'loggix_trackback');
define('COMMENT_TABLE',           'loggix_comment');
/*}}}*/

/**
 * Loggix Core Class
 *
 * @package   Loggix
 */
class Loggix_Core
{
    /**
     * Base Settings
     *
     * Database support status
     * ------------------------
     * SQLite2 : stable
     * SQLite3 : stable
     * MySQL   : unstable
     *
     * SQLite3 is recommended. If you want to use SQLite2, just remove SQLite3
     * database and SQLite2 database will be loaded automatically.
     */
   
    const LOGGIX_DATABASE_TYPE    = 'SQLite'; // SQLite | MySQL
    const LOGGIX_SQLITE_2         = '/data/loggix.sqlite.db';  // SQLite type 2
    const LOGGIX_SQLITE_3         = '/data/loggix.sqlite3.db'; // SQLite type 3
    const LOGGIX_MYSQL_HOST       = '127.0.0.1'; // MySQL hostname : "localhost" and "127.0.0.1" are common.
    const LOGGIX_MYSQL_DBNAME     = 'loggix';    // MySQL database name
    const LOGGIX_MYSQL_USER       = 'root';      // MySQL user name
    const LOGGIX_MYSQL_PASS       = '';          // MySQL user password
    const LOGGIX_RESOURCE_DIR     = '/data/resources/';
    const LOGGIX_MODULE_DIR       = '/lib/Loggix/Module/';
    const LOGGIX_PLUGIN_DIR       = '/plugins/';
    const LOGGIX_THEME_DIR        = '/theme/';
    const LOGGIX_TITLE_SEPARATOR  = ' : ';
    const LOGGIX_PROJ_URI         = 'https://github.com/kaz6120/Loggix';
    const LOGGIX_VERSION          = '11.5.24';
    

    // {{{ Properties

    /**
     * @var object
     * @see __construct()
     */
    //public static $db;
    public $db;

    /**
     * @var object
     * @see __construct()
     */
    //public static $plugin;
    public $plugin;

    /**
     * @var float
     * @see __construct()
     * @see Loggix_Utility::getGenerationTime()
     */
    public static $startTime;

    /**
     * @var array
     * @see __construct()
     */
    public static $config;
    
    
    /**
     * @var object
     * @see Loggix_Utility
     */    
    private static $_delegate = null;
    
    // }}}
   
    /**
     * Constructor : Open datbase and load functions and modules.
     *
     * @uses _includeModules()
     * @uses _includePlugins()
     * @uses getLanguage()
     */
    public function __construct()
    {
        global $pathToIndex, $lang, $module, $item;
        
        try {
            // Switch database
            switch (self::LOGGIX_DATABASE_TYPE) {
                case 'MySQL' :
                    $this->db = new PDO(
                                    'mysql:'
                                    . 'host='   . self::LOGGIX_MYSQL_HOST . ';'
                                    . 'dbname=' . self::LOGGIX_MYSQL_DBNAME, 
                                    self::LOGGIX_MYSQL_USER, 
                                    self::LOGGIX_MYSQL_PASS
                                );
                    break;
                default :
                    $sqlite2 = $pathToIndex . self::LOGGIX_SQLITE_2;
                    $sqlite3 = $pathToIndex . self::LOGGIX_SQLITE_3;        
                    $dbPath  = (file_exists($sqlite3))
                             ? 'sqlite:'  . $sqlite3
                             : 'sqlite2:' . $sqlite2;
                    $this->db = new PDO($dbPath);
            }

        } catch (PDOException $exception){
            die($exception->getMessage());
        }
        
        self::$startTime = microtime();
        self::$config = $this->getConfigArray();
        
        $this->_includeModules();
        $lang = $this->getLanguage();
        
        $this->plugin = new Loggix_Plugin;
        $this->_includePlugins();

    }

    
    /**
     * Relay the method call to Loggix_Utility
     *
     * @uses  call_user_func_array()
     */
    public function __call($method, $args)
    {
        $this->_delegate = new Loggix_Utility;
        return call_user_func_array(array($this->_delegate, $method), $args);
    }

    /**
     * Get Config settings
     *
     * @return array
     */
    public function getConfigArray()
    {
        $res = $this->db->query('SELECT * FROM ' . CONFIG_TABLE);
        $confArray = $res->fetchAll();
        foreach ($confArray as $row) {
            $config[$row['config_key']] = $row['config_value'];
        }
        return $config;
    }
    
    /**
     * Load Modules in /lib/Loggix/Module/ directory.
     *
     * @return void
     */
    private function _includeModules()
    {
        global $pathToIndex, $module;
        
        foreach (scandir($pathToIndex . self::LOGGIX_MODULE_DIR) as $moduleFile) {
            if (($moduleFile != '.')  && 
                ($moduleFile != '..') && 
                (strpos($moduleFile, '.php'))) {
                include_once $pathToIndex . self::LOGGIX_MODULE_DIR . $moduleFile;
            }
        }
    }


    /**
     * Load Modules in /plugins/ directory.
     *
     * @return void
     */
    private function _includePlugins()
    {
        global $pathToIndex, $module;
        
        foreach (scandir($pathToIndex . self::LOGGIX_PLUGIN_DIR) as $pluginFile) {
            if (($pluginFile != '.')  && 
                ($pluginFile != '..') && 
                (strpos($pluginFile, '.php'))) {
                include_once $pathToIndex . self::LOGGIX_PLUGIN_DIR . $pluginFile;
            }
        }
    }

    /**
     * Get Language File in /lang/ directory.
     *
     * @return array $lang
     */
    protected static function getLanguage()
    {
        global $pathToIndex;
        $lang = array();
        include $pathToIndex . '/lang/' . self::$config['language'] . '.lang.php';
        return $lang;
    }


    /**
     * Get Reqeust URI
     *
     * @return string
     */
    public static function getRequestUri()
    {
        return 'http' . ((!empty($_SERVER['HTTPS'])) ? 's' : '') . '://'
             . $_SERVER['HTTP_HOST'] 
             . $_SERVER['SCRIPT_NAME'] 
             . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
    }


    /**
     * Get Root URI
     *
     * @return string
     */
    public static function getRootUri()
    {
        return 'http' . ((!empty($_SERVER['HTTPS'])) ? 's' : '') .
               '://' . $_SERVER['HTTP_HOST'] . self::$config['root_dir'];
    }
    
    /**
     * Session Control
     *
     * @return string $sessionState :== 'on' | 'off'
     */

    public function getSessionState() 
    {
        global $pathToIndex, $dsn, $sessionState;

        $session = new Loggix_Session($this->db);

        /**
         * If user name and passoword is posted,
         * (1) Check if it is the user registered in user database.
         * (2) If the user exists on user database, authorize as a session user.
         */
        if (isset($_POST['user_name'], $_POST['user_pass'])) {
            $_POST['user_pass'] = sha1($_POST['user_pass']);
            $userName = $_POST['user_name'];
            $userPass = $_POST['user_pass'];
            $sql = 'SELECT '
                 .     'COUNT(user_id) '
                 . 'FROM ' 
                 .     USER_TABLE . ' '
                 . 'WHERE '
                 .     '(user_pass = :user_pass)'
                 .     ' AND '
                 .     '(user_name = :user_name)';
            $stmt = $this->db->prepare($sql);
            $stmt->execute(
                       array(
                           ':user_pass' => $userPass,
                           ':user_name' => $userName
                       )
                   );
            if ($stmt->fetchColumn() == 1) {
                $_SESSION['user_name'] = $userName;
                $_SESSION['user_pass'] = $userPass;
            }
        }        
        
        /**
         * If there is a session variables,
         * (1) Check if it is the user registered in user database.
         * (2) If it is OK, return status = on.
         * (3) If it is not OK, return status = off.
         */
        if (isset($_SESSION['user_name'], $_SESSION['user_pass'])) {
            $userName = $_SESSION['user_name'];
            $userPass = $_SESSION['user_pass'];
            $sql = 'SELECT '
                 .     'COUNT(user_id) '
                 . 'FROM ' 
                 .     USER_TABLE . ' '
                 . 'WHERE '
                 .     '(user_pass = :user_pass)'
                 .     ' AND '
                 .     '(user_name = :user_name)';
            $stmt = $this->db->prepare($sql);
            $stmt->execute(
                       array(
                           ':user_pass' => $userPass,
                           ':user_name' => $userName
                       )
                   );
            $sessionState = ($stmt->fetchColumn() == 1) 
                          ? 'on' 
                          : $this->getOutOfSession();
        } else {
            $sessionState = $this->getOutOfSession();
        }
        return $sessionState;
    }


    /**
     * Get out of session
     *
     * @return string $sessionState
     */
    public static function getOutOfSession()
    {
         $_SESSION = array();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }

        return $sessionState = 'off';
    }
    
    
    /**
     * Get Admin Menu
     *
     * @param  string $sessionState
     * @uses   getMenuList
     * @uses   setXmlLanuguage
     * @return string
     */
    public function getAdminMenu($sessionState)
    {
    
        global $pathToIndex, $lang, $item, $id;

        $manLang = $this->setXmlLanguage();
        
        // Check drafts
        $sql  = 'SELECT '
              .     'COUNT(l.id) '
              . 'FROM ' 
              .     LOG_TABLE . ' AS l '
              . 'WHERE '
              .     "l.draft = '1'";
        $res  = $this->db->query($sql);
        $rows = $res->fetchColumn();
        $numberOfDrafts = $lang['draft'] . ' (' . $rows . ')';
        
        // Check Downloads drafts
        $sql2  = 'SELECT '
               .     'COUNT(dlm.id) '
               . 'FROM ' 
               .     DOWNLOADS_META_TABLE . ' AS dlm '
               . 'WHERE '
               .     "dlm.draft = '1'";
        $res2  = $this->db->query($sql2);
        $rows2 = $res2->fetchColumn();
        $numberOfDownloadDrafts = $lang['dl_draft'] . ' (' . $rows2 . ')';
        
        // Admin menu list
        $menuList1 = array(
            $lang['logout']         => 'admin/login.php?status=logout',
            $lang['manage_users']   => 'admin/users.php'
            
        );

        $menuList2 = array(
            $lang['new_log']        => 'admin/write.php',
            $numberOfDrafts         => 'admin/drafts.php',
            $lang['log_tag']        => 'admin/tags.php',
            $lang['resources']      => 'admin/resources.php'
        );

        $menuList3 = array(
            $lang['new_dl']         => 'modules/downloads/admin/write.php',
            $numberOfDownloadDrafts => 'modules/downloads/admin/drafts.php',
            $lang['dl_tag']         => 'modules/downloads/admin/tags.php',
            $lang['dl_count']       => 'modules/downloads/admin/count.php'
        );

        $menuList4 = array(
            $lang['preferences']    => 'admin/preferences.php',
            $lang['system_info']    => 'admin/info.php',
            $lang['manual']         => 'modules/manual/' . $manLang . '/'
        );

        $item['admin']['list'][1] = $this->getMenuList($menuList1);
        $item['admin']['list'][2] = $this->getMenuList($menuList2);
        $item['admin']['list'][3] = $this->getMenuList($menuList3);
        $item['admin']['list'][4] = $this->getMenuList($menuList4);

        // Presentation
        if ($sessionState === 'on') {
            $templateFile = $pathToIndex 
                          . self::LOGGIX_THEME_DIR 
                          . 'admin/menu.html';;
            $adminMenu = new Loggix_View($templateFile);
            $adminMenu->assign('item', $item);
            $adminMenu->assign('lang', $lang);
            return $adminMenu->render();
        } else {
            return $adminMenu = '';
        }
    }


    /**
     * Menu List
     *
     * @param  array  $menuArray
     * @uses   getRequestUri
     * @return string
     */
    public function getMenuList($menuArray)
    {
        global $pathToIndex, $item, $id;
        $list = '';
        $reqUri = $this->getRequestUri();
        
        foreach ($menuArray as $key => $value) {
        
                $idValue = str_replace('.php', '', $value);
                $idValue = str_replace('?status=logout', '', $idValue);
                $idValue = str_replace('/', ' ', $idValue);
                $idValue = trim($idValue);
                $idValue = str_replace(' ', '-', $idValue);
                
            if ((((isset($id)) && ($id != '') && 
                 (($reqUri . '?id=' . $id) == $this->getRootUri() . preg_replace('/^.\//', '', $value)))) ||
                (((!isset($id)) && 
                  ($reqUri == $this->getRootUri() . preg_replace('/^.\//', '', $value)))) ||
                (((!isset($id)) && preg_match('/.*\/$/', $value) && 
                  ($reqUri == $this->getRootUri() . preg_replace('/^.\//', '', $value) . 'index.php')))
               ) {
                $list .= '<li id="' . $idValue . '" class="cur-menu">' . $key . "</li>\n";
            } else if (preg_match('/^(http|https):\/\//', $value)) {
                $list .= '<li id="' . $idValue . '" class="menu">'
                       . '<a href="' . $value . '" class="menu">' . $key . '</a>'
                       . "</li>\n";
            } else { 
                $list .= '<li id="' . $idValue . '" class="menu">'
                       . '<a href="' . $pathToIndex . '/' . $value . '" class="menu">'
                       . $key . '</a>'
                       . "</li>\n";
            }
        }
        return $list;
    }


    /**
     * Add tag data into tag map table
     *
     * @param  string  $tagMapTable
     * @param  integer $logId
     * @return void
      */
    public function addTag($tagMapTable, $logId)
    {

        // Add posted values to the tag map table.
        if (isset($_POST['tag'])) {
            $tag = array();
            $tag = $_POST['tag'];
            //echo var_dump($tag);
        } else {
            $tag[] = 1; // if a tag is not defined, apply default value, "1".
        }
        // (1) Check the old array
        // (2) Make an old tag id array from the old tag array.
        $sql = 'SELECT '
             .     'tag_id '
             . 'FROM '
             .     $tagMapTable . ' '
             . 'WHERE '
             .     'log_id = :log_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(
                   array(
                       ':log_id' => $logId
                   )
               );
        $oldTagArray = $stmt->fetchAll();
        $oldTagIdArray = array();
        foreach ($oldTagArray as $checkRow) {
            $oldTagIdArray[] = $checkRow['tag_id'];
        }
        // (3)If an old tag id is not in the new array, delete it.
        foreach ($oldTagIdArray as $oldTagId) {
             if (!in_array($oldTagId, $tag)) {
                 $sql2 = 'DELETE '
                       .     'FROM ' . $tagMapTable . ' '
                       . 'WHERE '
                       .     'tag_id = :tag_id'
                       .     ' AND '
                       .     'log_id = :log_id';
                 $stmt2 = $this->db->prepare($sql2);
                 $stmt2->execute(
                             array(
                                 ':tag_id' => $oldTagId,
                                 ':log_id' => $logId
                             )
                         );
             }
        }

        // Add tags
        //if (is_array($tag)) {
        foreach ($tag as $tagId) {
            $checkSql = 'SELECT '
                      .     'COUNT(id) '
                      . 'FROM ' 
                      .     $tagMapTable . ' '
                      . 'WHERE '
                      .     '(log_id = :log_id)'
                      .     ' AND '
                      .     '(tag_id = :tag_id)';
            $stmt3 = $this->db->prepare($checkSql);
            $stmt3->execute(
                        array(
                            ':log_id' => $logId,
                            ':tag_id' => $tagId
                        )
                    );
            $countId  = $stmt3->fetchColumn();
            if ($countId == '0') {
                $addTagSql = 'INSERT INTO '
                           .     $tagMapTable 
                           .         '(log_id, tag_id) '
                           .     'VALUES'
                           .         '(:log_id, :tag_id)';
                $stmt4 = $this->db->prepare($addTagSql);
                $stmt4->execute(
                            array(
                                ':log_id' => $logId,
                                ':tag_id' => $tagId
                            )
                        );
            }
        }

        //}
    }

    
    /**
     * Generate tag array
     *
     * @param  string $withDraft
     * @return array  $tagArray;
     */
    public function getTagArray($withDraft = 'no')
    {
        $tagArray = array();
        
        $sql = 'SELECT '
             .     't.id, t.tag_name '
             . 'FROM ' 
             .     LOG_TAG_TABLE . ' AS t';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        while ($row = $stmt->fetch()) {
            $sql2 = 'SELECT '
                  .     'COUNT(tm.id) '
                  . 'FROM ' 
                  .     LOG_TAG_MAP_TABLE . ' AS tm '
                  . 'WHERE '
                  .     'tm.tag_id = :tag_id ';               
            if ($withDraft == 'yes') {
                 $sql2 .= 'AND '
                        .     'tm.log_id '
                        . 'NOT IN '
                        .     '('
                        .         'SELECT '
                        .             'l.id '
                        .         'FROM ' 
                        .             LOG_TABLE . ' AS l '
                        .         'WHERE '
                        .             'l.draft = 1'
                        .     ')';
            }
            
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute(
                        array(
                            ':tag_id' => $row[0]
                        )
                    );
            $row['number_of_tag'] = $stmt2->fetchColumn();
            $tagArray[] = array($row[0], 
                                $row[1], 
                                $row['number_of_tag']);
        }
        
        return $tagArray;
    }


    /**
     * Generate tag ID array
     *
     * @param  string $tagMode
     * @return array  $tag_id_array
     */
    public function getTagIdArray($tagMode = null)
    {
        $tagTable = ($tagMode == 'Downloads') 
                         ? DOWNLOADS_TAG_MAP_TABLE
                         : LOG_TAG_MAP_TABLE;
        $checkSql = 'SELECT '
                  .     'tag_id '
                  . 'FROM ' 
                  .     $tagTable . ' '
                  . 'WHERE '
                  .     'log_id = :log_id';
        $stmt = $this->db->prepare($checkSql);
        $stmt->execute(
                   array(
                       ':log_id' => $_GET['id']
                   )
               );
        $checkRes = $stmt->fetchAll();
        $tagIdArray = array();
        foreach ($checkRes as $checkRow) {
            $tagIdArray[] = $checkRow['tag_id'];
        }
        return $tagIdArray;
    }


    /**
     * Get Tag Cloud Array
     *
     * 0: Tag ID
     * 1: Tag Name
     * 2: Number of articles stocked in this tag
     * 3: Checkbox status for this tag
     * 4: Tag direcotry
     * 5: Tag Level Value
     *
     * @uses   getTagArray
     * @uses   getTagIdArray
     * @return string
     * @see:   http://prism-perfect.net/archive/php-tag-cloud-tutorial/
     */
    public function getTagCloudArray($tagMode = null, $withDraft = 'yes')
    {
        global $item, $pathToIndex;
        
        $tagDir = ($tagMode == 'Downloads') 
                ? $pathToIndex . '/modules/downloads'
                : $pathToIndex;
        
        foreach ($this->getTagArray($withDraft) as $row) {
            $numberOfTagsArray[] = $row[2];
        }

        $maxQuantity = max($numberOfTagsArray);
        $minQuantity = min($numberOfTagsArray);
        
        // Set font size level from $minFontValue to $maxFontValue
        // Here, we set from level 1 to level 6, for example.
        $maxFontValue = '6';  // max font size at level 6 in css.
        $minFontValue = '1';  // min font size at level 1 in css.

       // $step   = ($maxFontValue - $minFontValue) / ($maxQuantity - $minQuantity);
        $quantityLevel = (($maxQuantity - $minQuantity) == 0) 
                       ? 5 
                       : $maxQuantity - $minQuantity;
        
        $step   = ($maxFontValue - $minFontValue) / $quantityLevel;
        
        $tagCloudArray = array();
        foreach ($this->getTagArray($withDraft) as $row) {

            // Add tag directory string and tag state for checkbox
            // to getTagArray() and create a new tag list array
            $row[0] = intval($row[0]);           // Tag ID Number
            $row[1] = htmlspecialchars($row[1]); // Tag Name
            $row[2] = intval($row[2]);           // Number in this tag
            $state = ((isset($_GET['id'])) && 
                      (in_array($row[0], $this->getTagIdArray($tagMode)))) 
                     ? 'checked="checked" ' 
                     : '';

            $tagLevel = ceil($minFontValue + (($row[2] - $minQuantity) * $step));
            $tagCloudArray[] = array(
                                   $row[0], 
                                   $row[1], 
                                   $row[2], 
                                   $state, 
                                   $tagDir,
                                   $tagLevel
                               );
            
        }
               
        return $tagCloudArray;
    }


    /**
     * Get Tag SQL Parameters
     *
     * @return array
     */
    public function getTagSqlParams()
    {

        return array('fields'     => '* ',
                     'main_table' => LOG_TABLE,
                     'title'      => 'title',
                     'comment'    => 'comment',
                     'draft'      => 'draft',
                     'date'       => 'date',
                     'map_table'  => LOG_TAG_MAP_TABLE,
                     'log_id'     => 'log_id',
                     'tag_id'     => 'tag_id',
                     'tag_name'   => 'tag_name',
                     'tag_table'  => LOG_TAG_TABLE
               );
    }


    /**
     * Get Tag SQL
     *
     * @param  array $params
     * @return string $sql
     */
    public function getTagSql($params)
    {
        global $date, $previousItemNumber, $key;
        
        $sql  = 'SELECT ' 
              .     $params['fields'] . ' '
              . 'FROM ' 
              .     $params['main_table'] . ' '
              . 'WHERE '
              .     '(' . $params['draft'] ." = '0') AND ";
        $sql .= (isset($date) && ($date != 'all')) 
                ? '(' . $params['date'] . " LIKE '" . $date . "%') AND " 
                : '';
        $sql .= 'id IN '
              .     '('
              .         'SELECT ' 
              .              $params['log_id'] . ' '
              .          'FROM ' 
              .              $params['map_table'] . ' '
              .          'WHERE ' 
              .              $params['tag_id'] . " = '" . $key . "'"
              .     ') '
              . 'ORDER BY '
              .     $params['date'] . ' '
              . 'DESC LIMIT ' 
              .     $previousItemNumber . ', ' . self::$config['page_max'];
              
        return $sql;
    }


    /**
     * Get Tag Hits SQL
     *
     * @param  array $params
     * @return string $sql
     */
    public function getTagHitsSql($params)
    {
        global $key, $date;
        
        // Count All Hit Data
        $sql  = 'SELECT '
              .     'COUNT(id) '
              .  'FROM ' 
              .     $params['main_table'] . ' '
              . 'WHERE '
              .     '(' . $params['draft'] . " = '0') AND ";
        $sql .= (isset($date) && ($date != 'all')) 
                ? '(' . $params['date'] . " LIKE '" . $date . "%') AND " 
                : '';
        $sql .= 'id IN '
              .     '('
              .         'SELECT ' 
              .             $params['log_id'] . ' '
              .         'FROM ' 
              .             $params['map_table'] . ' '
              .         'WHERE ' 
              .             $params['tag_id'] . " = '" . $key . "'"
              .     ')';

        return $sql;
    }

    /**
     * Get Search SQL Parameters
     *
     * @return array
     */
    public function getSearchSqlParams()
    {
        return array('fields'   => '* ',
                     'table'    => LOG_TABLE,
                     'title'    => 'title',
                     'comment'  => 'comment',
                     'date'     => 'date',
                     'draft'    => 'draft',
                     'group_by' => ''
               );
    }


    /**
     * Generate Search SQL
     *
     * $params = array(fields, table, title, comment date, draft, group_by)
     *
     * @param  array  $params = array(fields, table, title, comment date, draft, group_by)
     * @return string $sql
     */
    public function getSearchSql($params)
    {
        global $key, $previousItemNumber, $date, $expand;

        $sql  = 'SELECT ' 
              .     $params['fields'] . ' '
              . 'FROM ' 
              .     $params['table'] . ' '
              . 'WHERE '
              .     '(' . $params['draft'] . " = '0') " 
              . (($date != 'all') ? 'AND (' : '');
        if ($key != '') {
            if (!strrchr($key, ' ')) {
                $keys   = explode(',', $key);
                $and_or = 'OR';
            } else {
                $keys = explode(' ', $key);
                $and_or = 'AND';
            }
            $sql .= $params['title']   . " LIKE '%" . $keys[0] . "%' OR "
                  . $params['comment'] . " LIKE '%" . $keys[0] . "%')";
            for ($i = 1; $max = sizeof($keys), $i < $max; $i++) {
                $sql .= $and_or . ' '
                      . '('  
                      .     $params['title']   . " LIKE '%" . $keys[$i] . "%'"
                      .     ' OR ' 
                      .     $params['comment'] . " LIKE '%" . $keys[$i] . "%'"
                      . ')';
            }
            $sql .= ($date != 'all') 
                    ? ' AND (' . $params['date'] . " LIKE '" . $date . "%')" 
                    : '';
        } else if ($key == '') { // Monthly search
            $sql .= ($date != 'all') 
                    ? $params['date'] . " LIKE '" . $date . "%')" 
                    : '';
        }
        $sql .= (!empty($params['group_by'])) 
                ? ' GROUP BY ' . $params['group_by'] 
                : '';
        $sql .= ' ORDER BY ' . $params['date'] 
              . ' DESC LIMIT ' . $previousItemNumber . ', ' . self::$config['page_max'];
        
        return $sql;
    }


    /**
     * Get Search Hits SQL
     *
     * @param  array $params
     * @return string $sql
     */
    public function getSearchHitsSql($params)
    {
        global $key, $date;
        
        // Count All Hit Data
        if (!strrchr($key, ' ')) {
            $keys = explode(',', $key);
            $and_or = 'OR';
        } else {
            $keys = explode(' ', $key);
            $and_or = 'AND';
        }
        $sql = 'SELECT '
             .     'COUNT(id) '
             . 'FROM ' 
             .     $params['table'] . ' '
             . 'WHERE '
             .     '(' . $params['draft'] . " = '0') "
             .     'AND '
             .     '('   
             .         $params['title']   . " LIKE '%" . $keys[0] . "%'"
             .        ' OR ' 
             .         $params['comment'] . " LIKE '%" . $keys[0] . "%'"
             .     ') ';
        for ($i = 1; $max = sizeof($keys), $i < $max; $i++) {
            $sql .= $and_or . ' '
                  . '('  
                  .     $params['title']   . " LIKE '%" . $keys[$i] . "%'"
                  .     ' OR ' 
                  .     $params['comment'] . " LIKE '%" . $keys[$i] . "%'"
                  . ')';
        }
        $sql .= ($date != 'all') 
                ? ' AND (' . $params['date'] . " LIKE '" . $date . "%')" 
                : '';
        $sql .= (!empty($params['group_by'])) 
                ? ' GROUP BY ' . $params['group_by'] 
                : '';

        return $sql;
    }


    /**
     * Set Search Result Items
     *
     * @param  boolean $res
     * @param  integer $totalItemsCount
     * @param  integer $previousItemNumber
     * @param  integer $date
     * @return array   $item
     */
    public function setSearchItems($totalItemsCount, $previousItemNumber, $date)
    {
        global $lang, $key, $params, $config;
        
        $item = array();
        
        if ((($key == '') && (preg_match('/^[0-9]{4}-[0-9]{2}/', $date))) ||
            (($key == '') && ($date == 'all'))) {
            if (preg_match('/^[0-9]{4}-[0-9]{2}/', $date)) {
                $yyyy  = substr($date, 0, 4);
                $mm    = substr($date, 5, 2);
                $dateArray = getdate(mktime(0, 0, 0, $mm, 1, $yyyy));
                $month = $dateArray['month'];
                $mday  = $dateArray['mday'];
                $year  = $dateArray['year'];
                $archiveTitle = strtotime($mday . ' ' . $month . ' ' . $year);
            } else if ($date == 'all') {
                $archiveTitle = '0'; // All data
            }
            $resultMessage = $lang['show_log'];
        } else if ($key != '') {
            $archiveTitle  = ($date != '') ? '2' : '1';
            $resultMessage = $lang['hit_msg'];
        } else {
            $archiveTitle  = '0'; // All data
            $resultMessage = $lang['show_log'];
        }
        
        // Get Tag name as key
        if (!empty($_GET['t'])) {
            $sql = 'SELECT ' 
                 .     $params['tag_name'] . ' '
                 . 'FROM ' 
                 .     $params['tag_table'] . ' '
                 . 'WHERE '
                 .     'id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute(
                       array(
                           ':id' => $key
                       )
                   );
            $key  = $stmt->fetchColumn();
        }
        
        // Create the last row number to display
        $rowsToDisplay = (($previousItemNumber + self::$config['page_max']) > $totalItemsCount)
                       ? $totalItemsCount
                       : $previousItemNumber + self::$config['page_max'];

        // Pesentation of the results
        $item['keyword']        = htmlspecialchars($key);
        $item['date']           = $date;
        $item['archive_title']  = $archiveTitle;
        $item['hits']           = $totalItemsCount;
        $item['disp_page']      = $previousItemNumber + 1;
        $item['disp_rows']      = $rowsToDisplay;
        $item['result_message'] = $resultMessage;
        
        return $item;
    }

    /**
     * Get Total Items Count
     *
     * @param  string  $countItemsSql
     * @return integer
     */
    public function getTotalItemsCount($countItemsSql)
    {
        return $this->db->query($countItemsSql)->fetchColumn();
    }


    /**
     * Set variables for menu items
     *
     * @param  string $sessionState
     * @uses   getAdminMenu
     * @uses   getContentMenu
     * @uses   getCssSwitch
     * @uses   getCategoryList
     * @return array $item
     */
    public function setMenuItems($sessionState = null)
    {
        return $item = array();
    }


    /**
     * Destructor
     *
     */
    public function __destruct()
    {
        unset($this->db);
    }
}


