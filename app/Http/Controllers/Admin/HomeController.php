<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request){
        if (!DB::table('privileges')->where('user_id',Auth::id())->exists())
            abort(404);

        // 查询判题端进程
        exec('ps -eo pid,user,comm,vsz|grep polling 2>&1',$out,$status);
        if(count($out)>0){
            $run=true;
            $info='[ 正在运行 ] pid='.$out[0].'KB';
        }else{
            $run=false;
            $info='[ 停止运行 ]';
        }

        //服务器相关信息
        $systemInfo = [
            '服务器主机名'    =>$_SERVER["HTTP_HOST"],
            '服务器IP地址'   =>  $_SERVER['SERVER_ADDR'],
            '服务器域名'     =>  $_SERVER['SERVER_NAME'],
            '服务器Web端口'  =>  $_SERVER['SERVER_PORT'],
            '服务器操作系统'  =>  php_uname(),
            '服务器当前时间'  => date("Y-m-d H:i:s"),
            'PHP版本'       =>  PHP_VERSION,
            'PHP安装路径'    =>  DEFAULT_INCLUDE_PATH,
            'PHP运行方式'    =>  php_sapi_name(),
            'Laravel版本'   =>  $laravel = app()::VERSION,
            '最大上传限制'   => get_cfg_var ("upload_max_filesize"),
            '最大执行时间'   => get_cfg_var("max_execution_time")."秒",
            '脚本运行最大内存' => get_cfg_var ("memory_limit"),
            '服务器解译引擎'  => $_SERVER['SERVER_SOFTWARE'],
            '通信协议'       =>$_SERVER['SERVER_PROTOCOL']
        ];
        return view('admin.home',compact('run','info','systemInfo'));
    }

    public function cmd_polling(Request $request){
        $oper=$request->input('oper');
        if($oper==='start'||$oper==='restart')
            exec('sudo bash '.base_path('judge/startup.sh'),$out,$status);
        else if($oper==='stop')
            exec('sudo bash '.base_path('judge/stop.sh'),$out,$status);
        return back()->with('ret',implode('<br>',$out));
    }

    public function modify_env(Request $request){
        if($request->has('JG_NAME')){
            exec("sudo sed -i 's/^JG_NAME.*$/JG_NAME=\"".$request->get('JG_NAME')."\"/' ".app()->environmentFilePath(),$out,$status);
        }
        if($request->has('JG_MAX_RUNNING')){
            exec("sudo sed -i 's/^JG_MAX_RUNNING.*$/JG_MAX_RUNNING=".$request->get('JG_MAX_RUNNING')."/' ".app()->environmentFilePath(),$out,$status);
        }
        exec('php '.base_path('artisan').' optimize',$out,$status);

        //由于重新缓存的配置并不会立刻生效，需要将值返回前台，让用户看到修改成功的值
        return redirect(route('admin.home'))
            ->with('ret',implode('<br>',$out))
            ->with('JG_NAME',$request->get('JG_NAME'))
            ->with('JG_MAX_RUNNING',$request->get('JG_MAX_RUNNING'));
    }
}
