@extends('layouts.admin')

@section('title','题目管理 | 后台')

@section('content')

    <h2>问题管理</h2>
    <hr>
    <form action="" method="get" class="pull-right form-inline">
        <div class="form-inline mx-3">
            每页
            <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
                <option value="10" @if(isset($_GET['perPage'])&&$_GET['perPage']==10)selected @endif>10</option>
                <option value="20" @if(isset($_GET['perPage'])&&$_GET['perPage']==20)selected @endif>20</option>
                <option value="50" @if(isset($_GET['perPage'])&&$_GET['perPage']==50)selected @endif>50</option>
                <option value="100" @if(!isset($_GET['perPage'])||$_GET['perPage']==100)selected @endif>100</option>
            </select>
            题
        </div>
        <div class="form-inline mx-3">
            <input type="number" class="form-control text-center" placeholder="题目编号"
                   name="pid" value="{{isset($_GET['pid'])?$_GET['pid']:''}}">
        </div>
        <div class="form-inline mx-3">
            <input type="text" class="form-control text-center" placeholder="题目名称"
                   name="title" value="{{isset($_GET['title'])?$_GET['title']:''}}">
        </div>
        <div class="form-inline mx-3">
            <input type="text" class="form-control text-center" placeholder="来源/出处"
                   name="source" value="{{isset($_GET['source'])?$_GET['source']:''}}">
        </div>
        <button class="btn border">查询</button>
    </form>
    <div class="table-responsive">
        {{$problems->appends($_GET)->links()}}
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

        <a href="javascript:update_hidden(0);" class="ml-3">公开</a>
        <a href="javascript:" class="text-gray" onclick="whatisthis('选中的题目将被公开，允许普通用户在题库中查看和提交!')">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>

        <a href="javascript:update_hidden(1);" class="ml-3">隐藏</a>
        <a href="javascript:" class="text-gray" onclick="whatisthis('选中的题目将被隐藏，普通用户无法在题库中查看和提交，但不会影响竞赛!')">
            <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        </a>

{{--        <a href="javascript:" class="ml-3">删除</a>--}}
{{--        <a href="javascript:" class="text-gray" onclick="whatisthis('删除选中的题目，删除后对应题号将空缺！')">--}}
{{--            <i class="fa fa-question-circle-o" aria-hidden="true"></i>--}}
{{--        </a>--}}

{{--        <a href="javascript:" class="ml-3">删除并补位</a>--}}
{{--        <a href="javascript:" class="text-gray" onclick="whatisthis('删除选中的题目，后面的题目将自动向前移动以填充空缺的题号！')">--}}
{{--            <i class="fa fa-question-circle-o" aria-hidden="true"></i>--}}
{{--        </a>--}}

{{--        <a href="javascript:" class="ml-3">自动补位</a>--}}
{{--        <a href="javascript:" class="text-gray" onclick="whatisthis('若有空缺题号，则自动将后面的题号向前移动以填充空缺题号。<br>' +--}}
{{--            '如1001题被删除，则1002将变为1001题，1003题将变为1002题...以此类推！')">--}}
{{--            <i class="fa fa-question-circle-o" aria-hidden="true"></i>--}}
{{--        </a>--}}

{{--        <a href="javascript:" class="ml-3">转移至___之后</a>--}}
{{--        <a href="javascript:" class="text-gray" onclick="whatisthis('选中的题目将被插入到对应题号之后！')">--}}
{{--            <i class="fa fa-question-circle-o" aria-hidden="true"></i>--}}
{{--        </a>--}}

        <table class="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th></th>
                <th>题号</th>
                <th>题目</th>
                <th>类型</th>
                <th>出处</th>
                <th>特判</th>
                <th>解决/提交</th>
                <th>创建时间</th>
                <th>创建人</th>
                <th>当前状态</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($problems as $item)
                <tr>
                    <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                        <input type="checkbox" value="{{$item->id}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                    </td>
                    <td nowrap>{{$item->id}}</td>
                    <td nowrap><a href="{{route('problem',$item->id)}}" target="_blank">{{$item->title}}</a></td>
                    <td nowrap>{{$item->type?'代码填空':'编程'}}</td>
                    <td nowrap>{{$item->source}}</td>
                    <td nowrap>{{$item->spj?'特判':'否'}}</td>
                    <td nowrap>{{$item->solved}} / {{$item->submit}}</td>
                    <td nowrap>{{$item->created_at}}</td>
                    <td><a @if($item->creator)href="{{route('user',$item->creator)}}"@endif target="_blank">{{$item->creator}}</a></td>
                    <td nowrap>
                        <a href="javascript:" onclick="update_hidden('{{1-$item->hidden}}',{{$item->id}});"
                            class="px-1" title="点击切换">{{$item->hidden?'隐藏*不可见':'公开'}}</a>
                    </td>
                    <td nowrap>
                        <a href="{{route('admin.problem.update_withId',$item->id)}}" target="_blank" class="px-1"
                           data-toggle="tooltip" title="修改">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>
                        <a href="{{route('admin.problem.test_data',['pid'=>$item->id])}}" target="_blank" class="px-1" data-toggle="tooltip" title="测试数据">
                            <i class="fa fa-file" aria-hidden="true"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{$problems->appends($_GET)->links()}}
    </div>
    <script>
        function update_hidden(hidden,id=-1) {
            if(id!==-1){  ///单独修改一个
                $('td input[type=checkbox]').prop('checked',false)
                $('td input[value='+id+']').prop('checked',true)
            }
            // 修改题目状态 1公开 or 0隐藏
            var pids=[];
            $('td input[type=checkbox]:checked').each(function () { pids.push($(this).val()); });
            $.post(
                '{{route('admin.problem.update_hidden')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'pids':pids,
                    'hidden':hidden,
                },
                function (ret) {
                    if(id===-1){
                        Notiflix.Report.Init();
                        Notiflix.Report.Success('操作成功','成功更新'+ret+'条数据！注意：非最高管理员只能修改自己创建的题目。','confirm',function () {
                            location.reload();
                        })
                    }else{
                        if(ret==0) {
                            Notiflix.Notify.Failure('只有最高管理员或该题目的创建者可以修改！')
                            $('td input[type=checkbox]').prop('checked',false)
                        }else
                            location.reload();
                    }
                }
            );
        }
    </script>
@endsection
