@extends('layouts.admin')

@section('title','黑名单管理 | 后台')

@section('content')


    <div class="row">

        <div class="col-md-6 table-responsive">
            <h2>黑名单管理</h2>
            <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
            <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>

{{--            <a href="javascript:" class="ml-3">预设</a>--}}
{{--            <a href="javascript:" class="text-gray" data-toggle="tooltip"--}}
{{--               title="解释">--}}
{{--                <i class="fa fa-question-circle-o" aria-hidden="true"></i>--}}
{{--            </a>--}}

            <table class="table table-striped table-hover table-sm">
                <thead>
                <tr>
                    <th></th>
                    <th>登录名</th>
                    <th>姓名</th>
                    <th>创建时间</th>
                    <th>添加人</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                @foreach($blacklist as $item)
                    <tr data-toggle="tooltip" data-placement="bottom" title="拉黑原因：{{$item->reason}}">
                        <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                            <input type="checkbox" value="{{$item->id}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                        </td>
                        <td nowrap><a href="{{route('user',$item->username)}}" target="_blank">{{$item->username}}</a></td>
                        <td nowrap>{{$item->nick}}</td>
                        <td nowrap>{{$item->created_at}}</td>
                        <td><a @if($item->creator)href="{{route('user',$item->creator)}}"@endif target="_blank">{{$item->creator}}</a></td>
                        <td>
                            <a href="javascript:delete_black({{$item->id}})" class="px-1" title="删除">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-md-6 border-left">
            <h2>加入黑名单</h2>
            @if (session('msg'))
                <div class="alert alert-info">
                    {{ session('msg') }}
                </div>
            @endif
            <form action="{{route('admin.user.blacklist_create')}}" method="post">
                @csrf
                <div class="form-group col-8">
                    <lable class="form-inline">
                        用户账号：
                        <input type="text" autocomplete="off" name="username" class="form-control" required>
                    </lable>
                </div>
                <div class="form-group col-12">
                    <font class="pull-left">拉黑原因：</font>
                    <textarea name="reason" cols="45" autoHeight maxlength="255"></textarea>
                </div>
                <div class="form-group col-8 text-center">
                    <button class="btn border">提交</button>
                </div>
            </form>
            <div class="table-responsive border-top pt-5">
                <h5>黑名单说明</h5>
                <p>
                    加入黑名单的用户将无法查看题目、无法进入竞赛（但可查看榜单）、无法提交任何题目，
                    加入黑名单的管理员将无法进入后台管理。
                    <br>
                    当黑名单用户访问被限制的页面时，将向用户展示拉黑原因。
                </p>
            </div>
        </div>
    </div>

    <script>

        function delete_black(id) {
            Notiflix.Confirm.Init();
            Notiflix.Confirm.Show( '敏感操作', '确定将该用户从黑名单中移除?', '确认', '取消', function(){
                $.post(
                    '{{route('admin.user.blacklist_delete')}}',
                    {
                        '_token':'{{csrf_token()}}',
                        'id':id,
                    },
                    function (ret) {
                        location.reload();
                    }
                );
            });
        }


        // textarea自动高度
        $(function(){
            $.fn.autoHeight = function(){
                function autoHeight(elem){
                    elem.style.height = 'auto';
                    elem.scrollTop = 0; //防抖动
                    elem.style.height = elem.scrollHeight+2 + 'px';
                }
                this.each(function(){
                    autoHeight(this);
                    $(this).on('input', function(){
                        autoHeight(this);
                    });
                });
            }
            $('textarea[autoHeight]').autoHeight();
        })

        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection
