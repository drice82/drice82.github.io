<?php
/*

     WordPress免登录发布接口,支持最新Wordpress4.8.X
     最后更新2017-08-08
     适用于火车头采集器等任意采集器或脚本程序进行日志发布。


 ■ 功能特性：

     1. 随机时间安排与预约发布功能： 可以设定发布时间以及启用预约发布功能
     2. 自动处理服务器时间与博客时间的时区差异
     xxxxxx3. 永久链接的自动翻译设置。根据标题自动翻译为英文并进行seo处理xxxxxxx
     5. 多标签处理(多个标签可以用火车头默认的tag|||tag2|||tag3的形式)
     6. 增加了发文后ping功能
     7. 增加了“pending review”的设置
     8. 增加了多作者功能，发布参数中指定post_author
     9. 增加了自定义域功能，发布参数指定post_meta_list=key1$$value1|||key2$$value2，不同域之间用|||隔开，名称与内容之间用$$隔开。
     10. 增加自动增加分类功能，如果网站内没有这个分类，会自动增加分类。
     -----------以下是20150715版本更新内容----------
     11. 增加上传图片功能，根据主题或网站后台设置自动生成缩略图，并自动设置第一张图片为文章的特色图片。
     12. 增加支持单篇文章多个栏目分类和tag   ，多个栏目之间请用英文逗号分开，设置分类时可以是分类名和分类ID，也可以混合写 如： 分类一,4,二分类,6  (注意是半角的逗号分开)。
     13. 增加多作者功能，可设置多个作者随机发布文章。
     -----------以下是20150813版本更新内容----------
     14. 修复发布时间为空的BUG，需要按采集的时间发布，请在发布参数中指定post_date，正确的时间格式为2015-08-12 23:45:55或者2015-08-12 23:45
     15. 增加随机文章阅读数功能，可定义固定值或随机值范围。
     16. 由于谷歌被墙，故删除原有永久链接自动翻译功能，改为永久链接自动判断是否为中文并自动转换成MD5值，可设置字符串长度。
     -----------以下是20150818版本更新内容----------
     17. 修正预约发布的BUG，如果POST过来的数据包涵时间，则以时间为准立即发布，反之则以接口文件配置时间发布。即可使用预约发布，保存为草稿等功能。
     18. 增加自定义作者功能，如果提交的数据为用户名的话，会自动检测系统是否存在该用户，如果已存在则以该用户发布，不存在则自动新建用户（接口以针对中文用户名进行了处理）。
     -----------以下是20170604版本更新内容----------
	   19. 修正分类发布的问题,  不再强行创建分类, 不再插入乱码分类名称, 接口会将中文分类名称转换为拼音, 然后根据拼音来判断分类别名是否存在, 如果不存在, 会创建中文分类名称, 拼音的分类别名.
	   -----------以下是20170605版本更新内容----------
	   20. taxonomy之前的处理方式为给文章增加一个自定义字段,现在该方法保留,变量为$tax_input
	   21. 增加了taxonomy自定义分类方式的分类采集, 变量为$post_taxonomy, 数据格式请参照 "taxonomy$$term,term2,term3,term4|||taxonomy$$term,term2,term3,term4".
	   -----------以下是201706011版本更新内容----------
	   22. 增加了日志功能, lily_debug=true为打开,默认关闭,会在当前目录下生成 locoylog.txt文件
     -----------以下是201708.08版本更新内容----------
     23. 支持Wordpress 4.8.1, 修复和部分主题冲突导致HTTP 500错误的BUG
     -----------以下是201708.12版本更新内容----------
     24. 增加单独设置缩略图功能, 如需使用该功能, 请增加 缩略图标签, 并采集图片并设置下载
     25. 增加验证标题功能, 使用该功能需要修改配置参数$checkTitle=true,开启该功能后会检测标题是否存在，如存在则不采集当前数据
     -----------以下是201708.12版本更新内容----------
     26. 修复采集日期的BUG

   ■ 使用说明:（按照需求修改配置参数,添加配置时请注意添加引号）
     $post_author_default    = 1;    	  //默认作者的id，默认为admin（这里是作者ID号码，并非作者名）
     $post_status    = "publish";"publish"：立即发布,
     $time_interval  = 60;        //发布时间间隔，单位为秒 。可设置随机数值表达式，如12345 * rand(0,17)，设置为负数可将发布时间设置为当前时间减去这里设置的时间
     $post_next      = "next"; //now:发布时间=当前时间+间隔时间值
                               //next: 发布时间=最后一篇时间+间隔时间值
     $post_ping      = false;  //发布后是否执行ping
     $translate_slug = false;  //是否将中文标题转换为MD5值，如需开启请设置为true或MD5值长度，建议设置为大于10，小于33的数字。
     $secretWord     = '123456';  //接口密码，如果不需要密码，则设为$secretWord=false ;
     $post_category  = '';     //分类，默认为系统获取的分类ID，如果提交的数据是分类名称的话，会自动检测系统是否存在同名的分类，否则将新建一个分类，并将文章发布到新建分类里。
     $pViews				 = false;	 //文章已阅读数，默认关闭，可设置随机数值表达式，如rand(100,200)，也可以设置固定值。
     关于发布时间优先级的说明：如果采集以采集到的时间作为发布时间，则本文件内的关于时间的设置无效，反之则以本文件内的相关时间配置来决定发布时间。
*/

//-------------------配置参数开始，根据需要修改-------------------------
$post_author_default    = 1;
$post_status    = "publish";//立即发布
//$save    = "save";//发布到草稿箱
$time_interval  = 1;
$post_next      = "now";
$post_ping      = false;
$translate_slug = false;
$secretWord     = 'LilySoftware';
$pViews					=	false;
$checkTitle     = false; //检测标题是否重复
$lily_debug			= false;//调试模式, true会记录一些关键性信息, 如果发布失败, 请打开次选项, 接口会将异常信息写入locoylog.txt
//-------------------配置参数结束，以下请勿修改-------------------------


//开始
if (isset($_GET['action']))
{
  $hm_action=$_GET['action'];
} else
{
	if($lily_debug){
		writelog('操作被禁止');
	}
  die ("操作被禁止>");
}

$post = array_map('addslashes',$_POST);
@$tax_input = $_POST[tax_input];
include "./wp-config.php";


if ($post_ping) require_once("./wp-includes/comment.php");
if ( !class_exists("Snoopy") )	require_once ("./wp-includes/class-snoopy.php");
if ($hm_action== "list")
{
  hm_print_catogary_list();
}
elseif($hm_action== "update")
{
  hm_publish_pending_post();
}
elseif($hm_action == "save")
{
  if (isset($secretWord)&&($secretWord!=false)) {
    if (!isset($_GET['secret']) || $_GET['secret'] != $secretWord) {
      die('接口密码错误，请修改配置文件或者修改发布参数，保持两者统一。');
    }
  }
  extract($post);
  if ($post_title=='[标题]'||$post_title=='') {die('标题为空');if($lily_debug){writelog('标题为空');}}
  if($checkTitle){
    $sql = "SELECT ID FROM $wpdb->posts WHERE post_title = '$post_title'";
    $t_row = $wpdb->query($sql);
    if($t_row) {die('发布成功');};
  }
  if ($post_content=='[内容]'||$post_content=='') {die('内容为空');if($lily_debug){writelog('内容为空');}}
  if ($post_category=='[分类id]'||$post_category=='') {die('分类id为空');if($lily_debug){writelog('分类ID为空');}}
  if ($tag=='[SY_tag]') {
    $tag='';
  }
  if (!isset($post_date) ||strlen($post_date)<8) $post_date=false;
  if (!isset($post_author)) {
    $post_author=$post_author_default;
  } else {
    $post_author=hm_add_author($post_author);
  }
  if (!isset($post_meta_list)) $post_meta_list="";
  /*附件处理*/
  if (!empty($_FILES[fujian0][name])) {
    require_once('./wp-load.php');
    require_once('./wp-admin/includes/file.php');
    require_once('./wp-admin/includes/image.php');
    $i = 0;
    while (isset($_FILES['fujian'.$i])) {
      $fujian[$i] = $_FILES['fujian'.$i];
      $filename = $fujian[$i]['name'];
      $fileHouZ=array_pop(explode(".",$filename));
      //附件保存格式【时间】
      $upFileTime=date("YmdHis");
      //更改上传文件的文件名为时间+随机数+后缀
      $fujian[$i]['name'] = $upFileTime."-".mt_rand(1,100).".".$fileHouZ;
      $uploaded_file = wp_handle_upload($fujian[$i],array('test_form' => false));
      $post_content = str_replace("\'".$filename."\'","\"".$uploaded_file[url]."\"",$post_content);
      $post_content = str_replace($filename,$uploaded_file[url],$post_content);
      if (isset($uploaded_file['error']))wp_die($uploaded_file['error']);
	  
      $file = $uploaded_file['file'];
      $new_file = iconv('GBK','UTF-8',$file);
      $url = iconv('GBK','UTF-8',$uploaded_file['url']);
      $type = $uploaded_file['type'];
      $attachment = array(
                      'guid' => $url,
                      'post_mime_type' => $type,
                      'post_title' => $filename,
                      'post_content' => '',
                      'post_status' => 'inherit'
                    );
						  	  
      $attach_id = wp_insert_attachment($attachment,$new_file);

      if ($i==0)$fujianid=$attach_id;
	  if(strpos($fujian[$i]['type'], 'image') !== false){
		  $attach_data = wp_generate_attachment_metadata($attach_id,$file);
		  $attach_data['file'] = iconv('GBK','UTF-8',$attach_data['file']);
		  foreach ($attach_data['sizes'] as $key => $sizes) {
			$sizes['file'] = iconv('GBK','UTF-8',$sizes['file']);
			$attach_data['sizes'][$key]['file'] = $sizes['file'];
		  }
		  wp_update_attachment_metadata($attach_id,$attach_data);
	  }
      $i++;
    }
  }

  hm_do_save_post(array('post_title'=>$post_title,
                        'post_content'=>$post_content,
                        'post_category'=>$post_category,
						'post_excerpt'=>$post_excerpt,
                        'tags_input'=>$tag,
                        'post_date'=>$post_date,
                        'post_author'=>$post_author,
                        'post_meta_list'=>$post_meta_list,
						'post_taxonomy'=>$post_taxonomy,
                        'fujianid'=>$fujianid));
  echo '发布成功';
	if($lily_debug){
		writelog('发布成功');
	}
}
else
{
	if($lily_debug){
		writelog('非法操作');
	}
  echo '非法操作['.$hm_action.']';
}


/**
* 在当前文件夹下创建locoylog.txt日志文件
* @str (string|array}写入内容
*/
function writelog($str){
	$time_difference = absint(get_option('gmt_offset')) * 3600;
	$time_now = time()+$time_difference;
	$myfile = fopen("locoylog.txt", "a") or die("Unable to open file!");
	if(is_array($str)){
		ob_start();
		var_dump($str);
		$txt = ob_get_clean();
	}else{
		$txt = $str;
	}
	fwrite($myfile,"\n".date("Y-m-d H:i",$time_now)."  ".get_real_ip()."   ");
	fwrite($myfile, $txt);
	fclose($myfile);
}

//获取真实IP
function get_real_ip()
{
 $ip=false;
 if(!empty($_SERVER["HTTP_CLIENT_IP"])){
  $ip = $_SERVER["HTTP_CLIENT_IP"];
 }
 if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
  $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
  if($ip){
   array_unshift($ips, $ip); $ip = FALSE;
  }
  for($i = 0; $i < count($ips); $i++){
   if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])){
    $ip = $ips[$i];
    break;
   }
  }
 }
 return($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}


//汉字转换拼音类

class CUtf8_PY {
  /**
   * 拼音字符转换图
   * @var array
   */
  private static $_aMaps = array('a'=>-20319,'ai'=>-20317,'an'=>-20304,'ang'=>-20295,'ao'=>-20292,'ba'=>-20283,'bai'=>-20265,'ban'=>-20257,'bang'=>-20242,'bao'=>-20230,'bei'=>-20051,'ben'=>-20036,'beng'=>-20032,'bi'=>-20026,'bian'=>-20002,'biao'=>-19990,'bie'=>-19986,'bin'=>-19982,'bing'=>-19976,'bo'=>-19805,'bu'=>-19784,'ca'=>-19775,'cai'=>-19774,'can'=>-19763,'cang'=>-19756,'cao'=>-19751,'ce'=>-19746,'ceng'=>-19741,'cha'=>-19739,'chai'=>-19728,'chan'=>-19725,'chang'=>-19715,'chao'=>-19540,'che'=>-19531,'chen'=>-19525,'cheng'=>-19515,'chi'=>-19500,'chong'=>-19484,'chou'=>-19479,'chu'=>-19467,'chuai'=>-19289,'chuan'=>-19288,'chuang'=>-19281,'chui'=>-19275,'chun'=>-19270,'chuo'=>-19263,'ci'=>-19261,'cong'=>-19249,'cou'=>-19243,'cu'=>-19242,'cuan'=>-19238,'cui'=>-19235,'cun'=>-19227,'cuo'=>-19224,'da'=>-19218,'dai'=>-19212,'dan'=>-19038,'dang'=>-19023,'dao'=>-19018,'de'=>-19006,'deng'=>-19003,'di'=>-18996,'dian'=>-18977,'diao'=>-18961,'die'=>-18952,'ding'=>-18783,'diu'=>-18774,'dong'=>-18773,'dou'=>-18763,'du'=>-18756,'duan'=>-18741,'dui'=>-18735,'dun'=>-18731,'duo'=>-18722,'e'=>-18710,'en'=>-18697,'er'=>-18696,'fa'=>-18526,'fan'=>-18518,'fang'=>-18501,'fei'=>-18490,'fen'=>-18478,'feng'=>-18463,'fo'=>-18448,'fou'=>-18447,'fu'=>-18446,'ga'=>-18239,'gai'=>-18237,'gan'=>-18231,'gang'=>-18220,'gao'=>-18211,'ge'=>-18201,'gei'=>-18184,'gen'=>-18183,'geng'=>-18181,'gong'=>-18012,'gou'=>-17997,'gu'=>-17988,'gua'=>-17970,'guai'=>-17964,'guan'=>-17961,'guang'=>-17950,'gui'=>-17947,'gun'=>-17931,'guo'=>-17928,'ha'=>-17922,'hai'=>-17759,'han'=>-17752,'hang'=>-17733,'hao'=>-17730,'he'=>-17721,'hei'=>-17703,'hen'=>-17701,'heng'=>-17697,'hong'=>-17692,'hou'=>-17683,'hu'=>-17676,'hua'=>-17496,'huai'=>-17487,'huan'=>-17482,'huang'=>-17468,'hui'=>-17454,'hun'=>-17433,'huo'=>-17427,'ji'=>-17417,'jia'=>-17202,'jian'=>-17185,'jiang'=>-16983,'jiao'=>-16970,'jie'=>-16942,'jin'=>-16915,'jing'=>-16733,'jiong'=>-16708,'jiu'=>-16706,'ju'=>-16689,'juan'=>-16664,'jue'=>-16657,'jun'=>-16647,'ka'=>-16474,'kai'=>-16470,'kan'=>-16465,'kang'=>-16459,'kao'=>-16452,'ke'=>-16448,'ken'=>-16433,'keng'=>-16429,'kong'=>-16427,'kou'=>-16423,'ku'=>-16419,'kua'=>-16412,'kuai'=>-16407,'kuan'=>-16403,'kuang'=>-16401,'kui'=>-16393,'kun'=>-16220,'kuo'=>-16216,'la'=>-16212,'lai'=>-16205,'lan'=>-16202,'lang'=>-16187,'lao'=>-16180,'le'=>-16171,'lei'=>-16169,'leng'=>-16158,'li'=>-16155,'lia'=>-15959,'lian'=>-15958,'liang'=>-15944,'liao'=>-15933,'lie'=>-15920,'lin'=>-15915,'ling'=>-15903,'liu'=>-15889,'long'=>-15878,'lou'=>-15707,'lu'=>-15701,'lv'=>-15681,'luan'=>-15667,'lue'=>-15661,'lun'=>-15659,'luo'=>-15652,'ma'=>-15640,'mai'=>-15631,'man'=>-15625,'mang'=>-15454,'mao'=>-15448,'me'=>-15436,'mei'=>-15435,'men'=>-15419,'meng'=>-15416,'mi'=>-15408,'mian'=>-15394,'miao'=>-15385,'mie'=>-15377,'min'=>-15375,'ming'=>-15369,'miu'=>-15363,'mo'=>-15362,'mou'=>-15183,'mu'=>-15180,'na'=>-15165,'nai'=>-15158,'nan'=>-15153,'nang'=>-15150,'nao'=>-15149,'ne'=>-15144,'nei'=>-15143,'nen'=>-15141,'neng'=>-15140,'ni'=>-15139,'nian'=>-15128,'niang'=>-15121,'niao'=>-15119,'nie'=>-15117,'nin'=>-15110,'ning'=>-15109,'niu'=>-14941,'nong'=>-14937,'nu'=>-14933,'nv'=>-14930,'nuan'=>-14929,'nue'=>-14928,'nuo'=>-14926,'o'=>-14922,'ou'=>-14921,'pa'=>-14914,'pai'=>-14908,'pan'=>-14902,'pang'=>-14894,'pao'=>-14889,'pei'=>-14882,'pen'=>-14873,'peng'=>-14871,'pi'=>-14857,'pian'=>-14678,'piao'=>-14674,'pie'=>-14670,'pin'=>-14668,'ping'=>-14663,'po'=>-14654,'pu'=>-14645,'qi'=>-14630,'qia'=>-14594,'qian'=>-14429,'qiang'=>-14407,'qiao'=>-14399,'qie'=>-14384,'qin'=>-14379,'qing'=>-14368,'qiong'=>-14355,'qiu'=>-14353,'qu'=>-14345,'quan'=>-14170,'que'=>-14159,'qun'=>-14151,'ran'=>-14149,'rang'=>-14145,'rao'=>-14140,'re'=>-14137,'ren'=>-14135,'reng'=>-14125,'ri'=>-14123,'rong'=>-14122,'rou'=>-14112,'ru'=>-14109,'ruan'=>-14099,'rui'=>-14097,'run'=>-14094,'ruo'=>-14092,'sa'=>-14090,'sai'=>-14087,'san'=>-14083,'sang'=>-13917,'sao'=>-13914,'se'=>-13910,'sen'=>-13907,'seng'=>-13906,'sha'=>-13905,'shai'=>-13896,'shan'=>-13894,'shang'=>-13878,'shao'=>-13870,'she'=>-13859,'shen'=>-13847,'sheng'=>-13831,'shi'=>-13658,'shou'=>-13611,'shu'=>-13601,'shua'=>-13406,'shuai'=>-13404,'shuan'=>-13400,'shuang'=>-13398,'shui'=>-13395,'shun'=>-13391,'shuo'=>-13387,'si'=>-13383,'song'=>-13367,'sou'=>-13359,'su'=>-13356,'suan'=>-13343,'sui'=>-13340,'sun'=>-13329,'suo'=>-13326,'ta'=>-13318,'tai'=>-13147,'tan'=>-13138,'tang'=>-13120,'tao'=>-13107,'te'=>-13096,'teng'=>-13095,'ti'=>-13091,'tian'=>-13076,'tiao'=>-13068,'tie'=>-13063,'ting'=>-13060,'tong'=>-12888,'tou'=>-12875,'tu'=>-12871,'tuan'=>-12860,'tui'=>-12858,'tun'=>-12852,'tuo'=>-12849,'wa'=>-12838,'wai'=>-12831,'wan'=>-12829,'wang'=>-12812,'wei'=>-12802,'wen'=>-12607,'weng'=>-12597,'wo'=>-12594,'wu'=>-12585,'xi'=>-12556,'xia'=>-12359,'xian'=>-12346,'xiang'=>-12320,'xiao'=>-12300,'xie'=>-12120,'xin'=>-12099,'xing'=>-12089,'xiong'=>-12074,'xiu'=>-12067,'xu'=>-12058,'xuan'=>-12039,'xue'=>-11867,'xun'=>-11861,'ya'=>-11847,'yan'=>-11831,'yang'=>-11798,'yao'=>-11781,'ye'=>-11604,'yi'=>-11589,'yin'=>-11536,'ying'=>-11358,'yo'=>-11340,'yong'=>-11339,'you'=>-11324,'yu'=>-11303,'yuan'=>-11097,'yue'=>-11077,'yun'=>-11067,'za'=>-11055,'zai'=>-11052,'zan'=>-11045,'zang'=>-11041,'zao'=>-11038,'ze'=>-11024,'zei'=>-11020,'zen'=>-11019,'zeng'=>-11018,'zha'=>-11014,'zhai'=>-10838,'zhan'=>-10832,'zhang'=>-10815,'zhao'=>-10800,'zhe'=>-10790,'zhen'=>-10780,'zheng'=>-10764,'zhi'=>-10587,'zhong'=>-10544,'zhou'=>-10533,'zhu'=>-10519,'zhua'=>-10331,'zhuai'=>-10329,'zhuan'=>-10328,'zhuang'=>-10322,'zhui'=>-10315,'zhun'=>-10309,'zhuo'=>-10307,'zi'=>-10296,'zong'=>-10281,'zou'=>-10274,'zu'=>-10270,'zuan'=>-10262,'zui'=>-10260,'zun'=>-10256,'zuo'=>-10254);

  /**
   * 将中文编码成拼音
   * @param string $utf8Data utf8字符集数据
   * @param string $sRetFormat 返回格式 [head:首字母|all:全拼音]
   * @return string
   */
  public static function encode($utf8Data, $sRetFormat='head'){
      $sGBK = iconv('UTF-8', 'GBK', $utf8Data);
      $aBuf = array();
      for ($i=0, $iLoop=strlen($sGBK); $i<$iLoop; $i++) {
          $iChr = ord($sGBK{$i});
          if ($iChr>160)
              $iChr = ($iChr<<8) + ord($sGBK{++$i}) - 65536;
          if ('head' === $sRetFormat)
              $aBuf[] = substr(self::zh2py($iChr),0,1);
          else
              $aBuf[] = self::zh2py($iChr);
      }
      if ('head' === $sRetFormat)
          return implode('', $aBuf);
      else
          return implode('', $aBuf);
  }

  /**
   * 中文转换到拼音(每次处理一个字符)
   * @param number $iWORD 待处理字符双字节
   * @return string 拼音
   */
  private static function zh2py($iWORD) {
      if($iWORD>0 && $iWORD<160 ) {
          return chr($iWORD);
      } elseif ($iWORD<-20319||$iWORD>-10247) {
          return '';
      } else {
          foreach (self::$_aMaps as $py => $code) {
              if($code > $iWORD) break;
              $result = $py;
          }
          return $result;
      }
  }
}
//汉字转换拼音结束




function hm_debug_info($msg)
{
global $logDebugInfo;
if ($logDebugInfo) echo $msg."<br/>\n";
}

function hm_tranlate($text)
{
global $translate_slug;
$pattern = '/[^\x00-\x80]/';
if (preg_match($pattern,$text)) {
  $htmlret = substr(md5($text),0,$translate_slug);
} else {
  $htmlret =  $text;
}
return $htmlret;
}

function hm_print_catogary_list()
{
$cats = get_categories("hierarchical=0&hide_empty=0");
foreach ((array) $cats as $cat) {
  echo '<<<'.$cat->cat_ID.'--'.$cat->cat_name.'>>>';
}
}

function hm_get_post_time($post_next="normal")
{
global $time_interval;
global $wpdb;

$time_difference = absint(get_option('gmt_offset')) * 3600;
$tm_now = time()+$time_difference;

if ($post_next=='now') {
  $tm=time()+$time_difference;
} else { //if ($post_next=='next')
  $tm = time()+$time_difference;
  $posts = $wpdb->get_results( "SELECT post_date FROM $wpdb->posts ORDER BY post_date DESC limit 0,1" );
  foreach ( $posts as $post ) {
    $tm=strtotime($post->post_date);
  }
}
return $tm+$time_interval;
}

function hm_publish_pending_post()
{
global $wpdb;
$tm_now = time()+absint(get_option('gmt_offset')) * 3600;
$now_date=date("Y-m-d H:i:s",$tm_now);
$wpdb->get_results( "UPDATE $wpdb->posts set `post_status`='publish' WHERE `post_status`='pending' and `post_date`<'$now_date'" );
}

function hm_add_category($post_category, $post_taxonomy_name = 'category')
{
if (!function_exists('wp_insert_category')) @include "./wp-admin/includes/taxonomy.php";
global $wpdb;
$post_category_new=array();
$post_category_list= array_unique(explode(",",$post_category));
foreach ($post_category_list as $category) {
  $cat_ID =intval($category);
  if ($cat_ID==0 || $cat_ID > 1900) {
  $category_name = $category;
  if (preg_match("/[\x7f-\xff]/", $category)) {
  $category = CUtf8_PY::encode($category,'ALL');
  }
    $category = $wpdb->escape($category);
   $cat_ID = get_term_by('slug', $category, $post_taxonomy_name);
  if($cat_ID){
     array_push($post_category_new,$cat_ID ->term_id);
  }else{
      $cat_ID = wp_insert_category(array('cat_name' => $category_name, 'category_nicename' => $category, 'taxonomy' => $post_taxonomy_name));
    $cat_ID = get_term_by('slug', $category, $post_taxonomy_name);
     array_push($post_category_new,$cat_ID ->term_id);
  }
  } else {
    array_push($post_category_new,$cat_ID);
  }
}
if($post_taxonomy_name == 'category'){
return $post_category_new;
}else{
return array($post_taxonomy_name => $post_category_new);
}
return $post_category_new;
}

function hm_add_author($post_author)
{
global $wpdb,$post_author_default;
$User_ID =intval($post_author);
if ($User_ID == 0) {
  $pattern = '/[^\x00-\x80]/';
  if (preg_match($pattern,$post_author)) {
    $LoginName = substr(md5($post_author),0,10);
  } else {
    $LoginName =  $post_author;
  }
  $User_ID = $wpdb->get_col("SELECT ID FROM $wpdb->users WHERE user_login = '$LoginName' ORDER BY ID");
  $User_ID = $User_ID[0];
  if (empty($User_ID)) {
    $website = 'http://'.$_SERVER['HTTP_HOST'];
    $userdata = array(
                  'user_login'  =>  "$LoginName",
                  'first_name'	=>	$post_author,
                  'user_nicename'    =>  $post_author,
                  'display_name'    =>  $post_author,
                  'nickname'    =>  $post_author,
                  'user_url'    =>  $website,
                  'role'    =>  'contributor',
                  'user_pass'   =>  NULL);
    $User_ID = wp_insert_user( $userdata );
  }
  $post_author = $User_ID;
} else {
  $post_author = $post_author_default;
}
return $post_author;
}

function hm_strip_slashes($str)
{
if (get_magic_quotes_gpc()) {
  return stripslashes($str);
} else {
  return $str;
}
}
function checkDatetime($str)
{

$format="Y-m-d H:i";
$format1="Y-m-d H:i:s";
$format2="Y-m-d";
$unixTime=strtotime($str);
$checkDate= date($format, $unixTime);
$checkDate1= date($format1, $unixTime);
$checkDate2= date($format2, $unixTime);
if ($checkDate==$str or $checkDate1==$str or $checkDate2==$str) {
  return true;
} else {
  return false;
}
}

function hm_do_save_post($post_detail)
{
global $post_author,$post_ping,$post_status,$translate_slug,$autoAddCategory,$post_next,$pViews,$tax_input,$lily_debug,$comment,$commentname,$wpdb,$commentdate;
extract($post_detail);
$post_title=trim(hm_strip_slashes($post_title));
$post_name=$post_title;
if ($translate_slug) $post_name=hm_tranlate($post_name);
$post_name=sanitize_title( $post_name);
if ( strlen($post_name) < 2 ) $post_name="";
$post_content=hm_strip_slashes($post_content);
$tags_input=str_replace("|||",",",$tags_input);
if (isset($post_date) && $post_date && checkDatetime($post_date)) {
  $tm=strtotime($post_date);
  $time_difference =  absint(get_option('gmt_offset')) * 3600;
  $post_date=date("Y-m-d H:i:s",$tm);
  $post_date_gmt = gmdate('Y-m-d H:i:s', $tm-$time_difference);
} else {
  $tm=hm_get_post_time($post_next);
  $time_difference = absint(get_option('gmt_offset')) * 3600;
  $post_date=date("Y-m-d H:i:s",$tm);
  $post_date_gmt = gmdate('Y-m-d H:i:s', $tm-$time_difference);
  if ($post_status=='next') $post_status='publish';
}
$post_category=hm_add_category($post_category);
$post_data = compact('post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_category', 'post_status', 'post_excerpt', 'post_name','tags_input');
$post_data = add_magic_quotes($post_data);
$postID = wp_insert_post($post_data);
if(!empty($comment)){
  $comment = str_replace(array("\r\n", "\r", "\n"), "", $comment);
  $arraycomment = explode('|||', $comment);

  $commentname = str_replace(' ','',$commentname);
  $commentname = str_replace(array("\r\n", "\r", "\n"), "", $commentname);
  $arraycommentname = explode('|||', $commentname);
  $comment_count = count($arraycomment) -1 ;
  $wpdb->get_results("UPDATE $wpdb->posts set `comment_count` = $comment_count WHERE `ID` = $postID");
  foreach($arraycommentname as $k => $v){
    if($v != ''){
      $format="Y-m-d H:i:s";
      $d = strtotime($commentdate);
      if($d != ''){
        $date = date($format,$d);
        $gmtdate = gmdate($format, $d);
      }else{
        $date = date($format);
        $gmtdate = gmdate($format);
      }
      $res = $wpdb->get_results("INSERT INTO $wpdb->comments (`comment_post_ID`,`comment_author`,`comment_date`,`comment_date_gmt`,`comment_content`,`user_id`) VALUES ($postID,'$v','$date','$gmtdate','$arraycomment[$k]',1)");
    }
  }
 
}

if($lily_debug){
  writelog('postID: '.$postID);
}
//发布自定义taxonomy
if(!empty($post_taxonomy)){
    $strArr = explode('|||', $post_taxonomy);
    foreach ($strArr as $taxonomy){
    $term = explode('$$', $taxonomy);
    $tax_input = hm_add_category($term[1], $term[0]);
    $tax_input[$term[0]];
    wp_set_object_terms($postID, $tax_input[$term[0]], $term[0] );
    }
}
//发布自定义taxonomy 结束
if (!empty($fujianid)) {
  require_once('./wp-includes/post.php');
  set_post_thumbnail($postID,$fujianid);
}//tstx
if (!empty($post_meta_list)) {
  $post_meta_array= array_unique(explode("|||",$post_meta_list));
  foreach ($post_meta_array as $ppm) {
    $pp2=explode("$$",$ppm);
    if (!empty($pp2[0])&&!empty($pp2[1])) add_post_meta($postID,$pp2[0],$pp2[1],true);
  }
}
if (!empty($pViews) && $pViews) add_post_meta($postID,'views',$pViews,true);
if (!empty($tax_input)) {
  foreach(array_unique(array_filter($tax_input)) as $key => $value) {
    add_post_meta($postID,$key,$value,true);
  }
}
if ($post_ping)  generic_ping();
//自定义缩略图
if (!empty($_FILES[thumb0][name])) {
  require_once('./wp-load.php');
  require_once('./wp-admin/includes/file.php');
  require_once('./wp-admin/includes/image.php');
  $fujian = $_FILES['thumb0'];
  $filename = $fujian['name'];
  $fileHouZ=array_pop(explode(".",$filename));
  //附件保存格式【时间】
  $upFileTime=date("YmdHis");
  //更改上传文件的文件名为时间+随机数+后缀
  $fujian['name'] = $upFileTime."-".mt_rand(1,100).".".$fileHouZ;
  $uploaded_file = wp_handle_upload($fujian,array('test_form' => false));
  $post_content = str_replace("\'".$filename."\'","\"".$uploaded_file[url]."\"",$post_content);
  $post_content = str_replace($filename,$uploaded_file[url],$post_content);
  if (isset($uploaded_file['error']))wp_die($uploaded_file['error']);
  $file = $uploaded_file['file'];
  $new_file = iconv('GBK','UTF-8',$file);
  $url = iconv('GBK','UTF-8',$uploaded_file['url']);
  $type = $uploaded_file['type'];
  $attachment = array(
                    'guid' => $url,
                    'post_mime_type' => $type,
                    'post_title' => $filename,
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
  $attach_id = wp_insert_attachment($attachment,$new_file);
  set_post_thumbnail( $postID, $attach_id );
}
}
?>
