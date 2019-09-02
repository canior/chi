import { Component, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from "@angular/forms";
import { Router, ActivatedRoute } from '@angular/router';
import { NzMessageService,UploadFile,NzModalService } from 'ng-zorro-antd';
import { HttpService } from '../shared/shared.module';
import { Activity } from '../model/activity';
import { File } from '../model/file';
import {environment } from '../../environments/environment';

@Component({
	selector: 'app-activity-create',
	templateUrl: './activity-create.component.html',
	styleUrls: ['./activity-create.component.less']
})
export class ActivityCreateComponent implements OnInit {

	constructor(
		private activeRoute: ActivatedRoute,
		private http: HttpService,
		private router: Router,
        private NzModal: NzModalService,
        private message: NzMessageService
	) { }


	id:string;
	isLoading = true;
	selectActivity = new Activity;
	categorys = [];
    teachers = [];
    securitys = [];
    relevancys = [];

	ngOnInit() {
        this.activeRoute.queryParams.subscribe(params => {
            if(params.id && params.id != '' ) {
                this.id = params.id;
            }
            this.initData();
        });
    }
    

    getFileListData(data:File[]){
        let list = [];
        for (var i = data.length - 1; i >= 0; i--) {
            var item =  {
                uid: data[i].id,
                name: data[i].url,
                status: 'done',
                url: data[i].url,
            };
            list.push(item);
        }
        return list;
    }
    


    // 初始化基础数据
    initData() {

    	this.isLoading = false;

    	let url = '/activity/create';

        //网络请求
        this.http.get( url,{id:this.id} )
            .then( (res:any ) => {
                this.categorys = res.data.category;
                this.teachers = res.data.teacher;

                if( this.id ){
                    this.selectActivity = res.data.activity;
                    if( this.selectActivity.remark_image ){
                        this.remarkFile = this.getFileListData(this.selectActivity.remark_image);
                    }

                    if( this.selectActivity.share_image ){
                        this.shareFile = this.getFileListData(this.selectActivity.share_image);
                    }

                    if( this.selectActivity.content_image ){
                        this.contentFile = this.getFileListData(this.selectActivity.content_image);
                    }
                }

            }).catch((msg : string) => {
                this.message.error(msg);
            })
            .finally( () => {
                this.isLoading = false;
            })
    }

    doSubmit(){

        this.isLoading = true;

        let url = '/activity/new';
        if( this.id ){
            url = '/activity/update';
        }

        this.http.post(url, this.selectActivity )
            .then( (res:any ) => {
                if (res.code == 0) {
                    this.message.create('success',res.msg);
                    this.router.navigate(['backend/activity']);
                } else {
                    this.message.create('error',res.msg);
                }
            }).catch((msg : string) => {
                this.message.create('error',msg);
            })
            .finally( () => {
                this.isLoading = false;
            })
    }



    // 文件上传
    file_upload_url = environment.api+'/file/ngupload';
    showUploadList = {
        showPreviewIcon: true,
        showRemoveIcon: true,
        hidePreviewIconInNonImage: true
    };
    previewImage: string | undefined = '';
    previewVisible = false;

    // 文件上传
    remarkFile = [];
    // 监听文件选择器
    remarkFileHandle(info:any): void {
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectActivity.remark_image = info.file.response.fileId;
        }else if( info.type != undefined && info.type == "removed" ){
            this.selectActivity.remark_image = '';
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }


    // 文件上传
    shareFile = [];
    // 监听文件选择器
    shareFileHandle(info:any): void {
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectActivity.share_image = info.file.response.fileId;
        }else if( info.type != undefined && info.type == "removed" ){
            this.selectActivity.share_image = '';
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }

    // 文件上传
    contentFile = [];
    // 监听文件选择器
    contentFileHandle(info:any): void {
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectActivity.content_image = info.file.response.fileId;
        }else if( info.type != undefined && info.type == "removed" ){
            this.selectActivity.content_image = '';
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }


    //
    config: any = {
        height: 250,
        theme: 'modern',
        plugins: 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image imagetools link media template codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists textcolor wordcount contextmenu colorpicker textpattern',
        image_advtab: true,
        imagetools_toolbar: 'rotateleft rotateright | flipv fliph | editimage imageoptions',
    };
}
