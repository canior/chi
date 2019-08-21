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

    categorys = [];
    page = 1;
    num = 10;
    total_page:number;
    isLoading = false;

    category = [];

    isVisible = false;
    selectCategory = new Category;

    isVisibleWeibo = false;
    selectWeibo = [];


    //
    config: any = {
        height: 250,
        theme: 'modern',
        // powerpaste advcode toc tinymcespellchecker a11ychecker mediaembed linkchecker help
        plugins: 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image imagetools link media template codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists textcolor wordcount contextmenu colorpicker textpattern',
        // toolbar: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat',
        image_advtab: true,
        imagetools_toolbar: 'rotateleft rotateright | flipv fliph | editimage imageoptions',
        // templates: [
        //   { title: 'Test template 1', content: 'Test 1' },
        //   { title: 'Test template 2', content: 'Test 2' }
        // ],
        // content_css: [
        //   '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
        //   '//www.tinymce.com/css/codepen.min.css'
        // ]
    };


    // 文件上传
    file_upload_url = environment.api+'/common/upload';
    showUploadList = {
        showPreviewIcon: true,
        showRemoveIcon: true,
        hidePreviewIconInNonImage: true
    };
    fileList = [];
    previewImage: string | undefined = '';
    previewVisible = false;

    handlePreview = (file: UploadFile) => {
        this.previewImage = file.url || file.thumbUrl;
        this.previewVisible = true;
    };

    // 监听文件选择器
    handleChange(info:any): void {
        // console.log(info.type);
        if( info.file.response != undefined && info.file.response.data ){
            this.selectCategory.image = info.file.response.data;
        }else if( info.type != undefined && info.type == "removed" ){
            this.selectCategory.image = '';
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }


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

        //网络请求
        // this.http.get( '/category',{ page:this.page,num:this.num } )
        //     .then( (res:any ) => {
        //         if( res.code == 0 ){
        //             this.categorys = res.data;
        //             this.total_page = Number(res.total_page);
        //         }else{
        //             this.message.error(res.msg);
        //         }
        //     }).catch((msg : string) => {
        //         this.message.error(msg);
        //     })
        //     .finally( () => {
        //         this.isLoading = false;
        //     })


        this.isLoading = false;

        let url = '/course/createData/0';

        //网络请求
        this.http.get( url,{} )
            .then( (res:any ) => {
                this.category = res.data.category;
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


    //---------------- 编辑


    // 提交表单
    doSubmit(){

        this.isLoading = true;
        let url = '/category';
        if( this.selectCategory.id ){
            url = '/category/update/'+this.selectCategory.id;
        }

        this.http.post(url, this.selectCategory )
            .then( (res:any ) => {
                if (res.code == 0) {
                    this.message.create('success',res.msg);
                    this.isVisible = false;
                    this.initData();
                } else {
                    this.message.create('error',res.msg);
                }
            }).catch((res : any) => {
                this.message.create('error',res);
            })
            .finally( () => {
                this.isLoading = false;
            })
    }

    newCategory(){
        this.isVisible = true;
    }

    showModal(): void {
        this.isVisible = true;
    }

    handleOk(): void {
        this.doSubmit();
        
    }

    handleCancel(): void {
        this.isVisible = false;
        this.selectCategory = new Category;
        this.fileList = [];
    }


    showWeibo(item: any): void {
        this.isVisibleWeibo = true;


        this.isLoading = true;

        let url = '/category/'+item.id;
        this.http.get(url, {} )
            .then( (res:any ) => {
                if (res.code == 0) {
                    this.selectWeibo = res.data.weibo;
                } else {
                    this.message.create('error',res.msg);
                }
            }).catch((res : any) => {
                this.message.create('error',res);
            })
            .finally( () => {
                this.isLoading = false;
            })
    }



    closeWeibo(): void {
        this.isVisibleWeibo = false;
        this.selectWeibo = [];
    }

    editCategory(item: any): void {
        let category = new Category();
        category.id = item.id;
        category.username = item.username;
        category.password = item.password;
        category.mobile = item.mobile;
        category.email = item.email;
        category.nickname = item.nickname;
        category.weibo_url = item.weibo_url;
        category.image = item.image;
        category.profile = item.profile;

        if( item.image ){
            let imgitem =  {
                uid: 0,
                name: item.image,
                status: 'done',
                url: item.image_url
            };
            this.fileList = [imgitem];
        }

        this.selectCategory = category;
        this.showModal();
    }
}
