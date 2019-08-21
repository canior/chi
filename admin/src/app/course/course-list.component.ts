import { Component, OnInit } from '@angular/core';
import { HttpService } from '../shared/shared.module';
import { NzMessageService,NzModalService,NzNotificationService } from 'ng-zorro-antd';
import { Course } from '../model/course';

@Component({
    selector: 'app-course-list',
    templateUrl: './course-list.component.html',
    styleUrls: ['./course-list.component.less'],
    providers: [HttpService]
})
export class CourseListComponent implements OnInit {

    constructor(
        private http: HttpService,
        private message: NzMessageService,
        private notification: NzNotificationService,
        private NzModal: NzModalService,
    ) {
    }

    courses = [];
    page = 1;
    num = 10;
    total_page:number;
    isLoading = false;

    course = new Course;

    ngOnInit() {

        let page = Number(localStorage.getItem('course_page'));
        if( page ){
            this.page = Number(page);
        }else{
            this.page = 1;
            localStorage.setItem('course_page',String(1));
        }

        this.initData();
    }

    sort:string;
    order:string;
    sortChange(sort: { key: string; value: string }): void {
        this.sort = sort.key;
        this.order = sort.value == 'descend'?'desc':'asc';
        this.initData();
    }


    // 初始化基础数据
    initData() {
        this.isLoading = true;

        //网络请求
        this.http.get( '/course',{ ng:'ng',page:this.page,num:this.num,sort:this.sort,order:this.order } )
            .then( (res:any ) => {
                if( res.code == 0 ){
                    this.courses = res.data;
                    this.total_page = Number(res.total_page);
                }else{
                    this.message.error(res.msg);
                }
            }).catch((msg : string) => {
                this.message.error(msg);
            })
            .finally( () => {
                this.isLoading = false;
            })
    }

    deletedData(id : string) {
        
        this.isLoading = true;

        //网络请求
        this.http.post( '/course/delete/'+id,{} )
            .then( (res:any ) => {
                this.notification.create('success',res.msg,'');
                this.initData();
            }).catch((msg : string) => {
                this.notification.create('error',msg,'');
            })
            .finally( () => {
                this.isLoading = false;
            })
    }

    // 删除数据确认
    delListItem(id : string) {
        this.NzModal.confirm({
            nzTitle: '确定要删除吗?',
            nzOkText: '确认',
            nzOkType: 'danger',
            nzOnOk: () => this.deletedData(id),
            nzCancelText: '取消'
        });
    }


    // 翻页
    nzPageIndexChange(number:number){
        this.page = number;
        localStorage.setItem('course_page',String(this.page));

        // 因为页码改变会先执行本事件
        // 所以延迟10毫秒，如果页码改变就不执行数据获取
        let num = this.num;
        let that = this;
        setTimeout(function(){
            if( that.num == num ){
                that.initData();
            }
        },10);
    }

    // 每页条数
    nzPageSizeChange(number:number){
        this.num = number;
        this.initData();
    }
}
