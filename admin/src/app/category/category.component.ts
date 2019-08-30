import { Component, OnInit } from '@angular/core';
import { HttpService } from '../shared/shared.module';
import { NzMessageService,NzModalService,NzNotificationService } from 'ng-zorro-antd';
import { Category } from '../model/category';
import {environment } from '../../environments/environment';
import { UploadFile } from 'ng-zorro-antd';

@Component({
	selector: 'app-category',
	templateUrl: './category.component.html',
	styleUrls: ['./category.component.less']
})
export class CategoryComponent implements OnInit {
	constructor(
        private http: HttpService,
        private message: NzMessageService,
        private notice: NzNotificationService,
        private NzModal: NzModalService,
    ) {
    }


    page = 1;
    num = 10;
    total_page:number;
    isLoading = false;
    categorys = [];
    searchData = new Category;

    ngOnInit() {

        let page = Number(localStorage.getItem('category_page'));
        if( page ){
            this.page = Number(page);
        }else{
            this.page = 1;
            localStorage.setItem('category_page',String(1));
        }

        this.initData();
    }

    // 初始化基础数据
    initData() {
        this.isLoading = true;

        let data = { page:this.page,num:this.num,sortkey:this.sort,orderkey:this.order };
        data = Object.assign(data,this.searchData);


        //网络请求
        this.http.get( '/category',data )
            .then( (res:any ) => {
                if( res.code == 0 ){
                    this.categorys = res.data.categorys;
                    this.total_page = Number(res.data.total_page);
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

    searchDataChange(){
        this.page = 1;
    }

    deletedData(id : string) {
        
        this.isLoading = true;

        //网络请求
        this.http.post( '/category/delete/'+id,{} )
            .then( (res:any ) => {
                this.notice.create('success',res.msg,'');
                this.initData();
            }).catch((msg : string) => {
                this.notice.create('error',msg,'');
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
        localStorage.setItem('category_page',String(this.page));

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


    sort:string;
    order:string;
    sortChange(sort: { key: string; value: string }): void {
        this.sort = sort.key;
        this.order = sort.value == 'descend'?'desc':'asc';
        this.initData();
    }



    //checkbox-------------------------------------------------------------------------
    action:any;
    allChecked = false;
    indeterminate = false;
    
    handleItem(check:boolean,id:string){
        let checked_num = 0;
        for (var i = this.categorys.length - 1; i >= 0; i--) {
            if( this.categorys[i].id == id ){
                this.categorys[i].checked = check;
            }
            if( this.categorys[i].checked ){
                checked_num++;
            }
        }

        if( checked_num == this.categorys.length ){
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
        for (var i = this.categorys.length - 1; i >= 0; i--) {
            this.categorys[i].checked = check;
        }
    }

    disposeOption = [
        {"value":"deleted_true","title":"删除"},
        {"value":"publish_true","title":"已发布"},
        {"value":"publish_false","title":"未发布"},
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
        for (var i = this.categorys.length - 1; i >= 0; i--) {
            if( this.categorys[i].checked ){
                selectItem.push(this.categorys[i].id);
            }
        }
        if( selectItem.length < 1 ){
            return;
        }
        console.log(selectItem);

        this.isLoading = true;

        //网络请求
        this.http.post( '/category/dispose/',{ids:selectItem,action:this.action} )
            .then( (res:any ) => {
                this.notice.create('操作成功',res.msg,'');
                this.allChecked = false;
                this.initData();
            }).catch((msg : string) => {
                this.notice.create('error',msg,'');
            })
            .finally( () => {
                this.isLoading = false;
            })
    }
    //checkbox-------------------------------------------------------------------------
}
