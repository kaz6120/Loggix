<?php
/**
 * @package   Downloads
 * @author    Loggix Project
 * @since     5.6.16
 * @version   10.4.17 
 */


/**
 * Include Module class
 */
require_once $pathToIndex . '/lib/Loggix/Module.php';


/**
 * @package   Downloads
 */
class LM_Downloads extends Loggix_Module
{
    const THEME_PATH = '/modules/downloads/theme/';
    
    /**
     * Sending Downloadable file into SQLite database.
     *
     * @return void
     */
    public function sendDownloadableFile()
    {
        $this->insertTagSafe();
        
        $fileTitle   = $_POST['title'];
        $fileComment = $_POST['comment'];
        $draft       = $_POST['draft'];
        
        if (isset($_FILES['binfile'])) {
            clearstatcache(); //initialize
            $fileSrc  = $_FILES['binfile']["tmp_name"];
            $fileType = $_FILES['binfile']["type"];
            $fileName = $_FILES['binfile']["name"];
            if (!empty($fileSrc)) {
                $fileSize = filesize($fileSrc); // get the size of the file
                $fileHash = md5_file($fileSrc); // get the MD5 hash of the file
            }
            if ((isset($_POST['file_date'])) && 
                (preg_match("/^([0-9]+)-([0-9]+)-([0-9]+).([0-9]+):([0-9]+):([0-9]+)$/", 
                    $_POST['file_date']))
               ) {
                $fileDate = $_POST['file_date'];
                $cmod = preg_replace(
                    "/^([0-9]+)-([0-9]+)-([0-9]+).([0-9]+):([0-9]+):([0-9]+)$/", 
                    "$1$2$3$4$5$6", $fileDate);
            } else {
                //get the last access date of it
                $time      = filemtime($fileSrc);
                //format the UNIX timestamp
                $fileDate = gmdate('Y-m-d H:i:s',  $time  + (self::$config['tz'] * 3600));
                $cmod     = gmdate('Y-m-d H:i:s',  time() + (self::$config['tz'] * 3600));
            }            
            if (file_exists($fileSrc)) { // if file exists...
            
                // When relacing the old file...
                if (isset($_POST['id'], $_POST['replace_file'])) {
                    $id = intval($_POST['id']);
                    // Delete the old data...
                    $deleteSql = 'DELETE FROM ' 
                               .     DOWNLOADS_DATA_TABLE . ' '
                               . 'WHERE ' 
                               .     'masterid = :master_id';
                    $stmt = $this->db->prepare($deleteSql);
                    $deleteRes = $stmt->execute(
                                     array(
                                         ':master_id' => $id
                                     )
                                 );
                    if ($deleteRes == false) {
//                        echo var_dump($stmt->errorInfo());
                        die ('Error ID:0');
                    } else {
                        $updateSql = 'UPDATE ' 
                                   .     DOWNLOADS_META_TABLE . ' '
                                   . 'SET '
                                   .     '`file_name` = :file_name, '
                                   .     '`file_type` = :file_type, '
                                   .     '`file_size` = :file_size, '
                                   .     '`file_hash` = :file_hash '
                                   . 'WHERE '
                                   .     'id = :id';                          
                        $updateSql = $this->setDelimitedIdentifier($updateSql);
                        $stmt2 = $this->db->prepare($updateSql);
                        $updateRes = $stmt2->execute(
                                         array(
                                             ':file_name' => $fileName,
                                             ':file_type' => $fileType,
                                             ':file_size' => $fileSize,
                                             ':file_hash' => $fileHash,
                                             ':id'        => $id
                                         )
                                     );
                        if ($updateRes == false) {
//                           echo var_dump($stmt->errorInfo());
                            die('Error ID:1');
                        }
                    }
                    
                    $binaryId = $id;
                    
                // When uploading a new file
                } else {
                    // put these info into the data-info table
                    $insertSql  = 'INSERT INTO ' 
                                .     DOWNLOADS_META_TABLE . ' '
                                .         '('
                                .             '`file_title`, '
                                .             '`file_type`, '
                                .             '`file_name`, '
                                .             '`file_size`, '
                                .             '`file_date`, '
                                .             '`file_mod`, '
                                .             '`file_comment`, '
                                .             '`file_hash`, '
                                .             '`draft`'
                                .         ') '
                                .     'VALUES '
                                .         '(' 
                                .             ':file_title, '                          
                                .             ':file_type, '
                                .             ':file_name, '
                                .             ':file_size, '
                                .             ':file_date, '
                                .             ':file_mod, '
                                .             ':file_comment, '
                                .             ':file_hash, '
                                .             ':draft'
                                .         ')';
                    $insertSql = $this->setDelimitedIdentifier($insertSql);
                    $stmt = $this->db->prepare($insertSql);
                    $updateRes = $stmt->execute(
                                     array(
                                         ':file_title' => $fileTitle,
                                         ':file_type'  => $fileType,
                                         ':file_name'  => $fileName,
                                         ':file_size'  => $fileSize,
                                         ':file_date'  => $fileDate,
                                         ':file_mod'   => $cmod,
                                         ':file_comment' => $fileComment,
                                         ':file_hash'    => $fileHash,
                                         ':draft'        => $draft
                                     )
                                 );
                    
                    if ($updateRes == false) {
                        //echo var_dump($stmt->errorInfo());
                        die('Error ID:2');
                    }
                    // put data into the data table
                    $binaryId = $this->db->lastInsertId();
                }

                // open the file, put it in the file pointer(fp) with "r(read)" mode.
                $fp = fopen($fileSrc, "rb");
                while (!feof($fp)) {
                    //  "sqlite_escape_string" the binary data before insert.
                    //  Max values of string-type fields are:
                    //    (1)BLOB= 65535 byte,
                    //    (2)MEDIUMBLOB= 16777215 byte(1.6MB),
                    //    (3)LONGBLOB= 4294967295 byte(4.2GB)
                    //
                    // *KEEP IT SMALL* to insert it safely.
                    // 10 times of BLOB size is my choice..
                    // 65535 byte*10=655350 byte↓
                    //$binarydata = sqlite_udf_encode_binary(fread($fp, 655350));
                    $binaryData = fread($fp, 655350);
                    $insertDataSql  = 'INSERT INTO '
                                    .     DOWNLOADS_DATA_TABLE . ' '
                                    .         '(`masterid`, `file_data`) '
                                    .     'VALUES '
                                    .         '(:binary_id, :binary_data)';
                    $insertDataSql = $this->setDelimitedIdentifier($insertDataSql);         
                    $stmt = $this->db->prepare($insertDataSql);
                    $stmt->bindParam(':binary_id', $binaryId);
                    $stmt->bindParam(':binary_data', $binaryData);             
                    if (!$insertDataRes = $stmt->execute()) {
                       // echo var_dump($stmt->errorInfo());
                        die('Error ID:3');
                    }
                    
                }
                fclose($fp); //close the file...
                $id = $binaryId;
            } // close "file_exists..."
        } // close "isset..."
    }


    /**
     * Generate MD5 checksum of the file
     *
     * @param  int $fileId
     * @return string
     */
    public function getMD5($fileId)
    {
        $sql = 'SELECT '
               .     'file_hash '
             . 'FROM '
             .     DOWNLOADS_META_TABLE . ' '
             . 'WHERE '
             .     'id = ' . $fileId;
        $res = $this->db->query($sql);

        if (!empty($res)) {
            $row = $res->fetch();
            $md5 = $row[0];
        } else {
            $md5 = 'None';
        }
        return $item['md5'] = $md5;
    }


    /**
     * Generate tag array
     *
     * @return array
     */
    public function getTagArray($withDraft = 'no')
    {
        $tagArray = array();
        
        $sql = 'SELECT '
             .     't.id, t.tag_name '
             . 'FROM ' 
             .     DOWNLOADS_TAG_TABLE . ' AS t';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        while ($row = $stmt->fetch()) {
            $sql2 = 'SELECT '
                  .     'COUNT(tm.id) '
                  . 'FROM ' 
                  .     DOWNLOADS_TAG_MAP_TABLE . ' AS tm '
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
     * Get Tag SQL Parameters
     *
     * @return array
     */
    public function getTagSqlParams()
    {
        return array('fields'     => '*',
                     'main_table' => DOWNLOADS_META_TABLE,
                     'title'      => 'file_title',
                     'comment'    => 'file_comment',
                     'draft'      => 'draft',
                     'date'       => 'file_date',
                     'map_table'  => DOWNLOADS_TAG_MAP_TABLE,
                     'log_id'     => 'log_id',
                     'tag_id'     => 'tag_id',
                     'tag_name'   => 'tag_name',
                     'tag_table'  => DOWNLOADS_TAG_TABLE
        );
    }
    
    /**
     * Get Search SQL Parameters
     *
     * @return array
     */
    public function getSearchSqlParams()
    {
        return array('fields'   => '*',
                     'table'    => DOWNLOADS_META_TABLE,
                     'title'    => 'file_title',
                     'comment'  => 'file_comment',
                     'date'     => 'file_date',
                     'draft'    => 'draft',
                     'group_by' => ''
        );
    }
    

    /**
     * Download Counter
     *
     * @return string $list
     */
    public function getNumberOfDownloads()
    {
        global  $pathToIndex;
        
        $sql = 'SELECT '
             .     'id, file_title, file_name, file_date, file_count '
             . 'FROM ' 
             .     DOWNLOADS_META_TABLE . ' '
             . 'GROUP BY '
             .     'file_name '
             . 'ORDER BY '
             .     'file_count DESC';
        $rowArray = array();
        $res = $this->db->query($sql);
        while ($row = $res->fetch()) {
            $fileId            = intval($row['id']);
            $fileTitle         = $row['file_title'];
            $fileName          = $row['file_name'];
            $fileDownloadCount = $row['file_count'];
            $rowArray[] = array($fileId, 
                                $fileTitle, 
                                $fileName,
                                $fileDownloadCount);
        }
        return $rowArray;
    }


    /**
     * Set Entry Items
     *
     * @param  array $item
     * @return array $item
     */
    public function setEntryItems($item)
    {
        global $lang, $module, $pathToIndex;
        
        $item['id']        = intval($item['id']);
        $item['date']      = date(self::$config['post_date_format'], strtotime($item['file_date']));
        $item['title']     = htmlspecialchars($item['file_title']);
        $item['tag']  = '';
        if (isset($_GET['id'])) {
            foreach ($this->getTagArray('Downloads') as $row) {
                $item['tag'] .= (in_array($row[0], $this->getTagIdArray('Downloads'))) 
                               ? '<a href="' . $pathToIndex 
                                . '/modules/downloads/index.php?t=' . $row[0] . '&amp;ex=1">'
                                . htmlspecialchars($row[1]) . '</a> ' 
                              : '';                                    
            }
        }

        // Apply Smiley 
        $item['comment'] = $this->setSmiley($item['file_comment']);
        
        $item['comment']   = str_replace('href="./data', 
                                         'href="' . $pathToIndex . '/data', 
                                         $item['comment']);
        
        $item['comment']   = str_replace('src="./data', 
                                         'src="' . $pathToIndex . '/data', 
                                         $item['comment']);
        $item['comment']   = str_replace('src="./theme/images', 
                                         'src="' . $pathToIndex . '/theme/images', 
                                         $item['comment']);
        // Apply plugin filter
        $item['comment'] = $this->plugin->applyFilters('entry-content', $item['comment']);

        $item['file_type'] = $item['file_type'];
        $item['file_name'] = htmlspecialchars($item['file_name']);
        $item['file_size'] = $this->toMegaByte($item['file_size']);
        $item['md5']       = $this->getMD5($item['id']);
        /*
        $item['deposition'] = (preg_match('/(image|text)/', $item['file_type']))
                              ? 'inline' : 'attachment';
                              */
        return $item;
    }

    /**
     * Get Archived item list
     *
     * @uses   getModuleLanguage
     * @uses   getAdminEditMenu
     * @uses   setEntryItems
     * @uses   Loggix_View
     * @uses   Loggix_Exception
     * @return string $contentsView
     */
    public function getArchives($getItemsSql)
    {
         global $sessionState, $item, $module, $pathToIndex, $lang;
        
        $this->getModuleLanguage('downloads');

        $getItemsSql = $this->setDelimitedIdentifier($getItemsSql); 
        $stmt = $this->db->prepare($getItemsSql);
        $items = array();
        
        if ($stmt->execute() == true) {
            while ($item = $stmt->fetch()) {
                $item = $this->setEntryItems($item);
                $items[] = $item;
            }
            $templateFile = $pathToIndex . self::THEME_PATH . 'archives.html';
            $contentsView = new Loggix_View($templateFile);
            $templateVars = array('session_state' => $sessionState,
                                  'items'         => $items,
                                  'lang'          => $lang,
                                  'module'        => $module
                            );
            $contentsView->assign($templateVars);
        } else {
            if (!$_SERVER['QUERY_STRING']) {
                $templateFile = $pathToIndex . self::THEME_PATH . 'default.html';
                $contentsView = new Loggix_View($templateFile);
                $templateVars = array('config' => self::$config,
                                      'lang'   => $lang
                                );
                $contentsView->assign($templateVars);
            } else {
                throw new Loggix_Exception();
            }
        }
        
        return $this->plugin->applyFilters('downloads-index-view', $contentsView->render());
    }
}

