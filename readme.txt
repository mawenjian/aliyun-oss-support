=== 阿里云附件 ===
Contributors: 马文建(Wenjian Ma)
Donate link: http://mawenjian.net/977.html
Tags: attachment, aliyun, manager, images, thumbnail
License: GPLv2 or later  
Requires at least: 1.5
Tested up to: 4.1
Stable tag: 2.0

使用阿里云存储OSS作为附件存储空间。
This is a plugin that uses Aliyun Cloud Storage(Aliyun OSS) for attachments remote saving.

== Description ==

该插件支持使用阿里云存储OSS作为附件存储空间。
This is a plugin that uses Aliyun Cloud Storage(Aliyun OSS) for attachments remote saving.

== Installation ==

1. 下载，解压缩并上传到WordPress插件目录
2. 在插件管理后台激活插件
3. 工具 > OSS Support 

1. Download, unzip and upload to your WordPress plugins directory
2. activate the plugin within you WordPress Administration Backend
3. Go to Tools > OSS Support

== Changelog ==

= 2.0 =
* 更新OSS SDK到 v1.1.6 版本
* 修复只能上传图片不能上传其他类型文件的BUG;
* 支持OSS所有存储地域（杭州、北京、深圳、青岛、香港）；
* 增加启用插件时服务器运行环境测试；
* 增加AK/SK/BUCKET校验功能，如果AK/SK没有操作BUCKET的权限，或者BUCKET为“私有”或“公开读写”状态，则会进行提示；
* 增加插件卸载时upload_path_url复原功能；
* 允许缩略图不同步到OSS；
* 优化代码，把大部分代码进行了重写，增加了代码注释；
* 代码同步到了Github（https://github.com/mawenjian/aliyun-oss-support），方便各位创建新的分支。
* 完善文字描述；

= 1.0 =
* 实现插件原型

== Upgrade Notice ==

= 2.0 =
进行了十分重要的更新


== Frequently Asked Questions ==
* 暂无

== Screenshots ==
* 暂无