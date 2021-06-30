<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    @include('layouts.head')
    <title>@yield('title')</title>

    <style type="text/css">
        .nav-link, .btn {
            text-transform: none;  /*不使用大写*/
        }

        /*侧边菜单*/
        .sidebar{
            position: fixed;  /*相对于窗口定位*/
            top: 56px;
            bottom: 0;
            left: 0;
            padding:0;
            z-index:10;
            background-color: rgba(255,255,255,.85);
        }
        .sidebar-sticky {
            height: 100%;
            padding: .7rem 0;
            overflow-y: auto;
        }
        /*下面是菜单栏滚动条样式*/
        .sidebar-sticky::-webkit-scrollbar {
            width : 5px;  /*滚动条整体样式*/
        }
        .sidebar-sticky::-webkit-scrollbar-thumb {
            border-radius: 10px;  /*滚动条里面小方块*/
            box-shadow   : inset 0 0 5px rgb(3, 255, 0);
            background   : rgba(86, 169, 226, 0.79);
        }
        .sidebar-sticky::-webkit-scrollbar-track {
            box-shadow   : inset 0 0 5px rgba(0, 0, 0, 0.2);/*滚动条里面轨道*/
            border-radius: 10px;
            background   : #ededed;
        }

        /*下面是菜单项右侧小箭头*/
        .sidebar a[aria-expanded="true"]::before {
            display: block;
            position: absolute;
            right: 20px;
            font-family:FontAwesome;
            font-weight:normal;
            font-style:normal;
            content: '\f078';
        }
        .sidebar a[aria-expanded="false"]::before {
            display: block;
            position: absolute;
            right: 20px;
            font-family:FontAwesome;
            font-weight:normal;
            font-style:normal;
            content:"\f054"
        }

        /*选中的菜单项样式*/
        .nav-item .active{
            background-color: #e6e6e6;
            color: #000000;
        }

        /*侧边菜单项不换行*/
        #left-menu a{
            white-space:nowrap;
        }


        @media screen and (min-width: 768px) {
            /*大屏幕，隐藏侧边栏按钮*/
            #btn-left-menu{
                display: none;
            }
        }
        @media screen and (max-width: 768px) {
            /*小屏幕，初始隐藏侧边栏*/
            #left-menu{
                display: none;
            }
        }


        /*所有table的表头不换行*/
        th{
            white-space: nowrap
        }
    </style>
</head>
<body>

<div class="h-100" style="padding-top: 70px;">

    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top" style="z-index: 5">

        <button id="btn-left-menu" class="btn border m-0"
            onclick="$('#left-menu').css('display')=='none'?$('#left-menu').slideLeftShow():$('#left-menu').slideLeftHide()">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand pl-2" href="{{route('admin.home')}}">后台管理</a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse bg-white" id="bs-example-navbar-collapse-1">
            <ul class="navbar-nav">
                <li class="nav-item" style="text-align:right"><a class="nav-link text-nowrap mb-0" href="{{route('home')}}">
                        <i class="fa fa-home" aria-hidden="true"></i> 返回前台</a></li>
                <li class="nav-item" style="text-align:right"><a class="nav-link text-nowrap mb-0" href="{{route('status')}}">
                        <i class="fa fa-paper-plane-o" aria-hidden="true">&nbsp;</i>{{trans('main.Status')}}</a></li>

            </ul>


            <ul class="navbar-nav ml-auto">
                <!-- Authentication Links -->
                <li class="nav-item dropdown" style="text-align:right">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        {{ Auth::user()->username }} <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">

                        <a class="dropdown-item" style="margin-left: auto" href="{{route('user',Auth::user()->username)}}">{{trans('main.Profile')}}</a>
                        <a class="dropdown-item" style="margin-left: auto" href="{{route('password_reset',Auth::user()->username)}}">{{trans('sentence.Reset Password')}}</a>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item" style="margin-left: auto" href="{{ route('logout') }}"
                           onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            {{ __('main.Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>

            </ul>

        </div>
    </nav>

    <nav id="left-menu" class="col-10 col-sm-6 col-md-2 sidebar border">
        <div class="sidebar-sticky">
            <ul class="list-unstyled">
                <li class="nav-item">
                    <a class="nav-link" href="{{route('admin.home')}}">
                        <i class="fa fa-bar-chart fa-lg" aria-hidden="true"></i> 概览
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link border-top position-relative" href="#" data-toggle="collapse"
                       data-target="#menu-notice" aria-expanded="false">
                        <i class="fa fa-sticky-note-o mr-1" aria-hidden="true"></i>
                        公告管理
                    </a>
                    <ul id="menu-notice" class="collapse">

                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.notice.list')}}">
                                <i class="fa fa-list" aria-hidden="true"></i> 公告列表</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.notice.add')}}">
                                <i class="fa fa-plus" aria-hidden="true"></i> 发布公告</a>
                        </li>

                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link border-top position-relative" href="#" data-toggle="collapse"
                       data-target="#menu-user" aria-expanded="false">
                        <i class="fa fa-users mr-1" aria-hidden="true"></i>
                        账号管理
                    </a>
                    <ul id="menu-user" class="collapse">

                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.user.list')}}">
                                <i class="fa fa-list" aria-hidden="true"></i> 账号列表</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.user.privileges')}}">
                                <i class="fa fa-universal-access" aria-hidden="true"></i> 权限管理</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.user.create')}}">
                                <i class="fa fa-user-plus" aria-hidden="true"></i> 账号批量生成</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.user.reset_pwd')}}">
                                <i class="fa fa-refresh" aria-hidden="true"></i> 账号密码重置</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.user.blacklist')}}">
                                <i class="fa fa-user-secret" aria-hidden="true"></i> 黑名单</a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link border-top position-relative" href="#" data-toggle="collapse"
                       data-target="#menu-problem" aria-expanded="false">
                        <i class="fa fa-file-text-o fa-lg mr-2" aria-hidden="true"></i>题库管理
                    </a>
                    <ul id="menu-problem" class="collapse">
                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.problem.list')}}">
                                <i class="fa fa-list" aria-hidden="true"></i> 题库</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.problem.add')}}">
                                <i class="fa fa-plus" aria-hidden="true"></i> 添加题目</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.problem.update')}}">
                                <i class="fa fa-edit" aria-hidden="true"></i> 修改题目</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.problem.tags')}}">
                                <i class="fa fa-tag" aria-hidden="true"></i> 标签管理</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.problem.tag_pool')}}">
                                <i class="fa fa-tags" aria-hidden="true"></i> 标签库</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.problem.test_data')}}">
                                <i class="fa fa-file-text" aria-hidden="true"></i> 测试数据管理</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.problem.rejudge')}}">
                                <i class="fa fa-recycle" aria-hidden="true"></i> 重判提交</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.problem.import_export')}}">
                                <i class="fa fa-sign-in" aria-hidden="true"></i> 导入与导出</a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link border-top position-relative" href="#" data-toggle="collapse"
                       data-target="#menu-contest" aria-expanded="false">
                        <i class="fa fa-trophy fa-lg mr-2" aria-hidden="true"></i>竞赛管理
                    </a>
                    <ul id="menu-contest" class="collapse">
                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.contest.list')}}">
                                <i class="fa fa-list" aria-hidden="true"></i> 竞赛列表</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-top" href="{{route('admin.contest.add')}}">
                                <i class="fa fa-plus" aria-hidden="true"></i> 添加竞赛</a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link border-top" href="{{route('admin.settings')}}">
                        <i class="fa fa-cogs fa-lg" aria-hidden="true"></i> 系统配置
                    </a>
                </li>

            </ul>
        </div>
    </nav>


    <main class="col-12 col-sm-12 col-md-10  ml-auto">

        @yield('content')
        @include('layouts.footer')
    </main>

</div>

<script type="text/javascript">
    // 移动端 左侧菜单栏滑动效果
    jQuery.fn.slideLeftHide = function( speed, callback ) {
        this.animate({
            width : "hide",
            paddingLeft : "hide",
            paddingRight : "hide",
            marginLeft : "hide",
            marginRight : "hide"
        }, speed, callback );
    };
    jQuery.fn.slideLeftShow = function( speed, callback ) {
        this.animate({
            width : "show",
            paddingLeft : "show",
            paddingRight : "show",
            marginLeft : "show",
            marginRight : "show"
        }, speed, callback );
    };
</script>

<script type="text/javascript">

    // 遍历导航栏按钮，如果href与当前位置相等，就active
    $(function () {
        $("ul li.nav-item").each(function () {
            var a = $(this).find("a:first")[0];
            var href = $(a).attr("href")
            var url=location.href.split('?')[0];
            if(/\d+$/.test(url))
                url=url.substring(0,url.lastIndexOf('/')); //去掉编号参数
            if (url===href) {
                $(a).addClass("active");
                $(this).parent().prev().click();
                $('.sidebar-sticky').animate({scrollTop:$(this).parent().position().top-50+'px'});
            }
        });
    })

</script>

</body>
</html>
