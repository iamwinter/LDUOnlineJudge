#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include<time.h>
#include<mysql/mysql.h>
#include<unistd.h>
#include<sys/types.h>
#include<sys/wait.h>
#include<signal.h>


#define OJ_WT  0    //waiting
#define OJ_QI  1    //queueing
#define OJ_CI  2    //compiling
#define OJ_RI  3    //running
#define OJ_AC  4    //accepted
#define OJ_PE  5    //presentation error
#define OJ_WA  6    //wrong answer
#define OJ_TL  7    //time limit exceeded
#define OJ_ML  8    //time limit exceeded
#define OJ_OL  9    //output limit exceeded
#define OJ_RE 10    //runtime error
#define OJ_CE 11    //compile error
#define OJ_TC 12    //test completed
#define OJ_SK 13    //skipped


char *db_host;
char *db_port;
char *db_user;
char *db_pass;
char *db_name;
char *JG_DATA_DIR;   //测试数据所在目录
int  max_running;    //最大同时判题数
char *JG_NAME;

MYSQL *mysql;    //数据库连接对象
MYSQL_RES *mysql_res;   //sql查询结果
MYSQL_ROW mysql_row;    //sql查询到的单行数据
char sql[256];   //暂存sql语句


void get_wating_solution(int solution_queue[],int &queueing_cnt) //从solutions表读取max_running个待判编号
{
    queueing_cnt=0;
    sprintf(sql,"SELECT id FROM solutions WHERE result=%d ORDER BY id ASC limit %d",OJ_WT,max_running);
    if(mysql_real_query(mysql,sql,strlen(sql))!=0){
        printf("sql failed:\n%s\n",sql);
        exit(1);
    }
    mysql_res=mysql_store_result(mysql);    //保存查询结果
    char *sid_str=new char[max_running*11];
    sid_str[0]='\0';
    while((mysql_row=mysql_fetch_row(mysql_res)))  //将结果读入判题队列
    {
        solution_queue[queueing_cnt++]=atoi(mysql_row[0]);
        if(sid_str[0]!='\0')strcat(sid_str,",");
        strcat(sid_str,mysql_row[0]);
    }
    mysql_free_result(mysql_res); //必须释放结果集，因为它是malloc申请在堆里的内存
    if(queueing_cnt>0)  //更新已读入的solution的result=queueing
    {
        printf("Judger named [%s] is gonna judge following %d sid: (%s)\n",JG_NAME,queueing_cnt,sid_str);
        sprintf(sql,"UPDATE solutions SET result=%d,judger='%s' WHERE id in (%s)",OJ_QI,JG_NAME,sid_str); //更新状态
        mysql_real_query(mysql,sql,strlen(sql));
    }
    delete sid_str;
}

void polling()  //轮询数据库收集待判提交
{
    int running_cnt=0,queueing_cnt;     //正在判题数,排队数
    int *solution_queue=new int[max_running];  //判题队列
    int pid,did;
    char sid_str[12];
    sprintf(sql,"UPDATE solutions SET result=0 where result<=%d and judger='%s'",OJ_RI,JG_NAME); //将上次停机未判完的记录重判
    mysql_real_query(mysql,sql,strlen(sql));
    while(true)
    {
        get_wating_solution(solution_queue,queueing_cnt);  //获取判题队列
        if(queueing_cnt==0)
        {
            while( (did=waitpid(-1,NULL,WNOHANG))>0 ) //回收僵尸进程,WNOHANG不等待,若无死进程立马返回0; 其实不回收也可以，判题前会回收一次
            {
                running_cnt--;
                printf("Recycled a process: %d\n",did);
            }
//            printf("Solution queue is empty, process is sleeping for 1 second... [ time : %d ]\n",(int)clock());
            sleep(1); //当前无题可判，休息1秒
            continue;
        }

        for(int i=0;i<queueing_cnt;i++)     //遍历队列
        {
            if(running_cnt>=max_running)   //已达到最大正在判题数,等待任意判题进程结束,亦可回收僵尸进程
            {
                waitpid(-1,NULL,0);
                running_cnt--;
            }

            sprintf(sid_str,"%d",solution_queue[i]);
            running_cnt++;
            if( (pid=fork()) == 0 )  //当前为子进程，进行一次判题
            {
                if( 0 > execl("./judge","",db_host,db_port,db_user,db_pass,db_name,sid_str,JG_DATA_DIR,(char*)NULL) )
                    perror("Polling execl error:");
                exit(0);  //结束子进程
            }
            else if(pid<0) //创建子进程出错
            {
                printf("Error: fork error!\n");
                exit(1);
            }
        }
    }
    while((pid=waitpid(-1,NULL,0))>0) //回收所有子进程，下班了
        printf("Process %d was recycled\n",pid);
}

int main (int argc, char* argv[])
{
    if(argc!=8+1){
        printf("Polling Error: argv error!\n%d\n",argc);
        exit(1);
    }
    db_host=argv[1];
    db_port=argv[2];
    db_user=argv[3];
    db_pass=argv[4];
    db_name=argv[5];
    max_running=atoi(argv[6]);
    JG_DATA_DIR=argv[7];
    JG_NAME=argv[8];

    mysql = mysql_init(NULL);   //初始化数据库连接变量
    mysql_options(mysql,MYSQL_SET_CHARSET_NAME,"utf8mb4");//判题机名称可能有中文，故设置utf8mb4
    mysql = mysql_real_connect(mysql,db_host,db_user,db_pass,db_name,atoi(db_port),NULL,0);
    if(!mysql){
        printf("Polling Error: Can't connect to database!\n");
        exit(3);
    }

    polling();
    mysql_close(mysql);
    printf("[Polling]: Good Bye!\n");
    return 0;
}
