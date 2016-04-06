<?php
/**
 * @package 阿里云附件
 * @version 2.1
 */
/*
Plugin Name: 阿里云附件
Plugin URI: http://mawenjian.net/977.html
Description: 使用阿里云存储OSS作为附件存储空间。（注意：从v1.0版本更新的用户请重新设置相关参数！）This is a plugin that uses Aliyun Cloud Storage(Aliyun OSS) for attachments remote saving. 
Author: 马文建(Wenjian Ma)
Version: 2.1
Author URI: http://mawenjian.net/
*/

require_once('sdk.class.php');

if ( !defined('WP_PLUGIN_URL') )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );                           //  plugin url

define('OSS_BASENAME', plugin_basename(__FILE__));
define('OSS_BASEFOLDER', plugin_basename(dirname(__FILE__)));
define('OSS_FILENAME', str_replace(DFM_BASEFOLDER.'/', '', plugin_basename(__FILE__)));

// 初始化选项
register_activation_hook(__FILE__, 'oss_set_options');

/**
 * 初始化选项
 */
function oss_set_options() {
    $options = array(
        'bucket' => "",
        'ak' => "",
    	'sk' => "",
		'host' => "oss.aliyuncs.com",
		'nothumb' => "false",
		'nolocalsaving' => "false",
		'upload_url_path' => "",
		
    );
    
    add_option('oss_options', $options, '', 'yes');
}

/**
 * 服务器运行环境测试
 */
function test_server_env() {
	try {
		//实例化存储对象
		$test_ak = 'hello';
		$test_sk = 'world';
		$test_bucket = 'imgs-storage';
		$aliyun_oss = new ALIOSS($test_ak, $test_sk);
		$oss_option = array(ALIOSS::OSS_CONTENT_TYPE => 'text/xml');
		$response = $aliyun_oss->get_bucket_acl($test_bucket, $oss_option);
	} catch(Exception $ex) {
		echo "
			<div id='oss-warning' class='updated fade'><p><strong>注意：</strong>测试结果显示，Aliyun OSS Support插件似乎不能在本服务器上正常运行...</p></div>
			".$ex->getMessage();
	}
}

function oss_admin_warnings() {
    $oss_options = get_option('oss_options', TRUE);

    $oss_bucket = attribute_escape($oss_options['bucket']);
	if ( !$oss_options['bucket'] && !isset($_POST['submit']) ) {
		function oss_warning() {
			echo "
			<div id='oss-warning' class='updated fade'><p><strong>".__('OSS is almost ready.')."</strong> ".sprintf(__('You must <a href="%1$s">enter your OSS Bucket </a> for it to work.'), "options-general.php?page=" . OSS_BASEFOLDER . "/oss-support.php")."</p></div>
			";
			//执行服务器运行环境测试
			test_server_env();
		}
		add_action('admin_notices', 'oss_warning');
		return;
	} 
}
oss_admin_warnings();

/**
 *上传函数
 *@param $object
 *@param $file
 *@param $opt
 *@return bool
 */
function _file_upload( $object , $file , $opt = array()){
	
	//如果文件不存在，直接返回FALSE
	if( !@file_exists($file) )
		return FALSE;
		
	//获取WP配置信息
	$oss_options = get_option('oss_options', TRUE);
    $oss_bucket = attribute_escape($oss_options['bucket']);
	$oss_ak = attribute_escape($oss_options['ak']);
	$oss_sk = attribute_escape($oss_options['sk']);
	$oss_host = attribute_escape($oss_options['host']);
	if($oss_host==null || $oss_host == '')
		$oss_host = 'oss.aliyuncs.com';

	if(@file_exists($file)) {
		try {
			//实例化存储对象
			if(!is_object($aliyun_oss))
				$aliyun_oss = new ALIOSS($oss_ak, $oss_sk ,$oss_host);
			//上传原始文件，$opt暂时没有使用
			$aliyun_oss->upload_file_by_file( $oss_bucket, $object, $file, $opt );

			return TRUE;
			
		} catch(Exception $ex) {
			return FALSE;
		}
		
	} else {
		return FALSE;
	}
}


/**
 * 是否需要删除本地文件
 * @return bool
*/
function _is_delete_local_file() {
	$oss_options = get_option('oss_options', TRUE);
	return (attribute_escape($oss_options['nolocalsaving'])=='true');
}

/**
 * 删除本地文件
 *
 * @param $file 本地文件路径
 * @return bool
 */
function _delete_local_file($file){
	try{
	  //文件不存在
		if(!@file_exists($file))
			return TRUE;
		//删除文件
		if(!@unlink($file))
			return FALSE;
		return TRUE;
	}
	catch(Exception $ex){
		return FALSE;
	}
}


/**
 * 将文件按内容写入OSS
 * @param $uploadpath 存放路径
 * @param $content 写入的内容
 * @return bool
 */
function _file_upload_by_contents($uploadpath, $content, $type) {
	//获取WP配置信息
	$oss_options = get_option('oss_options', TRUE);
    $oss_bucket = attribute_escape($oss_options['bucket']);
	$oss_ak = attribute_escape($oss_options['ak']);
	$oss_sk = attribute_escape($oss_options['sk']);
	$oss_host = attribute_escape($oss_options['host']);
	if($oss_host==null || $oss_host == '')
		$oss_host = 'oss.aliyuncs.com';

	try {
		//实例化存储对象
		if(!is_object($aliyun_oss))
			$aliyun_oss = new ALIOSS($oss_ak, $oss_sk ,$oss_host);
		//上传原始文件，$opt暂时没有使用
		$upload_file_options = array(
			'content' => $content,
			'length' => strlen($content),
			ALIOSS::OSS_HEADERS => array(
				//'Expires' => '2012-10-01 08:00:00',
			),
			ALIOSS::OSS_CONTENT_TYPE => $type,
		);
		$aliyun_oss->upload_file_by_content( $oss_bucket, $uploadpath, $upload_file_options );

		return TRUE;
		
	} catch(Exception $ex) {
		return FALSE;
	}
}

/**
 * 判断是否为上传插件/主题等系统操作
 */
function _is_system_op() {
	if ($_GET["action"] == 'upload-plugin' || $_GET["action"] == 'upload-theme') {
		return true;
	} else {
		return false;
	}
}

/**
 * 上传附件（包括图片的原图）
 * @param $metadata
 * @return array()
 */
function upload_attachments($metadata) {
	//避免上传插件/主题时出现同步到OSS的情况
	if(_is_system_op()) {
		return $metadata;
	}

	$wp_uploads = wp_upload_dir();
	//生成object在OSS中的存储路径
	if(get_option('upload_path') == '.') {
		//如果含有“./”则去除之
		$metadata['file'] = str_replace("./" ,'' ,$metadata['file']);	
	}
	$object = str_replace(get_home_path(), '', $metadata['file']);
	
	//在本地的存储路径
	//$file = $metadata['file'];
	$file = get_home_path().$object;	//向上兼容，较早的WordPress版本上$metadata['file']存放的是相对路径
	
	//设置可选参数
	$opt =array('Content-Type' => $metadata['type']);
	
	//执行上传操作
	_file_upload ( $object, $file, $opt);
	
	//如果不在本地保存，则删除本地文件（已废弃）
	//if( _is_delete_local_file() ){
	//	_delete_local_file($file);
	//}
	
	return $metadata;
}
add_filter('wp_handle_upload', 'upload_attachments', 999999);

/**
 * 上传图片的缩略图
 * @param $metadata
 * @return array
 */
function upload_thumbs($metadata) {
	if(_is_system_op()) {
		return $metadata;
	}

	//上传所有缩略图
	if (isset($metadata['sizes']) && count($metadata['sizes']) > 0)
	{
		//获取OSS插件的配置信息
		$oss_options = get_option('oss_options', TRUE);
		//是否需要上传缩略图（已废弃）
		//$nothumb = (attribute_escape($oss_options['nothumb']) == 'true');
		//是否需要删除本地文件（已废弃）
		//$is_delete_local_file = (attribute_escape($oss_options['nolocalsaving'])=='true');
		
		//获取上传路径
		$wp_uploads = wp_upload_dir();
		//得到本地文件夹和远端文件夹
		$file_path = $wp_uploads['path'].'/';
		if(get_option('upload_path') == '.') {
			$file_path = str_replace("./" ,'' , $file_path);
		}
		$object_path = str_replace(get_home_path(), '', $file_path);
		
		
		//there may be duplicated filenames,so ....
		foreach ($metadata['sizes'] as $val)
		{
			//生成object在OSS中的存储路径
			$object = $object_path.$val['file'];
			//生成本地存储路径
			$file = $file_path . $val['file'];
			//设置可选参数
			$opt =array('Content-Type' => $val['mime-type']);

			_file_upload ( $object, $file, $opt );
			
			//如果没有禁用上传缩略图功能，则执行上传操作（已废弃）
			//if( $nothumb == false ) {
			//	_file_upload ( $object, $file, $opt );
			//}
			//如果不在本地保存，则删除（已废弃）
			//if($is_delete_local_file) {
			//	_delete_local_file($file);
			//}
		}
	}
	
	return $metadata;
}
add_filter('wp_generate_attachment_metadata', 'upload_thumbs', 999999);


/**
 * 通过XML RPC协议上传
 * @param $methods
 * @return $methods
 */
function xml_to_oss($methods) {
    $methods['wp.uploadFile'] = 'oss_xmlrpc_upload';
    $methods['metaWeblog.newMediaObject'] = 'oss_xmlrpc_upload';
    return $methods;
}
/**
 * XML RPC相应处理函数
 * @param $args
 * @return array('file' => ,'url' => ,'type' => );
 */
function oss_xmlrpc_upload($args){
	$data  = $args[3];
	$object = sanitize_file_name( $data['name'] );
	$type = $data['type'];
	$contents = $data['bits'];
	
	$wp_upload_dir = wp_upload_dir();
	$upload_url_path = get_option('upload_url_path');
	$object_path =  $wp_upload_dir['url'].'/'.$object;
	$url = $upload_url_path.'/'.$object;
	_file_upload_by_contents($object_path, $contents, $type);

	return array( 'file' => $url, 'url' => $url, 'type' => $type );
}
add_filter( 'xmlrpc_methods', 'xml_to_oss');


/**
 * 删除远程服务器上的单个文件
 * @static
 * @param $file
 * @return void
 */
function delete_remote_file($file)
{	
	//获取WP配置信息
	$oss_options = get_option('oss_options', TRUE);
    $oss_bucket = attribute_escape($oss_options['bucket']);
	$oss_ak = attribute_escape($oss_options['ak']);
	$oss_sk = attribute_escape($oss_options['sk']);
	$oss_host = attribute_escape($oss_options['host']);
	if($oss_host==null || $oss_host == '')
		$oss_host = 'oss.aliyuncs.com';
	
	//得到远端路径
	$del_file_path = str_replace(get_home_path(), '', $file);
	try{
		//实例化存储对象
		if(!is_object($aliyun_oss))
			$aliyun_oss = new ALIOSS($oss_ak, $oss_sk, $oss_host);
		//删除文件
		$aliyun_oss->delete_object( $oss_bucket, $del_file_path);
	} catch(Exception $ex){}

	return $file;
}
add_action('wp_delete_file', 'delete_remote_file', 999999);

/**
 * 当upload_path为根目录时，需要移除URL中出现的“绝对路径”
 */
function modefiy_img_url($url, $post_id) {
	$home_path =  get_home_path();
    $url = str_replace($home_path ,'' ,$url);
	//_logged('2modify_url.txt',"url=$url");
	return $url;
}
if(get_option('upload_path') == '.') {
	add_filter('wp_get_attachment_url', 'modefiy_img_url', 30, 2);
}

function oss_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/oss-support.php' ) ) {
		$links[] = '<a href="options-general.php?page=' . OSS_BASEFOLDER . '/oss-support.php">'.__('Settings').'</a>';
	}

	return $links;
}

add_filter( 'plugin_action_links', 'oss_plugin_action_links', 10, 2 );

function oss_add_setting_page() {
    add_options_page('OSS Setting', 'OSS Setting', 8, __FILE__, 'oss_setting_page');
}

add_action('admin_menu', 'oss_add_setting_page');

function oss_setting_page() {

	$options = array();
	if($_POST['bucket']) {
		$options['bucket'] = trim(stripslashes($_POST['bucket']));
	}
	if($_POST['ak']) {
		$options['ak'] = trim(stripslashes($_POST['ak']));
	}
	if($_POST['sk']) {
		$options['sk'] = trim(stripslashes($_POST['sk']));
	}
	if($_POST['host']) {
		$options['host'] = trim(stripslashes($_POST['host']));
	}
	if($_POST['nothumb']) {
		$options['nothumb'] = (isset($_POST['nothumb']))?'true':'false';
	}
	if($_POST['nolocalsaving']) {
		$options['nolocalsaving'] = (isset($_POST['nolocalsaving']))?'true':'false';
	}
	if($_POST['upload_url_path']) {
		//仅用于插件卸载时比较使用
		$options['upload_url_path'] = trim(stripslashes($_POST['upload_url_path']));
	}
	
	//检查提交的AK/SK是否有管理该bucket的权限
	$flag = 0;
	if($_POST['bucket']&&$_POST['ak']&&$_POST['sk']){
		try{
			if(!is_object($aliyun_oss))
				$aliyun_oss = new ALIOSS( $options['ak'], $options['sk'], $options['host']);
			$oss_option = array(ALIOSS::OSS_CONTENT_TYPE => 'text/xml');
			$response = $aliyun_oss->get_bucket_acl($options['bucket'],$oss_option);
			if($response->status == 200) {
				$flag = 1;
				if( preg_match('/<Grant>public-read-write<\/Grant>/i',$response->body) > 0 ) {
					$flag = -11;
				} elseif( preg_match('/<Grant>private<\/Grant>/i',$response->body) > 0 ) {
					$flag = -12;
				}
			} elseif ($response->status == 403 && preg_match('/<Endpoint>/i',$response->body) > 0) {
				$flag = -2;
			}
			
		} catch(Exception $ex){
			$flag = -1;
		}
	}

	if($options !== array() ){
		//更新数据库
		update_option('oss_options', $options);
		
		$upload_path = trim(trim(stripslashes($_POST['upload_path'])),'/');
		$upload_path = ($upload_path == '') ? ('wp-content/uploads') : ($upload_path);
		update_option('upload_path', $upload_path );
		
		$upload_url_path = trim(trim(stripslashes($_POST['upload_url_path'])),'/');
		update_option('upload_url_path', $upload_url_path );
        
?>
<div class="updated"><p><strong>设置已保存！
<?php
	if($flag==0)
		echo '<span style="color:#F00">注意：您的AK/SK没有管理该Bucket的权限，因此不能正常使用！</span>';
	elseif($flag == -1)
		echo '<span style="color:#F00">注意：网络通信错误，未能校验您的AK/SK是否对该bucket是否具有管理权限</span>';
	elseif($flag == -11)
		echo '<span style="color:#F00">注意：该BUCKET现在处于“公开读写”状态，会有安全隐患哦！设置成“公开读”就足够了。</span>';
	elseif($flag == -12)
		echo '<span style="color:#F00">注意：该BUCKET现在处于“私有”状态，不能被其他人访问哦！建议将BUKET权限设置成“公开读”。</span>';
	elseif($flag == -2)
		echo '<span style="color:#F00">注意：该BUCKET的“存储地域”或“HOST主机”可能搞错了，请再次确认下。</span>';
?>
</strong></p></div>
<?php
    }

    $oss_options = get_option('oss_options', TRUE);
	$upload_path = get_option('upload_path');
	$upload_url_path = get_option('upload_url_path');

    $oss_bucket = attribute_escape($oss_options['bucket']);
    $oss_ak = attribute_escape($oss_options['ak']);
    $oss_sk = attribute_escape($oss_options['sk']);
	$oss_host = attribute_escape($oss_options['host']);
	if($oss_host==null || $oss_host == '')
		$oss_host = 'oss.aliyuncs.com';
	
	//不上传缩略图（已废弃）
	//$oss_nothumb = attribute_escape($oss_options['nothumb']);
	//$oss_nothumb = ( $oss_nothumb == 'true' );
	$oss_nothumb = false;
	
	//不在本地保留备份（已废弃）
	//$oss_nolocalsaving = attribute_escape($oss_options['nolocalsaving']);
	//$oss_nolocalsaving = ( $oss_nolocalsaving == 'true' );
	$oss_nolocalsaving = false;
?>
<div class="wrap" style="margin: 10px;">
    <h2>阿里云附件 v2.1 设置</h2>
    <form name="form1" method="post" action="<?php echo wp_nonce_url('./options-general.php?page=' . OSS_BASEFOLDER . '/oss-support.php'); ?>">
		<table class="form-table">
			<tr>
				<th><legend>Bucket 设置</legend></th>
				<td>
					<input type="text" name="bucket" value="<?php echo $oss_bucket;?>" size="50" placeholder="BUCKET"/>
					<p>请先访问 <a href="http://i.aliyun.com/dashboard?type=oss" target="_blank">阿里云存储</a> 创建 <code>bucket</code> ，再填写以上内容。</p>
				</td>
			</tr>
			<tr>
				<th><legend>Access Key</legend></th>
				<td><input type="text" name="ak" value="<?php echo $oss_ak;?>" size="50" placeholder="Access Key"/></td>
			</tr>
			<tr>
				<th><legend>Secret Key</legend></th>
				<td>
					<input type="password" name="sk" value="<?php echo $oss_sk;?>" size="50" placeholder="Secret Key"/>
					<p>访问 <a href="https://ak-console.aliyun.com/#/accesskey" target="_blank">阿里云 密钥管理页面</a>，获取 <code>AK/SK</code> 。</p>
				</td>
			</tr>
			<tr>
				<th><legend>HOST主机</legend></th>
				<td>
					<select id="hosts_set" onchange="hostChange()">
						<option value="oss-cn-qingdao.aliyuncs.com">青岛节点（外网）</option>
						<option value="oss-cn-qingdao-internal.aliyuncs.com">青岛节点（内网）</option>
						
						<option value="oss-cn-beijing.aliyuncs.com">北京节点（外网）</option>
						<option value="oss-cn-beijing-internal.aliyuncs.com">北京节点（内网）</option>
						
						<option value="oss-cn-hangzhou.aliyuncs.com">杭州节点（外网）</option>
						<option value="oss-cn-hangzhou-internal.aliyuncs.com">杭州节点（内网）</option>
						
						<option value="oss-cn-shanghai.aliyuncs.com">上海节点（外网）</option>
						<option value="oss-cn-shanghai-internal.aliyuncs.com">上海节点（内网）</option>
						
						<option value="oss-cn-hongkong.aliyuncs.com">香港节点（外网）</option>
						<option value="oss-cn-hongkong-internal.aliyuncs.com">香港节点（内网）</option>
						
						<option value="oss-cn-shenzhen.aliyuncs.com">深圳节点（外网）</option>
						<option value="oss-cn-shenzhen-internal.aliyuncs.com">深圳节点（内网）</option>
						
						<option value="oss-us-west-1.aliyuncs.com">美国节点（外网）</option>
						<option value="oss-us-west-1-internal.aliyuncs.com">美国节点（内网）</option>
						
						<option value="oss-ap-southeast-1.aliyuncs.com">新加坡节点（外网）</option>
						<option value="oss-ap-southeast-1-internal.aliyuncs.com">新加坡节点（内网）</option>
						
						<option value="">自定义设置</option>
					</select>
					<input type="text" id="oss_host" name="host" value="<?php echo $oss_host;?>" size="29" placeholder="oss.aliyuncs.com" onchange="initSelectSet()"/>
					<p>默认 <code>杭州节点（外网）</code> ，其他节点需要更改。<a href="http://help.aliyun.com/view/11108271_13438690.html" target="_blank">【查看详情】</a></p>
					<script type="text/javascript">
						function initSelectSet() {
							var hostname = document.getElementById("oss_host").value;
							var hostSet = document.getElementById("hosts_set");
							var i = 0;
							for (i = 0; i < hostSet.options.length; i++) {
								if (hostSet.options[i].value == hostname) {
									hostSet.selectedIndex = i;
									break;
								}
							}
							if(i == hostSet.options.length) {
								hostSet.selectedIndex = i-1;
							}
						}
						function hostChange(){
							var host = document.getElementById("hosts_set").value;
							document.getElementById("oss_host").value = host;
						}
						initSelectSet();
					</script>
				</td>
			</tr>

			<!-- 下面部分已废弃 -->
			<tr style="display:none;">
				<th><legend>不上传缩略图</legend></th>
				<td><input type="checkbox" name="nothumb" <?php if($oss_nothumb) echo 'checked="TRUE"';?> disabled="true" /></td>
			</tr>
			<tr style="display:none;">
				<th><legend>不在本地保留备份</legend></th>
				<td><input type="checkbox" name="nolocalsaving" <?php if($oss_nolocalsaving) echo 'checked="TRUE"';?> disabled="true" /></td>
			</tr>
			<!-- 上面部分已废弃 -->

			<tr>
				<th><legend>本地文件夹：</legend></th>
				<td>
					<input type="text" name="upload_path" value="<?php echo $upload_path;?>" size="50" placeholder="请输入上传文件夹"/>
					<p>附件在服务器上的存储位置，例如： <code>wp-content/uploads</code> （注意不要以“/”开头和结尾），根目录请输入<code>.</code>。</p>
				</td>
			</tr>
			<tr>
				<th><legend>URL前缀：</legend></th>
				<td>
					<input type="text" name="upload_url_path" value="<?php echo $upload_url_path;?>" size="50" placeholder="请输入URL前缀"/>
					<p><b>注意：</b></p>
					<p>1）URL前缀的格式为 <code>http://{OSS域名}</code> （“本地文件夹”为 <code>.</code> 时），或者 <code>http://{OSS域名}/{本地文件夹}</code> ，“本地文件夹”务必与上面保持一致（结尾无 <code>/</code> ）。</p>
					<p>2）OSS中的存放路径（即“文件夹”）与上述 <code>本地文件夹</code> 中定义的路径是相同的（出于方便切换考虑）。</p>
					<p>3）如果需要使用 <code>独立域名</code> ，直接将 <code>{OSS域名}</code> 替换为 <code>独立域名</code> 即可。</p>
				</td>
			</tr>
			<tr>
				<th><legend>更新选项</legend></th>
				<td><input type="submit" name="submit" value="更新" /></td>
			</tr>
		</table>
    </form>
</div>
<?php
}
?>