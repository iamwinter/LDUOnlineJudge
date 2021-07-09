<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        if(!isset($_GET['pid']))$_GET['inc_contest']='on'; //未筛选时默认包含竞赛提交
        $solutions=DB::table('solutions')
            ->join('users','solutions.user_id','=','users.id')
            ->leftJoin('contests','solutions.contest_id','=','contests.id')  //非必须，left
            ->leftJoin('contest_problems',function ($q){
                $q->on('solutions.contest_id','=','contest_problems.contest_id')->on('solutions.problem_id','=','contest_problems.problem_id');
            })
            ->select('solutions.id','solutions.contest_id','contest_problems.index','solutions.problem_id','solutions.user_id','nick','username',
                'result','time','memory','language', 'submit_time', 'solutions.judge_type', 'pass_rate','judger', 'sim_rate', 'sim_sid')
            ->when(isset($_GET['inc_contest']),function ($q){
                if(Auth::check()&&Auth::user()->privilege('solution'))
                    return $q;
                return $q->where(function ($q){
                    return $q->where('solutions.contest_id',-1)->orWhere('end_time','<',date('Y-m-d H:i:s'));
                });//普通用户只能查看已结束比赛的solution
            })
            ->when(!isset($_GET['inc_contest']),function ($q){return $q->where('solutions.contest_id',-1);})
            ->when(isset($_GET['sim_rate'])&&$_GET['sim_rate']!=0,function ($q){return $q->where('sim_rate','>=',$_GET['sim_rate']);})
            ->when(isset($_GET['sid'])&&$_GET['sid']!='',function ($q){return $q->where('solutions.id',$_GET['sid']);})
            ->when(isset($_GET['pid'])&&$_GET['pid']!='',function ($q){return $q->where('solutions.problem_id',$_GET['pid']);})
            ->when(isset($_GET['username'])&&$_GET['username']!='',function ($q){return $q->where('username','like',$_GET['username'].'%');})
            ->when(isset($_GET['result'])&&$_GET['result']!='-1',function ($q){return $q->where('result',$_GET['result']);})
            ->when(isset($_GET['language'])&&$_GET['language']!='-1',function ($q){return $q->where('language',$_GET['language']);})
            ->orderByDesc('solutions.id')
            ->paginate(10);

        return view('client.status',compact('solutions'));
    }

    //状态页面使用ajax实时更新题目的判题结果
    public function ajax_get_status(Request $request){
        if($request->ajax()){
            $sids=$request->input('sids');
            $solutions=DB::table('solutions')
                ->select(['id','judge_type','result','time','memory','pass_rate'])
                ->whereIn('id',$sids)->get();
            $ret=[];
            foreach ($solutions as $item){
                $ret[]=[
                    'id'=>$item->id,
                    'result'=>$item->result,
                    'color'=>config('oj.resColor.'.$item->result),
                    'text'=>config('oj.result.'.$item->result).($item->judge_type=='oi' ? sprintf(' (%s)',round($item->pass_rate*100)) : null),
                    'time'=>$item->time.'MS',
                    'memory'=>round($item->memory,2).'MB'
                ];
            }
            return json_encode($ret);
        }
        return json_encode([]);
    }

    public function solution($id){
        $solution=DB::table('solutions')
            ->join('users','solutions.user_id','=','users.id')
            ->leftJoin('contest_problems',function ($q){
                $q->on('solutions.contest_id','=','contest_problems.contest_id')->on('solutions.problem_id','=','contest_problems.problem_id');
            })
            ->select(['solutions.id','solutions.problem_id','index','solutions.contest_id','user_id','username',
                'result','pass_rate','time','memory','judge_type','submit_time','judge_time',
                'code','code_length','language','error_info','wrong_data'])
            ->where('solutions.id',$id)->first();
        if(Auth::user()->privilege('solution')||
            (Auth::id()==$solution->user_id && $solution->submit_time>Auth::user()->created_at))
            return view('client.solution',compact('solution'));
        return view('client.fail',['msg'=>trans('sentence.Permission denied')]);
    }

    public function solution_wrong_data($id,$type){
        $solution = DB::table('solutions')
            ->leftJoin('contests','solutions.contest_id','=','contests.id')  //非必须，left
            ->select('solutions.problem_id','solutions.user_id','contests.end_time','solutions.wrong_data')
            ->where('solutions.id',$id)
            ->first();
        if(($solution && Auth::id()==$solution->user_id && $solution->wrong_data!==null)
            ||Auth::user()->privilege('solution')){
            if(date('Y-m-d H:i:s') < $solution->end_time) //比赛未结束
                return view('client.fail',['msg'=>trans('sentence.not_end')]);
            return Storage::Download("data/".$solution->problem_id."/test/".$solution->wrong_data.'.'.$type);
        }
        return view('client.fail',['msg'=>trans('sentence.Permission denied')]);
    }

    /*
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //拦截非管理员的频繁提交
        if(!Auth::user()->privilege('teacher')){
            $last_submit_time = DB::table('solutions')
                ->where('user_id',Auth::id())
                ->orderByDesc('submit_time')
                ->value('submit_time');
            if(time()-strtotime($last_submit_time)<intval(get_setting('submit_interval')))
                return view('client.fail',['msg'=>trans('sentence.submit_frequently',['sec'=>get_setting('submit_interval')])]);
        }


        //获取前台提交的solution信息
        $data = $request->input('solution');
        $problem = DB::table('problems')->find($data['pid']); //找到题目
        $submitted_result=0; //提交后的默认结果Waiting
        if(isset($data['cid'])){
            $contest=DB::table("contests")->select('judge_instantly','judge_type','allow_lang','end_time')->find($data['cid']);
            if( !( (1<<$data['language'])&$contest->allow_lang ) )//使用了不允许的代码语言
                return view('client.fail',['msg'=>'Using a programming language that is not allowed!']);
            if($contest->judge_instantly==0&&time()<strtotime($contest->end_time)){ //赛后判题，之前的提交都作废=>Skipped
                DB::table('solutions')->where('contest_id',$data['cid'])
                    ->where('problem_id',$data['pid'])
                    ->where('user_id',Auth::id())
                    ->update(['result'=>13]);
                $submitted_result=15; //Submitted
            }
        }else{ //通过题库提交，需要判断一下用户权限
            $hidden=$problem->hidden;
            if(!Auth::user()->privilege('teacher') && $hidden==1) //不是管理员&&问题隐藏 => 不允许提交
                return view('client.fail',['msg'=>trans('main.Problem').$data['pid'].'：'.trans('main.Hidden')]);
        }

        if(null!=($file=$request->file('code_file')))//用户提交了文件,从临时文件中直接提取文本
            $data['code']=autoiconv(file_get_contents($file->getRealPath()));
        else if($problem->type==1)//填空题，填充用户的答案
        {
            $data['code']=$problem->fill_in_blank;
            foreach ($request->input('filled') as $ans) {
                $data['code'] = preg_replace("/\?\?/",$ans,$data['code'],1);
            }
        }

        if(strlen($data['code'])<3)
            return view('client.fail',['msg'=>'代码长度过程！']);

        DB::table('solutions')->insert([
            'problem_id'    => $data['pid'],
            'contest_id'    => isset($data['cid'])?$data['cid']:-1,
            'user_id'       => Auth::id(),
            'result'        => $submitted_result,
            'language'      => ($data['language']!=null)?$data['language']:0,
            'submit_time'   => date('Y-m-d H:i:s'),

            'judge_type'    => isset($contest->judge_type)?$contest->judge_type:'oi', //acm,oi

            'ip'            => $request->getClientIp(),
            'code_length'   => strlen($data['code']),
            'code'          => $data['code']
            ]);

        Cookie::queue('submit_language',$data['language']);//Cookie记住用户使用的语言，以后提交默认该语言
        if(isset($contest)) //竞赛提交
            return redirect(route('contest.status',[$data['cid'],'index'=>$data['index'],'username'=>Auth::user()->username]));

        return redirect(route('status',['pid'=>$data['pid'],'username'=>Auth::user()->username]));
    }
}
