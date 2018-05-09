## MetaPlus For Typecho

customized your `tag/category` archive page.

 - customized title
 - customized introduce content
 - customized css


### Usage 

in the `archive.php` tpl file:


```
<?php
if (($this->is('category') || $this->is('tag'))
        && Typecho_Plugin::exists('MetaPlus')
        && $mp = MetaPlus_Plugin::related($this->_archiveSlug)):
    echo $mp['title']; // customized title
    echo $mp['css'];  // pure css without `style` tag
    echo $mp['html']; // html
endif;
?>
```


**notice**

check the `/var/Typecho/Plugin.php` file at line **410-411**;
if your code did not like below, then fixed it.

```
public static function exists($pluginName) {
    return array_key_exists($pluginName, self::$_plugins['activated']);
}
```

now you can use `Typecho_Plugin::exists` to check whether the plugin is intalled or not.


