<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 通用异步服务组件
 *
 */
class MetaPlus_Widget extends Widget_Abstract implements Widget_Interface_Do{
    /**
     * 锚点id
     *
     * @access protected
     * @return string
     */
    protected function ___theId()
    {
        return 'id-' . $this->id;
    }

    /**
     * 获取单条记录
     *
     * @access public
     * @return Typecho_Db_Query
     */
    public function findOne($val = '', $field = 'id')
    {
        return $this->db->fetchRow(
                        $this->select()
                            ->where($field .' = ?', $val)
                            ->limit(1)
                    );
    }

    /**
     * 获取原始查询对象
     *
     * @access public
     * @return Typecho_Db_Query
     */
    public function select()
    {
        return $this->db->select()->from(self::getTable());
    }

    /**
     * 插入一条记录
     *
     * @access public
     * @param array $options 记录插入值
     * @return integer
     */
    public function insert(array $options)
    {
        return $this->db->query(
                        $this->db->insert(self::getTable())->rows($options)
                    );
    }

    /**
     * 更新记录
     *
     * @access public
     * @param array $options 记录更新值
     * @param Typecho_Db_Query $condition 更新条件
     * @return integer
     */
    public function update(array $options, Typecho_Db_Query $condition)
    {
        return $this->db->query($condition->update(self::getTable())->rows($options));
    }

    /**
     * 删除记录
     *
     * @access public
     * @param Typecho_Db_Query $condition 删除条件
     * @return integer
     */
    public function delete(Typecho_Db_Query $condition)
    {
        return $this->db->query($condition->delete(self::getTable()));
    }

    /**
     * 获取记录总数
     *
     * @access public
     * @param Typecho_Db_Query $condition 计算条件
     * @return integer
     */
    public function size(Typecho_Db_Query $condition)
    {
        return $this->db->fetchObject(
                        $condition->select(array('COUNT(id)' => 'num'))->from(self::getTable())
                    )->num;
    }

    /**
     * 入口函数
     *
     * @access public
     * @return void
     */
    public function execute(){
        /** 编辑以上权限 */
        $this->user->pass('administrator');

        $select = $this->select()->order('ctime', Typecho_Db::SORT_DESC);
        $this->db->fetchAll($select, array($this, 'push'));
    }


    private static function getPackage($replace=''){
        return str_ireplace('_Widget', $replace, __CLASS__);
    }

    private static function getAction(){
        return 'metaplus-manage';
    }

    private static function getPanel($panel='Manage.php'){
        return self::getPackage('/'. $panel);
    }

    private static function getTable(){
        return 'table.'. strtolower(self::getPackage());
    }


    public function date($date, $format='Y-m-d H:i:s'){
        $date = new Typecho_Date($date);
        return $date->format(empty($format) ? $this->options->postDateFormat : $format);
    }

    public function created($format='Y-m-d H:i:s'){
        echo $this->date( $this->ctime, $format );
    }

    public function meta($slug = ''){
        return $this->db->fetchRow(
                    $this->db->select()->from('table.metas')
                        ->where('slug = ?', $slug ?: $this->slug)
                        ->limit(1)
                );
    }

    public function metaField($key = ''){
        if (!$meta = $this->meta()) {
            return '';
        }
        return isset($meta[$key]) ? $meta[$key] : '';
    }

    public function metaName(){
        echo $this->metaField('name') ?: '** 无法关联！（不存在或已被删除） **';
    }


    /**
     * 生成表单
     *
     * @access public
     * @param string $action 表单动作
     * @return Typecho_Widget_Helper_Form_Element
     */
    public function form($action = NULL)
    {
        /** 构建表格 */
        $form = new Typecho_Widget_Helper_Form($this->security->getIndex('/action/'. self::getAction()),
            Typecho_Widget_Helper_Form::POST_METHOD);

        /** Meta ID */
        $slug = new Typecho_Widget_Helper_Form_Element_Text('slug', NULL, NULL, _t('关联(分类/标签)英文名 *'), _t('分类/标签英文名，不可修改.'));
        $form->addInput($slug);

        /** 标题 */
        $title = new Typecho_Widget_Helper_Form_Element_Text('title', NULL, NULL, _t('自定义标题 *'));
        $form->addInput($title);

        /** 详情 */
        $html = new Typecho_Widget_Helper_Form_Element_Textarea('html', NULL, NULL, _t('自定义内容 *'),
        _t('自定义页面显示内容，支持html代码。'));
        $form->addInput($html);

        /** css */
        $css =  new Typecho_Widget_Helper_Form_Element_Textarea('css', NULL, NULL,
        _t('自定义css'), _t('自定义css.'));
        $form->addInput($css);

        /** 是否启用 */
        $status = new Typecho_Widget_Helper_Form_Element_Radio('status',
        array('1' => _t('开启'), '0' => _t('关闭')),
        ($this->status ? 1 : 0), _t('是否启用'), _t('关闭后，前台将不在显示.'));
        $form->addInput($status);

        /** 动作 */
        $do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
        $form->addInput($do);

        /** 主键 */
        $id = new Typecho_Widget_Helper_Form_Element_Hidden('id');
        $form->addInput($id);

        /** 提交按钮 */
        $submit = new Typecho_Widget_Helper_Form_Element_Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        if (isset($this->request->id) && 'insert' != $action) {
            /** 更新模式 */
            if (!$item = $this->findOne($this->request->id)) {
                $this->response->redirect( Helper::url( self::getPanel('Manage.php') ) );
            }

            $slug->value($item['slug']);
            $slug->input->setAttribute('disabled', 'disabled');

            $title->value($item['title']);
            $html->value($item['html']);
            $css->value($item['css']);

            $do->value('update');
            $id->value($item['id']);
            $submit->value(_t('编辑关联'));
            $_action = 'update';
        } else {
            $do->value('insert');
            $submit->value(_t('添加关联'));
            $_action = 'insert';
        }

        if (empty($action)) {
            $action = $_action;
        }

        /** 给表单增加规则 */
        if ('insert' == $action) {
            $slug->addRule('required', _t('必须填写关联(分类/标签)英文名'));
        }
        if ('insert' == $action || 'update' == $action) {
            $title->addRule('required', _t('标题不能为空'));
            $html->addRule('required', _t('内容不能为空'));
        }

        if ('update' == $action) {
            $id->addRule('required', _t('主键不存在'));
            $id->addRule(array($this, 'findOne'), _t('不存在'));
        }

        return $form;
    }



    /**
     * 增加
     *
     * @access public
     * @return void
     */
    public function insertRelation()
    {
        if ($this->form('insert')->validate()) {
            $this->response->goBack();
        } 

        $slug = $this->request->filter('slug')->slug;
        if (!$this->meta($slug)) {
            $this->widget('Widget_Notice')->set('该分类/标签无法关联');
            $this->response->goBack();
        } 
        if ($this->findOne($slug, 'slug')) {
            $this->widget('Widget_Notice')->set('该分类/标签已添加过关联');
            $this->response->goBack();
        } 

        /** 取出数据 */
        $item = $this->request->from('slug', 'title', 'html', 'css', 'status');

        $item['ctime'] = $this->options->gmtTime;

        /** 插入数据 */
        $item['id'] = $this->insert($item);
        $this->push($item);

        /** 设置高亮 */
        $this->widget('Widget_Notice')->highlight($this->theId);

        /** 提示信息 */
        $this->widget('Widget_Notice')->set(_t('关联 [%s] 已经被增加', $item['id']), 'success');

        /** 转向原页 */
        $this->response->redirect( Helper::url( self::getPanel('Manage.php') ) );
    }

    /**
     * 更新分类
     *
     * @access public
     * @return void
     */
    public function updateRelation()
    {

        if ($this->form('update')->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $id   = $this->request->filter('int')->id;
        $item = $this->request->from('title', 'html', 'css', 'status');

        /** 更新数据 */
        $this->update($item, $this->db->sql()->where('id = ?', $id));
        $this->push($item);

        /** 设置高亮 */
        $this->widget('Widget_Notice')->highlight($this->theId);

        /** 提示信息 */
        $this->widget('Widget_Notice')->set(_t('关联 [%s] 已经被更新', $id), 'success');

        /** 转向原页 */
        $this->response->redirect( Helper::url( self::getPanel('Manage.php') ) );
    }


    /**
     * 将每行的值压入堆栈
     *
     * @access public
     * @param array $value 每行的值
     * @return array
     */
    public function push(array $value){
        $value = $this->filter($value);
        return parent::push($value);
    }

    /**
     * 将每行的值压入堆栈
     *
     * @access public
     * @param array $value 每行的值
     * @return array
     */
    public function filter(array $value){
        $value['editUrl'] = Helper::url(self::getPanel('Edit.php&id='.$value['id']));
        return $value;
    }

    /**
     * 删除
     *
     * @access public
     * @return void
     */
    public function deleteRelation()
    {
        $ids = $this->request->filter('int')->getArray('id');
        $deleteCount = 0;

        foreach ($ids as $id) {
            if ($this->delete($this->db->sql()->where('id = ?', $id))) {
                $deleteCount ++;
            }
        }

        /** 提示信息 */
        $this->widget('Widget_Notice')->set($deleteCount > 0 ? _t('关联已经删除') : _t('没有关联被删除'),
        $deleteCount > 0 ? 'success' : 'notice');

        /** 转向原页 */
        $this->response->goBack();
    }

    /**
     * 获取菜单标题
     *
     * @return string
     * @throws Typecho_Widget_Exception
     */
    public function getMenuTitle(){
        if (isset($this->request->id)) {
            $item = $this->db->fetchRow($this->select()
                ->where('id = ?', $this->request->id));

            if (!empty($item)) {
                return _t('编辑关联 %s', $item['id']);
            }
        
        } 
        throw new Typecho_Widget_Exception(_t('关联不存在'), 404);
    }


    /**
     * 入口函数
     *
     * @access public
     * @return void
     */
    public function action(){
        $this->user->pass('administrator');
        $this->security->protect();
        $this->on($this->request->is('do=insert'))->insertRelation();
        $this->on($this->request->is('do=update'))->updateRelation();
        $this->on($this->request->is('do=delete'))->deleteRelation();
        $this->response->redirect($this->options->adminUrl);
    }
}
