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
    searchData = new Course;
    categorys = [];
    teachers = [];

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

        this.allChecked = false;
        this.indeterminate = false;

        this.isLoading = true;

        let data = { page:this.page,num:this.num,sortkey:this.sort,orderkey:this.order};
        data = Object.assign(data,this.searchData);

        //网络请求
        this.http.get( '/course', data )
            .then( (res:any ) => {
                if( res.code == 0 ){
                    this.courses = res.data.courses;
                    this.total_page = Number(res.data.total_page);

                    this.categorys = res.data.category;
                    this.teachers = res.data.teacher;

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


    initSearch(){
        this.searchData = new Course;
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


    //checkbox-------------------------------------------------------------------------
    action:any;
    allChecked = false;
    indeterminate = false;
    
    handleItem(check:boolean,id:string){
        let checked_num = 0;
        for (var i = this.courses.length - 1; i >= 0; i--) {
            if( this.courses[i].id == id ){
                this.courses[i].checked = check;
            }
            if( this.courses[i].checked ){
                checked_num++;
            }
        }

        if( checked_num == this.courses.length ){
            this.allChecked = true;
            this.indeterminate = false;
        }else if( checked_num == 0 ){
            this.allChecked = false;
            this.indeterminate = false;
        }else{
            this.indeterminate = true;
            this.allChecked = false;
        }
    }

    handleAllItem(check:boolean){
        this.indeterminate = false;
        for (var i = this.courses.length - 1; i >= 0; i--) {
            this.courses[i].checked = check;
        }
    }

    disposeOption = [
        {"value":"deleted_true","title":"删除"},
        {"value":"publish_true","title":"已发布"},
        {"value":"publish_false","title":"未发布"},
        {"value":"free_true","title":"上免费专区"},
        {"value":"free_false","title":"下免费专区"},
        {"value":"recommend_true","title":"上推荐专区"},
        {"value":"recommend_false","title":"下推荐专区"},
        {"value":"new_true","title":"上最新专区"},
        {"value":"new_false","title":"下最新专区"},
    ]
    disposeConfirm(){
        let optionTitle = '';
        for (var i = this.disposeOption.length - 1; i >= 0; i--) {
            if( this.disposeOption[i].value ==  this.action ){
                optionTitle = this.disposeOption[i].title;
            }
        }

        this.NzModal.confirm({
            nzTitle: '确定要['+optionTitle+']选中的数据吗?',
            nzOkText: '确认',
            nzOkType: 'danger',
            nzOnOk: () => this.dispose(),
            nzCancelText: '取消'
        });

    }

    dispose(){
        // ids
        var selectItem = [];
        for (var i = this.courses.length - 1; i >= 0; i--) {
            if( this.courses[i].checked ){
                selectItem.push(this.courses[i].id);
            }
        }
        if( selectItem.length < 1 ){
            return;
        }
        console.log(selectItem);

        this.isLoading = true;

        //网络请求
        this.http.post( '/course/dispose/',{ids:selectItem,action:this.action} )
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
    //checkbox-------------------------------------------------------------------------
}
