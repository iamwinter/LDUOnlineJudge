<?php

use Illuminate\Support\Facades\Storage;


/**
 * @param $problem_id
 * @param bool $from_sample
 * @return array  返回二维字符串数组，第一维[test0,test1,...]，第二维[.in, .out]
 * 读取样例/测试文件
 */
function read_problem_data($problem_id, $from_sample=true){
    $dir='data/'.$problem_id.'/'.($from_sample?'sample':'test');
    foreach (Storage::allFiles($dir) as $filepath){
        $name=pathinfo($filepath,PATHINFO_FILENAME);  //文件名
        $ext=pathinfo($filepath,PATHINFO_EXTENSION);    //拓展名
        if(!isset($flag[$name]))$flag[$name]=0;
        if($ext==='in')$flag[$name]++;
        if($ext==='out')$flag[$name]++;
    }
    $samples=[];
    foreach (isset($flag)?$flag:[] as $key=>$val){  //in和out都有的，读取
        if($val==2){
            $in =Storage::get($dir.'/'.$key.'.in');
            $out=Storage::get($dir.'/'.$key.'.out');
            $samples[]=[$in,$out];
        }
    }
    return $samples;
}

/**
 * 保存样例/测试到文件
 * @param $problem_id
 * @param $ins
 * @param $outs
 * @param bool $as_sample
 * @param bool $del_old
 */
function save_problem_data($problem_id, $ins,$outs, $from_sample=true, $del_old=true){
    $dir=($from_sample?'sample':'test');
    if($del_old) Storage::deleteDirectory(sprintf('data/%d/%s',$problem_id,$dir));  //删除原有文件
    foreach ($ins as $i=>$in)Storage::put(sprintf('data/%d/%s/%d.in',$problem_id,$dir,$i),$in);
    foreach ($outs as $i=>$out)Storage::put(sprintf('data/%d/%s/%d.out',$problem_id,$dir,$i),$out);
}

/**
 * @param $problem_id
 * @param $code
 * @return mixed
 *  保存特判文件
 */
function save_problem_spj($problem_id, $code){
    Storage::put(sprintf('data/%d/spj/spj.cpp',$problem_id),$code);
    $dir=storage_path('app/data/'.$problem_id.'/spj');
    exec("sudo g++ ".$dir."/spj.cpp -o ".$dir."/spj -lm -std=c++11 2>&1", $out);
    return implode('<br>',$out);
}

/**
 * 获取本题的特判代码
 * @param $problem_id
 * @return string
 */
function get_spj_code($problem_id){
    $fpath=sprintf('data/%d/spj/spj.cpp',$problem_id);
    if(Storage::exists($fpath))
        return Storage::get($fpath);
    return null;
}

//将一个数字题号（从1开始）转为大写字母
function index2ch(int $index){
    if($index<26)
        return chr($index+65);
    return $index+1; //Z的下一题是27题
}

//从txt文件读取的内容转码
function autoiconv($text,$type = "gb2312//ignore"){
    define('UTF32_BIG_ENDIAN_BOM', chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
    define('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
    define('UTF16_BIG_ENDIAN_BOM', chr(0xFE) . chr(0xFF));
    define('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
    define('UTF8_BOM', chr(0xEF) . chr(0xBB) . chr(0xBF));
    $first2 = substr($text, 0, 2);
    $first3 = substr($text, 0, 3);
    $first4 = substr($text, 0, 3);
    $encodType = "";
    if ($first3 == UTF8_BOM)
        $encodType = 'UTF-8 BOM';
    else if ($first4 == UTF32_BIG_ENDIAN_BOM)
        $encodType = 'UTF-32BE';
    else if ($first4 == UTF32_LITTLE_ENDIAN_BOM)
        $encodType = 'UTF-32LE';
    else if ($first2 == UTF16_BIG_ENDIAN_BOM)
        $encodType = 'UTF-16BE';
    else if ($first2 == UTF16_LITTLE_ENDIAN_BOM)
        $encodType = 'UTF-16LE';
    //下面的判断主要还是判断ANSI编码的·
    if ($encodType == '') { //即默认创建的txt文本-ANSI编码的
//        $content = mb_convert_encoding($text,"UTF-8","auto");
            $content = iconv("GBK", "UTF-8//ignore", $text);
    } else if ($encodType == 'UTF-8 BOM') {//本来就是UTF-8不用转换
        $content = $text;
    } else {//其他的格式都转化为UTF-8就可以了
        $content = iconv($encodType, "UTF-8", $text);
    }
    return $content;
}
