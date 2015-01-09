aliyun-oss-support
==================

使用阿里云存储OSS作为WordPress附件存储空间。This is a plugin for WordPress that uses Aliyun Cloud Storage(OSS) for attachments remote saving.

插件地址：http://mawenjian.net/p/977.html

同类插件：https://github.com/IvanChou/aliyun-oss-support

个人博客：http://mawenjian.net

____________________

### 说明：<br />
1、启用插件后，最好将原来上传到WordPress的附件同步到OSS的相应目录，否则在WordPress后台原来上传图片的缩略图会不能显示。<br />
2、启用插件后，建议在上传文件前规范文件的命名规则，避免因不符合OSS的Object命名规范而导致同步失败。个人建议文件命名使用“26个英文字母”、“数字0-9”以及“-”，除此之外的字符，一律不使用。<br />
3、启用插件后，对于体积较大的文件，不建议使用WordPress后台上传，因为需要Web服务器进行周转，效率较低，也容易出错（尤其是海外服务器）；建议直接通过OSS管理后台或相关工具上传到相应位置。<br />
4、如果您有任何意见或建议，请到 http://mawenjian.net/p/977.html 提交；<br />
5、欢迎其他OSS类同步插件将我新加入的功能纳入他们的插件（直接粘贴代码也无所谓，当然，最好可以提及下idea来自于我）。为广大网友提供更好用的插件才是我们的最终目的，其他都不重要。<br />

____________________

### 版本号：2.0 beta

修正日期：2015-1-5

#### 修订内容：

1、修复了v1.0版本中网友提出的BUG（我能想到的）；<br />
2、更新OSS SDK到最新的 v1.1.6 版本；<br />
3、修复只能上传图片不能上传其他类型文件的BUG;<br />
4、支持OSS所有存储地域（杭州、北京、深圳、青岛、香港）和内外网支持；<br />
5、增加插件启用时的服务器运行环境测试，如果服务器不满足基本要求，则会进行提示；<br />
6、增加AK/SK/BUCKET校验功能，如果AK/SK没有操作BUCKET的权限，或者BUCKET为“私有”或“公开读写”状态，则会进行相应提示；<br />
7、增加插件卸载复原功能，会在插件卸载的时候将upload_path_url参数还原；<br />
8、允许用户选择是否将图片的缩略图不同步到OSS；<br />
9、优化代码结构，把大部分代码进行了重写，增加了完整的代码注释，对可能产生的错误和可能抛出的异常进行了相应处理，增强了代码的健壮性；<br />
10、代码同步到了Github（ https://github.com/mawenjian/aliyun-oss-support ），方便各位有兴趣的朋友创建新的分支。<br />
11、完善了插件配置页面的文字描述，即使是小白也能按说明把插件配置好。<br />

![github](https://raw.githubusercontent.com/mawenjian/aliyun-oss-support/master/screenshot.jpg "ScreenShot")
