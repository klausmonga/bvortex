<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Qrcode {
	private $_data;
	private $_error_correct;
	private $_module_size;
	private $_version;
	private $_dir_path;
	private $_point_color;
	private $_background_color;

	/* DO NOT CHANGE */
	private $_valid_error_correct = array('L', 'M', 'Q', 'H'); // L 7% M 15% Q 25% H 30%
	private $_max_version = 40;
	/* DO NOT CHANGE */

	public function __construct() {
		$this->set_file_path(APPPATH . 'cache/');
		$this->clear();
		log_message('debug', "Qrcode Class Initialized");
	}

	public function clear() {
		$this->_data = '';
		$this->_error_correct = 'M';
		$this->_module_size = 4;
		$this->_version = 0;
		$this->_point_color = array(0, 0, 0);
		$this->_background_color = array(255, 255, 255);
		return $this;
	}

	public function set_file_path($dir) {
		$dir = rtrim($dir, '/') . '/';
		if( is_file($dir) ) {
			log_message('debug', 'Qrcode: Set File Path to a file.');
		} else {
			if( !is_dir($dir) ) {
				$parent_dir = $dir;
				do {
					$parent_dir = dirname($parent_dir);
				} while( !is_dir($parent_dir) );
				$perms = '0' . substr(sprintf('%o', fileperms($parent_dir)), -3);
				if( @mkdir($dir, $perms, TRUE) ) {
					@chmod($dir, $perms);
				} else {
					log_message('debug', 'Qrcode: Can not make new dir.');
				}
			}
			if( !is_dir($dir) || !is_really_writable($dir) ) {
				log_message('debug', 'Qrcode: Set to a unwritable dir.');
			} else {
				$this->_dir_path = $dir;
			}
		}
		return $this;
	}

	public function set_data($data) {
		$this->_data = (string) $data;
		return $this;
	}

	public function set_error_correct($error_correct) {
		if( in_array($error_correct, $this->_valid_error_correct) ) {
			$this->_error_correct = $error_correct;
		} else {
			log_message('debug', 'Qrcode: Error correction level must be a valid value.');
		}
		return $this;
	}

	public function set_module_size($module_size) {
		$module_size = intval($module_size);
		if( $module_size > 0 ) {
			$this->_module_size = $module_size;
		} else {
			log_message('debug', 'Qrcode: Module size must be an integer more then 1.');
		}
		return $this;
	}

	public function set_point_color() {
		$color = func_get_args();
		$color = $this->check_color($color);
		if( is_array($color) ) {
			$this->_point_color = $color;
		} else {
			log_message('debug', 'Qrcode: Error point color value.');
		}
		return $this;
	}

	public function set_background_color() {
		$color = func_get_args();
		$color = $this->check_color($color);
		if( is_array($color) ) {
			$this->_background_color = $color;
		} else {
			log_message('debug', 'Qrcode: Error background color value.');
		}
		return $this;
	}

	private function check_color($color) {
		if( count($color) == 1 && preg_match('/^[0-9a-f]{6}$/i', $color[0]) == 1 ) {
			$color = strtolower($color[0]);
			$color = str_split($color, 2);
			$color = array_map('hexdec', $color);
			return $color;
		} elseif( count($color) == 3 ) {
			$is_color = TRUE;
			foreach( $color as $_color ) {
				if( !is_int($_color) || 0 > $_color || 255 > $_color ) {
					$is_color = FALSE;
				}
			}
			if( $is_color ) {
				return $color;
			}
		}
		return FALSE;
	}

	public function set_version($version) {
		$version = intval($version);
		if( $version > 0 && $version <= $this->_max_version ) {
			$this->_version = $version;
		} else {
			log_message('debug', 'Qrcode: Version must be an integer between 1 and 40.');
		}
		return $this;
	}

	public function build($file_name = '') {
		if( empty($file_name) ) {
			$file_name = sprintf('%s_%s%d',
				substr(md5($this->_data), 0, 10),
				$this->_error_correct,
				$this->_module_size
			);
		}
		$file = $this->_dir_path . $file_name . '.png';
		if( file_exists($file) ) {
			for( $i = 1; $i < 100; $i++ ) {
				$file = $this->_dir_path . $file_name . '_' . $i . '.png';
				if( !file_exists($file) ) {
					$file_name = $file_name . '_' . $i;
					break;
				}
			}
		}
		if( $this->_build($file) ) {
			return $file_name . '.png';
		}
		return FALSE;
	}

	private function _build($file) {
/*
#
# QRcode image PHP scripts  version 0.50j (C)2000-2013,Y.Swetake
#  This program outputs a png image of "QRcode model 2". 
#  You cannot use a several functions of QRcode in this version. 
#  See README.txt .
#
#  This version supports QRcode model2 version 1-40.
#
#  This program requires PHP4.1 and gd 1.6 or higher.
#  You must set $path & $image_path the path to QRcode data file.
#
# THIS SOFTWARE IS PROVIDED BY Y.Swetake ``AS IS'' AND ANY EXPRESS OR
# IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
# OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
# IN NO EVENT SHALL Y.Swetake OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
# INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
# (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
# LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)  HOWEVER CAUSED 
# AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
# USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#
*/

$path = APPPATH . 'libraries/Qrcode/data';
$image_path = APPPATH . 'libraries/Qrcode/image';

$qrcode_data_string = $this->_data;
$qrcode_error_correct = $this->_error_correct;
$qrcode_module_size = $this->_module_size;
$qrcode_version = $this->_version;

$qrcode_data_string = rawurldecode($qrcode_data_string);
$data_length = strlen($qrcode_data_string);

$data_counter = 0;
$data_value = array();
$data_bits[$data_counter] = 4;

/*  --- determine encode mode */

if(preg_match('/[^0-9]/',$qrcode_data_string)!=0){
	if(preg_match('/[^0-9A-Z \$\*\%\+\.\/\:\-]/',$qrcode_data_string)!=0) {
		$codeword_num_plus=array(0,0,0,0,0,0,0,0,0,0,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8);
		$data_value[$data_counter]=4;
		$data_counter++;
		$data_value[$data_counter]=$data_length;
		$data_bits[$data_counter]=8;/* #version 1-9 */
		$codeword_num_counter_value=$data_counter;
		$data_counter++;
		for($i=0;$i<$data_length;$i++){
			$data_value[$data_counter]=ord(substr($qrcode_data_string,$i,1));
			$data_bits[$data_counter]=8;
			$data_counter++;
		}
	} else {
		$codeword_num_plus=array(0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,4,4,4,4,4,4,4,4,4);
		$data_value[$data_counter]=2;
		$data_counter++;
		$data_value[$data_counter]=$data_length;
		$data_bits[$data_counter]=9; /* #version 1-9 */
		$codeword_num_counter_value=$data_counter;
		$alphanumeric_character_hash=array('0'=>0,'1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'A'=>10,'B'=>11,'C'=>12,'D'=>13,'E'=>14,'F'=>15,'G'=>16,'H'=>17,'I'=>18,'J'=>19,'K'=>20,'L'=>21,'M'=>22,'N'=>23,'O'=>24,'P'=>25,'Q'=>26,'R'=>27,'S'=>28,'T'=>29,'U'=>30,'V'=>31,'W'=>32,'X'=>33,'Y'=>34,'Z'=>35,' '=>36,'$'=>37,'%'=>38,'*'=>39,'+'=>40,'-'=>41,'.'=>42,'/'=>43,':'=>44);
		$data_counter++;
		for($i=0;$i<$data_length;$i++){
			if(($i%2)==0){
				$data_value[$data_counter]=$alphanumeric_character_hash[substr($qrcode_data_string,$i,1)];
				$data_bits[$data_counter]=6;
			}else{
				$data_value[$data_counter]=$data_value[$data_counter]*45+$alphanumeric_character_hash[substr($qrcode_data_string,$i,1)];
				$data_bits[$data_counter]=11;
				$data_counter++;
			}
			$i++;
		}
	}
}else{
	$codeword_num_plus=array(0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,4,4,4,4,4,4,4,4,4);
	$data_value[$data_counter]=1;
	$data_counter++;
	$data_value[$data_counter]=$data_length;
	$data_bits[$data_counter]=10;
	$codeword_num_counter_value=$data_counter;
	$data_counter++;
	for($i=0;$i<$data_length;$i++){
		if(($i%3)==0){
			$data_value[$data_counter]=substr($qrcode_data_string,$i,1);
			$data_bits[$data_counter]=4;
		}else{
			$data_value[$data_counter]=$data_value[$data_counter]*10+substr($qrcode_data_string,$i,1);
			if(($i%3)==1){
				$data_bits[$data_counter]=7;
			}else{
				$data_bits[$data_counter]=10;
				$data_counter++;
			}
		}
	}
}
if(@$data_bits[$data_counter]>0){
	$data_counter++;
}
$total_data_bits=0;
for($i=0;$i<$data_counter;$i++){
	$total_data_bits+=$data_bits[$i];
}
$ecc_character_hash=array('L'=>'1','l'=>'1','M'=>'0','m'=>'0','Q'=>'3','q'=>'3','H'=>'2','h'=>'2');
$ec=@$ecc_character_hash[$qrcode_error_correct];
if(!$ec){
	$ec=0;
}
$max_data_bits_array=array(0,128,224,352,512,688,864,992,1232,1456,1728,2032,2320,2672,2920,3320,3624,4056,4504,5016,5352,5712,6256,6880,7312,8000,8496,9024,9544,10136,10984,11640,12328,13048,13800,14496,15312,15936,16816,17728,18672,
152,272,440,640,864,1088,1248,1552,1856,2192,2592,2960,3424,3688,4184,4712,5176,5768,6360,6888,7456,8048,8752,9392,10208,10960,11744,12248,13048,13880,14744,15640,16568,17528,18448,19472,20528,21616,22496,23648,
72,128,208,288,368,480,528,688,800,976,1120,1264,1440,1576,1784,2024,2264,2504,2728,3080,3248,3536,3712,4112,4304,4768,5024,5288,5608,5960,6344,6760,7208,7688,7888,8432,8768,9136,9776,10208,
104,176,272,384,496,608,704,880,1056,1232,1440,1648,1952,2088,2360,2600,2936,3176,3560,3880,4096,4544,4912,5312,5744,6032,6464,6968,7288,7880,8264,8920,9368,9848,10288,10832,11408,12016,12656,13328);
if(!is_numeric($qrcode_version)){
	$qrcode_version=0;
}
if(!$qrcode_version){
	$i=1+40*$ec;
	$j=$i+39;
	$qrcode_version=1;
	while($i<=$j){
		if(($max_data_bits_array[$i])>=$total_data_bits+$codeword_num_plus[$qrcode_version]	){
			$max_data_bits=$max_data_bits_array[$i];
			break;
		}
		$i++;
		$qrcode_version++;
	}
}else{
	$max_data_bits=$max_data_bits_array[$qrcode_version+40*$ec];
}
if($qrcode_version>$this->_max_version){
	log_message('error', 'Qrcode: too large version.');
	returnFALSE;
}
$total_data_bits+=$codeword_num_plus[$qrcode_version];
$data_bits[$codeword_num_counter_value]+=$codeword_num_plus[$qrcode_version];
$max_codewords_array=array(0,26,44,70,100,134,172,196,242,292,346,404,466,532,581,655,733,815,901,991,1085,1156,1258,1364,1474,1588,1706,1828,1921,2051,2185,2323,2465,2611,2761,2876,3034,3196,3362,3532,3706);
$max_codewords=$max_codewords_array[$qrcode_version];
$max_modules_1side=17+($qrcode_version<<2);
$matrix_remain_bit=array(0,0,7,7,7,7,7,0,0,0,0,0,0,0,3,3,3,3,3,3,3,4,4,4,4,4,4,4,3,3,3,3,3,3,3,0,0,0,0,0,0);
$byte_num=$matrix_remain_bit[$qrcode_version]+($max_codewords<<3);
$filename=$path.'/qrv'.$qrcode_version.'_'.$ec.'.dat';
$fp1=fopen($filename,'rb');
$matx=fread($fp1,$byte_num);
$maty=fread($fp1,$byte_num);
$masks=fread($fp1,$byte_num);
$fi_x=fread($fp1,15);
$fi_y=fread($fp1,15);
$rs_ecc_codewords=ord(fread($fp1,1));
$rso=fread($fp1,128);
fclose($fp1);
$matrix_x_array=unpack('C*',$matx);
$matrix_y_array=unpack('C*',$maty);
$mask_array=unpack('C*',$masks);
$rs_block_order=unpack('C*',$rso);
$format_information_x2=unpack('C*',$fi_x);
$format_information_y2=unpack('C*',$fi_y);
$format_information_x1=array(0,1,2,3,4,5,7,8,8,8,8,8,8,8,8);
$format_information_y1=array(8,8,8,8,8,8,8,8,7,5,4,3,2,1,0);
$max_data_codewords=($max_data_bits>>3);
$filename=$path.'/rsc'.$rs_ecc_codewords.'.dat';
$fp0=fopen($filename,'rb');
for($i=0;$i<256;$i++){
	$rs_cal_table_array[$i]=fread($fp0,$rs_ecc_codewords);
}
fclose($fp0);
if($total_data_bits<=$max_data_bits-4){
	$data_value[$data_counter]=0;
	$data_bits[$data_counter]=4;
}else{
	if($total_data_bits<$max_data_bits){
		$data_value[$data_counter]=0;
		$data_bits[$data_counter]=$max_data_bits-$total_data_bits;
	}else{
		if($total_data_bits>$max_data_bits){
			log_message('error', 'Qrcode: Overflow error.');
			returnFALSE;
		}
	}
}
$codewords_counter=0;
$codewords[0]=0;
$remaining_bits=8;
for($i=0;$i<=$data_counter;$i++){
	$buffer=@$data_value[$i];
	$buffer_bits=@$data_bits[$i];
	$flag=1;
	while($flag){
		if($remaining_bits>$buffer_bits){
			$codewords[$codewords_counter]=((@$codewords[$codewords_counter]<<$buffer_bits)|$buffer);
			$remaining_bits-=$buffer_bits;
			$flag=0;
		}else{
			$buffer_bits-=$remaining_bits;
			$codewords[$codewords_counter]=(($codewords[$codewords_counter]<<$remaining_bits)|($buffer>>$buffer_bits));
			if($buffer_bits==0){
				$flag=0;
			}else{
				$buffer=($buffer&((1<<$buffer_bits)-1));
				$flag=1;	
			}
			$codewords_counter++;
			if($codewords_counter<$max_data_codewords-1){
				$codewords[$codewords_counter]=0;
			}
			$remaining_bits=8;
		}
	}
}
if($remaining_bits!=8){
	$codewords[$codewords_counter]=$codewords[$codewords_counter]<<$remaining_bits;
}else{
	$codewords_counter--;
}
if($codewords_counter<$max_data_codewords-1){
	$flag=1;
	while($codewords_counter<$max_data_codewords-1){
		$codewords_counter++;
		if($flag==1){
			$codewords[$codewords_counter]=236;
		}else{
			$codewords[$codewords_counter]=17;
		}
		$flag=$flag*(-1);
	}
}
$rs_block_number=0;
$rs_temp[0]='';
for($i=0,$j=0;$i<$max_data_codewords;$i++){
	$rs_temp[$rs_block_number].=chr($codewords[$i]);
	$j++;
	if($j>=$rs_block_order[$rs_block_number+1]-$rs_ecc_codewords){
		$j=0;
		$rs_block_number++;
		$rs_temp[$rs_block_number]='';
	}
}
$rs_block_number=0;
$rs_block_order_num=count($rs_block_order);
while($rs_block_number<$rs_block_order_num){
	$rs_codewords=$rs_block_order[$rs_block_number+1];
	$rs_data_codewords=$rs_codewords-$rs_ecc_codewords;
	$rstemp=$rs_temp[$rs_block_number].str_repeat(chr(0),$rs_ecc_codewords);
	$padding_data=str_repeat(chr(0),$rs_data_codewords);
	for($j=$rs_data_codewords;$j>0;$j--){
		$first=ord(substr($rstemp,0,1));
		if($first){
			$left_chr=substr($rstemp,1);
			$cal=$rs_cal_table_array[$first].$padding_data;
			$rstemp=$left_chr^$cal;
		}else{
			$rstemp=substr($rstemp,1);
		}
	}
	$codewords=array_merge($codewords,unpack('C*',$rstemp));
	$rs_block_number++;
}
for($i=0;$i<$max_modules_1side;$i++){
	for($j=0;$j<$max_modules_1side;$j++){
		$matrix_content[$j][$i]=0;
	}
}
for($i=0;$i<$max_codewords;$i++){
	$codeword_i=$codewords[$i];
	for($j=8;$j>=1;$j--){
		$codeword_bits_number=($i<<3)+$j;
		$matrix_content[$matrix_x_array[$codeword_bits_number]][$matrix_y_array[$codeword_bits_number]]=((255*($codeword_i&1))^$mask_array[$codeword_bits_number]);
		$codeword_i=$codeword_i>>1;
	}
}
$matrix_remain=$matrix_remain_bit[$qrcode_version];
while($matrix_remain){
	$remain_bit_temp=$matrix_remain+($max_codewords<<3);
	$matrix_content[$matrix_x_array[$remain_bit_temp]][$matrix_y_array[$remain_bit_temp]]=(0^$mask_array[$remain_bit_temp]);
	$matrix_remain--;
}
$min_demerit_score=0;
$hor_master='';
$ver_master='';
for($i=0;$i<$max_modules_1side;$i++){
	for($j=0;$j<$max_modules_1side;$j++){
		$hor_master=$hor_master.chr($matrix_content[$j][$i]);
		$ver_master=$ver_master.chr($matrix_content[$i][$j]);
	}
}
$all_matrix=$max_modules_1side*$max_modules_1side;
for($i=0;$i<8;$i++){
	$demerit_n1=0;
	$ptn_temp=array();
	$bit=1<<$i;
	$bit_r=(~$bit)&255;
	$bit_mask=str_repeat(chr($bit),$all_matrix);
	$hor=$hor_master&$bit_mask;
	$ver=$ver_master&$bit_mask;
	$ver_shift1=$ver.str_repeat(chr(170),$max_modules_1side);
	$ver_shift2=str_repeat(chr(170),$max_modules_1side).$ver;
	$ver_shift1_0=$ver.str_repeat(chr(0),$max_modules_1side);
	$ver_shift2_0=str_repeat(chr(0),$max_modules_1side).$ver;
	$ver_or=chunk_split(~($ver_shift1|$ver_shift2),$max_modules_1side,chr(170));
	$ver_and=chunk_split(~($ver_shift1_0&$ver_shift2_0),$max_modules_1side,chr(170));
	$hor=chunk_split(~$hor,$max_modules_1side,chr(170));
	$ver=chunk_split(~$ver,$max_modules_1side,chr(170));
	$hor=$hor.chr(170).$ver;
	$n1_search='/'.str_repeat(chr(255),5).'+|'.str_repeat(chr($bit_r),5).'+/';
	$n3_search=chr($bit_r).chr(255).chr($bit_r).chr($bit_r).chr($bit_r).chr(255).chr($bit_r);
	$demerit_n3=substr_count($hor,$n3_search)*40;
	$demerit_n4=floor(abs(((100*(substr_count($ver,chr($bit_r))/($byte_num)))-50)/5))*10;
	$n2_search1='/'.chr($bit_r).chr($bit_r).'+/';
	$n2_search2='/'.chr(255).chr(255).'+/';
	$demerit_n2=0;
	preg_match_all($n2_search1,$ver_and,$ptn_temp);
	foreach($ptn_temp[0]as$str_temp){
		$demerit_n2+=(strlen($str_temp)-1);
	}
	$ptn_temp=array();
	preg_match_all($n2_search2,$ver_or,$ptn_temp);
	foreach($ptn_temp[0]as$str_temp){
		$demerit_n2+=(strlen($str_temp)-1);
	}
	$demerit_n2*=3;
	$ptn_temp=array();
	preg_match_all($n1_search,$hor,$ptn_temp);
	foreach($ptn_temp[0]as$str_temp){
		$demerit_n1+=(strlen($str_temp)-2);
	}
	$demerit_score=$demerit_n1+$demerit_n2+$demerit_n3+$demerit_n4;
	if($demerit_score<=$min_demerit_score||$i==0){
		$mask_number=$i;
		$min_demerit_score=$demerit_score;
	}
}
$mask_content=1<<$mask_number;
$format_information_value=(($ec<<3)|$mask_number);
$format_information_array=array('101010000010010','101000100100101','101111001111100','101101101001011','100010111111001','100000011001110','100111110010111','100101010100000','111011111000100','111001011110011','111110110101010','111100010011101','110011000101111','110001100011000','110110001000001','110100101110110','001011010001001','001001110111110','001110011100111','001100111010000','000011101100010','000001001010101','000110100001100','000100000111011','011010101011111','011000001101000','011111100110001','011101000000110','010010010110100','010000110000011','010111011011010','010101111101101');
for($i=0;$i<15;$i++){
	$content=substr($format_information_array[$format_information_value],$i,1);
	$matrix_content[$format_information_x1[$i]][$format_information_y1[$i]]=$content*255;
	$matrix_content[$format_information_x2[$i+1]][$format_information_y2[$i+1]]=$content*255;
}
$mib=$max_modules_1side+8;
$qrcode_image_size=$mib*$qrcode_module_size;

$output_image=imagecreate($qrcode_image_size,$qrcode_image_size);
$image_path=$image_path.'/qrv'.$qrcode_version.'.png';
$base_image=imagecreatefrompng($image_path);
$color[0]=imagecolorat($base_image,0,0);
$color[1]=imagecolorat($base_image,4,4);
$mxe=4+$max_modules_1side;
for($i=4,$ii=0;$i<$mxe;$i++,$ii++){
	for($j=4,$jj=0;$j<$mxe;$j++,$jj++){
		if($matrix_content[$ii][$jj]&$mask_content){
			imagesetpixel($base_image,$i,$j,$color[1]);
		}
	}
}
imagecolorset($base_image,$color[1],$this->_point_color[0],$this->_point_color[1],$this->_point_color[2]);
imagecolorset($base_image,$color[0],$this->_background_color[0],$this->_background_color[1],$this->_background_color[2]);
imagecopyresized($output_image,$base_image,0,0,0,0,$qrcode_image_size,$qrcode_image_size,$mib,$mib);
imagepng($output_image,$file);
imagedestroy($output_image);
chmod($file, FILE_READ_MODE);
		return TRUE;
	}
}
/* End of file Qrcode.php */
/* Location: ./application/libraries/Qrcode.php */