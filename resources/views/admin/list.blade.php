@extends('layouts.admin')

@section('title',$secTitle.' | 后台')

@section('content')

    <h2>{{$secTitle}}</h2>
    <div class="table-responsive">
        {{$list->appends($_GET)->links()}}
        <a href="javascript:$('td input[type=checkbox]').prop('checked',true)" class="btn border">全选</a>
        <a href="javascript:$('td input[type=checkbox]').prop('checked',false)" class="btn border">取消</a>
        @if(isset($oper_checked))
            @foreach($oper_checked as $item)
                {!! $item !!}
            @endforeach
        @endif
        <table class="table table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th></th>
                    @foreach($thead as $th)
                        <th>{{$th}}</th>
                    @endforeach
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($list as $item)
                    <tr>
                        <td onclick="var cb=$(this).find('input[type=checkbox]');cb.prop('checked',!cb.prop('checked'))">
                            <input type="checkbox" value="{{$item->id}}" onclick="window.event.stopPropagation();" style="vertical-align:middle;zoom: 140%">
                        </td>
                        @foreach($item as $key=>$td)
                            <td nowrap>{!! $td !!}</td>
                        @endforeach
                        <td nowrap>@if(isset($operation)){!! $operation[$item->id] !!} @endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{$list->appends($_GET)->links()}}
    </div>
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip({placement:'bottom'}); //提示
        });

        function change_hidden_to(hidden) {
            // 修改题目状态 1公开 or 0隐藏
            var pids=[];
            $('td input[type=checkbox]:checked').each(function () { pids.push($(this).val()); });
            $.post(
                '{{route('admin.change_hidden_to')}}',
                {
                    '_token':'{{csrf_token()}}',
                    'pids':pids,
                    'hidden':hidden,
                },
                function (ret) {
                    location.reload();
                    alert(ret+'条数据已更新状态！');
                }
            );
        }


        function change_revise_to(revise) {
            // 修改用户可以修改个人资料的次数
            var uids=[];
            $('td input[type=checkbox]:checked').each(function () { uids.push($(this).val()); });
            {{--$.post(--}}
            {{--    '{{route('admin.change_revise_to')}}',--}}
            {{--    {--}}
            {{--        '_token':'{{csrf_token()}}',--}}
            {{--        'uids':uids,--}}
            {{--        'revise':revise,--}}
            {{--    },--}}
            {{--    function (ret) {--}}
            {{--        location.reload();--}}
            {{--        alert(ret+'条数据已更新！');--}}
            {{--    }--}}
            {{--);--}}
            $.ajax({
                headers: {'X-CSRF-TOKEN': "{{csrf_token()}}"},
                type:"post",
                url: "{{route('admin.change_revise_to')}}",
                data:{'uids':uids,'revise':revise},
                dataType:"json",
                success:function (ret) {
                    location.reload();
                    alert(ret+'条数据已更新！');
                },
                error:function (ret) {
                    alert("系统错误 "+ret.toString());
                    console.log(ret);
                }
            })
        }
    </script>
@endsection
