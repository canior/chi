import { Component, OnInit } from '@angular/core';
import { HttpService } from '../shared/shared.module';
import { NzMessageService,NzModalService,NzNotificationService } from 'ng-zorro-antd';
import { Album } from '../model/album';
import {environment } from '../../environments/environment';
import { UploadFile } from 'ng-zorro-antd';

@Component({
	selector: 'app-album',
	templateUrl: './album.component.html',
	styleUrls: ['./album.component.less']
})
export class AlbumComponent implements OnInit {
	constructor(
        private http: HttpService,
        private message: NzMessageService,
        private notice: NzNotificationService,
        private NzModal: NzModalService,
    ) {
    }

    albums = [];
    page = 1;
    num = 10;
    total_page:number;
    isLoading = false;

    isVisible = false;
    selectAlbum = new Album;

    isVisibleWeibo = false;
    selectWeibo = [];


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
            this.selectAlbum.image = info.file.response.data;
        }else if( info.type != undefined && info.type == "removed" ){
            this.selectAlbum.image = '';
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }


    ngOnInit() {

        let page = Number(localStorage.getItem('album_page'));
        if( page ){
            this.page = Number(page);
        }else{
            this.page = 1;
            localStorage.setItem('album_page',String(1));
        }

        this.initData();
    }

    // 初始化基础数据
    initData() {
        this.isLoading = true;

        //网络请求
        this.http.get( '/album',{ page:this.page,num:this.num } )
            .then( (res:any ) => {
                if( res.code == 0 ){
                    this.albums = res.data;
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
        this.http.post( '/album/delete/'+id,{} )
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
        localStorage.setItem('album_page',String(this.page));

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
        let url = '/album';
        if( this.selectAlbum.id ){
            url = '/album/update/'+this.selectAlbum.id;
        }

        this.http.post(url, this.selectAlbum )
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

    newAlbum(){
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
        this.selectAlbum = new Album;
        this.fileList = [];
    }


    showWeibo(item: any): void {
        this.isVisibleWeibo = true;


        this.isLoading = true;

        let url = '/album/'+item.id;
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

    editAlbum(item: any): void {
        let album = new Album();
        album.id = item.id;
        album.username = item.username;
        album.password = item.password;
        album.mobile = item.mobile;
        album.email = item.email;
        album.nickname = item.nickname;
        album.weibo_url = item.weibo_url;
        album.image = item.image;
        album.profile = item.profile;

        if( item.image ){
            let imgitem =  {
                uid: 0,
                name: item.image,
                status: 'done',
                url: item.image_url
            };
            this.fileList = [imgitem];
        }

        this.selectAlbum = album;
        this.showModal();
    }
}
