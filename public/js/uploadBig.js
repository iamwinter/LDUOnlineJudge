function uploadBig(obj) {
    var args={
        url:obj.url,        //必须,相当于form的action
        _token:obj._token,  //必须，laravel token：'{{csrf_token()}}'
        files:obj.files,    //必须，上传的文件列表
        data:obj.data,     //可选，除files外的其他数据
        blockSize:1024*(obj.blockSize!==undefined?obj.blockSize:900),  //可选，每块的大小，默认900KB
        before:   obj.before,    //可选，上传前执行函数
        uploading:obj.uploading, //可选，上传中执行函数
        success:  obj.success,   //可选，上传成功执行函数
        error:    obj.error,     //可选，上传出错执行函数
    };

    function dfs_ajax(index=0,start=0) {
        var formData = new FormData();
        formData.append('filename',args.files[index].name);     //文件原始名
        formData.append('block_id',Math.round(start/args.blockSize));  //块号
        formData.append('block_tot',Math.ceil(args.files[index].size/args.blockSize));//块数
        formData.append('block',args.files[index].slice(start,start+args.blockSize)); //文件块
        if(args.data!==undefined && start+args.blockSize>=args.files[index].size)//要上传最后一块了
        {
            for(let key of Object.keys(args.data))
                formData.append(key,args.data[key]); //除文件外的附加数据
        }
        $.ajax({
            headers: {'X-CSRF-TOKEN': args._token},
            url: args.url ,
            type: 'post',
            data: formData,
            processData: false,
            contentType: false,
            success:function(ret){
                // 只有ret返回0，才代表文件需要继续上传
                if(ret!==0 || index===args.files.length-1 && start+args.blockSize>=args.files[index].size)//最后一个上传完毕
                {
                    //上传成功...回调函数[文件总数，控制器返回值]
                    if(args.success!==undefined)
                        args.success(args.files.length,ret);
                }
                else
                {
                    //上传中...回调函数，参数[文件总数，当前第几个，当前文件已上传大小KB]
                    if(args.uploading!==undefined)
                        args.uploading(args.files.length,index+1,(start+args.blockSize)/1024,args.files[index].size/1024);

                    if(start+args.blockSize>=args.files[index].size)//跳到下一个文件
                        dfs_ajax(index+1,0);//递归
                    else
                        dfs_ajax(index,start+args.blockSize);//递归
                }

            },
            error:function(xhr,status,err){
                if(args.error!==undefined)
                    args.error(xhr,status,err);
            }
        });
    }

    //上传开始前回调函数，参数[文件数量，合计大小KB]
    if(args.before!==undefined){
        var total_size=0;
        for(var file_temp of args.files)total_size+=file_temp.size;
        args.before(args.files.length,total_size);
    }

    dfs_ajax(); //递归按顺序开始执行ajax
}
