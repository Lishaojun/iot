<?PHP

include_once 'inc/init.php';
include_once 'inc/operateDB.php';

$gpshost = HostAddr."/rphp/getaddr.php";
$lat = 0;
$lon = 0;
$mcc = 0;
$mnc = 0;
$cellid = 0;
$lac = 0;
$addr_result = "";
$sw_ver = 0;

function request_by_fileget($remote_server)
{
	$context = array( 
	    'http'=>array( 
            'method'=>'GET',
			'timeout'=>7)
           ); 
    $stream_context = stream_context_create($context); 
    $data = file_get_contents($remote_server,FALSE,$stream_context); 
    return $data; 
}
/*使用谷歌灵图方案定位*/
function Google_Lingtu_Locate($mcc, $mnc, $nbr_info_lac, $nbr_info_cellid, &$JW_mode, &$lat, &$lon)
{
	global $gpshost;

	$strhttp_getaddr = sprintf("%s?mcc=%s&mnc=%s&lac=%s&cellid=%s", $gpshost, $mcc, $mnc, $nbr_info_lac, $nbr_info_cellid);
	$strhttp_getaddr = request_by_fileget($strhttp_getaddr);
	if( $strhttp_getaddr )
	{			
		parse_str($strhttp_getaddr);
		$lat = $wzdw_lat;
		$lon = $wzdw_lon;
		$addr_result = $wzdw_addr_result;	
		$JW_mode = $wzdw_jwmode;
	}
	return $addr_result;
}
function SendImage( $gps_lat, $gps_lon )
{
	/*A*/
	$url="http://maps.google.com/maps?q=%s,%s";
	$s =sprintf( $url, $gps_lat, $gps_lon );
	Header("Location: $s ");
}

error_reporting(0);

$cellid = $_GET["cellid"];
$lac = $_GET["lac"];
$mcc = $_GET["c"];
$mnc = $_GET["n"];
$sw_ver = $_GET["v"];
if( $cellid == 0 && $lac == 0 )
{
	exit("cellid and lac are zero");
}
else
{

	$addr_result = Base_Locate_byDB($mcc, $mnc, $lat, $lon, $lac, $cellid);
	//本地数据库失败后转到谷歌
	if( $lat == 0 && $lon == 0 )
	{
		$addr_result = Google_Lingtu_Locate($mcc, $mnc, $lac, $cellid, $JW_mode, $lat, $lon);
	}
	if( $lat != 0 && $lon != 0 )
	{
		SendImage( $lat, $lon );
		exit(0);
	}
	else
	{
		$addr_result = iconv('UTF-8','GB2312',"很抱歉，网络繁忙或该位置暂时无法定位！");
		echo $addr_result;
		exit(0);
	}
}

?>
