@extends('layouts.client')

@if(isset($contest))
    @section('title',trans('main.Problem').' '.index2ch($problem->index).' | '.trans('main.Contest').' '.$contest->id.' | '.get_setting('siteName'))
@else
    @section('title',trans('main.Problem').' '.$problem->id.' | '.get_setting('siteName'))
@endif

@section('content')

    <div class="container">
        <div class="row">
            {{-- 竞赛下，显示菜单 --}}
            @if(isset($contest))
                <div class="col-12">
                    @include('contest.menu')
                </div>
            @endif

            <div class="col-lg-9 col-md-8 col-sm-12 col-12">
                <div class="my-container bg-white d-inline-block ck-content">
                    {{--                非竞赛&&题目未公开，则提示 --}}
                    @if(!isset($contest)&&$problem->hidden==1)
                        [<font class="text-red">{{trans('main.Hidden')}}</font>]
                    @endif
                    <h4 class="text-center">{{isset($contest)?index2ch($problem->index):$problem->id}}
                        . {{$problem->title}}
                        {{--                    管理员编辑题目的连接 --}}
                        @if(Auth::check()&&Auth::user()->privilege('teacher'))
                            <font style="font-size: 0.85rem">
                                [ <a href="{{route('admin.problem.update_withId',$problem->id)}}"
                                     target="_blank">{{__('main.Edit')}}</a> ]
                                [ <a href="{{route('admin.problem.test_data',['pid'=>$problem->id])}}"
                                     target="_blank">{{__('main.Test Data')}}</a> ]
                            </font>
                        @endif
                        @if(isset($contest)&&(Auth::check()&&Auth::user()->privilege('problem')||$contest->end_time<date('Y-m-d H:i:s')))
                            <font style="font-size: 0.85rem">
                                [
                                <a href="{{route('problem',$problem->id)}}">{{__('main.Problem')}} {{$problem->id}}</a>
                                <i class="fa fa-external-link text-sky" aria-hidden="true"></i>
                                ]
                            </font>
                        @endif
                        @if(!isset($contest)||$contest->open_discussion||time()>strtotime($contest->end_time))
                            <font style="font-size: 0.85rem">
                                [
                                <a href="javascript:"
                                   onclick="$('html,body').animate({scrollTop:$('#discussion_block').offset().top-20}, 400);"
                                   data-toggle="modal" data-target="#modal-discussion">
                                    {{__('main.Discussion')}}
                                </a>
                                ]
                            </font>
                        @endif
                    </h4>
                    <hr class="mt-0 mb-1">
                    <div>
                        <h4 class="text-sky">Description</h4>
                        {!! $problem->description !!}

                        @if($problem->input!=null)
                            <h4 class="mt-2 text-sky">Input</h4>
                            {!!$problem->input !!}
                        @endif

                        @if($problem->output!=null)
                            <h4 class="mt-2 text-sky">Output</h4>
                            {!!$problem->output !!}
                        @endif

                        @if(count($samples) > 0)
                            <h4 class="mt-2 text-sky">Samples</h4>
                        @endif
                        @foreach($samples as $i=>$sam)
                            <div class="border mb-4">
                                <div class="border-bottom pl-2 bg-light">Input
                                    <a href="javascript:" onclick="copy('sam_in{{$i}}')">{{__('main.Copy')}}</a>
                                </div>
                                <pre class="m-1" id="sam_in{{$i}}">{{$sam[0]}}</pre>
                                <div class="border-top border-bottom pl-2 bg-light">Output</div>
                                <pre class="m-1">{{$sam[1]}}</pre>
                            </div>
                        @endforeach

                        @if($problem->hint!=null)
                            <h4 class="mt-2 text-sky">Hint</h4>
                            {!! $problem->hint !!}
                        @endif

                        @if( ($problem->source!=null) && (!isset($contest)||$contest->end_time<date('Y-m-d H:i:s')) )
                            <h4 class="mt-2 text-sky">Source</h4>
                            {{$problem->source}}
                        @endif
                    </div>
                </div>

                @include('client.code_editor')

                @if(!isset($contest)||$contest->open_discussion||time()>strtotime($contest->end_time))
                    <div id="discussion_block" class="my-container bg-white ck-content">
                        <div class="d-flex">
                            <h4 class="flex-row">{{__('main.Discussions')}}</h4>
                            @if(Auth::check())
                                <button class="btn btn-info flex-row ml-2" data-toggle="modal"
                                        data-target="#edit-discussion"
                                        onclick="$('#form_edit_discussion')[0].reset()">{{__('main.New Discussion')}}</button>
                            @endif
                        </div>
                        {{--                    <hr class="mt-0 mb-1">--}}
                        <ul id="discussion-content" class="border-bottom list-unstyled"></ul>
                        <a href="javascript:" onclick="load_discussion()">{{__('main.More')}}>></a>
                    </div>
                @endif
            </div>

            <div class="col-lg-3 col-md-4 col-sm-12 col-12">

                {{-- 题目信息 --}}
                <div class="my-container bg-white">
                    <h5>{{__('main.Problem')}} {{__('main.Information')}}</h5>
                    <hr class="mt-0">
                    <div class="table-responsive">
                        <table id="table-overview" class="table table-sm">
                            <tbody>
                            <style type="text/css">
                                #table-overview td {
                                    border: 0;
                                    text-align: left
                                }
                            </style>
                            <tr>
                                <td nowrap>{{__('main.Time Limit')}}:</td>
                                <td nowrap>{{$problem->time_limit}}MS</td>
                            </tr>
                            <tr>
                                <td nowrap>{{__("main.Memory Limit")}}:</td>
                                <td nowrap>{{$problem->memory_limit}}MB</td>
                            </tr>
                            <tr>
                                <td nowrap>{{__('main.Special Judge')}}:</td>
                                @if($problem->spj==1)
                                    <td><font class="text-red">{{__('main.Yes')}}</font> @if(!$hasSpj)
                                            ({{__('sentence.Wrong spj')}}) @endif</td>
                                @else
                                    <td>{{__('main.No')}}</td>
                                @endif
                            </tr>
                            <tr>
                                <td nowrap>{{__("main.Solved")}} / {{__("main.Submitted")}}:</td>
                                <td nowrap>{{$problem->solved}}/ {{$problem->submit}}</td>
                            </tr>
                            @if(count($tags)>0 && (!isset($contest)||time()>strtotime($contest->end_time)))
                                <tr>
                                    <td nowrap>{{__("main.Tags")}}:</td>
                                    <td>
                                        @foreach($tags as $item)
                                            <font class="px-1 text-nowrap">{{$item->name}}(<i class="fa fa-user-o"
                                                                                              aria-hidden="true"
                                                                                              style="padding:0 1px"></i>{{$item->count}}
                                                )</font>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>


                    </div>
                </div>

                {{-- 饼状图--}}
                @if(count($results)>0)
                    <div class="my-container bg-white">

                        <h5>{{__('main.Submitted')}} {{__('main.Analysis')}}</h5>
                        <hr class="mt-0">
                        <div id="pieChart"></div>
                    </div>
                @endif

                {{-- 题库中查看题目时，显示涉及到的竞赛 --}}
                @if(!isset($contest)&&count($contests)>0)
                    <div class="my-container bg-white">

                        <h5>{{__('main.Contests involved')}}</h5>
                        <hr class="mt-0">

                        <div class="table-responsive">
                            <table id="table-overview" class="table table-sm">
                                <tbody>
                                <style type="text/css">
                                    #table-overview td {
                                        border: 0;
                                        text-align: left
                                    }
                                </style>
                                @foreach($contests as $item)
                                    <tr>
                                        <td><a href="{{route('contest.home',$item->id)}}">{{$item->id}}
                                                . {{$item->title}}</a></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                @endif

                {{-- 已经AC的用户进行标签标记 --}}
                @if($tag_mark_enable)
                    {{--                    模态框选择标签 --}}
                    <div class="modal fade" id="modal_tag_pool">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">

                                <!-- 模态框头部 -->
                                <div class="modal-header">
                                    <h4 class="modal-title">{{__('main.Tag Pool')}}</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>

                                <!-- 模态框主体 -->
                                <div class="modal-body ck-content">
                                    <div class="alert alert-success">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                                            &times;
                                        </button>
                                        {{__('sentence.tag_pool_select')}}
                                    </div>
                                    @foreach($tag_pool as $tag)
                                        <div class="d-inline text-nowrap mr-1">
                                            <i class="fa fa-tag" aria-hidden="true"></i>
                                            <a href="javascript:"
                                               onclick="add_tag_input($('#add_tag_btn'),'{{$tag->name}}');
                                                   // $('#modal_tag_pool').modal('hide')"
                                            >{{$tag->name}}</a>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- 模态框底部 -->
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="my-container bg-white">

                        <h5>{{trans('main.Tag Marking')}}</h5>
                        <hr class="mt-0">

                        <form action="{{route('tag_mark')}}" method="post" onsubmit="return check_tag_count();">
                            @csrf
                            <input name="problem_id" value="{{$problem->id}}" hidden>

                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;
                                </button>
                                {{__('sentence.Congratulations')}}
                            </div>
                            <div class="form-inline mb-2">
                                <font>{{__('main.Tag')}}：</font>
                                {{--                                <div class="form-inline">--}}
                                {{--                                    <input type="text" class="form-control mr-2" oninput="input_auto_width($(this))" required name="tag_names[]" style="width: 50px">--}}
                                {{--                                </div>--}}
                                <a id="add_tag_btn" class="btn btn-sm border mb-0" onclick="add_tag_input($(this))">
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                    {{__('main.Input').' '.__('main.Tag')}}
                                </a>
                                <a class="btn btn-sm border mb-0 ml-1" data-toggle="modal"
                                   data-target="#modal_tag_pool">
                                    <i class="fa fa-list" aria-hidden="true"></i>
                                    {{__('main.Tag Pool')}}
                                </a>
                            </div>
                            @if(count($tags)>0)
                                <div class="form-group">
                                    <font>{{__('main.Most Tagged')}}：</font>
                                    @foreach($tags as $item)
                                        <div class="d-inline text-nowrap">
                                            <i class="fa fa-tag" aria-hidden="true"></i>
                                            <a href="javascript:"
                                               onclick="add_tag_input($('#add_tag_btn'),'{{$item->name}}')">{{$item->name}}</a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <button type="submit" class="btn bg-success text-white mt-1"
                                    @guest disabled @endguest>{{trans('main.Submit')}}</button>
                        </form>

                    </div>
                @endif

                {{-- 题库中查看，显示提交记录--}}
                @if(!isset($contest))
                    <div class="my-container bg-white">

                        <h5>{{trans('main.MySolution')}}</h5>
                        <hr class="mt-0">

                        @if($solutions->count()==0)
                            <div class="alert alert-dismissible"><h6>{{trans('sentence.noSolutions')}}</h6></div>
                        @else
                            <div class="table-responsive">
                                <table id="table-solutions-sm" class="table table-hover">
                                    <thead>
                                    <tr>

                                        <th>#</th>
                                        <th>Result</th>
                                        <th>Time</th>
                                        <th>Memory</th>
                                        <th>Language</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <style type="text/css">
                                        #table-solutions-sm td, #table-solutions-sm th {
                                            padding: 0;
                                            text-align: center
                                        }
                                    </style>
                                    @foreach($solutions as $sol)
                                        <tr>
                                            <td>
                                                <a href="{{route('solution',$sol->id)}}"
                                                   target="_blank">{{$sol->id}}</a>
                                            </td>
                                            <td class="{{config('oj.resColor.'.$sol->result)}}">
                                                @if($sol->result<4)
                                                    <i class="fa fa-spinner" aria-hidden="true"></i>
                                                @endif
                                                {{config('oj.result.'.$sol->result)}}
                                            </td>
                                            <td>{{$sol->time}}ms</td>
                                            <td>{{round($sol->memory,2)}}MB</td>
                                            <td>{{config('oj.lang.'.$sol->language)}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="text-right">
                            <a href="{{route('status',['pid'=>$problem->id,'username'=>Auth::check()?Auth::user()->username:null])}}">{{trans('main.More')}}
                                >></a>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{--     模态框  编辑讨论内容 --}}
    @if(Auth::check()&&(!isset($contest)||$contest->open_discussion||time()>strtotime($contest->end_time)))
        {{--                模态框，管理员编辑公告 --}}
        <div class="modal fade" id="edit-discussion">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- 模态框头部 -->
                    <div class="modal-header">
                        <h4 id="notice-title" class="modal-title">{{__('main.New Discussion')}}</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <form id="form_edit_discussion" action="{{route('edit_discussion',$problem->id)}}" method="post">
                        <!-- 模态框主体 -->
                        <div class="modal-body">
                            @csrf
                            <input name="discussion_id" hidden>
                            <input name="reply_username" hidden>
                            <tips class="alert alert-info mb-0">备注：编辑框支持Latex公式
                                （tips：\$行内公式\$(注意反斜杠)，$$单行居中公式$$）
                            </tips>
                            <div class="form-group mt-2">
                                <textarea id="content" name="content"
                                          class="form-control-plaintext border bg-white"></textarea>
                            </div>
                        </div>

                        <!-- 模态框底部 -->
                        <div class="modal-footer p-4">
                            <button type="submit" class="btn btn-success">{{__('main.Submit')}}</button>
                            <button type="button" class="btn btn-secondary"
                                    data-dismiss="modal">{{__('main.Cancel')}}</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    @endif

    {{-- ckeditor样式 --}}
    @if(Auth::check())
        <script src="{{asset('static/ckeditor5-build-classic/ckeditor.js')}}"></script>
        <script src="{{asset('static/ckeditor5-build-classic/translations/zh-cn.js')}}"></script>
        <script type="text/javascript">

            //编辑框配置
            var config = {
                language: "zh-cn",
                removePlugins: ['Autoformat'],  //取消markdown自动排版
                ckfinder: {
                    uploadUrl: '{{route('ck_upload_image',['_token'=>csrf_token()])}}'
                }
            };
            //各个编辑框ckeditor
            var editor = ClassicEditor.create(document.querySelector('#content'), config).then(editor => {
                window.editor = editor;
                // console.log(editor.getData());
            }).catch(error => {
                console.log(error);
            });
        </script>
    @endif

    {{--copy--}}
    <script type="text/javascript">
        function copy(tag_id) {
            $("body").append('<textarea id="copy_temp">' + $('#' + tag_id).html() + '</textarea>');
            $("#copy_temp").select();
            document.execCommand("Copy");
            $("#copy_temp").remove();
            Notiflix.Notify.Success('{{__('sentence.copy')}}');
        }
    </script>

    {{--mathjax公式--}}
    <script type="text/x-mathjax-config">
        window.MathJax.Hub.Config({
            showProcessingMessages: false, //关闭js加载过程信息
            messageStyle: "none", //不显示信息
            jax: ["input/TeX", "output/HTML-CSS"],
            tex2jax: {
                inlineMath: [["\\$", "\\$"], ["\\(", "\\)"]], //行内公式选择符
                displayMath: [["$$", "$$"], ["\\[", "\\]"]], //段内公式选择符
                skipTags: ["script", "noscript", "style", "textarea", "pre", "code", "a", "tips"] //避开某些标签
            },
            "HTML-CSS": {
                availableFonts: ["STIX", "TeX"], //可选字体
                showMathMenu: false //关闭右击菜单显示
            }
        });
        window.MathJax.Hub.Queue(["Typeset", MathJax.Hub,document.getElementsByClassName("ck-content")]);


    </script>
    <script type="text/javascript" src="{{asset('static/MathJax-2.7.7/MathJax.js?config=TeX-AMS_HTML')}}"></script>

    {{-- 饼状图 --}}
    <script src="{{asset('static/echarts/echarts.min.js')}}"></script>
    <script type="text/javascript">
        var myChart = echarts.init(document.getElementById('pieChart'));
        myChart.setOption({
            title: {
                // text: '实例'
            },
            tooltip: {
                showDelay: 20,   // 显示延迟，添加显示延迟可以避免频繁切换，单位ms
                hideDelay: 20,   // 隐藏延迟，单位ms
                formatter: '{b} : {c} ({d}%)'
            },
            legend: {
                data: ['销量']
            },
            series: [{
                // name: '提交',
                type: 'pie',
                data: [
                        @foreach($results as $item)
                    {
                        name: '{{config('oj.result.'.$item->result)}}', value: {{$item->result_count}}
                    },
                    @endforeach
                ]
            }]
        });

        function echart_resize(myChart) {
            var dom = $("#pieChart")
            var width = dom.width() * 0.8
            myChart.resize({height: width});
            dom.height(width)
        }

        echart_resize(myChart)
        window.onresize = function () {
            echart_resize(myChart)
        }
    </script>

    {{-- 问题标签的操作 --}}
    <script type="text/javascript">
        @if(session('tag_marked'))
            $(function () {
                Notiflix.Notify.Success("{{__('sentence.tag_marked')}}");
            })
        @endif
        var tag_input_count = 0;

        function add_tag_input(that, defa = null) {
            if (tag_input_count >= 5) {
                Notiflix.Notify.Failure("{{__('sentence.tag_marked_exceed')}}")
                return;
            }
            var dom = "<div class=\"form-inline\">\n" +
                "    <input type=\"text\" class=\"form-control mr-2\" oninput=\"input_auto_width($(this))\" required name=\"tag_names[]\" style=\"width: 50px\">\n" +
                "    <a style=\"margin-left: -25px;cursor: pointer\" onclick=\"delete_tag_input($(this))\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>\n" +
                "</div>";
            $(that).before(dom);
            var input = $(that).prev().children("input");
            // input.focus();
            if (defa != null) {
                input.val(defa);
                input_auto_width(input);
            }
            tag_input_count++;
        }

        //初始化, 至少一个输入框
        add_tag_input($("#add_tag_btn"))

        function delete_tag_input(that) {
            tag_input_count--;
            $(that).parent().remove();
        }

        function check_tag_count() {
            if (tag_input_count > 0)
                return true;
            Notiflix.Notify.Failure("{{__('sentence.tag_marked_zero')}}");
            return false;
        }

        //输入框根据字数自动调整宽度
        function input_auto_width(that) {
            $(that).val($(that).val().replace(/\s+/g, '')); //禁止输入空格
            var sensor = $('<font>' + $(that).val() + '</font>').css({display: 'none'});
            $('body').append(sensor);
            var width = sensor.width();
            sensor.remove();
            $(that).css('width', (width + 30) + 'px');
        }
    </script>

    {{-- 讨论板的操作 --}}
    <script type="text/javascript">
        @if(session('discussion_added'))
        $(function () {
            Notiflix.Notify.Success("{{__('sentence.discussion_added')}}");
        })
        @endif
        @if(session('discussion_add_failed'))
        $(function () {
            Notiflix.Notify.Failure("五分钟内只允许发起一次讨论！");
        })
        @endif
        // 加载discussion
        let discussion_page = 0;
        function load_discussion() {
            discussion_page++;
            $.post(
                '{{route('load_discussion')}}',
                {
                    '_token': '{{csrf_token()}}',
                    'problem_id': '{{$problem->id}}',
                    'page': discussion_page
                },
                function (ret) {
                    ret = JSON.parse(ret);
                    // console.log(ret);
                    let discussions = ret[0];
                    let replies = ret[1];
                    for (let i = 0, len = discussions.length; i < len; i++) {
                        const dis = discussions[i];
                        //主评论
                        let dis_div =
                            "<div class=\"overflow-hidden border-top pt-1\">\n" +
                            "   <p class=\"mb-0\">" + dis.username + "：" + "</p>\n" +
                            "   <div class=\"pl-1\">" + dis.content + "</div>" +
                            "   <div class=\"float-right\" style=\"font-size: 0.85rem\">\n" +
                            (dis.top ? "[<font class=\"text-red px-1\">{{trans('main.Top')}}</font>]" : '') +
                            (dis.hidden ? "[<font class=\"text-red px-1\">{{trans('main.Hidden')}}</font>]" : '') +
                            @if(Auth::check()&&Auth::user()->privilege('problem_tag'))
                            (dis.top ?
                                "    <a href=\"javascript:top_discussion(" + dis.id + ",0)\" class=\"px-1\">{{__('main.Cancel Top')}}</a>\n" :
                                "    <a href=\"javascript:top_discussion(" + dis.id + ",1)\" class=\"px-1\">{{__('main.To Top')}}</a>\n") +
                            (dis.hidden ?
                                "    <a href=\"javascript:hidden_discussion(" + dis.id + ",0)\" class=\"px-1\">{{__('main.Public')}}</a>\n" :
                                "    <a href=\"javascript:hidden_discussion(" + dis.id + ",1)\" class=\"px-1\">{{__('main.Hidden')}}</a>\n") +
                            "    <a href=\"javascript:\" onclick=\"delete_discussion(" + dis.id + ",$(this))\" class=\"px-1\">{{__('main.Delete')}}</a>\n" +
                            @endif
                                @if(Auth::check())
                                "    <a href=\"javascript:reply(" + dis.id + ")\" class=\"px-1\">{{__('main.Reply')}}</a>\n" +
                            @endif
                                "        <span>" + dis.created_at + "</span>\n" +
                            "    </div>\n" +
                            "</div>";
                        //子评论
                        let son_ul = "";
                        if (replies.hasOwnProperty(dis.id)) //有子评论
                        {
                            son_ul = "<ul>";
                            for (let j = 0, lenj = replies[dis.id].length; j < lenj; j++) {
                                const son_dis = replies[dis.id][j];
                                let reply_name = (son_dis.reply_username == null ? "" : " <font class='bg-light'>@" + son_dis.reply_username + "</font>");
                                let son_li =
                                    "<li class=\"overflow-hidden border-top pt-1\">\n" +
                                    "    <font>" + son_dis.username + reply_name + "：</font>\n" +
                                    "    <div class=\"pl-1\">" + son_dis.content + "</div>\n" +
                                    "    <div class=\"float-right\" style=\"font-size: 0.85rem\">\n" +
                                    (son_dis.hidden ? "[<font class=\"text-red px-1\">{{trans('main.Hidden')}}</font>]" : '') +
                                    @if(Auth::check()&&Auth::user()->privilege('problem_tag'))
                                    (son_dis.hidden ?
                                        "   <a href=\"javascript:hidden_discussion(" + son_dis.id + ",0)\" class=\"px-1\">{{__('main.Public')}}</a>\n" :
                                        "   <a href=\"javascript:hidden_discussion(" + son_dis.id + ",1)\" class=\"px-1\">{{__('main.Hidden')}}</a>\n") +
                                    "   <a href=\"javascript:\" onclick=\"delete_discussion(" + dis.id + ",$(this))\" class=\"px-1\">{{__('main.Delete')}}</a>\n" +
                                    @endif
                                        @if(Auth::check())
                                        "   <a href=\"javascript:reply(" + dis.id + ",\'" + $(son_dis.username).html() + "\')\" class=\"px-1\">{{__('main.Reply')}}</a>\n" +
                                    @endif
                                        "       <span>" + son_dis.created_at + "</span>\n" +
                                    "   </div>\n" +
                                    "</li>";
                                son_ul += son_li;
                            }
                            son_ul += "</ul>";
                        }
                        $("<li>" + dis_div + son_ul + "</li>").hide(0).slideDown(200).appendTo("#discussion-content");
                    }
                    if (discussions.length < 1)
                        $("#discussion-content").append("<p>{{__('sentence.No more discussions')}}</p>");
                }
            );
            window.MathJax.Hub.Queue(["Typeset", MathJax.Hub, document.getElementsByClassName("ck-content")]);
        }

        load_discussion()


        function delete_discussion(id, that) {
            $.post(
                '{{route('delete_discussion')}}',
                {
                    '_token': '{{csrf_token()}}',
                    'id': id,
                },
                function (ret) {
                    Notiflix.Notify.Success("删除成功！");
                    $(that).parent().parent().slideUp(200)
                }
            );
        }

        function top_discussion(id, way) {
            $.post(
                '{{route('top_discussion')}}',
                {
                    '_token': '{{csrf_token()}}',
                    'id': id,
                    'way': way
                },
                function (ret) {
                    Notiflix.Notify.Success(way ? "已置顶显示！" : "已取消置顶！");
                }
            );
        }

        function hidden_discussion(id, value) {
            $.post(
                '{{route('hidden_discussion')}}',
                {
                    '_token': '{{csrf_token()}}',
                    'id': id,
                    'value': value
                },
                function (ret) {
                    Notiflix.Notify.Success(value ? "已隐藏，仅管理员可见！" : "已公开，所有用户可见！");
                }
            );
        }

        function reply(id, username = '') {
            $("#edit-discussion").modal('show');
            $("input[name=discussion_id]").val(id);
            $("input[name=reply_username]").val(username);
            $("#content").val();
        }
    </script>

@endsection
