@extends('layouts.admin')

@section('title','账号权限管理 | 后台')

@section('content')


    <div class="row">

        <div class="col-md-6 table-responsive">
            <h2>账号权限管理</h2>
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
                    <th>权限编号</th>
                    <th>登录名</th>
                    <th>姓名</th>
                    <th>权限</th>
                    <th>创建时间</th>
                    <th>添加人</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                @foreach($privileges as $item)
                    <tr>
                        <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                            <input type="checkbox" value="{{$item->id}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                        </td>
                        <td>{{$item->id}}</td>
                        <td nowrap><a href="{{route('user',$item->username)}}" target="_blank">{{$item->username}}</a></td>
                        <td nowrap>{{$item->nick}}</td>
                        <td nowrap>{{$item->authority}}</td>
                        <td nowrap>{{$item->created_at}}</td>
                        <td><a @if($item->creator)href="{{route('user',$item->creator)}}"@endif target="_blank">{{$item->creator}}</a></td>
                        <td>
                            @if($item->username!='admin'||$item->authority!='admin')
                                <a href="javascript:delete_privilege({{$item->id}})" class="px-1" title="删除">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-md-6 border-left">
            <h2>增设特权账号</h2>
            @if (session('msg'))
                <div class="alert alert-info">
                    {{ session('msg') }}
                </div>
            @endif
            <form action="{{route('admin.user.privilege_create')}}" method="post"
                onsubmit="if($('#privi').val()==='admin')return confirm('将admin权限分配给用户会使后台数据非常危险!\n' +
                    '推荐您分配单独权限给用户！\n' +
                    '若仍要执行，请点击确定')">
                @csrf
                <input type="text" name="type" value="add" hidden>
                <div class="form-group col-8">
                    <lable class="form-inline">
                        用户：
                        <input type="text" autocomplete="off" name="username" class="form-control" required>
                    </lable>
                </div>
                <div class="form-group col-8">
                    <label class="form-inline">
                        权限：
                        <select id="privi" class="form-control border border-bottom-0 px-3 bg-white" name="privilege[authority]">
                            <option value="admin">admin（超级管理员）</option>
                            <option value="teacher">teacher（教师）</option>
                            <option value="solution">solution（查看代码）</option>
{{--                            <option value="problem_list">problem_list（查看问题列表）</option>--}}
{{--                            <option value="problem_tag">problem_tag（管理问题标签与讨论板）</option>--}}
{{--                            <option value="edit_problem">edit_problem（编辑/添加题目）</option>--}}
{{--                            <option value="problem_data">problem_data（管理测试数据）</option>--}}
{{--                            <option value="problem_rejudge">problem_rejudge（重判提交记录）</option>--}}
{{--                            <option value="import_export_problem">import_export_problem（导入/导出题目）</option>--}}
{{--                            <option value="contest">contest（管理竞赛）</option>--}}
                            <option value="balloon">balloon（派送气球）</option>
                        </select>
                    </label>
                </div>
                <div class="form-group col-8 text-center">
                    <button class="btn border">提交</button>
                </div>
            </form>
            <div class="table-responsive border-top pt-3">
                <h5>权限说明</h5>
                <table id="table-overview" class="table table-sm"><style type="text/css">
                        #table-overview th,#table-overview td{border: 0;text-align: left}
                    </style>
                    <thead>
                        <th>权限代号</th>
                        <th>权限解释</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>admin</td><td nowrap>超级管理员：所有权限</td>
                        </tr>
                        <tr>
                            <td nowrap>teacher</td>
                            <td nowrap>
                                <p class="mb-0">教师：</p>
                                <p class="mb-0" style="text-indent:2em;">添加/修改题目</p>
                                <p class="mb-0" style="text-indent:2em;">问题标签</p>
                                <p class="mb-0" style="text-indent:2em;">问题讨论版</p>
                                <p class="mb-0" style="text-indent:2em;">管理测试数据</p>
                                <p class="mb-0" style="text-indent:2em;">管理竞赛</p>
                                <p class="mb-0" style="text-indent:2em;">重判学生代码</p>
                            </td>
                        </tr>
                        <tr>
                            <td nowrap>solution</td><td nowrap>查看所有用户提交的代码</td>
                        </tr>
{{--                        <tr>--}}
{{--                            <td nowrap>problem_list</td><td nowrap>查看题目列表</td>--}}
{{--                        </tr>--}}
{{--                        <tr>--}}
{{--                            <td nowrap>problem_tag</td><td nowrap>管理问题标签、讨论板</td>--}}
{{--                        </tr>--}}
{{--                        <tr>--}}
{{--                            <td nowrap>edit_problem</td><td nowrap>添加、修改题目内容（包括spj）</td>--}}
{{--                        </tr>--}}
{{--                        <tr>--}}
{{--                            <td nowrap>problem_data</td><td nowrap>管理题目测试数据</td>--}}
{{--                        </tr>--}}
{{--                        <tr>--}}
{{--                            <td nowrap>problem_rejudge</td><td nowrap>重判提交记录</td>--}}
{{--                        </tr>--}}
{{--                        <tr>--}}
{{--                            <td nowrap>import_export_problem</td><td nowrap>导入与导出题目</td>--}}
{{--                        </tr>--}}
{{--                        <tr>--}}
{{--                            <td nowrap>contest</td><td nowrap>管理竞赛，增删改查</td>--}}
{{--                        </tr>--}}
                        <tr>
                            <td nowrap>balloon</td><td nowrap>在竞赛中查看气球派送信息</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>

        function delete_privilege(id) {
            Notiflix.Confirm.Init();
            Notiflix.Confirm.Show( '敏感操作', '确定删除该权限?', '确认', '取消', function(){
                $.post(
                    '{{route('admin.user.privilege_delete')}}',
                    {
                        '_token':'{{csrf_token()}}',
                        'id':id,
                        'type':'delete',
                    },
                    function (ret) {
                        location.reload();
                    }
                );
            });
        }
    </script>
@endsection
