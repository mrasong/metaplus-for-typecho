<?php 
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 
 * 分类/标签增强插件，可自定义显示标题、描述信息、css 等
 *
 * @package MetaPlus
 * @author mrasong
 * @version 1.0.0
 * @link http://mrasong.com/a/metaplus-for-typecho
 * @usage 
 * in the archive.php tpl file:
 *
 * <?php
 * if (($this->is('category') || $this->is('tag'))
            && Typecho_Plugin::exists('MetaPlus')
            && $mp = MetaPlus_Plugin::related($this->_archiveSlug)):
 *      echo $mp['title']; // customized title
 *      echo $mp['css'];  // pure css without `style` tag
 *      echo $mp['html']; // html
 * endif;
 * ?>
 */
class MetaPlus_Plugin implements Typecho_Plugin_Interface {

    /* 激活插件方法 */
    public static function activate(){
        self::createTable();

        $mid = Helper::addMenu(self::getMenu());
        Helper::addPanel($mid, self::getPanel('Manage.php'), '[分类/标签]关联管理', '[分类/标签]关联管理工具', 'administrator', false, 'extending.php?panel='. self::getPanel('Edit.php') );
        Helper::addPanel($mid, self::getPanel('Edit.php'), '添加关联', '添加关联', 'administrator', false, '');

        helper::addAction(self::getAction(), self::getWidget());
        return _t( '插件启动成功！请<a href="%s">点击这里</a>管理', 
                        Helper::url(self::getPanel('Manage.php')) );
    }
     
    /* 禁用插件方法 */
    public static function deactivate(){
        self::dropTable();

        $mid = Helper::removeMenu(self::getMenu());
        Helper::removePanel($mid, self::getPanel('Manage.php'));
        Helper::removePanel($mid, self::getPanel('Edit.php'));

        Helper::removeAction(self::getAction());
    }
     
    /* 插件配置方法 */
    public static function config(Typecho_Widget_Helper_Form $form){}
     
    /* 个人用户的配置方法 */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
     
    /* 插件实现方法 */
    public static function render(){}



    private static function getMenu(){
        return 'MetaPlus';
    }  

    private static function getPackage($replace=''){
        return str_ireplace('_Plugin', $replace, __CLASS__);
    }

    private static function getAction(){
        return 'metaplus-manage';
    }

    private static function getWidget(){
        return self::getPackage('_Widget');
    }

    private static function getPanel($panel='Manage.php'){
        return self::getPackage('/'. $panel);
    } 


    public static function getDb(){
        return Typecho_Db::get();
    }

    private static function getTable($select=true){
        return ( $select ? 'table.' : self::getDb()->getPrefix() ) . strtolower(self::getPackage());
    }

    private static function query($sql=''){
        $sql = explode(';', str_ireplace('%table%', self::getTable(false), $sql));
        foreach ($sql as $query) {
            if ( !empty(trim($query)) ) {
                self::getDb()->query( $query, Typecho_Db::WRITE );
            }
        }
    }

    /** 
     * 创建表
     */
    public static function createTable(){
        self::dropTable();
        self::query( self::getSQL(1) );
    }

    /**
     * 清除表
     */
    public static function dropTable(){
        self::query( self::getSQL(0) );
    }

    private static function getSQL($type=1){
        $db = self::getDb();
        $create = '';
        $drop   = '';

        switch ($db->getAdapterName()) {
            case 'Mysql':
            case 'Pdo_Mysql':
                    $create = <<<SQL
                            CREATE TABLE `%table%` (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `slug` varchar(150) NOT NULL default '',
                                `title` varchar(255) NOT NULL default '',
                                `html` text,
                                `css` text,
                                `ctime` int(10) unsigned NOT NULL default 0,
                                `status` tinyint(1) unsigned NOT NULL default 0,
                                PRIMARY KEY  (`id`),
                                UNIQUE KEY `UDX_SLUG` (`slug`)
                            ) AUTO_INCREMENT=10001;
SQL;

                    $drop = 'DROP TABLE IF EXISTS `%table%`;';
                    break;

            case 'SQLite':
            case 'Pdo_SQLite':
                    $create = <<<SQL
                            CREATE TABLE `%table%` (
                                `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, 
                                `slug` varchar(150) NOT NULL default '', 
                                `title` varchar(255) NOT NULL default '',
                                `html` text , 
                                `css` text , 
                                `ctime` int(10) NOT NULL default 0, 
                                `status` int(10) NOT NULL default 0
                            );
                            CREATE UNIQUE INDEX 'UDX_SLUG' ON %table% ("slug");
                            INSERT INTO sqlite_sequence (name, seq) VALUES ('%table%', 10000);
SQL;
                    $drop = 'DROP TABLE IF EXISTS `%table%`;';
                    break;

            case 'Pgsql':
            case 'Pdo_Pgsql':
                    $create = <<<SQL
                            CREATE SEQUENCE "%table%_seq" START WITH 10001;
                            CREATE TABLE  "%table%" ( 
                                "id" INT NOT NULL DEFAULT nextval('%table%_seq'),
                                "slug" VARCHAR NOT NULL DEFAULT '',
                                "title" VARCHAR NOT NULL DEFAULT '',
                                "html" TEXT NULL DEFAULT NULL,
                                "css" TEXT NULL DEFAULT NULL,
                                "ctime" INT NOT NULL DEFAULT 0,
                                "status" INT NOT NULL DEFAULT 0,
                                PRIMARY KEY ("id"),
                                UNIQUE ("slug")
                            );
SQL;
                    $drop = 'DROP TABLE IF EXISTS "%table%"; DROP SEQUENCE IF EXISTS "%table%_seq";';
                    break;
        }

        return $type===1 ? $create : $drop;
    }

    /* 显示关联 */
    public static function related($slug = ''){
        $db = self::getDb();
        return $db->fetchRow(
                    $db->select()
                        ->from(self::getTable())
                        ->where('slug = ?', $slug)
                        ->where('status = ?', 1)
                        ->limit(1)
                    );
    }

}
