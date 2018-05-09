<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
include 'common.php';
include 'header.php';
include 'menu.php';
?>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main manage-metas">
            
                <div class="col-mb-12" role="main">
                    
                    <form method="post" name="manage_categories" class="operate-form">
                    <div class="typecho-list-operate clearfix">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('你确认要删除这些关联吗?'); ?>" href="<?php $security->index('/action/metaplus-manage?do=delete'); ?>"><?php _e('删除'); ?></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="message notice">
                        <ul>
                            <li>
                                <b>关联</b> 被关联的分类或者标签名称。
                                点击链接可进入分类/标签修改页面。
                            </li>
                            <li>
                                <b>类型</b> 
                                分类(category)/标签(tag)。
                            </li>
                            <li>
                                <b>自定义标题</b> 
                                点击链接可对自定义标题、内容、状态等进行修改。
                            </li>
                            <li>
                                <b>状态</b> 
                                关闭后，前台将不再显示自定义标题、内容等。
                            </li>
                        </ul>
                    </div>

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <thead>
                                <colgroup>
                                    <col width="30%"/>
                                    <col width="10%"/>
                                    <col width="40%"/>
                                    <col width="15%"/>
                                    <col width="5%"/>
                                </colgroup>
                                <tr class="nodrag">
                                    <th><?php _e('关联'); ?></th>
                                    <th><?php _e('类型'); ?></th>
                                    <th><?php _e('自定义标题'); ?></th>
                                    <th><?php _e('创建时间'); ?></th>
                                    <th><?php _e('状态'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php Typecho_Widget::widget('MetaPlus_Widget')->to($item); ?>
                                <?php if( $item->have() ): ?>
                                <?php while ( $item->next() ): ?>
                                <tr id="id-<?php $item->id(); ?>">
                                    <td>
                                        <input type="checkbox" value="<?php $item->id(); ?>" name="id[]"/>
                                        <a href="<?php $options->adminUrl(sprintf('%s.php?mid=%s', $item->metaField('type')=='tag' ? 'manage-tags' : 'category', $item->metaField('mid'))); ?>">
                                            <?php $item->metaName(); ?>
                                        </a>
                                    </td>
                                    <td><?php echo $item->metaField('type'); ?></td>
                                    <td><a href="<?php $item->editUrl(); ?>"><?php $item->title(); ?></a></td>
                                    <td><?php $item->created(); ?></td>
                                    <td><?php echo $item->status ? '开启' : '关闭'; ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7"><h6 class="typecho-list-table-title"><?php _e('没有任何关联'); ?></h6></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    </form>
                    
                </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<script type="text/javascript">
(function () {
    $(document).ready(function () {
        var table = $('.typecho-list-table');

        table.tableSelectable({
            checkEl     :   'input[type=checkbox]',
            rowEl       :   'tr',
            selectAllEl :   '.typecho-table-select-all',
            actionEl    :   '.dropdown-menu a'
        });

        $('.btn-drop').dropdownMenu({
            btnEl       :   '.dropdown-toggle',
            menuEl      :   '.dropdown-menu'
        });

        <?php if (isset($request->id)): ?>
        $('.typecho-mini-panel').effect('highlight', '#AACB36');
        <?php endif; ?>
    });
})();
</script>
<?php include 'footer.php'; ?>

