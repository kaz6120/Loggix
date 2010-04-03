<?php
/**
 * Loggix_Application - Main class for creating Loggix Application.
 *
 * PHP version 5
 *
 * @package   Loggix
 * @copyright Copyright (C) Loggix Project.
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.16
 * @version   10.4.3
 */

/**
 * Include Core Class
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Core.php';


/**
 * Loggix Application Class
 *
 * @package   Loggix
 */
class Loggix_Application extends Loggix_Core 
{

    /**
     * Set Document Title
     *
     * @param  array $sections
     * @return string
     * @uses   is_array
     */    
    public function setTitle($sections)
    {

        $mutableSections = '';
        
        if (is_array($sections)) {
            foreach ($sections as $value) {
                 $mutableSections .= parent::LOGGIX_TITLE_SEPARATOR . $value; 
            }
        } else {
            $mutableSections = parent::LOGGIX_TITLE_SEPARATOR . $sections;
        }
        
        return $this->plugin->applyFilters('title', self::$config['loggix_title'] . $mutableSections);
    }


    /**
     * Set XML Language
     *
     * @return string
     */
    public static function setXmlLanguage()
    {

        switch(self::$config['language']) {
            case 'english':
                return 'en';
                break;
            default:
                return 'ja';
                break;
        }
    }



    /**
     * Get Content Menu
     *
     * @return string
     * @uses   getMenuList
     */
    public function getContentMenu()
    {
        global $item;
              
        $menuArray = $this->explodeAssoc(',', PHP_EOL, 
                                str_replace(
                                    '\r\n', PHP_EOL, self::$config['menu_list']
                                )
                            );
        return $item['content_list'] = $this->getMenuList($menuArray);
    }


    /**
     * Get CSS list
     *
     * @return string
     */    
    public function getCssSwitchArray()
    {
        return $this->explodeAssoc(',', PHP_EOL, 
                          str_replace(
                              '\r\n', PHP_EOL, self::$config['css_list']
                          )
                      );
        
    }


    
    /**
     * Set Entry Items
     *
     * @param  array $item
     * @uses   getCategoryArray
     * @uses   getCategoryIdArray
     * @uses   smiley
     * @return array $item
     */
    public function setEntryItems($item)
    {
        global $pathToIndex, $lang, $module;       
        
//        echo var_dump($item);

        $item['id']      = intval($item['id']);
        $item['title']   = htmlspecialchars($item['title']);
        $item['comment'] = $item['comment']; 
        $item['date']    = $item['date'];
        $item['tag']     = '';
                
        if (isset($_GET['id'])) {
            foreach ($this->getTagArray() as $row) {
                $item['tag'] .= (in_array($row[0], $this->getTagIdArray())) 
                              ? '<a href="' . $pathToIndex 
                                . '/index.php?t=' . $row[0] . '&amp;ex=1">'
                                . htmlspecialchars($row[1]) . '</a> ' 
                              : '';
            }
        }
         
        // Apply Smiley 
        $item['comment'] = $this->setSmiley($item['comment']);

        $item['comment'] = str_replace('href="./data', 
                                             'href="' . $pathToIndex . '/data', 
                                              $item['comment']);
        
        $item['comment'] = str_replace('src="./data', 
                                             'src="' . $pathToIndex . '/data', 
                                              $item['comment']);
        
        $item['comment'] = str_replace('src="./theme/images', 
                                       'src="' . $pathToIndex . '/theme/images', 
                                       $item['comment']);

        // Apply plugin filter
        $item['comment'] = $this->plugin->applyFilters('entry-content', $item['comment']);

        // Visitor's comments
        if (class_exists('Loggix_Module_Comment')) {
            $aComment = new Loggix_Module_Comment;
            $item['comments'] = $aComment->getCommentStatus($item);
            $module['LM']['comment']['list'] = $aComment->getCommentList($item);
        }
       
        // Trackback
        if (class_exists('Loggix_Module_Trackback')) {
            $aTrackback = new Loggix_Module_Trackback;
            $item['trackbacks']  = $aTrackback->getTrackbackStatus($item);
            $module['LM']['trackback']['uri']  = $aTrackback->getTrackbackUri($item);
            $module['LM']['trackback']['list'] = $aTrackback->getTrackbackList($item);
        }
        // RSS
        if (class_exists('Loggix_Module_Rss')) {
            $aRss = new Loggix_Module_Rss;
            $item['comment'] = $aRss->toEnclosure($item['comment']);
        }
 
        //echo var_dump($item);

        return $item;
    }


    /**
     * Get Archived Entry list
     *
     * @param  string $res
     * @uses   setEntryItems
     * @uses   Loggix_View
     * @uses   Loggix_Exception
     * @return string $contentsView
     */
    public function getArchives($getItemsSql)
    {
        global $sessionState, $module, $pathToIndex, $lang;
        
        $getItemsSql = $this->setDelimitedIdentifier($getItemsSql);
        //echo $getItemsSql;
        $stmt = $this->db->prepare($getItemsSql);
        if ($stmt->execute() == true) {
            // Index by date
            $item = $stmt->fetch();
            if (((self::$config['show_date_title'] == 'yes') || 
                 (isset($_GET['d']))
                )
                && 
                (empty($_GET['t']))
               ) {
                //$item = $res->fetch();             
                $titleDate = date(self::$config['title_date_format'], strtotime($item['date']));
                $firstDate = $titleDate;
                do {
                    $tempDate = date(self::$config['title_date_format'], strtotime($item['date']));
                    if ($titleDate != $tempDate) { 
                        $titleDate = $tempDate;
                        $item['title_date'] = $tempDate;
                        $item['insert_date_div'] = 'YES';
                    } else {
                        $item['insert_date_div'] = 'NO';
                    }
                    $items[] = $this->setEntryItems($item);
                } while ($item = $stmt->fetch());
                $templateFile = $pathToIndex . parent::LOGGIX_THEME_DIR . 'archives-by-date.html';
                $contentsView = new Loggix_View($templateFile);
                $templateVars = array('session_state' => $sessionState,
                                      'first_date'    => $firstDate,
                                      'items'         => $items,
                                      'lang'          => $lang,
                                      'module'        => $module
                                );
                $contentsView->assign($templateVars);
            // Normal Index
            } else {
                do {
                    $items[] = $this->setEntryItems($item);
                    //echo var_dump($items);
                } while($item = $stmt->fetch());
                $aFile = (isset($_GET['t'])) 
                       ? 'archives-by-tags.html' 
                       : 'archives.html';
                $templateFile = $pathToIndex . parent::LOGGIX_THEME_DIR . $aFile;
                $contentsView = new Loggix_View($templateFile);
                $templateVars = array('session_state' => $sessionState,
                                      'items'         => $items,
                                      'lang'          => $lang,
                                      'module'        => $module
                                );
                $contentsView->assign($templateVars);
            }
        } else {
            if (!$_SERVER['QUERY_STRING']) {
                $templateFile = $pathToIndex . parent::LOGGIX_THEME_DIR . 'default.html';
                $contentsView = new Loggix_View($templateFile);
                $templateVars = array('config' => self::$config,
                                      'lang'   => $lang
                                );
                $contentsView->assign($templateVars);
            } else {
                throw new Loggix_Exception();
            }
        }
        return $this->plugin->applyFilters('index-view', $contentsView->render());
    }

    
    /**
     * Generate Pager
     *
     * @param  string $totalItemsCount   Number of the hit result
     * @return string $pager  Pager string
     * @uses   ini_get
     * @uses   http_build_query
     * @uses   count
     */
    public static function getPager($totalItemsCount, $pageNumberToShow, $date, $expand = '0')
    {

        global $pathToIndex, $lang, $key;
        
        // Keyword or Tag
        if (!empty($_GET['k'])) {
            $key = htmlspecialchars($_GET['k']);
        } else if (!empty($_GET['t'])) {
            $key = intval($_GET['t']);
        } else {
            $key = '';
        }
        
        // Initialize
        $pageArray  = array();
        $arrayKey   = 0;
        $pageNumber = 0;
        $limit      = 0;
        $result     = 0;
        
        $archiveMode = (!empty($_GET['t'])) ? 'k=&amp;t' : 'k';

        if (!empty($totalItemsCount)) {
            // Start Pager
            $pager = '<p class="pager">' . "\n";
            for ($limit; $limit < $totalItemsCount; $limit += self::$config['page_max']) {
                $pageNumber++;
                // Link Expand Key
                $expandKey = ($expand == '1') ? '1' : '0';
                if (isset($pageNumberToShow)) {
                    if ($pageNumber == $pageNumberToShow) {
                        $tagArray['tag']    = '<span id="current-page">';
                        $tagArray['anchor'] = $pageNumber . '</span>';
                        $pageArray[] = $tagArray;
                        $arrayKey = count($pageArray) == 0 ? 0 : count($pageArray) - 1;
                    } else {
                        if ($pageNumber > $pageNumberToShow + 5) {
                            $tagArray['tag']    = '_';
                            $tagArray['anchor'] =  '_';
                        } else if ($pageNumber < $pageNumberToShow - 5) {
                            $tagArray['tag']    = '_';
                            $tagArray['anchor'] =  '_';
                        } else {
                            $tagArray['tag']    = '<a href="' . $_SERVER['PHP_SELF'] . '?'
                                                . $archiveMode . '=' . $key
                                                . '&amp;d='  . $date
                                                . '&amp;p='  . $limit
                                                . '&amp;pm=' . self::$config['page_max']
                                                . '&amp;pn=' . $pageNumber
                                                . '&amp;ex=' . $expandKey . '">';
                            $tagArray['anchor'] = $pageNumber . '</a>';
                        }
                        //$pageArray[] = str_replace('_', '', $tagArray);
                        $pageArray[] = $tagArray;
                        
                    }
                }
            }
            
            
            // "Prev" anchor
            if ($arrayKey > 0) {
                $arrayKeyMinusOne = $arrayKey - 1;
                $pager .= '<span class="prev">' . $pageArray[$arrayKeyMinusOne]['tag']
                        . $lang['previous'] . "</a></span>\n";
            }
            // Expandable pager link (between "Prev" and "Next")
            if ($expand == '1') {
                foreach($pageArray as $value) {
                    $pager .= $value['tag'] . $value['anchor'] . "\n";
                }
            }
            // "Next" anchor            
            if (isset($pageNumberToShow) && $pageNumberToShow != $pageNumber) {
                $arrayKeyPlusOne = $arrayKey + 1;
                $pager .= '<span class="next">' . $pageArray[$arrayKeyPlusOne]['tag']
                        . $lang['next'] . "</a></span>\n";
            }
            
            // End Pager
            $pager .= "</p>\n";
        }
        // Link to past archives
        $linkToArchives = '<p id="prev-logs">' . "\n"
                        . '<a href="' . $_SERVER['PHP_SELF'] . '?'
                        . $archiveMode . '='
                        . '&amp;p=' . self::$config['page_max']
                        . '&amp;pn=2'
                        . '&amp;d=all">' . $lang['prev_logs'] . '</a>'
                        . "\n</p>";
        // Show or Not
        if ($totalItemsCount > self::$config['page_max']) {
            return ((!$_SERVER['QUERY_STRING']) || (stristr($_SERVER['QUERY_STRING'], '=') === false)) 
                   ? $linkToArchives 
                   : $pager;
        } else {
            return '';
        }

    }

    
    /**
     * Get Attachment File Table View
     *
     * @uses   Loggix_View
     * @return string
     */
    public function setAttachments()
    {
        global $pathToIndex, $lang;
        
        $uploadFileList = '';
        
        for ($i = 1; $i < self::$config['upload_file_max']+1; $i++) {
            $item['i'] = $i;
            $items[] = $item;
        }
        
        $templateFile = $pathToIndex . parent::LOGGIX_THEME_DIR . 'admin/attachments.html';
        $attachmentsView = new Loggix_View($templateFile);
        $attachmentsView->assign('items', $items);
        $attachmentsView->assign('lang', $lang);
        
        return $attachmentsView->render();
    }


    /**
     * Send Attachment Files
     *
     * @return void
     * @uses   move_uploaded_file
     */
    public function sendAttachments() 
    {
        global $lang, $pathToIndex;
        
        self::$config['uploaddir'] = $pathToIndex . self::LOGGIX_RESOURCE_DIR;
        
        for ($i = 1; $i < self::$config['upload_file_max']+1; $i++) {
            if (isset($_FILES['myfile'])) {
                move_uploaded_file($_FILES['myfile']['tmp_name'][$i], 
                                   self::$config['uploaddir'] . $_FILES['myfile']['name'][$i]);
            }
        }
    }


    /**
     * Set variables for menu items
     *
     * @param  string $sessionState
     * @uses   getRequestUri
     * @uses   getTagCloudArray
     * @uses   getAdminMenu
     * @uses   getContentMenu
     * @uses   getCssSwitchArray
     * @return array $item
     */

    public function setMenuItems($sessionState = null)
    {
        global $pathToIndex, $item, $module;
        
        if (stristr($this->getRequestURI(), 'modules/downloads')) {
           $item['search_dir'] = $pathToIndex . '/modules/downloads';
           $item['tag_cloud']  = $this->getTagCloudArray('Downloads');
        } else if (stristr($this->getRequestURI(), 'modules/comment')) {
           $item['search_dir'] = $pathToIndex . '/modules/comment';
           $item['tag_cloud']  = $this->getTagCloudArray();
        } else if (stristr($this->getRequestURI(), 'modules/trackback')) {
           $item['search_dir'] = $pathToIndex . '/modules/trackback';
           $item['tag_cloud']  = $this->getTagCloudArray();
        } else {
           $item['search_dir'] = $pathToIndex;
           $item['tag_cloud']  = $this->getTagCloudArray();
        }
        
        $item['admin']['menu'] = $this->getAdminMenu($sessionState);
        $item['content_menu']  = $this->getContentMenu();
        $item['css_list']      = $this->getCssSwitchArray();
        
        return $item;
    }


    /**
     * Send HTTP Headers to User Agent
     * 
     * @param  string $getLastModifiedSql
     * @return void
     */
    public function sendHttpHeaders($getLastModifiedSql)
    {
        global $pathToIndex;
        
        // Get Last-Modified
        if (!empty($getLastModifiedSql)) { 
            $res = $this->db->query($getLastModifiedSql);
            $row = $res->fetch();
            if (isset($row['date'])) {
                $date = $row['date'];
            } else if (isset($row['file_date'])) {
                $date = $row['file_date'];
            } else {
                $date = '';
            }
        } else {
            $date = $this->getDataLastModified('Y-m-d H:i:s');
        }
        
        $lastModifiedGmt = (!empty($date)) 
                         ? gmdate('D, d M Y H:i:s', strtotime($date)) . ' GMT' 
                         : 'Tue, 24 Jan 1984 00:00:00 GMT';

        // Select XML Version
        switch (self::$config['xml_version']) {
            case '1.1': // Based on W3C Note
                $mediaType = 'application/xhtml+xml';
                break;
            case '1.1-content-negotiation': // Content Negotiation
                if ((stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml')) || 
                    (stristr($_SERVER['HTTP_USER_AGENT'], 'W3C_Validator')) ||
                    (stristr($_SERVER['HTTP_USER_AGENT'], 'Another_HTML-lint'))) {
                    $mediaType = 'application/xhtml+xml';
                } else {
                    $mediaType = 'text/html';
                }
                break;
            default :
                $mediaType = 'text/html';
                break;
        };

        header('Last-Modified: ' . $lastModifiedGmt);
        header('Content-Type: ' . $mediaType . '; charset=utf-8');
        header('Vary: Accept');
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');

    }


    /**
     * Display XHTML
     * 
     * @param  array $item
     * @param  string $sessionState
     * @uses   setMenuItems
     * @uses   sendHttpHeaders
     * @uses   View
     */
    public function display($item, $sessionState)
    {
        global $pathToIndex, $module, $lang, $getLastModifiedSql;

        
        // ob_start('ob_gzhandler');
        ob_start();
        
        $item['site_title'] = $this->plugin->applyFilters('h1', self::$config['loggix_title']);
        
        if (strpos($this->getRequestURI(), 'downloads')) {
            $this->getModuleLanguage('downloads');
            $lang['search_key'] = $lang['downloads']['search_key'];
        } else if (strpos($this->getRequestURI(), 'comment')) {
            $this->getModuleLanguage('comment');
            $lang['search_key'] = $lang['comment']['search_key'];
        } else if (strpos($this->getRequestURI(), 'trackback')) {
            $this->getModuleLanguage('trackback');
            $lang['search_key'] = $lang['trackback']['search_key'];
        }
        
        // Navigation View
        $templateFile = $pathToIndex . parent::LOGGIX_THEME_DIR . 'navigation.html';
        $navView = new Loggix_View($templateFile);
        $navView->assign('item', $this->setMenuItems($sessionState));
        $navView->assign('lang', $lang);
        $navView->assign('module', $module);
        $item['navigation'] = $this->plugin->applyFilters('navigation', $navView->render());

        // Send HTTP Headers
        $this->sendHttpHeaders($getLastModifiedSql);

        $item['gt'] = $this->getGenerationTime(self::$startTime);

        // Render XHTML View
        $xhtml = new Loggix_View();
        $xhtml->assign('module', $module);
        $xhtml->assign('item', $item);
        
        echo $xhtml->render($pathToIndex . parent::LOGGIX_THEME_DIR . 'base.html');

        ob_end_flush();
    }
}
