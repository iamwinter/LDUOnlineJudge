@extends('layouts.client')

@section('title',trans('main.Problems').' | '.get_setting('siteName'))

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-12 col-md-10">
                <div class="my-container bg-white">
                    <div class="overflow-hidden">
                        <h4 class="pull-left">{{__('main.Problems')}}</h4>
                        <form id="find_form" action="" method="get" class="pull-right form-inline">
                            <input type="number" id="tag_id" name="tag_id" hidden>
                            <div class="form-inline custom-control custom-checkbox">
                                <input type="checkbox" name="show_hidden" class="custom-control-input" id="customCheck"
                                       @if(isset($_GET['show_hidden']))checked @endif
                                       onchange="this.form.submit()">
                                <label class="custom-control-label pt-1" for="customCheck">{{__('sentence.show_hidden_problems')}}</label>
                            </div>
                            <div class="form-inline mx-3">
                                <select name="perPage" class="form-control px-2" onchange="this.form.submit();">
                                    <option value="20" @if(isset($_GET['perPage'])&&$_GET['perPage']==20)selected @endif>20</option>
                                    <option value="50" @if(isset($_GET['perPage'])&&$_GET['perPage']==50)selected @endif>50</option>
                                    <option value="100" @if(!isset($_GET['perPage'])||$_GET['perPage']==100)selected @endif>100</option>
                                </select>
                            </div>
                            <div class="form-inline mx-3">
                                <input type="number" class="form-control text-center" placeholder="{{__('main.Problem')}} {{__('main.ID')}}" onchange="this.form.submit();"
                                       name="pid" value="{{isset($_GET['pid'])?$_GET['pid']:''}}">
                            </div>
                            <div class="form-inline mx-3">
                                <input type="text" class="form-control text-center" placeholder="{{__('main.Title')}}" onchange="this.form.submit();"
                                       name="title" value="{{isset($_GET['title'])?$_GET['title']:''}}">
                            </div>
                            <div class="form-inline mx-3">
                                <input type="text" class="form-control text-center" placeholder="{{__('main.Source')}}" onchange="this.form.submit();"
                                       name="source" value="{{isset($_GET['source'])?$_GET['source']:''}}">
                            </div>
                            <button class="btn border">{{__('main.Find')}}</button>
                        </form>
                    </div>
                    {{$problems->appends($_GET)->links()}}
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{trans('main.Title')}}</th>
                                <th>{{trans('main.Source')}}</th>
                                <th>{{trans('main.AC/Submitted')}}</th>
                                <th>{{__('main.Tag')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($problems as $item)
                                <tr>
                                    <td>{{$item->id}}</td>
                                    @if($item->hidden==0 || Auth::check()&&Auth::user()->privilege('teacher'))
                                        <td nowrap>
                                            <a href="{{route('problem',$item->id)}}">{{$item->title}}</a>
                                            @if($item->hidden==1)
                                                (<font class="text-red">{{trans('main.Hidden')}}</font>)
                                            @endif
                                        </td>
                                        <td nowrap>
                                            <a title="{{$item->source}}">{{$item->source}}</a>
                                        </td>
                                        <td nowrap>
                                            {{$item->accepted}} / {{$item->submit}}
                                            ({{round($item->accepted/max(1.0,$item->submit)*100)}}%)
                                        </td>
                                        <td nowrap>
                                            @foreach($item->tags as $tag)
                                                <div class="d-inline text-nowrap mr-1">
                                                    <i class="fa fa-tag" aria-hidden="true"></i><a href="javascript:findByTagId({{$tag->id}})">{{$tag->name}}</a>
                                                </div>
                                            @endforeach
                                        </td>
                                    @else
                                        <td>--- {{trans('main.Hidden')}} ---</td>
                                        <td>-</td>
                                        <td>-&nbsp;/&nbsp;-</td>
                                        <td>-</td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{$problems->appends($_GET)->links()}}
                    @if(count($problems)==0)
                        <p class="text-center">{{__('sentence.No data')}}</p>
                    @endif
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="my-container bg-white">
                    <h4>{{__('main.Tags')}}</h4>
                    <hr>
                    <div style="font-size: 0.9rem">
                        @foreach($tag_pool as $tag)
                            <div class="d-inline text-nowrap mr-1">
                                <i class="fa fa-tag" aria-hidden="true"></i><a href="javascript:findByTagId({{$tag->id}})">{{$tag->name}}</a>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function findByTagId(tag_id) {
            $('#tag_id').val(tag_id);
            $('#find_form').submit();
        }
    </script>
@endsection
